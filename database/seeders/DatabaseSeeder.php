<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
        ]);

        $state = env('INVOICE_STATE', 'Maharashtra');
        $otherState = $state === 'Maharashtra' ? 'Karnataka' : 'Maharashtra';

        $now = now();

        $clients = [
            ['user_id' => $user->id, 'name' => 'Local Trading Co.', 'email' => 'local@test.com', 'phone' => '9000000001', 'gstin' => '27AAAPL1234C1Z1', 'state' => $state, 'address' => '123 Main Street, Business Park, ' . $state, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Vendor Supplies', 'email' => 'vendor@test.com', 'phone' => '9000000002', 'gstin' => '27BBAPL1234C1Z2', 'state' => $state, 'address' => 'Tower B, Industrial Area, ' . $state, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'OutStation Buyer', 'email' => 'outstation@test.com', 'phone' => '9000000003', 'gstin' => '29CCAPL1234C1Z3', 'state' => $otherState, 'address' => '456 Cross Street, ' . $otherState, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('clients')->insert($clients);

        $products = [
            ['user_id' => $user->id, 'name' => 'Dual-Band Router', 'description' => 'Business-grade router with VPN support', 'rate' => 6500.00, 'gst_percent' => 18.00, 'unit' => 'unit', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => '4MP Indoor CCTV Camera', 'description' => 'Infrared dome camera with night vision', 'rate' => 7200.00, 'gst_percent' => 18.00, 'unit' => 'unit', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Business Laptop', 'description' => '14-inch laptop with 16GB RAM and SSD', 'rate' => 55000.00, 'gst_percent' => 18.00, 'unit' => 'piece', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'PoE Network Switch', 'description' => '8-port Gigabit switch with PoE support', 'rate' => 4800.00, 'gst_percent' => 18.00, 'unit' => 'unit', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Smart Speaker', 'description' => 'Voice-enabled assistant speaker', 'rate' => 3900.00, 'gst_percent' => 18.00, 'unit' => 'piece', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Portable UPS', 'description' => '1kVA UPS for retail counters', 'rate' => 18500.00, 'gst_percent' => 18.00, 'unit' => 'unit', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('products')->insert($products);
    }
}
