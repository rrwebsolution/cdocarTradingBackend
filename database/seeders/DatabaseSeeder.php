<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // User::factory(10)->create();

        User::updateOrCreate([
            'email' => 'admin@autocdo.com',
        ], [
            'role_id' => 1,
            'name' => 'Administrator',
            'password' => Hash::make('@password123'),
        ]);
    }
}
