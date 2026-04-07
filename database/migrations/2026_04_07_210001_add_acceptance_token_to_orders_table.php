<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('acceptance_token')->nullable()->after('status');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE orders MODIFY status ENUM('pending','accepted','confirmed','in_progress','partially_billed','fulfilled','fully_billed','cancelled') NOT NULL DEFAULT 'pending'"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE orders SET status = 'confirmed' WHERE status IN ('pending','accepted')");
            DB::statement(
                "ALTER TABLE orders MODIFY status ENUM('confirmed','in_progress','partially_billed','fulfilled','fully_billed','cancelled') NOT NULL DEFAULT 'confirmed'"
            );
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('acceptance_token');
        });
    }
};
