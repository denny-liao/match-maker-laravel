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
        Schema::create('room_records', function (Blueprint $table) {
            $table->id();
            // 關聯房間與使用者
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->index();

            // 房內角色：host(房主), player(一般成員)
            $table->string('role', 20)->default('player');
            // 成員狀態：unready(未準備), ready(已準備), disconnected(斷線), left(已離開), kicked(被踢除)
            $table->string('status', 20)->default('unready');

            // 參與者自訂編號
            $table->unsignedSmallInteger('participant_number')->nullable();

            // 進出時間（left_at 為 null 代表目前還在房間內）
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();

            // 投票給目標 user ids
            $table->json('voted_user_ids')->nullable();
            
            $table->timestamps();

            // 複合索引：加速查詢「某房間目前在線成員（left_at IS NULL）」
            $table->index(['room_id', 'left_at']);
            // 複合索引：加速查詢「某玩家目前是否已在其他房間內」
            $table->index(['user_id', 'left_at']);

            // 確保同一個房間（room_id）內，投票編號（participant_number）不會重複
            $table->unique(['room_id', 'participant_number']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_records');
    }
};
