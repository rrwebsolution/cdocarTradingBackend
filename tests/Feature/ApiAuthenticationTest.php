<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('users can register for admin approval', function () {
    Storage::fake('public');

    $response = $this->post('/api/register', [
        'address' => 'Cagayan de Oro City',
        'name' => 'Test User',
        'mobile_number' => '09123456789',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'username' => 'testuser',
        'valid_id_file' => UploadedFile::fake()->image('valid-id.jpg'),
        'valid_id_type' => 'Driver License',
        'device_name' => 'test-device',
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'role'],
            'message',
        ])
        ->assertJsonPath('user.role.name', 'Customer')
        ->assertJsonPath('message', 'Registration submitted for admin approval.');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'status' => 'inactive',
        'username' => 'testuser',
    ]);

    $this->assertDatabaseHas('customers', [
        'contact' => '09123456789',
        'email' => 'test@example.com',
        'status' => 'pending',
        'valid_id_type' => 'Driver License',
    ]);

    $customer = \App\Models\Customer::where('email', 'test@example.com')->firstOrFail();
    expect($customer->valid_id_url)->not->toBeNull();

    $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ])->assertForbidden();
});

test('approving a customer activates the linked user account', function () {
    $user = User::factory()->create([
        'email' => 'pending@example.com',
        'password' => Hash::make('password'),
        'status' => 'inactive',
    ]);
    $customer = \App\Models\Customer::create([
        'user_id' => $user->id,
        'name' => 'Pending Customer',
        'email' => 'pending@example.com',
        'status' => 'pending',
    ]);
    $admin = User::factory()->create();

    $this->actingAs($admin, 'sanctum')->patchJson("/api/customers/{$customer->id}", [
        'status' => 'approved',
    ])->assertOk();

    $this->assertDatabaseHas('users', [
        'email' => 'pending@example.com',
        'status' => 'active',
    ]);

    $this->postJson('/api/login', [
        'email' => 'pending@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ])->assertOk();
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

test('vite frontend origin can make api preflight requests', function () {
    $this->withHeaders([
        'Origin' => 'http://localhost:5173',
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'content-type, accept',
    ])->optionsJson('/api/login')
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
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
