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
        Schema::table('comments', function (Blueprint $table) {
            $table->string('author_name', 50)->nullable()->after('author_id');
            $table->unsignedInteger('donate_amount')->default(0)->after('message');
            // 未付款 pending、 已付款 published
            $table->string('status',20)->default('published')->index()->after('donate_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['author_name', 'donate_amount', 'status']);
        });
    }
};
