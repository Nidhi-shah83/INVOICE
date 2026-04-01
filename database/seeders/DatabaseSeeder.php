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
            ['user_id' => $user->id, 'name' => 'Service A', 'description' => 'GST free service', 'rate' => 1200.00, 'gst_percent' => 0.00, 'unit' => 'hour', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Product B', 'description' => 'Basic product', 'rate' => 500.00, 'gst_percent' => 5.00, 'unit' => 'piece', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Product C', 'description' => 'Standard item', 'rate' => 1500.00, 'gst_percent' => 12.00, 'unit' => 'set', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Product D', 'description' => 'Premium item', 'rate' => 2500.00, 'gst_percent' => 18.00, 'unit' => 'pack', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => $user->id, 'name' => 'Product E', 'description' => 'Luxury item', 'rate' => 4500.00, 'gst_percent' => 28.00, 'unit' => 'box', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('products')->insert($products);
    }
}
