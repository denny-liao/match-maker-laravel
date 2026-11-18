<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            // 公開房號（例如 6 碼英數字），方便玩家輸入加入，避免直接暴露自增 ID
            $table->string('code', 16)->unique();
            // 房主 ID， users 表
            $table->unsignedBigInteger('host_id')->index();
            // 房間名稱
            $table->string('title', 100);
            // 房間狀態：waiting(等待中), playing(進行中), closed(已關閉)
            $table->string('status', 20)->default('waiting')->index();
            // 人數上限
            $table->unsignedTinyInteger('max_players')->default(20);
            // 私人房設定與密碼（無密碼則為 null）
            $table->boolean('is_private')->default(false);
            $table->string('password')->nullable();
            // 遊戲/活動實際開始與結束時間
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('closure_reason', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
