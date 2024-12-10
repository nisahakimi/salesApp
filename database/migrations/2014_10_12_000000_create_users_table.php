<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'kasir'])->default('kasir');
            $table->boolean('is_active')->default(false); // Aktivasi akun
            $table->rememberToken();
            $table->timestamps();
            $table->string('created_by');
            $table->string('updated_by');
        });

        // DB::table('users')->insert([
        //     'name' => 'Super Admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'), // Gunakan hash
        //     'role' => 'admin',
        //     'is_active' => true,
        //     'created_by' => 'System',
        //     'updated_by' => 'System',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
