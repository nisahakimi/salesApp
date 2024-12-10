<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

     public function it_returns_an_error_when_start_date_is_invalid()
     {
         /** @var User $admin */
         $admin = User::factory()->create(['role' => 'admin']);

         // Make a GET request to the laporan endpoint with an invalid 'start_date'
         $response = $this->actingAs($admin, 'api')->getJson('/api/laporan?start_date=029-2-201&end_date=invalid-date');


         // Assert the response has a 400 status code and contains the expected error message
         $response->assertStatus(400);
         $response->assertJson([
             'message' => 'Invalid start date format. Use YYYY-MM-DD.',
         ]);
     }

     /** @test */
     public function it_returns_an_error_when_end_date_is_invalid()
     {
         /** @var User $admin */
         $admin = User::factory()->create(['role' => 'admin']);

         // Make a GET request to the laporan endpoint with an invalid 'end_date'
         $response = $this->actingAs($admin, 'api')->getJson('/api/laporan?start_date=2024-12-01&end_date=invalid-date');

         $response->assertStatus(400); // Expect a 400 error
         $response->assertJson([
             'message' => 'Invalid end date format. Use YYYY-MM-DD.',
         ]);
     }

     /** @test */
     public function it_returns_transactions_within_the_specified_date_range()
     {
         /** @var User $admin */
         $admin = User::factory()->create(['role' => 'admin']);

         // Create products
         $product = Product::factory()->create();  // Assuming you have a factory for products

         // Create transactions with different created_at dates, linked to products
         $transaction1 = Transaction::factory()->create([
             'created_at' => Carbon::now()->subDays(5),
             'product_id' => $product->id,
         ]);
         $transaction2 = Transaction::factory()->create([
             'created_at' => Carbon::now()->subDays(3),
             'product_id' => $product->id,
         ]);
         $transaction3 = Transaction::factory()->create([
             'created_at' => Carbon::now()->subDay(),
             'product_id' => $product->id,
         ]);

         $startDate = Carbon::now()->subDays(4)->toDateString();
         $endDate = Carbon::now()->subDays(1)->toDateString();

         // Make a GET request to the laporan endpoint with valid 'start_date' and 'end_date'
         $response = $this->actingAs($admin, 'api')->getJson("/api/laporan?start_date={$startDate}&end_date={$endDate}");

         // Assert the response has a 200 status code
         $response->assertStatus(200);

         // Assert that 2 transactions are returned (transaction2 and transaction3)
         $response->assertJsonCount(2);
         $response->assertJsonFragment(['id' => $transaction2->id]);
         $response->assertJsonFragment(['id' => $transaction3->id]);
     }

     /** @test */
     public function it_returns_empty_when_no_transactions_match_the_date_range()
     {
         /** @var User $admin */
         $admin = User::factory()->create(['role' => 'admin']);

         // Create products
         $product = Product::factory()->create();  // Assuming you have a factory for products

         // Create transactions with dates outside of the desired range
         $transaction1 = Transaction::factory()->create([
             'created_at' => Carbon::now()->subDays(10),
             'product_id' => $product->id,
         ]);
         $transaction2 = Transaction::factory()->create([
             'created_at' => Carbon::now()->subDays(8),
             'product_id' => $product->id,
         ]);

         $startDate = Carbon::now()->subDays(5)->toDateString();
         $endDate = Carbon::now()->subDays(3)->toDateString();

         // Make a GET request to the laporan endpoint with valid 'start_date' and 'end_date'
         $response = $this->actingAs($admin, 'api')->getJson("/api/laporan?start_date={$startDate}&end_date={$endDate}");

         // Assert the response has a 200 status code
         $response->assertStatus(200);

         // Assert that no transactions are returned as none match the date range
         $response->assertJsonCount(0); // Expecting no transactions in this range
     }
}
