<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductTest extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;


    public function test_admin_can_access_product_routes()
    {
        // Create an admin user
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Log in as admin using the correct model instance
        $response = $this->actingAs($admin, 'api')->getJson('/api/products');

        // Assert that the admin can access the products route
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_product_routes()
    {
        // Create a kasir user

        /** @var User $kasir */
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        // Log in as kasir
        $response = $this->actingAs($kasir, 'api')->getJson('/api/products');

        // Assert that the kasir cannot access the products route
        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized']);
    }
    /** @test */
    public function admin_can_create_product()
    {
        // Create an admin user
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $productData = [
            'name' => 'Test Product',
            'price' => 1000,
            'stock' => 50,
        ];
        $response = $this->actingAs($admin, 'api')->postJson('/api/products', $productData);


        // Assert that the product was created
        $response->assertStatus(201); // Check if status code is 201 (Created)


        // Verify the product exists in the database
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 1000,
            'stock' => 50,
        ]);
    }

    /** @test */
    public function non_admin_cannot_create_product()
    {
        // Create a kasir user
        /** @var User $kasir */
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $productData = [
            'name' => 'Test Product',
            'price' => 1000,
            'stock' => 50,
        ];

        $response = $this->actingAs($kasir, 'api')->postJson('/api/products', $productData);

        // Assert that kasir cannot create a product
        $response->assertStatus(403); // Forbidden status

    }

    public function test_admin_can_update_product()
    {
        // Create an admin user
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create a product to update
        $product = \App\Models\Product::factory()->create([
            'name' => 'Old Product Name',
            'price' => 1000,
            'stock' => 50,
        ]);

        // Updated product data
        $updatedData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => 2000,  // Updated price
            'stock' => 100,  // Updated stock
        ];

        // Log in as the admin and make the update request
        $response = $this->actingAs($admin, 'api')->putJson("/api/products/{$product->id}", $updatedData);

        // Assert that the admin can update the product
        $response->assertStatus(200);

        // Verify that the product data has been updated in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => '2000.00',  // Adjusted to match the database format
            'stock' => 100,
        ]);
    }
    public function test_non_admin_cannot_update_product()
    {
        // Create a kasir user
        /** @var User $kasir */
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        // Create a product to update
        $product = \App\Models\Product::factory()->create([
            'name' => 'Product to be updated',
            'description' => 'Description',
            'price' => 1000,
            'stock' => 50,
        ]);

        // Updated product data
        $updatedData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => 2000,
            'stock' => 100,
        ];

        // Log in as kasir and try to update the product
        $response = $this->actingAs($kasir, 'api')->putJson("/api/products/{$product->id}", $updatedData);

        // Assert that kasir cannot update the product
        $response->assertStatus(403);  // Forbidden
    }
    public function test_admin_can_delete_product()
    {
        // Create an admin user
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create a product to delete
        $product = \App\Models\Product::factory()->create([
            'name' => 'Product to be deleted',
            'description' => 'Description of product',
            'price' => 1000,
            'stock' => 50,
        ]);

        // Log in as admin and make the delete request
        $response = $this->actingAs($admin, 'api')->deleteJson("/api/products/{$product->id}");

        // Assert that the admin can delete the product
        $response->assertStatus(200);

        // Verify that the product has been removed from the database
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
