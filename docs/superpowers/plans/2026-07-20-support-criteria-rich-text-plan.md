# Support Criteria Rich Text Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Summernote Rich Text editing to the support-criteria activity and indicator fields, persist the HTML safely, and render it as Rich Text throughout support-criteria evaluation views.

**Architecture:** Reuse the existing Summernote 0.8.18 Lite setup and `.richtext-editor` conventions in the create and edit criteria-config scripts. Keep the existing support-criteria selectors and payload shape, add a shared plain-text conversion path for validation/accessibility, store `activity_name` as `TEXT`, and render user-authored HTML only through `SafeHtml`.

**Tech Stack:** Laravel/PHP, Blade, Pest/PHPUnit feature tests, jQuery, Summernote 0.8.18 Lite, HTMLPurifier.

## Global Constraints

- Use Summernote 0.8.18 Lite and the already-loaded Thai locale.
- Use the existing toolbar: style, bold, italic, underline, clear, color, ul, ol, paragraph, table, link, hr, fullscreen, codeview, help.
- Preserve `support_activity_name`, `support_indicator`, routes, payload names, permissions, scoring, and evaluation flow.
- Store HTML in `support_criterias.activity_name` and `support_criterias.indicator`.
- Render HTML only through `SafeHtml::richText()`; plain-text contexts must strip tags and decode entities.
- Do not modify unrelated dirty-worktree files or rewrite the existing support-criteria accessibility changes.

---

### Task 1: Add rich-text validation and database capacity

**Files:**
- Create: `app/Rules/HasRichText.php`
- Create: `database/migrations/2026_07_20_000003_change_support_activity_name_to_text.php`
- Modify: `app/Support/SafeHtml.php:9-28`
- Modify: `app/Http/Controllers/ReportStructureController.php:350-368,606-623`
- Test: `tests/Feature/Report/SupportCriteriaTemplateTest.php`
- Test: `tests/Unit/Support/SafeHtmlTest.php`

**Interfaces:**
- `SafeHtml::plainText(?string $html): string` returns decoded, tag-free, whitespace-collapsed text.
- `HasRichText::validate(string $attribute, mixed $value, Closure $fail): void` fails when the value contains no visible text.
- Both controller validation arrays use `['required', 'string', new HasRichText]` for support `activity_name` and `indicator`; `activity_name` has no `max:255` rule.

- [ ] **Step 1: Write the failing tests**

Add tests that demonstrate the required behavior before production changes:

```php
it('stores formatted support criteria content longer than 255 characters', function () {
    $html = '<p><strong>กิจกรรม</strong> '.str_repeat('รายละเอียด ', 40).'</p>';

    $response = $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => $html,
        'indicator' => '<ul><li>ทำครบตามแผน</li></ul>',
        'target_value' => 90,
        'weight' => 100,
    ]]));

    $response->assertCreated();
    $this->assertDatabaseHas('support_criterias', [
        'activity_name' => $html,
        'indicator' => '<ul><li>ทำครบตามแผน</li></ul>',
    ]);
});

it('rejects support criteria rich text with no visible text', function () {
    $response = $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => '<p><br></p>',
        'indicator' => '<p>เกณฑ์</p>',
        'target_value' => 90,
        'weight' => 100,
    ]]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('categories.0.evaluation_lists.0.support_criterias.0.activity_name');
});

it('converts rich text to safe plain text for non-html contexts', function () {
    expect(SafeHtml::plainText('<p><strong>A</strong> &amp; B</p>'))->toBe('A & B');
});
```

- [ ] **Step 2: Run the focused tests to verify they fail**

Run: `php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Unit/Support/SafeHtmlTest.php`

Expected: FAIL because `SafeHtml::plainText()` and the rich-text validation rule do not exist, and the schema still limits `activity_name` to 255 characters.

- [ ] **Step 3: Implement the minimal validation and schema changes**

Add the plain-text helper and validation rule:

```php
public static function plainText(?string $html): string
{
    $decoded = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim((string) preg_replace('/\s+/u', ' ', strip_tags($decoded)));
}
```

```php
final class HasRichText implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || SafeHtml::plainText($value) === '') {
            $fail('กรุณากรอกข้อความที่มีเนื้อหา');
        }
    }
}
```

Use `new HasRichText` in both store and update support-criteria rules, and add a forward migration:

```php
Schema::table('support_criterias', function (Blueprint $table): void {
    $table->text('activity_name')->change();
});
```

- [ ] **Step 4: Run the focused tests to verify they pass**

Run: `php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Unit/Support/SafeHtmlTest.php`

Expected: PASS, including persistence of HTML over 255 characters and rejection of `<p><br></p>`.

- [ ] **Step 5: Commit**

```bash
git add app/Rules/HasRichText.php app/Support/SafeHtml.php app/Http/Controllers/ReportStructureController.php database/migrations/2026_07_20_000003_change_support_activity_name_to_text.php tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Unit/Support/SafeHtmlTest.php
git commit -m "feat: support rich text validation for support criteria"
```

### Task 2: Convert create/edit support fields to Summernote with safe dynamic lifecycle

**Files:**
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php:29-40`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php:214-264,1300-1330`
- Modify: `resources/views/criteria_config/partials/script-edit-summernote-helpers.blade.php:1-120`
- Modify: `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php:1-40`
- Modify: `resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php` where rich-text clone cleanup is shared
- Test: `tests/Feature/SupportCriteriaRichTextEditorTest.php`

**Interfaces:**
- `buildSummernoteOptions($editor)` and the create-page initializer use the approved toolbar and `lang: 'th-TH'`.
- `resetSummernoteClone(node): void` removes cloned `.note-editor` wrappers and restores visible textareas before a block is appended.
- `initializeSummernote(container = null): void` remains idempotent and initializes only uninitialized `.richtext-editor` elements in the requested container.

- [ ] **Step 1: Write the failing render and lifecycle contract tests**

Create a focused test asserting both fields are textareas and both create/edit scripts expose the lifecycle hooks:

```php
it('renders both support criteria fields as rich text editors', function () {
    $html = view('criteria_config.partials.support-criteria-template')->render();

    expect($html)
        ->toContain('<textarea')
        ->toContain('support_activity_name richtext-editor')
        ->toContain('support_indicator richtext-editor')
        ->not->toContain('class="support_activity_name mt-2 block')
        ->not->toContain('class="support_indicator mt-2 block');
});

it('keeps summernote lifecycle hooks for dynamic support criteria blocks', function () {
    $create = view('criteria_config.partials.create-script')->render();
    $edit = view('criteria_config.partials.edit-script')->render();

    expect($create.$edit)
        ->toContain('resetSummernoteClone')
        ->toContain("lang: 'th-TH'")
        ->toContain("['insert', ['link', 'hr']]");
});
```

- [ ] **Step 2: Run the editor contract to verify it fails**

Run: `php artisan test tests/Feature/SupportCriteriaRichTextEditorTest.php`

Expected: FAIL because the template still contains two `<input type="text">` fields and no support-specific reset/initialization contract.

- [ ] **Step 3: Replace the inputs and implement idempotent clone handling**

Use textareas while preserving the existing selectors:

```blade
<textarea rows="8"
    class="support_activity_name richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
    placeholder="กิจกรรม/โครงการ/งาน"></textarea>
```

Apply the same structure to `support_indicator`. Extend both create and edit Summernote paths with the approved options, and ensure clone handlers call `resetSummernoteClone(block)` before clearing values. Clear `textarea` values alongside inputs, append the block, then call `initializeSummernote(block)`. Destroy the instance before removing a block.

The reset helper must remove cloned `.note-editor` siblings, remove Summernote marker classes/data, restore the textarea display state, and leave the new textarea empty. It must never copy the original editor content into a newly added block.

- [ ] **Step 4: Run the editor contract to verify it passes**

Run: `php artisan test tests/Feature/SupportCriteriaRichTextEditorTest.php`

Expected: PASS with exactly two support `.richtext-editor` textareas in the template contract and lifecycle hooks present in both page scripts.

- [ ] **Step 5: Commit**

```bash
git add resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-summernote-helpers.blade.php resources/views/criteria_config/partials/script-edit-support-handlers.blade.php resources/views/criteria_config/partials/script-edit-clone-helpers.blade.php tests/Feature/SupportCriteriaRichTextEditorTest.php
git commit -m "feat: add summernote to support criteria fields"
```

### Task 3: Preserve HTML through create/edit collection and enforce browser-side content checks

**Files:**
- Modify: `resources/views/criteria_config/partials/create-script.blade.php:1300-1330`
- Modify: `resources/views/criteria_config/partials/script-edit-data-helpers.blade.php:12-20`
- Modify: `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php:198-225`
- Modify: `resources/views/criteria_config/partials/script-edit-submit-handler.blade.php:10-15`
- Test: `tests/Feature/SupportCriteriaRichTextEditorTest.php`

**Interfaces:**
- `getRichTextValue(element): string` returns the Summernote code when initialized and textarea value otherwise.
- `hasVisibleRichText(value): boolean` uses the same visible-text rule as server validation.
- Form collectors continue returning `activity_name` and `indicator` under the existing JSON payload keys.

- [ ] **Step 1: Write the failing collector contract tests**

Extend the editor contract to require both collectors to read HTML rather than only `.value.trim()`:

```php
it('collects support criteria HTML from create and edit scripts', function () {
    $html = view('criteria_config.partials.create-script')->render()
        .view('criteria_config.partials.script-edit-collect-form-data')->render();

    expect($html)
        ->toContain("getRichTextValue(supportBlock.querySelector('.support_activity_name'))")
        ->toContain("getRichTextValue(supportBlock.querySelector('.support_indicator'))");
});
```

- [ ] **Step 2: Run the contract to verify it fails**

Run: `php artisan test tests/Feature/SupportCriteriaRichTextEditorTest.php`

Expected: FAIL because the collectors currently call `.value.trim()` directly and the create path has no shared fallback helper.

- [ ] **Step 3: Implement the minimal collection and client validation changes**

Use one helper in each page’s existing script scope:

```js
function getRichTextValue(element) {
    const $element = $(element);

    if ($element.next('.note-editor').length && typeof $element.summernote === 'function') {
        return ($element.summernote('code') || '').trim();
    }

    return (element?.value || '').trim();
}
```

Replace both support field reads in create and edit collectors with `getRichTextValue(...)`. Before building each payload, reject a value whose visible text is empty and use the existing support-criteria error path. Keep the textarea fallback so the form remains usable if Summernote fails to load.

- [ ] **Step 4: Run the collector contract and related tests**

Run: `php artisan test tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php`

Expected: PASS, with old plain-text support records still accepted and formatted HTML retained in the submitted payload.

- [ ] **Step 5: Commit**

```bash
git add resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/script-edit-data-helpers.blade.php resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php resources/views/criteria_config/partials/script-edit-submit-handler.blade.php tests/Feature/SupportCriteriaRichTextEditorTest.php
git commit -m "feat: preserve support criteria editor html"
```

### Task 4: Render sanitized Rich Text and plain-text accessibility labels

**Files:**
- Modify: `resources/views/components/support-criteria-table.blade.php:26-27,90-104,180-205`
- Modify: `resources/views/components/support-criteria-table-script.blade.php` only where activity names are copied into `data-*`/ARIA text
- Test: `tests/Feature/SupportCriteriaEvaluationViewTest.php`

**Interfaces:**
- HTML display uses `{!! \App\Support\SafeHtml::richText($item['activity_name']) !!}` and the equivalent indicator expression.
- Plain-text labels use `SafeHtml::plainText($item['activity_name'])` and never contain HTML tags.

- [ ] **Step 1: Write failing rendering and sanitization tests**

Add a formatted item to the existing view fixture and assert safe markup is rendered while unsafe markup is absent:

```php
it('renders support activity and indicator as sanitized rich text', function () {
    $item = array_replace(supportViewItem(), [
        'activity_name' => '<p><strong>กิจกรรม</strong></p><script>alert(1)</script>',
        'indicator' => '<ul><li>ครบตามแผน</li></ul>',
    ]);

    $html = view('components.support-criteria-table', [
        'items' => [$item],
        'readonly' => false,
        'evidenceEditable' => true,
        'requireReason' => false,
    ])->render();

    expect($html)
        ->toContain('<strong>กิจกรรม</strong>')
        ->toContain('<ul>')
        ->not->toContain('<script>')
        ->not->toContain('alert(1)');
});
```

- [ ] **Step 2: Run the view test to verify it fails**

Run: `php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php`

Expected: FAIL because the component currently escapes both fields with `{{ ... }}` and renders no formatted markup.

- [ ] **Step 3: Render through SafeHtml and sanitize plain-text contexts**

Replace display-only escaped expressions in desktop/mobile/modal support views with `SafeHtml::richText(...)`. Keep numeric values escaped. For button labels, `data-support-activity`, and validation messages, pass `SafeHtml::plainText(...)` or a precomputed plain-text value. Do not introduce `innerHTML` in the support script.

- [ ] **Step 4: Run view and security tests to verify they pass**

Run: `php artisan test tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Unit/Support/SafeHtmlTest.php`

Expected: PASS; `<strong>` and `<ul>` remain, while script/event-handler markup is removed and accessibility text contains no tags.

- [ ] **Step 5: Commit**

```bash
git add app/Support/SafeHtml.php resources/views/components/support-criteria-table.blade.php resources/views/components/support-criteria-table-script.blade.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Unit/Support/SafeHtmlTest.php
git commit -m "feat: render support criteria rich text safely"
```

### Task 5: Run the complete regression suite and perform browser smoke checks

**Files:**
- Modify: none unless a test exposes a regression from Tasks 1-4
- Test: existing support-criteria, criteria-config, and evaluation feature tests

**Interfaces:**
- No new interfaces; this task verifies the complete behavior promised by the spec.

- [ ] **Step 1: Run focused backend and view tests**

Run: `php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SupportCriteriaEvaluationViewTest.php tests/Feature/SupportCriteriaRichTextEditorTest.php tests/Unit/Support/SafeHtmlTest.php`

Expected: PASS with zero failures.

- [ ] **Step 2: Run the complete test suite**

Run: `php artisan test`

Expected: PASS with zero failures and no new warnings.

- [ ] **Step 3: Run formatting/static checks used by the repository**

Run: `vendor/bin/pint --test`

Expected: PASS with no files needing formatting.

- [ ] **Step 4: Perform the browser smoke flow**

On the criteria-config create page, type formatted content in both support fields, add a second support criterion, and verify the new editor is empty and has one toolbar. Save and reopen the edit page; verify HTML is preserved. Open the evaluatee/evaluator support table at desktop and mobile widths; verify the activity and indicator formatting is visible, scripts are not executed, and plain-text button/ARIA labels remain readable.

- [ ] **Step 5: Commit any test-only corrections and report evidence**

If a correction is required, run its focused test first, then commit only the relevant files with a message describing the regression. Report the exact test commands and exit results; do not claim completion without fresh passing output.
