<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasTable('users')) {
            return;
        }

        $userIds = DB::table('users')->pluck('id');
        if ($userIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($userIds as $userId) {
            $rows[] = [
                'user_id' => $userId,
                'key' => 'company_prefix',
                'value' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = [
                'user_id' => $userId,
                'key' => 'quote_prefix',
                'value' => 'QT',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = [
                'user_id' => $userId,
                'key' => 'order_prefix',
                'value' => 'ORD',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = [
                'user_id' => $userId,
                'key' => 'invoice_prefix',
                'value' => 'INV',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('settings')->insertOrIgnore($rows);
    }

    public function down(): void
    {
    }
};
