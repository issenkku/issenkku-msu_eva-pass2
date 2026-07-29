<?php

use App\Support\FriendlyErrorPage;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth'])->get('/__test/protected-page', fn () => 'protected');
    Route::middleware('web')->get('/__test/session-error/{status}', function (string $status) {
        abort((int) $status, 'internal session detail');
    });
});

test('an unauthenticated protected web request redirects to Login with a warning', function () {
    $this->get('/__test/protected-page')
        ->assertRedirect(route('login'))
        ->assertSessionHas('session_warning', FriendlyErrorPage::SESSION_EXPIRED_MESSAGE);
});

test('explicit web 401 and 419 responses redirect to Login with the same warning', function (int $status) {
    $this->get("/__test/session-error/{$status}")
        ->assertRedirect(route('login'))
        ->assertSessionHas('session_warning', FriendlyErrorPage::SESSION_EXPIRED_MESSAGE);
})->with([401, 419]);

test('the Login page renders the session warning once in an accessible region', function () {
    $html = $this->withSession([
        'session_warning' => FriendlyErrorPage::SESSION_EXPIRED_MESSAGE,
    ])->get('/login')->assertOk()->getContent();

    expect(substr_count($html, FriendlyErrorPage::SESSION_EXPIRED_MESSAGE))
        ->toBe(1)
        ->and($html)
        ->toContain('role="alert"')
        ->toContain('aria-live="assertive"');
});

test('JSON 401 and 419 responses are not redirected to Login', function (int $status) {
    $this->getJson("/__test/session-error/{$status}")
        ->assertStatus($status)
        ->assertHeader('content-type', 'application/json');
})->with([401, 419]);
