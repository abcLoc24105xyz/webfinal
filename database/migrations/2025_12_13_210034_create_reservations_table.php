<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reservations', function (Blueprint $table) {
            $table->char('booking_code', 50)->primary();
            $table->char('ticket_code', 20)->nullable()->unique();
            $table->unsignedInteger('user_id');
            $table->binary('show_id', 16);
            $table->integer('total_amount')->unsigned()->comment('VNĐ');
            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_id', 100)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            
            $table->index('user_id', 'idx_user');
            $table->index('show_id', 'idx_show');
            $table->index('status', 'idx_status');
            $table->index('ticket_code', 'idx_ticket_code');
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('show_id')->references('show_id')->on('shows')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('reservations');
    }
};
