<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promo_user_usage', function (Blueprint $table) {
            $table->id();
            $table->string('promo_code', 20);
            $table->integer('user_id')->unsigned();
            $table->string('booking_code', 50)->nullable();
            $table->string('ticket_code', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['promo_code', 'user_id'], 'uq_promo_user');
            $table->index('booking_code', 'idx_booking_code');
            $table->index('ticket_code', 'idx_ticket_code');
            
            $table->foreign('promo_code')->references('promo_code')->on('promocode')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('booking_code')->references('booking_code')->on('reservations')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('promo_user_usage');
    }
};
