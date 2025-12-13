<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cinemas', function (Blueprint $table) {
            $table->smallInteger('cinema_id')->primary();
            $table->string('cinema_name', 150);
            $table->string('address', 255);
            $table->string('phone', 15)->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=hoạt động, 0=ngừng');
            
            $table->unique(['cinema_name', 'address'], 'uq_name_address');
        });
    }

    public function down(): void {
        Schema::dropIfExists('cinemas');
    }
};
