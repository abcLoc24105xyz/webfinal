<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show');
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'required|string|max:15',
            'ava'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $user->full_name = $request->full_name;
        $user->phone = $request->phone;

        if ($request->hasFile('ava')) {
            if ($user->ava) {
                Storage::disk('public')->delete($user->ava);
            }
            $path = $request->file('ava')->store('avatars', 'public');
            $user->ava = $path;
        }

        $user->save();

        return redirect()->route('profile.show')
                         ->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * ✅ CHỈNH SỬA: Chỉ hiển thị các đơn PAID (thanh toán thành công)
     * Và chỉ hiển thị những đơn CÓ ticket_code
     */
    public function history()
    {
        $bookings = Auth::user()->reservations()
            ->where('status', 'paid')           // ← CHỈ LẤY ĐƠN PAID
            ->whereNotNull('ticket_code')       // ← CHỈ LẤY CÓ TICKET_CODE
            ->with([
                'show.movie',
                'show.cinema',
                'show.room',
                'seats'
            ])
            ->latest('created_at')
            ->paginate(10);

        return view('profile.history', compact('bookings'));
    }

    /**
     * ✅ CHỈNH SỬA: Chỉ hiển thị khi có ticket_code
     */
    public function ticketDetail($booking_code)
    {
        $booking = Auth::user()->reservations()
            ->where('booking_code', $booking_code)
            ->where('status', 'paid')              // ← CHỈ LẤY PAID
            ->whereNotNull('ticket_code')          // ← PHẢI CÓ TICKET_CODE
            ->with([
                'show.movie',
                'show.cinema',
                'show.room',
                'seats',
                'combos'
            ])
            ->firstOrFail();

        return view('profile.ticket-detail', compact('booking'));
    }
}