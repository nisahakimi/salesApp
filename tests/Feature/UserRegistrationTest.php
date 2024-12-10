<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

     use RefreshDatabase;

    public function test_register()
    {


        $data = [
            'name' => 'Kasir Test',
            'email' => 'kasir@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'User successfully registered, awaiting activation',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kasir@example.com',
            'role' => 'kasir',
            'is_active' => false,
        ]);
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'kasir@example.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'kasir@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
             ->assertJson([
                  'message' => 'Your account is not activated. Please contact the admin.',
          ]);
    }

    public function test_admin_can_login_and_get_token()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',

            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['id', 'name', 'role'],
        ]);
    }
    public function test_admin_can_activate_kasir()
    {
        // Create an admin user
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        /** @var User $kasir */
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => false,
        ]);

        // Log in as admin and make the activate request
        $response = $this->actingAs($admin, 'api')->putJson("/api/users/{$kasir->id}/activate");

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert that the kasir user is now active
        $kasir->refresh(); // Refresh the model instance to get the updated values
        $this->assertTrue($kasir->is_active);
        $this->assertEquals($admin->name, $kasir->updated_by); // Check that the admin's name is recorded as the one who activated
    }

    public function test_activation_for_non_existing_user()
    {
        // Create an admin user
         /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Log in as admin and try to activate a non-existing user
        $response = $this->actingAs($admin, 'api')->putJson('/api/users/999/activate');

        // Assert that the response status is 404 (Not Found)
        $response->assertStatus(404);
    }

}
