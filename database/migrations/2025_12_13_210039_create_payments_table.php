<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')->autoIncrement()->primary();

            // Mã đơn hàng từ cổng thanh toán (MoMo orderId)
            $table->string('order_id', 150)->nullable()->index();

            // Khách hàng
            $table->unsignedInteger('user_id');

            // Liên kết đặt vé
            $table->char('booking_code', 50);

            // Số tiền (VNĐ)
            $table->unsignedInteger('amount')->default(0);

            // Phương thức: momo_atm, momo_wallet, free, manual...
            $table->string('payment_method', 50)->nullable();

            // Trạng thái: pending | completed | cancelled
            $table->string('status', 20)->default('pending');

            // Thời gian thanh toán thành công
            $table->timestamp('paid_at')->nullable();

            // Thời gian tạo
            $table->timestamp('created_at')->useCurrent();

            // Index & FK
            $table->index('booking_code');
            $table->index('status');

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');

            $table->foreign('booking_code')
                ->references('booking_code')->on('reservations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};