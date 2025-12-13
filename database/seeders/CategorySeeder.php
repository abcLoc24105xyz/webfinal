<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder {
    public function run(): void {
        DB::table('categories')->insert([
            ['cate_id' => 1, 'name' => 'Hành động - Phiêu lưu'],
            ['cate_id' => 2, 'name' => 'Tình cảm - Lãng mạn'],
            ['cate_id' => 3, 'name' => 'Hài hước'],
            ['cate_id' => 4, 'name' => 'Kinh dị - Giật gân'],
            ['cate_id' => 5, 'name' => 'Hoạt hình - Gia đình'],
            ['cate_id' => 6, 'name' => 'Khoa học viễn tưởng - Giả tưởng'],
        ]);
    }
}