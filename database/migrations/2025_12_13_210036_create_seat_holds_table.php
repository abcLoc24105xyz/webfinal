<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seat_holds', function (Blueprint $table) {
            $table->id();
            $table->binary('show_id', 16);
            $table->integer('seat_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('created_at')->nullable();
            
            $table->unique(['show_id', 'seat_id'], 'uq_hold');
            $table->index('expires_at', 'seat_holds_expires_at_index');
            
            $table->foreign('show_id')->references('show_id')->on('shows')->onDelete('cascade')->onUpdate('cascade');  
            $table->foreign('seat_id')->references('seat_id')->on('seats')->onDelete('cascade')->onUpdate('cascade');  
        });
    }

    public function down(): void {
        Schema::dropIfExists('seat_holds');
    }
};