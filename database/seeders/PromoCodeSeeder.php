<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromoCodeSeeder extends Seeder {
    public function run(): void {
        DB::table('promocode')->insert([
            [
                'promo_code' => 'HOLIDAY30',
                'description' => 'Giảm 30% cuối tuần',
                'discount_type' => 1,
                'discount_value' => 30,
                'min_order_value' => 0,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(1)->toDateString(),
                'usage_limit' => 200,
                'used_count' => 0,
                'status' => 1,
            ],
            [
                'promo_code' => 'NEWYEAR2026',
                'description' => 'Giảm 20% Tết 2026',
                'discount_type' => 1,
                'discount_value' => 20,
                'min_order_value' => 0,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(1)->toDateString(),
                'usage_limit' => 100,
                'used_count' => 0,
                'status' => 1,
            ],
            [
                'promo_code' => 'XMAS2025',
                'description' => 'Giảm giá Giáng Sinh',
                'discount_type' => 2,
                'discount_value' => 20000,
                'min_order_value' => 0,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(1)->toDateString(),
                'usage_limit' => 500,
                'used_count' => 0,
                'status' => 1,
            ],
        ]);
    }
}