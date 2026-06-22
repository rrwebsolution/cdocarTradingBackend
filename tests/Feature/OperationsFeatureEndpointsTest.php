<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns live records for added operation modules', function (): void {
    $user = User::factory()->create();

    $endpoints = [
        '/api/activity-logs',
        '/api/financing-records',
        '/api/pre-sale-repairs',
        '/api/system-documents',
        '/api/vehicle-releases',
    ];

    foreach ($endpoints as $endpoint) {
        $this
            ->actingAs($user, 'sanctum')
            ->getJson($endpoint)
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
});
