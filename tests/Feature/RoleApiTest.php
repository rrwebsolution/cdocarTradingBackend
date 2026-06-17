<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('roles cannot be listed without an api token', function () {
    $this->getJson('/api/roles')
        ->assertUnauthorized();
});

test('roles can be listed', function () {
    $this->seed(RoleSeeder::class);
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/roles');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Admin')
        ->assertJsonPath('data.1.name', 'Secretary')
        ->assertJsonPath('data.2.name', 'Mechanic')
        ->assertJsonPath('data.3.name', 'Carwasher')
        ->assertJsonPath('data.4.name', 'Customer')
        ->assertJsonPath('data.0.status', 'active')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'status'],
            ],
        ]);
});
