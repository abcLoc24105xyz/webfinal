<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {

            // Khóa chính tự tăng
            $table->smallIncrements('movie_id');

            // Thông tin phim
            $table->string('title', 255);
            $table->string('slug', 200)->unique();

            // Thể loại
            $table->unsignedTinyInteger('cate_id')->nullable();

            // Đạo diễn
            $table->string('director', 150)->nullable();

            // Thời lượng (phút)
            $table->smallInteger('duration');

            // Mô tả
            $table->text('description')->nullable();

            // Ngày khởi chiếu
            $table->date('release_date')->nullable();

            // Ngày chiếu sớm
            $table->date('early_premiere_date')->nullable();

            // Poster
            $table->string('poster', 255)->nullable();

            // Trailer
            $table->string('trailer', 255)->nullable();

            // Đánh giá (0-100 hoặc x10)
            $table->tinyInteger('rating')->nullable();

            // Giới hạn tuổi
            $table->tinyInteger('age_limit')->default(0);

            // Trạng thái
            $table->tinyInteger('status')->default(1);

            // Thời gian tạo
            $table->timestamp('created_at')->useCurrent();

            // Index
            $table->index('title', 'idx_title');
            $table->index('release_date', 'idx_release');
            $table->index('status', 'idx_status');

            // Foreign key
            $table->foreign('cate_id')
                ->references('cate_id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};