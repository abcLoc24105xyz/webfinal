<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shows', function (Blueprint $table) {
            $table->binary('show_id', 16)->primary();
            $table->smallInteger('movie_id')->nullable();
            $table->smallInteger('cinema_id');
            $table->char('room_code', 6);
            $table->date('show_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->smallInteger('remaining_seats')->default(0);
            
            $table->unique(['cinema_id', 'room_code', 'show_date', 'start_time'], 'uq_unique_show');
            $table->index(['cinema_id', 'show_date'], 'idx_cinema_date');
            $table->index(['movie_id', 'show_date'], 'idx_movie_date');
            $table->index('show_date', 'idx_date');
            
            $table->foreign('movie_id')->references('movie_id')->on('movies')->onDelete('setNull');
            $table->foreign('cinema_id')->references('cinema_id')->on('cinemas')->onDelete('cascade');
            $table->foreign('room_code')->references('room_code')->on('rooms')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('shows');
    }
};