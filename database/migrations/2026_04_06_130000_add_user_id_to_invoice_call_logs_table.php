<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('invoice_call_logs')) {
            return;
        }

        if (! Schema::hasColumn('invoice_call_logs', 'user_id')) {
            Schema::table('invoice_call_logs', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        $orphanLogs = DB::table('invoice_call_logs')
            ->select('id', 'invoice_number')
            ->whereNull('user_id')
            ->get();

        foreach ($orphanLogs as $log) {
            $userId = DB::table('invoices')
                ->where('invoice_number', $log->invoice_number)
                ->value('user_id');

            if (! $userId) {
                continue;
            }

            DB::table('invoice_call_logs')
                ->where('id', $log->id)
                ->update(['user_id' => $userId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoice_call_logs') || ! Schema::hasColumn('invoice_call_logs', 'user_id')) {
            return;
        }

        Schema::table('invoice_call_logs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
