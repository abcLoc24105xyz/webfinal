<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\PromoUserUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'show.movie'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->paginate(20);

        return view('admin.payments.index', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'show.movie', 'show.room.cinema', 'seats', 'combos']);
        return view('admin.payments.show', compact('reservation'));
    }

    /**
     * Duyệt thủ công: pending → confirmed + sinh ticket_code nếu chưa có
     */
    public function confirm(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt đơn hàng đang chờ!');
        }

        DB::beginTransaction();
        try {
            $ticketCode = $reservation->ticket_code;
            if (!$ticketCode) {
                do {
                    $ticketCode = 'TKT' . now()->format('dmy') . strtoupper(bin2hex(random_bytes(3)));
                } while (Reservation::where('ticket_code', $ticketCode)->exists());
            }

            $reservation->update([
                'status'      => 'confirmed',
                'ticket_code' => $ticketCode,
                'paid_at'     => now(),
                'expires_at'  => null,
            ]);

            DB::commit();
            return back()->with('success', 'Đã duyệt đơn hàng ' . $reservation->booking_code . ' thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['pending', 'confirmed', 'paid'])) {
            return back()->with('error', 'Không thể hủy đơn hàng ở trạng thái này!');
        }

        DB::beginTransaction();
        try {
            $reservation->update(['status' => 'cancelled']);

            // Xóa tracking mã giảm giá nếu có
            PromoUserUsage::where('booking_code', $reservation->booking_code)->delete();

            DB::commit();
            return back()->with('success', 'Đã hủy đơn hàng ' . $reservation->booking_code . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}