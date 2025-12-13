<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->char('room_code', 6)->primary();
            $table->unsignedSmallInteger('cinema_id'); // dùng unsignedSmallInteger để chắc chắn khớp với kiểu unsigned smallint trong cinemas
            $table->string('room_name', 100)->nullable();
            $table->tinyInteger('room_type')->comment('1=Normal,2=VIP,3=IMAX');
            $table->smallInteger('total_seats');
            $table->timestamps();

            // Unique constraint
            $table->unique(['cinema_id', 'room_code'], 'uq_room_cinema');

            // Foreign key với kiểu dữ liệu chắc chắn khớp (unsigned smallint)
            $table->foreign('cinema_id')
                  ->references('cinema_id')
                  ->on('cinemas')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};