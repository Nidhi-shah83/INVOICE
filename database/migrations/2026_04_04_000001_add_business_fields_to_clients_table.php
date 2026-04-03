<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('alternate_phone')->nullable()->after('phone');
            $table->string('city')->default('')->after('address');
            $table->string('pincode')->default('')->after('city');
            $table->string('country')->default('India')->after('pincode');
            $table->string('place_of_supply')->default('')->after('state');
            $table->enum('client_type', ['individual', 'business'])->default('individual')->after('place_of_supply');
            $table->text('notes')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'alternate_phone',
                'city',
                'pincode',
                'country',
                'place_of_supply',
                'client_type',
                'notes',
            ]);
        });
    }
};
