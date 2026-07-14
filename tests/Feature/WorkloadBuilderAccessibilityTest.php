<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('workload builder exposes accessible names and collapse states for icon controls', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'web')
        ->get(route('workload-config.index'))
        ->assertOk();

    $html = $response->getContent();

    expect(str_contains($html, 'id="workload-nav-list" aria-busy="true"'))->toBeTrue()
        ->and(preg_match_all('/<button[^>]*class="[^"]*nav-item2[^"]*"[^>]*disabled[^>]*tabindex="-1"[^>]*>\\s*กำลังโหลดรายการ\\.{3}\\s*<\\/button>/u', $html))->toBe(1)
        ->and(preg_match_all('/<button[^>]*class="[^"]*workload-drag-handle[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(3)
        ->and(preg_match_all('/<button[^>]*class="[^"]*workload-collapse-toggle[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(2)
        ->and(preg_match_all('/<button[^>]*class="[^"]*delete_category_btn[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(2)
        ->and(preg_match_all('/<button[^>]*class="[^"]*workload-item-remove[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(1)
        ->and(preg_match_all('/<button[^>]*class="[^"]*workload-toast-close[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(1)
        ->and(preg_match_all('/<input[^>]*class="[^"]*workload-main-category[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(1)
        ->and(preg_match_all('/<input[^>]*class="[^"]*workload-sub-category[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(1)
        ->and(preg_match_all('/<input[^>]*class="[^"]*workload-item-name[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(1)
        ->and(preg_match_all('/<input[^>]*class="[^"]*workload-item-score[^"]*"[^>]*aria-label="[^"]+"/u', $html))->toBe(1)
        ->and(preg_match_all('/<div[^>]*class="[^"]*workload-formula-preview-value[^"]*"[^>]*aria-live="polite"[^>]*aria-atomic="true"[^>]*>/u', $html))->toBe(1)
        ->and(str_contains($html, 'พรีวิวสูตร'))->toBeTrue();

    expect($html)
        ->toContain("toggleButton.setAttribute('aria-expanded'")
        ->and($html)
        ->toContain("toggleButton.setAttribute('aria-controls'")
        ->and($html)
        ->toContain("editButton.setAttribute('aria-label'")
        ->and($html)
        ->toContain("button.setAttribute('aria-label'")
        ->and($html)
        ->toContain("navList.setAttribute('aria-busy', 'false')")
        ->and($html)
        ->toContain('updateFormulaPreview')
        ->and($html)
        ->toContain("formulaText.addEventListener('input', updateFormulaPreview)");
});
