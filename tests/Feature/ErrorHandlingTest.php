<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('renders a friendly 500 page without exposing the stack trace', function (): void {
    config(['app.debug' => false]);

    Route::get('/__boom-test', fn () => throw new RuntimeException('detail teknis rahasia'));

    $response = $this->get('/__boom-test');

    $response->assertStatus(500);
    $response->assertSee('Maaf, ada kesalahan teknis');
    $response->assertDontSee('detail teknis rahasia');
});

it('keeps returning a plain 404 page when debug is off', function (): void {
    config(['app.debug' => false]);

    $this->get('/halaman-tidak-ada')->assertNotFound();
});

it('redirects failed login back with errors instead of rendering 500 when debug is off', function (): void {
    config(['app.debug' => false]);

    $response = $this->post('/login', [
        'email' => 'tidak-ada@kampus.test',
        'password' => 'salah12345678',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('redirects guests to login instead of rendering 500 when debug is off', function (): void {
    config(['app.debug' => false]);

    $this->get('/dashboard')->assertRedirect(route('login'));
});
