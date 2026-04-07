<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('quotes', 'approval_token')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->uuid('approval_token')->nullable()->unique()->after('accept_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quotes', 'approval_token')) {
            Schema::table('quotes', function (Blueprint $table) {
                $table->dropUnique(['approval_token']);
                $table->dropColumn('approval_token');
            });
        }
    }
};
