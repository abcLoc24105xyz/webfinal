<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('combos', function (Blueprint $table) {
            $table->tinyInteger('combo_id')->primary();
            $table->string('combo_name', 150)->unique('uq_combo_name');
            $table->string('description', 500)->nullable();
            $table->integer('price')->unsigned()->comment('VNĐ');
            $table->string('image', 255)->nullable();
            $table->tinyInteger('status')->default(1);
        });
    }

    public function down(): void {
        Schema::dropIfExists('combos');
    }
};