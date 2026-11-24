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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reference_id')->index();
            $table->string('trade_no', 20)->unique();
            $table->timestamp('trade_date')->useCurrent();
            // ecpay
            $table->string('channel', 20);
            $table->string('channel_trade_no', 20)->nullable();
            $table->unsignedInteger('amount');
    	    // pending:剛建立請款
	        // waiting_payment: 已取號, 非即時支付
	        // success:實際入帳成功
	        // expired:超過繳費期限未付
	        // failed:異常或扣款失敗
            $table->string('status')->default('pending');
            $table->string('note', 20)->nullable();
            $table->timestamp('paid_at')->nullable();
            // TODO: 排程處裡
            $table->timestamp('expire_at')->nullable();
            $table->json('payment_info')->nullable();
            $table->json('raw_response')->nullable();

            $table->unique(['channel', 'channel_trade_no']);
            $table->index(['status', 'expire_at']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
