<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seats', function (Blueprint $table) {
            $table->integer('seat_id')->primary();
            $table->char('room_code', 6);
            $table->char('seat_num', 4)->comment('ví dụ A1, E10');
            $table->tinyInteger('seat_type')->comment('1=Regular,2=VIP,3=Couple');
            $table->integer('default_price')->unsigned()->comment('VNĐ');
            
            $table->unique(['room_code', 'seat_num'], 'uq_seat_room');
            $table->foreign('room_code')->references('room_code')->on('rooms')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('seats');
    }
};