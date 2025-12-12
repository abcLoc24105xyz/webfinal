<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'booking_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'booking_code',
        'ticket_code',      // ← THÊM DÒNG NÀY
        'user_id',
        'show_id',
        'total_amount',
        'status',
        'payment_method',
        'payment_id',
        'paid_at',
        'expires_at',
        'created_at'
    ];

    protected $dates = ['created_at', 'paid_at', 'expires_at'];

    // Quan hệ
    public function show()
    {
        return $this->belongsTo(Show::class, 'show_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // GHẾ ĐÃ ĐẶT
    public function seats()
    {
        return $this->belongsToMany(
            Seat::class,
            'reservation_seats',
            'booking_code',
            'seat_id'
        )->withPivot('seat_price');
    }

    // COMBO ĐÃ CHỌN
    public function combos()
    {
        return $this->belongsToMany(
            Combo::class,
            'reservation_combos',
            'booking_code',
            'combo_id'
        )->withPivot('quantity', 'combo_price');
    }

    // THANH TOÁN
    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_code', 'booking_code');
    }

    // PROMO USAGE
    public function promoUsage()
    {
        return $this->hasMany(PromoUserUsage::class, 'booking_code', 'booking_code');
    }

    // Trạng thái
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}