<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rooms', function (Blueprint $table) {
            $table->char('room_code', 6)->primary();
            $table->smallInteger('cinema_id');
            $table->string('room_name', 100)->nullable();
            $table->tinyInteger('room_type')->comment('1=Normal,2=VIP,3=IMAX');
            $table->smallInteger('total_seats');
            
            $table->unique(['cinema_id', 'room_code'], 'uq_room_cinema');
            $table->foreign('cinema_id')->references('cinema_id')->on('cinemas')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('rooms');
    }
};
