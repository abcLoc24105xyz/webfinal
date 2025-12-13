<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('movies', function (Blueprint $table) {
            $table->smallInteger('movie_id')->primary();
            $table->string('title', 255);
            $table->string('slug', 200)->unique();
            $table->tinyInteger('cate_id')->nullable();
            $table->string('director', 150)->nullable();
            $table->smallInteger('duration')->comment('phút');
            $table->text('description')->nullable();
            $table->date('release_date')->nullable();
            $table->date('early_premiere_date')->nullable()->comment('Ngày chiếu sớm/chiếu đặc biệt');
            $table->string('poster', 255)->nullable();
            $table->string('trailer', 255)->nullable();
            $table->tinyInteger('rating')->nullable()->comment('x10, ví dụ 45 = 4.5');
            $table->tinyInteger('age_limit')->default(0)->comment('0=P,13=T13,16=T16,18=T18');
            $table->tinyInteger('status')->default(1)->comment('1=sắp chiếu,2=đang chiếu,3=kết thúc');
            $table->date('created_at')->default(now());
            
            $table->index('title', 'idx_title');
            $table->index('release_date', 'idx_release');
            $table->index('status', 'idx_status');
            $table->foreign('cate_id')->references('cate_id')->on('categories')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('movies');
    }
};
