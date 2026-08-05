<?php

namespace Tests\Feature\Settings;

use App\Models\Setting\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebsiteSettingsAsyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        $this->admin = User::factory()->create([
            'employee_id' => 'SETTINGSASYNC',
            'status' => 'active',
        ]);
        $this->admin->assignRole('admin');
        Storage::fake('public');
    }

    public function test_multipart_settings_save_returns_persisted_urls_and_fragments(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post(route('settings.store'), [
                'university' => 'Async University',
                'faculty' => 'Async Faculty',
                'notification_days' => 9,
                'logo' => UploadedFile::fake()->image('logo.png'),
                'background' => UploadedFile::fake()->image('background.jpg'),
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.settings.university', 'Async University')
            ->assertJsonStructure([
                'message',
                'data' => ['settings', 'logo_url', 'background_url'],
                'html' => ['background_library', 'info'],
            ]);

        expect($response->json('data.logo_url'))->toContain('/storage/site-logos/')
            ->and($response->json('data.background_url'))->toContain('/storage/site-backgrounds/')
            ->and($response->json('html.background_library'))->toContain('data-background-library-region')
            ->and($response->json('html.info'))->toContain('data-settings-info-region');
    }

    public function test_async_settings_can_select_and_delete_library_backgrounds(): void
    {
        Storage::disk('public')->put('site-backgrounds/selected.webp', 'selected');
        Storage::disk('public')->put('site-backgrounds/delete.webp', 'delete');
        $setting = Settings::create([
            'university' => 'MSU',
            'faculty' => 'Public Health',
            'notification_days' => 7,
        ]);

        $this->actingAs($this->admin, 'web')
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('settings.store'), [
                'id' => $setting->id,
                'university' => 'MSU',
                'faculty' => 'Public Health',
                'notification_days' => 7,
                'selected_background_path' => 'site-backgrounds/selected.webp',
                'delete_background_paths' => ['site-backgrounds/delete.webp'],
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.settings.background_path', 'site-backgrounds/selected.webp');

        Storage::disk('public')->assertMissing('site-backgrounds/delete.webp');
    }

    public function test_native_settings_submission_keeps_the_redirect_fallback(): void
    {
        $this->actingAs($this->admin, 'web')
            ->post(route('settings.store'), [
                'university' => 'Native University',
                'faculty' => 'Native Faculty',
                'notification_days' => 5,
            ])
            ->assertRedirect(route('settings.index'));
    }
}
