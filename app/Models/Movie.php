<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Movie extends Model
{
    protected $table = 'movies';

    protected $primaryKey = 'movie_id';

    // movie_id là AUTO_INCREMENT
    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    // ==================== STATUS ====================
    const STATUS_COMING_SOON = 1;
    const STATUS_SHOWING = 2;
    const STATUS_ENDED = 3;

    // ==================== AGE LIMIT ====================
    const AGE_LIMIT_P = 0;
    const AGE_LIMIT_T13 = 13;
    const AGE_LIMIT_T16 = 16;
    const AGE_LIMIT_T18 = 18;

    protected $fillable = [
        'title',
        'slug',
        'cate_id',
        'director',
        'duration',
        'description',
        'release_date',
        'poster',
        'trailer',
        'rating',
        'age_limit',
        'status',
    'created_at',
    'early_premiere_date', // Thêm dòng này
    ];

    protected $casts = [
        'release_date' => 'date',
        'early_premiere_date' => 'date',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class, 'cate_id', 'cate_id');
    }

    public function shows()
    {
        return $this->hasMany(Show::class, 'movie_id', 'movie_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scope
    |--------------------------------------------------------------------------
    */

    public function scopeComingSoon($query)
    {
        return $query->where('status', self::STATUS_COMING_SOON);
    }

    public function scopeShowing($query)
    {
        return $query->where('status', self::STATUS_SHOWING);
    }

    public function scopeEnded($query)
    {
        return $query->where('status', self::STATUS_ENDED);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function hasEarlyPremiere()
    {
        return !empty($this->early_premiere_date);
    }

    public function getStartShowingDate()
    {
        return $this->early_premiere_date ?: $this->release_date;
    }

    public function getStatusLabel()
    {
        return match ($this->status) {
            self::STATUS_COMING_SOON => 'Sắp chiếu',
            self::STATUS_SHOWING => 'Đang chiếu',
            self::STATUS_ENDED => 'Kết thúc',
            default => 'Không xác định',
        };
    }

    public function getAgeLimitLabel()
    {
        return match ($this->age_limit) {
            self::AGE_LIMIT_P => 'P',
            self::AGE_LIMIT_T13 => 'T13',
            self::AGE_LIMIT_T16 => 'T16',
            self::AGE_LIMIT_T18 => 'T18',
            default => '-',
        };
    }

    public function getFormattedReleaseDateAttribute()
    {
        return $this->release_date
            ? $this->release_date->format('d/m/Y')
            : '-';
    }

    public function getFormattedEarlyPremiereDateAttribute()
    {
        return $this->early_premiere_date
            ? $this->early_premiere_date->format('d/m/Y')
            : '-';
    }
}