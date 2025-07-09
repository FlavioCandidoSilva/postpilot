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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('queue_status')->default('pending')->after('status');
            $table->text('failure_reason')->nullable()->after('queue_status');
            $table->integer('retry_count')->default(0)->after('failure_reason');
            $table->timestamp('last_attempt_at')->nullable()->after('retry_count');
            $table->integer('queue_priority')->default(0)->after('last_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('queue_status');
            $table->dropColumn('failure_reason');
            $table->dropColumn('retry_count');
            $table->dropColumn('last_attempt_at');
            $table->dropColumn('queue_priority');
        });
    }
};
