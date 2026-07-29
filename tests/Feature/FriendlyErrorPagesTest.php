<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    config(['app.debug' => false]);

    Route::middleware('web')->get('/__test/friendly-error/{status}', function (string $status) {
        abort((int) $status, 'SENSITIVE_EXCEPTION_DETAIL');
    });

    Route::middleware('web')->get('/__test/friendly-crash', function () {
        throw new RuntimeException('SENSITIVE_RUNTIME_DETAIL');
    });
});

test('friendly web errors retain status and show approved Thai copy', function (
    int $status,
    string $title
) {
    $this->get("/__test/friendly-error/{$status}")
        ->assertStatus($status)
        ->assertSeeText($title)
        ->assertDontSeeText('SENSITIVE_EXCEPTION_DETAIL')
        ->assertDontSee(base_path(), escape: false)
        ->assertDontSeeText("Error {$status}");
})->with([
    [403, 'ไม่สามารถเข้าถึงหน้านี้ได้'],
    [404, 'ไม่พบหน้าที่ต้องการ'],
    [500, 'ระบบขัดข้องชั่วคราว'],
    [503, 'ระบบยังไม่พร้อมใช้งาน'],
]);

test('an unexpected exception uses the friendly 500 page without leaking details', function () {
    $this->get('/__test/friendly-crash')
        ->assertInternalServerError()
        ->assertSeeText('ระบบขัดข้องชั่วคราว')
        ->assertDontSeeText('SENSITIVE_RUNTIME_DETAIL')
        ->assertDontSee(base_path(), escape: false);
});

test('status views render safely when Laravel supplies no action variables', function (
    int $status,
    string $title
) {
    expect(view("errors.{$status}")->render())->toContain($title);
})->with([
    [403, 'ไม่สามารถเข้าถึงหน้านี้ได้'],
    [404, 'ไม่พบหน้าที่ต้องการ'],
    [500, 'ระบบขัดข้องชั่วคราว'],
    [503, 'ระบบยังไม่พร้อมใช้งาน'],
]);

test('an admin error page links to the admin dashboard', function () {
    Role::create(['name' => 'admin']);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/__test/friendly-error/403')
        ->assertForbidden()
        ->assertSee(route('dashboard'), escape: false);
});

test('an anonymous error page safely links to Login', function () {
    $this->get('/__test/friendly-error/404')
        ->assertNotFound()
        ->assertSee(route('login'), escape: false);
});

test('server and maintenance pages retain a retry action for the current URL', function (int $status) {
    $url = url("/__test/friendly-error/{$status}");

    $this->get($url)
        ->assertStatus($status)
        ->assertSee($url, escape: false)
        ->assertSeeText('ลองอีกครั้ง');
})->with([500, 503]);

test('JSON errors retain JSON content type and are never converted to friendly HTML', function (int $status) {
    $this->getJson("/__test/friendly-error/{$status}")
        ->assertStatus($status)
        ->assertHeader('content-type', 'application/json')
        ->assertDontSee('resources/views/components/error-page.blade.php');
})->with([403, 404, 500, 503]);
