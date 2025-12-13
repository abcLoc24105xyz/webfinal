<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->primary();
            $table->string('full_name', 150)->nullable();
            $table->string('email', 150)->unique('uq_email');
            $table->string('password', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->dateTime('otp_expiry')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('ava', 255)->nullable();
            $table->string('provider', 20)->nullable();
            $table->string('provider_id', 255)->nullable()->unique();
            $table->text('provider_avatar')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};
