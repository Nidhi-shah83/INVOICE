<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('invoice_call_logs')) {
            return;
        }

        Schema::table('invoice_call_logs', function (Blueprint $table): void {
            $table->index(['user_id', 'invoice_number']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoice_call_logs')) {
            return;
        }

        Schema::table('invoice_call_logs', function (Blueprint $table): void {
            $table->dropIndex('invoice_call_logs_user_id_invoice_number_index');
        });
    }
};
