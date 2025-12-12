<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationSeat;
use App\Models\ReservationCombo;
use App\Models\Promocode;
use App\Models\PromoUserUsage;
use App\Models\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class PaymentController extends Controller
{
    // ==================== KIỂM TRA HẠN GIỮ GHẾ ====================
    public function checkSeatLockTime()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
            }

            $tempCode = session('temp_booking_code');
            if (!$tempCode) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy booking'], 400);
            }

            $reservation = Reservation::where('booking_code', $tempCode)
                ->where('status', 'pending')
                ->first();

            if (!$reservation) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 400);
            }

            $expiresAt = Carbon::parse($reservation->expires_at);
            $now = Carbon::now();
            $diffMinutes = $expiresAt->diffInMinutes($now, false);

            if ($diffMinutes <= 0) {
                // Hết hạn - giải phóng ghế
                $this->releaseSeats($tempCode);
                return response()->json([
                    'success' => false,
                    'expired' => true,
                    'message' => 'Thời gian giữ ghế đã hết. Vui lòng chọn ghế lại.'
                ]);
            }

            return response()->json([
                'success' => true,
                'remaining_minutes' => ceil($diffMinutes),
                'expires_at' => $expiresAt->toDateTimeString()
            ]);

        } catch (Exception $e) {
            Log::error('Check lock time error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống'], 500);
        }
    }

    // ==================== GIẢI PHÓNG GHẾ ====================
    private function releaseSeats($bookingCode)
    {
        DB::transaction(function () use ($bookingCode) {
            $reservation = Reservation::where('booking_code', $bookingCode)
                ->where('status', 'pending')
                ->first();

            if ($reservation) {
                $seatCount = ReservationSeat::where('booking_code', $bookingCode)->count();
                
                Show::find($reservation->show_id)
                    ->increment('remaining_seats', $seatCount);

                ReservationSeat::where('booking_code', $bookingCode)->delete();
                ReservationCombo::where('booking_code', $bookingCode)->delete();
                $reservation->delete();
            }
        });
    }

    // ==================== HỦY BOOKING HẾT HẠN ====================
    public function cancelExpiredReservations()
    {
        try {
            // ✅ TÌM TẤT CẢ PENDING RESERVATION ĐÃ QUÁ 15 PHÚT
            $expiredReservations = Reservation::where('status', 'pending')
                ->where('expires_at', '<', now())
                ->get();

            foreach ($expiredReservations as $reservation) {
                DB::transaction(function () use ($reservation) {
                    // ✅ LẤY SỐ GHẾ ĐỂ RESTORE
                    $seatCount = ReservationSeat::where('booking_code', $reservation->booking_code)->count();

                    // ✅ XÓA SEATS & COMBOS
                    ReservationSeat::where('booking_code', $reservation->booking_code)->delete();
                    ReservationCombo::where('booking_code', $reservation->booking_code)->delete();

                    // ✅ CẬP NHẬT TRẠNG THÁI THÀNH 'expired'
                    $reservation->update([
                        'status' => 'expired',
                        'expires_at' => null
                    ]);

                    // ✅ RESTORE GHẾ CHO SHOW
                    if ($seatCount > 0) {
                        Show::find($reservation->show_id)
                            ->increment('remaining_seats', $seatCount);
                    }

                    Log::info("Expired reservation cancelled", [
                        'booking_code' => $reservation->booking_code,
                        'user_id' => $reservation->user_id,
                        'seats_released' => $seatCount
                    ]);
                });
            }

            Log::info("Cancel expired reservations completed", [
                'expired_count' => $expiredReservations->count()
            ]);

            return response()->json([
                'success' => true,
                'cancelled_count' => $expiredReservations->count()
            ]);

        } catch (\Exception $e) {
            Log::error('CancelExpiredReservations error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ==================== CONTINUE PAYMENT ====================
    public function continueMomoPayment()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
            }

            $tempCode = session('temp_booking_code');
            if (!$tempCode) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy booking'], 400);
            }

            $reservation = Reservation::where('booking_code', $tempCode)
                ->where('status', 'pending')
                ->first();

            if (!$reservation) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 400);
            }

            // Kiểm tra thời gian hết hạn
            $expiresAt = Carbon::parse($reservation->expires_at);
            if ($expiresAt->isPast()) {
                $this->releaseSeats($tempCode);
                return response()->json([
                    'success' => false,
                    'expired' => true,
                    'message' => 'Thời gian giữ ghế đã hết. Vui lòng chọn ghế lại.'
                ]);
            }

            // Proceed with payment
            return $this->processMomoPayment($tempCode, $reservation);

        } catch (Exception $e) {
            Log::critical('Continue payment error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống'], 500);
        }
    }

    public function momoPayment()
    {
        return redirect()->route('booking.summary');
    }

    public function createMomoPayment(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập!'], 401);
            }

            $booking    = session('booking');
            $tempCode   = session('temp_booking_code');

            Log::info('Booking Session Data', [
                'booking' => $booking,
                'tempCode' => $tempCode,
                'user_id' => Auth::id()
            ]);

            if (!$booking || !is_array($booking) || empty($booking['seats']) || !$tempCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phiên hết hạn! Vui lòng chọn ghế lại.',
                    'debug' => [
                        'has_booking' => !empty($booking),
                        'has_tempCode' => !empty($tempCode),
                        'has_seats' => !empty($booking['seats'] ?? []),
                    ]
                ], 400);
            }

            // ✅ KIỂM TRA PENDING RESERVATION CÓ HẾT HẠN KHÔNG
            $existing = Reservation::where('booking_code', $tempCode)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                $expiresAt = Carbon::parse($existing->expires_at);
                if ($expiresAt->isPast()) {
                    // ✅ HẾT HẠN - GIẢI PHÓNG GHẾ
                    $this->releaseSeats($tempCode);
                    return response()->json([
                        'success' => false,
                        'expired' => true,
                        'message' => 'Thời gian giữ ghế đã hết. Vui lòng chọn ghế lại.'
                    ], 400);
                }
            }

            $amount = (int)($booking['grand_total'] ?? 0);

            if ($amount <= 0) {
                $this->createZeroPaymentReservation($tempCode, $booking);
                return response()->json([
                    'success'      => true,
                    'zero_payment' => true,
                    'redirect_url' => route('booking.detail', $tempCode)
                ]);
            }

            $existing = Reservation::where('booking_code', $tempCode)
                ->whereIn('status', ['pending', 'paid'])
                ->first();

            if ($existing && $existing->status === 'paid') {
                return response()->json([
                    'success' => true,
                    'payUrl'  => route('booking.detail', $tempCode)
                ]);
            }

            if (!$existing) {
                $this->createPendingReservation($tempCode, $booking);
            }

            return $this->processMomoPayment($tempCode, $existing ?? Reservation::where('booking_code', $tempCode)->first());

        } catch (Exception $e) {
            Log::critical('MoMo Error', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Hệ thống bận!', 'error' => $e->getMessage()], 500);
        }
    }

    private function processMomoPayment($tempCode, $reservation)
    {
        $booking = session('booking');
        $amount = (int)($booking['grand_total'] ?? 0);

        // ✅ TẠO ORDER ID UNIQUE MỖI LẦN (Tránh trùng)
        $orderId     = $tempCode . '_' . uniqid();
        $requestId   = $orderId . '_' . time();
        $orderInfo   = "Thanh toán vé phim - Mã: $tempCode";
        $redirectUrl = route('momo.return');
        $ipnUrl      = route('momo.ipn');

        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey   = env('MOMO_ACCESS_KEY');
        $secretKey   = env('MOMO_SECRET_KEY');

        if (!$partnerCode || !$accessKey || !$secretKey) {
            Log::error('Momo Config Missing');
            return response()->json(['success' => false, 'message' => 'Cấu hình Momo không đúng!'], 500);
        }

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=payWithATM";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $payload = [
            "partnerCode" => $partnerCode,
            "accessKey" => $accessKey,
            "requestId" => $requestId,
            "amount" => $amount,
            "orderId" => $orderId,
            "orderInfo" => $orderInfo,
            "redirectUrl" => $redirectUrl,
            "ipnUrl" => $ipnUrl,
            "lang" => "vi",
            "extraData" => "",
            "requestType" => "payWithATM",
            "signature" => $signature
        ];

        Log::info('Momo Payload', ['payload' => $payload]);

        $response = $this->curlPost('https://test-payment.momo.vn/v2/gateway/api/create', $payload);
        $result   = json_decode($response, true);

        Log::info('Momo Response', ['result' => $result]);

        if (!$result || $result['resultCode'] != 0) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Lỗi MoMo',
                'momo_error' => $result
            ], 400);
        }

        return response()->json(['success' => true, 'payUrl' => $result['payUrl']]);
    }

    // ==================== CALLBACK ====================
    public function showPaymentFailed()
    {
        return view('booking.payment-failed');
    }

    public function momoReturn(Request $request)
    {
        if ($request->resultCode == 0) {
            // ✅ EXTRACT BOOKING CODE TỪ ORDER ID
            $orderId = $request->orderId;
            $bookingCode = explode('_', $orderId)[0] ?? $orderId;
            
            // ✅ KIỂM TRA CÓ EXPIRED KHÔNG
            $reservation = Reservation::where('booking_code', $bookingCode)->first();
            
            if ($reservation && $reservation->status === 'expired') {
                Log::warning('Payment callback for expired reservation', [
                    'booking_code' => $bookingCode,
                    'order_id' => $orderId
                ]);
                return redirect()->route('payment.failed')
                    ->with('error', 'Thời gian giữ ghế đã hết. Vui lòng đặt vé lại.')
                    ->with('booking_code', $bookingCode)
                    ->with('show_id', $reservation->show_id);
            }
            
            $this->confirmPaidReservation($bookingCode);
            return redirect()->route('booking.detail', $bookingCode)
                ->with('success', 'Thanh toán thành công!');
        }

        // ✅ THANH TOÁN THẤT BẠI - HIỂN THỊ TRANG CHI TIẾT
        $orderId = $request->orderId;
        $bookingCode = explode('_', $orderId)[0] ?? $orderId;
        $reservation = Reservation::where('booking_code', $bookingCode)->first();
        $showId = $reservation?->show_id;

        return redirect()->route('payment.failed')
            ->with('error', 'Thanh toán thất bại: ' . ($request->message ?? 'Lỗi không xác định'))
            ->with('booking_code', $bookingCode)
            ->with('show_id', $showId)
            ->with('payment_error_details', [
                'result_code' => $request->resultCode,
                'message' => $request->message ?? 'Không xác định',
                'order_id' => $request->orderId,
                'request_id' => $request->requestId ?? 'N/A'
            ]);
    }

    public function momoIpn(Request $request)
    {
        if ($request->resultCode == 0) {
            // ✅ EXTRACT BOOKING CODE TỪ ORDER ID
            $orderId = $request->orderId;
            $bookingCode = explode('_', $orderId)[0] ?? $orderId;
            
            // ✅ KIỂM TRA CÓ EXPIRED KHÔNG
            $reservation = Reservation::where('booking_code', $bookingCode)->first();
            
            if ($reservation && $reservation->status === 'expired') {
                Log::warning('Payment received for expired reservation', [
                    'booking_code' => $bookingCode,
                    'order_id' => $orderId
                ]);
                return response()->json(['ErrCode' => 0]);
            }
            
            $this->confirmPaidReservation($bookingCode);
        }
        return response()->json(['ErrCode' => 0]);
    }

    // ==================== 0 ĐỒNG – TẠO ĐƠN PAID NGAY ====================
    private function createZeroPaymentReservation($bookingCode, $booking)
    {
        if (Reservation::where('booking_code', $bookingCode)->where('status', 'paid')->exists()) {
            return;
        }

        DB::transaction(function () use ($bookingCode, $booking) {
            $show = Show::find($booking['show_id']);
            $ticketCode = $this->generateTicketCode();

            $reservation = Reservation::create([
                'booking_code'   => $bookingCode,
                'ticket_code'    => $ticketCode,
                'user_id'        => Auth::id(),
                'show_id'        => $booking['show_id'],
                'total_amount'   => 0,
                'status'         => 'paid',
                'payment_method' => 'free',
                'paid_at'        => now(),
            ]);

            foreach ($booking['seats'] ?? [] as $s) {
                ReservationSeat::create([
                    'booking_code' => $bookingCode,
                    'seat_id'      => $s['seat_id'],
                    'seat_price'   => $s['price'] ?? 0
                ]);
            }

            if (!empty($booking['combos'])) {
                foreach ($booking['combos'] as $c) {
                    ReservationCombo::create([
                        'booking_code' => $bookingCode,
                        'combo_id'     => $c['id'],
                        'quantity'     => $c['quantity'],
                        'combo_price'  => $c['price']
                    ]);
                }
            }

            $this->recordPromoUsage($reservation, $bookingCode, $ticketCode);
            $show->decrement('remaining_seats', count($booking['seats']));
            $reservation->load(['show.movie', 'show.cinema', 'show.room']);
            $this->sendConfirmationEmail($reservation, $bookingCode, $ticketCode);
        });

        session()->forget(['booking', 'temp_booking_code', 'applied_promo', 'discount_amount']);
    }

    // ==================== TẠO ĐƠN PENDING ====================
    private function createPendingReservation($bookingCode, $booking)
    {
        DB::transaction(function () use ($bookingCode, $booking) {
            $show = Show::find($booking['show_id']);

            Reservation::create([
                'booking_code'   => $bookingCode,
                'ticket_code'    => null,
                'user_id'        => Auth::id(),
                'show_id'        => $booking['show_id'],
                'total_amount'   => $booking['grand_total'],
                'status'         => 'pending',
                'payment_method' => 'momo_atm',
                'payment_id'     => $bookingCode,
                'expires_at'     => now()->addMinutes(15),
            ]);

            foreach ($booking['seats'] ?? [] as $s) {
                ReservationSeat::create([
                    'booking_code' => $bookingCode,
                    'seat_id'      => $s['seat_id'],
                    'seat_price'   => $s['price'] ?? 0
                ]);
            }

            if (!empty($booking['combos'])) {
                foreach ($booking['combos'] as $c) {
                    ReservationCombo::create([
                        'booking_code' => $bookingCode,
                        'combo_id'     => $c['id'],
                        'quantity'     => $c['quantity'],
                        'combo_price'  => $c['price']
                    ]);
                }
            }

            $show->decrement('remaining_seats', count($booking['seats']));
        });
    }

    // ==================== XÁC NHẬN THANH TOÁN THÀNH CÔNG (MoMo) ====================
    private function confirmPaidReservation($bookingCode)
    {
        $reservation = Reservation::where('booking_code', $bookingCode)
                                  ->where('status', 'pending')
                                  ->first();

        if (!$reservation) return;

        DB::transaction(function () use ($reservation, $bookingCode) {
            $ticketCode = $this->generateTicketCode();

            $reservation->update([
                'status'      => 'paid',
                'paid_at'     => now(),
                'expires_at'  => null,
                'ticket_code' => $ticketCode,
            ]);

            $this->recordPromoUsage($reservation, $bookingCode, $ticketCode);
            $reservation->load(['show.movie', 'show.cinema', 'show.room']);
            $this->sendConfirmationEmail($reservation, $bookingCode, $ticketCode);
        });

        session()->forget(['booking', 'temp_booking_code', 'applied_promo', 'discount_amount']);
    }

    // ==================== TẠO MÃ VÉ (TICKET_CODE) ====================
    private function generateTicketCode(): string
    {
        do {
            $dateString = now()->format('dmy');
            $randomString = strtoupper(bin2hex(random_bytes(3)));
            $ticketCode = 'TKT' . $dateString . $randomString;
        } while (Reservation::where('ticket_code', $ticketCode)->exists());

        return $ticketCode;
    }

    // ==================== LƯU TRACKING MÃ GIẢM GIÁ ====================
    private function recordPromoUsage($reservation, $bookingCode, $ticketCode)
    {
        $promoCode = session('applied_promo');

        if (!$promoCode) {
            return;
        }

        $promo = Promocode::find($promoCode);
        if (!$promo) {
            return;
        }

        try {
            PromoUserUsage::firstOrCreate(
                [
                    'promo_code' => $promoCode,
                    'user_id'    => $reservation->user_id,
                ],
                [
                    'booking_code' => $bookingCode,
                    'ticket_code'  => $ticketCode,
                ]
            );

            $promo->increment('used_count');

            Log::info("Promo usage recorded: {$promoCode} by user {$reservation->user_id}");

        } catch (\Exception $e) {
            Log::warning('Promo usage tracking failed: ' . $e->getMessage());
        }
    }

    // ==================== GỬI EMAIL ====================
    private function sendConfirmationEmail($reservation, $bookingCode, $ticketCode)
    {
        try {
            $user = Auth::user();
            if (!$user?->email) return;

            if (!$ticketCode) {
                Log::warning('Email not sent: ticket_code is null', ['booking_code' => $bookingCode]);
                return;
            }

            $seats = ReservationSeat::where('booking_code', $bookingCode)
                ->join('seats', 'reservation_seats.seat_id', '=', 'seats.seat_id')
                ->pluck('seats.seat_num')->toArray();

            $combos = ReservationCombo::where('booking_code', $bookingCode)
                ->join('combos', 'reservation_combos.combo_id', '=', 'combos.combo_id')
                ->get(['combos.combo_name', 'reservation_combos.quantity', 'reservation_combos.combo_price']);

            $qrCodeUrl = $ticketCode ? $this->generateQRCode($ticketCode) : null;

            Mail::send('emails.booking-confirmation', [
                'user'        => $user,
                'reservation' => $reservation,
                'seats'       => $seats,
                'combos'      => $combos,
                'bookingCode' => $bookingCode,
                'ticketCode'  => $ticketCode,
                'qrCodeUrl'   => $qrCodeUrl,
                'detailLink'  => route('booking.detail', $bookingCode),
                'isFree'      => ($reservation->total_amount == 0)
            ], function ($m) use ($user) {
                $m->to($user->email)->subject('Xác nhận đặt vé thành công - GhienCine');
            });

            Log::info("Confirmation email sent", [
                'email' => $user->email,
                'ticket_code' => $ticketCode,
                'booking_code' => $bookingCode
            ]);

        } catch (Exception $e) {
            Log::error('Email error: ' . $e->getMessage());
        }
    }

    private function generateQRCode($ticketCode)
    {
        if (!$ticketCode) {
            Log::warning('QR code generation attempted with null ticket_code');
            return null;
        }
        
        return "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($ticketCode);
    }

    private function curlPost($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    
}