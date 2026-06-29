<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\ReservationCombo;
use App\Models\PromoUserUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $reservation->load(['user', 'show.movie', 'show.room', 'seats', 'combos']);
        return view('admin.payments.show', compact('reservation'));
    }

    /**
     * Duyệt thủ công: pending → paid + sinh ticket_code + gửi mail cho khách
     */
    public function confirm(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể duyệt đơn hàng đang chờ!');
        }

        DB::beginTransaction();
        try {
            // Sinh ticket_code nếu chưa có
            $ticketCode = $reservation->ticket_code;
            if (!$ticketCode) {
                do {
                    $ticketCode = 'TKT' . now()->format('dmy') . strtoupper(bin2hex(random_bytes(3)));
                } while (Reservation::where('ticket_code', $ticketCode)->exists());
            }

            $reservation->update([
                'status'      => 'paid',
                'ticket_code' => $ticketCode,
                'paid_at'     => now(),
                'expires_at'  => null,
            ]);

            DB::commit();

            // Gửi mail sau khi commit (lấy user từ reservation, không phải Auth admin)
            $reservation->load(['user', 'show.movie', 'show.cinema', 'show.room', 'seats', 'combos']);
            $this->sendConfirmationEmail($reservation, $ticketCode);

            return back()->with('success', 'Đã duyệt đơn ' . $reservation->booking_code . ' và gửi email cho khách!');
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
        if (!in_array($reservation->status, ['pending', 'paid'])) {
            return back()->with('error', 'Không thể hủy đơn hàng ở trạng thái này!');
        }

        DB::beginTransaction();
        try {
            $reservation->update(['status' => 'cancelled']);
            PromoUserUsage::where('booking_code', $reservation->booking_code)->delete();

            DB::commit();
            return back()->with('success', 'Đã hủy đơn hàng ' . $reservation->booking_code . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Gửi email xác nhận cho KHÁCH (không phải admin)
     */
    private function sendConfirmationEmail(Reservation $reservation, string $ticketCode): void
    {
        try {
            $user = $reservation->user;

            if (!$user?->email) {
                Log::warning('Admin confirm: no email for user', ['booking_code' => $reservation->booking_code]);
                return;
            }

            $bookingCode = $reservation->booking_code;

            $seats = ReservationSeat::where('booking_code', $bookingCode)
                ->join('seats', 'reservation_seats.seat_id', '=', 'seats.seat_id')
                ->pluck('seats.seat_num')
                ->toArray();

            $combos = ReservationCombo::where('booking_code', $bookingCode)
                ->join('combos', 'reservation_combos.combo_id', '=', 'combos.combo_id')
                ->get(['combos.combo_name', 'reservation_combos.quantity', 'reservation_combos.combo_price']);

            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=' . urlencode($ticketCode);

            Mail::send('emails.booking-confirmation', [
                'user'        => $user,
                'reservation' => $reservation,
                'seats'       => $seats,
                'combos'      => $combos,
                'bookingCode' => $bookingCode,
                'ticketCode'  => $ticketCode,
                'qrCodeUrl'   => $qrCodeUrl,
                'detailLink'  => route('booking.detail', $bookingCode),
                'isFree'      => ($reservation->total_amount == 0),
            ], function ($m) use ($user) {
                $m->to($user->email)->subject('Xác nhận đặt vé thành công - GhienCine');
            });

            Log::info('Admin confirm: email sent', [
                'email'        => $user->email,
                'booking_code' => $bookingCode,
                'ticket_code'  => $ticketCode,
            ]);

        } catch (\Exception $e) {
            // Mail lỗi không rollback DB, chỉ log
            Log::error('Admin confirm: email failed - ' . $e->getMessage(), [
                'booking_code' => $reservation->booking_code,
            ]);
        }
    }
}