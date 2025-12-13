<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservation_combos', function (Blueprint $table) {
            $table->char('booking_code', 12);
            $table->tinyInteger('combo_id');
            $table->tinyInteger('quantity')->default(1);
            $table->integer('combo_price')->unsigned();
            
            $table->primary(['booking_code', 'combo_id']);
            $table->foreign('booking_code')->references('booking_code')->on('reservations')->onDelete('cascade');
            $table->foreign('combo_id')->references('combo_id')->on('combos')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('reservation_combos');
    }
};