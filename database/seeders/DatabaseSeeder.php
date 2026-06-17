<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
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
        $this->call(CoreOperationSeeder::class);

        // User::factory(10)->create();

        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'role_id' => Role::where('name', 'Customer')->value('id'),
            'name' => 'Test User',
            'password' => Hash::make('password'),
        ]);
    }
}
