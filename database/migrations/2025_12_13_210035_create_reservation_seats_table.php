<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservation_seats', function (Blueprint $table) {
            $table->char('booking_code', 12);
            $table->unsignedInteger('seat_id');
            $table->integer('seat_price')->unsigned();
            
            $table->primary(['booking_code', 'seat_id']);
            $table->foreign('booking_code')->references('booking_code')->on('reservations')->onDelete('cascade')->onUpdate('cascade'); 
            $table->foreign('seat_id')->references('seat_id')->on('seats')->onDelete('cascade')->onUpdate('cascade');  
        });
    }

    public function down(): void {
        Schema::dropIfExists('reservation_seats');
    }
};