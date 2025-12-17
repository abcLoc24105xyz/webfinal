<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;  // ← BẮT BUỘC PHẢI CÓ DÒNG NÀY!
>>>>>>> 3a03ec3 (final)
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
<<<<<<< HEAD
    use HasFactory, Notifiable;
=======
    use HasFactory, Notifiable; // ← PHẢI CÓ HasFactory!!!
>>>>>>> 3a03ec3 (final)

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = true;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'status',
        'otp_code',
        'otp_expiry',
        'password_changed_at',
        'ava',
        'provider', 'provider_id', 'provider_avatar',
<<<<<<< HEAD
        'email_verified_at',  // Thêm để tránh lỗi khi update
=======
>>>>>>> 3a03ec3 (final)
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'provider_id',
    ];

    protected $casts = [
        'otp_expiry'          => 'datetime',
        'email_verified_at'   => 'datetime',
        'password_changed_at' => 'datetime',
    ];

    // Quan hệ
    public function reservations()
    {
        return $this->hasMany(\App\Models\Reservation::class, 'user_id');
    }

    public function seatHolds()
    {
        return $this->hasMany(\App\Models\SeatHold::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'user_id');
    }
}