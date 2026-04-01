<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->uuid('accept_token')->nullable()->unique()->after('order_id');
            $table->timestamp('accepted_at')->nullable()->after('accept_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['accept_token']);
            $table->dropColumn(['accept_token', 'accepted_at']);
        });
    }
};
