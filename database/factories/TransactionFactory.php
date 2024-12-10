<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => (string) Str::uuid(), // Generate UUID for id
        'product_id' => Product::factory(),
        'quantity' => 2,
        'total_price' => 2000.00,
        'status' => 'pending',
        'user_id' => User::factory(), // Create a user for the transaction
        'created_by' => 'System',
        'updated_by' => 'System',
        ];
    }
}
