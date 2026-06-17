<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('users can register and receive an api token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'device_name' => 'test-device',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'role'],
            'token',
            'token_type',
        ])
        ->assertJsonPath('user.role.name', 'Admin')
        ->assertJsonPath('token_type', 'Bearer');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('users can login and receive an api token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
            'token_type',
        ])
        ->assertJsonPath('token_type', 'Bearer');
});

test('authenticated users can retrieve their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/user');

    $response
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});

test('users cannot retrieve their profile without an api token', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});

test('users can logout and revoke their current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out.');

    $this->assertDatabaseCount('personal_access_tokens', 0);

    Auth::forgetGuards();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();
});

test('password reset requests return a json status', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/forgot-password', [
        'email' => $user->email,
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure(['status']);
});

test('users can reset their password', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure(['status']);

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
