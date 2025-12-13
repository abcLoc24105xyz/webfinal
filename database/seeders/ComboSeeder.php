<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComboSeeder extends Seeder {
    public function run(): void {
        DB::table('combos')->insert([
            [
                'combo_id' => 1,
                'combo_name' => 'Combo Solo',
                'description' => '1 ly nước ngọt (700ml) + 1 bắp rang cơm nhỏ',
                'price' => 80000,
                'image' => 'cb_solo.png',
                'status' => 1,
            ],
            [
                'combo_id' => 2,
                'combo_name' => 'Combo Couple',
                'description' => '2 ly nước ngọt + 1 bắp rang cơm vừa',
                'price' => 80000,
                'image' => 'cb_couple.png',
                'status' => 1,
            ],
            [
                'combo_id' => 3,
                'combo_name' => 'Combo Family',
                'description' => '3 ly nước + 1 bắp lớn + khoai tây chiên',
                'price' => 160000,
                'image' => 'cb_family.png',
                'status' => 1,
            ],
            [
                'combo_id' => 4,
                'combo_name' => 'Combo Premium',
                'description' => '2 nước ngọt + bắp caramel + xúc xích phô mai',
                'price' => 145000,
                'image' => 'cb_premium.png',
                'status' => 1,
            ],
            [
                'combo_id' => 5,
                'combo_name' => 'Combo Student',
                'description' => '1 bắp thường + 1 nước ngọt (ưu đãi SV)',
                'price' => 50000,
                'image' => 'cb_student.png',
                'status' => 1,
            ],
            [
                'combo_id' => 6,
                'combo_name' => 'Combo Classic',
                'description' => '1 bắp tự chọn + 1 nước',
                'price' => 60000,
                'image' => 'cb_student.png',
                'status' => 1,
            ],
            [
                'combo_id' => 7,
                'combo_name' => 'Combo VIP',
                'description' => '2 nước ép + bắp caramel + sushi mini',
                'price' => 200000,
                'image' => 'cb_VIP.png',
                'status' => 1,
            ],
        ]);
    }
}
