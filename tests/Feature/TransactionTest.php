<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_kasir_can_add_transaction_data()
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Acting as the admin to create product
        $this->actingAs($admin);

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'description' => 'Description of product',
            'price' => 1000,
            'stock' => 50,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Product 1',
            'description' => 'Description of product',
            'price' => '1000.00',
            'stock' => 50,
        ]);

        /** @var User $kasir */
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => false,
        ]);

        // Admin activates kasir
        $response = $this->actingAs($admin, 'api')->putJson("/api/users/{$kasir->id}/activate");


        $response->assertStatus(200);
        $kasir->refresh();
        $this->assertTrue($kasir->is_active);
        $this->assertEquals($admin->name, $kasir->updated_by);


        $this->actingAs($kasir, 'api');

        // Add a transaction
        $response = $this->postJson('/api/transactions', [
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,  // Total price calculation
        ]);


        $response->assertStatus(201);
        $response->assertJson([
            'id' => true, // Check if id is present (UUID)
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
            'status' => 'pending',
            'user_id' => true,
            'created_at' => true,
            'updated_at' => true,
        ]);

        // Assert the transaction is stored in the database
        $this->assertDatabaseHas('transactions', [
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
        ]);

        // Assert that the product stock is updated correctly
        $product->refresh();
        $this->assertEquals(48, $product->stock);
    }


    public function testUpdateTransaction()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // Create a product using the Product factory
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'price' => 1000,
            'stock' => 100,
        ]);

        // Create a transaction using the Transaction factory
        $transaction = Transaction::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
            'status' => 'pending',
            'user_id' => $admin->id, // Assign the admin's user ID

        ]);

        // Update transaction status to 'completed'
        $response = $this->actingAs($admin, 'api')->putJson("/api/transactions/{$transaction->id}", [
            'status' => 'completed',
            'product_id' => $transaction->product_id,
        ]);

        // Assert the status code is 200 (OK)
        $response->assertStatus(200);

        // Refresh the transaction and assert the status has been updated
        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
    }

    public function testRefundTransaction()
    {
        // Create a user with admin role
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'price' => 1000,
            'stock' => 100,
        ]);

        // Create a pending transaction
        $transaction = Transaction::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
            'status' => 'pending',
            'user_id' => $admin->id,
        ]);

        // Refund the transaction
        $response = $this->actingAs($admin, 'api')->postJson("/api/transactions/{$transaction->id}/refund");

        // Assert status is 200 and the transaction is refunded
        $response->assertStatus(200);
        $transaction->refresh();
        $this->assertEquals('refunded', $transaction->status);

        // Assert the product stock has been incremented
        $product->refresh();
        $this->assertEquals(102, $product->stock);
    }

    public function testRefundTransactionAlreadyCompleted()
    {
        // Create a user with admin role
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'price' => 1000,
            'stock' => 100,
        ]);

        // Create a completed transaction
        $transaction = Transaction::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
            'status' => 'completed',
            'user_id' => $admin->id,
        ]);

        // Try to refund the completed transaction
        $response = $this->actingAs($admin, 'api')->postJson("/api/transactions/{$transaction->id}/refund");
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Only not completed transactions can be refunded',
        ]);
    }

    public function testDestroyTransaction()
    {
        // Create a user with admin role
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'price' => 1000,
            'stock' => 100,
        ]);

        // Create a pending transaction
        $transaction = Transaction::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
            'status' => 'pending',
            'user_id' => $admin->id,
        ]);

        // Destroy the transaction
        $response = $this->actingAs($admin, 'api')->deleteJson("/api/transactions/{$transaction->id}");

        // Assert status is 200 and the transaction is deleted
        $response->assertStatus(200);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);

        // Assert the product stock has been incremented
        $product->refresh();
        $this->assertEquals(102, $product->stock);
    }

    public function testDestroyCompletedTransaction()
    {
        // Create a user with admin role
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        // Create a product
        $product = Product::factory()->create([
            'name' => 'Product 1',
            'price' => 1000,
            'stock' => 100,
        ]);

        // Create a completed transaction
        $transaction = Transaction::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => $product->price * 2,
            'status' => 'completed',
            'user_id' => $admin->id,
        ]);

        // Try to delete the completed transaction
        $response = $this->actingAs($admin, 'api')->deleteJson("/api/transactions/{$transaction->id}");

        // Assert status is 400 and the appropriate message is returned
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Completed transactions cannot be deleted',
        ]);
    }
}
