<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('phone');
        });

        // Tiến độ xem video của từng tài khoản (để "Tiếp tục học" trên mọi thiết bị)
        Schema::create('video_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);   // giây đang xem dở
            $table->unsignedInteger('duration')->default(0);   // tổng số giây
            $table->boolean('completed')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'video_id']);
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_progress');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('avatar_path'));
    }
};
