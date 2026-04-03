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
            $table->enum('discount_type', ['flat', 'percent'])->default('flat')->after('total');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('round_off', 12, 2)->default(0)->after('discount_amount');
            $table->decimal('grand_total', 12, 2)->default(0)->after('round_off');
            $table->string('currency')->default('INR')->after('grand_total');
            $table->string('payment_terms')->nullable()->after('currency');
            $table->text('terms_conditions')->nullable()->after('payment_terms');
            $table->string('salesperson')->nullable()->after('terms_conditions');
            $table->string('reference_no')->nullable()->after('salesperson');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_amount',
                'round_off',
                'grand_total',
                'currency',
                'payment_terms',
                'terms_conditions',
                'salesperson',
                'reference_no',
            ]);
        });
    }
};
