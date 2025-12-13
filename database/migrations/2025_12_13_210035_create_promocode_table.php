<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promocode', function (Blueprint $table) {
            $table->string('promo_code', 20)->primary();
            $table->string('description', 255)->nullable();
            $table->tinyInteger('discount_type')->comment('1=percent,2=amount');
            $table->integer('discount_value')->unsigned();
            $table->integer('min_order_value')->unsigned()->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('usage_limit')->unsigned()->nullable();
            $table->integer('used_count')->unsigned()->default(0);
            $table->tinyInteger('status')->default(1);
        });
    }

    public function down(): void {
        Schema::dropIfExists('promocode');
    }
};
