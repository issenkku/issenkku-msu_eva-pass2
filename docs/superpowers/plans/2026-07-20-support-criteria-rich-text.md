# Support Criteria Rich Text Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Summernote Rich Text editing to both support-criteria text fields and render their saved formatting safely throughout evaluation views.

**Architecture:** A shared Blade JavaScript partial owns Summernote configuration, value extraction, blank-content checks, and clone/destroy lifecycle for create and edit forms. Laravel stores editor HTML, validates readable text, expands `activity_name` to `TEXT`, and renders allowed markup through `SafeHtml` while deriving plain text for accessibility metadata.

**Tech Stack:** Laravel 11, PHP 8.2, Blade, Pest 3, jQuery 3.6, Summernote Lite 0.8.18, HTMLPurifier, SQLite tests.

## Global Constraints

- Reuse Summernote 0.8.18 Lite and `th-TH`; add no editor dependency.
- Use toolbar groups `style`, `font`, `color`, `para`, `table`, `insert: link/hr`, and `view: fullscreen/codeview/help`.
- Preserve payload keys, routes, permissions, score calculations, and evaluation workflow.
- Store editor HTML unchanged; sanitize rich output through `SafeHtml::richText()`.
- Use plain text in `data-*`, `aria-label`, validation messages, and JavaScript labels.
- Preserve unrelated working-tree changes and stage only files named by each task.
- Follow red-green-refactor for every production change.

## File Map

- `app/Support/SafeHtml.php`: purified HTML and canonical plain-text conversion.
- `app/Rules/RichTextRequired.php`: rejects HTML without readable text.
- `database/migrations/2026_07_20_000003_change_support_activity_name_to_text.php`: expands storage.
- `app/Http/Controllers/ReportStructureController.php`: create/update validation.
- `resources/views/criteria_config/partials/support-criteria-template.blade.php`: two rich textareas.
- `resources/views/criteria_config/partials/script-summernote-helpers.blade.php`: shared editor API.
- `resources/views/criteria_config/partials/create-script.blade.php`: create lifecycle and collector.
- `resources/views/criteria_config/partials/edit-script.blade.php`: shared helper include.
- `resources/views/criteria_config/partials/script-edit-data-helpers.blade.php`: removes duplicate reader.
- `resources/views/criteria_config/partials/script-edit-support-handlers.blade.php`: edit lifecycle.
- `resources/views/criteria_config/partials/script-edit-collect-form-data.blade.php`: edit collector.
- `resources/views/components/support-criteria-table.blade.php`: safe rich rendering/plain metadata.
- `tests/Feature/SafeHtmlTest.php`: safety and validation primitives.
- `tests/Feature/Report/SupportCriteriaTemplateTest.php`: schema and persistence.
- `tests/Feature/SupportCriteriaTemplateViewTest.php`: editor contracts.
- `tests/Feature/SupportCriteriaEvaluationViewTest.php`: safe display contracts.

---

### Task 1: Canonical Rich Text Safety and Required Validation

**Files:**
- Create: `app/Rules/RichTextRequired.php`
- Create: `tests/Feature/SafeHtmlTest.php`
- Modify: `app/Support/SafeHtml.php`

**Interfaces:**
- Produces: `SafeHtml::plainText(?string $html): string`.
- Produces: `RichTextRequired implements ValidationRule`.

- [ ] **Step 1: Write failing purifier/plain-text and rule tests**

```php
<?php

use App\Rules\RichTextRequired;
use App\Support\SafeHtml;
use Illuminate\Support\Facades\Validator;

test('rich text is purified while allowed formatting remains', function () {
    $html = (string) SafeHtml::richText('<p onclick="alert(1)"><strong>งานหลัก</strong><script>alert(2)</script></p>');

    expect($html)->toContain('<strong>งานหลัก</strong>')
        ->not->toContain('onclick')->not->toContain('<script');
});

test('rich text converts to normalized readable plain text', function () {
    expect(SafeHtml::plainText('<p><strong>งานหลัก</strong>&nbsp; ประจำปี</p><script>alert(1)</script>'))
        ->toBe('งานหลัก ประจำปี');
});

test('rich text required rejects markup without readable text', function (string $html) {
    $validator = Validator::make(['content' => $html], [
        'content' => ['required', 'string', new RichTextRequired],
    ]);
    expect($validator->fails())->toBeTrue();
})->with(['<p><br></p>', '<p>&nbsp;</p>', '<script>alert(1)</script>']);

test('rich text required accepts formatted readable text', function () {
    $validator = Validator::make(['content' => '<ul><li>ส่งตรงเวลา</li></ul>'], [
        'content' => ['required', 'string', new RichTextRequired],
    ]);
    expect($validator->passes())->toBeTrue();
});
```

- [ ] **Step 2: Run RED**

Run: `php artisan test tests/Feature/SafeHtmlTest.php`

Expected: FAIL because `SafeHtml::plainText()` and `RichTextRequired` do not exist.

- [ ] **Step 3: Implement the helper and rule**

Add to `SafeHtml` after `richText()`:

```php
public static function plainText(?string $html): string
{
    if (! filled($html)) {
        return '';
    }

    $text = html_entity_decode(
        strip_tags((string) self::richText($html)),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );
    $text = str_replace("\u{00A0}", ' ', $text);

    return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
}
```

Create `app/Rules/RichTextRequired.php`:

```php
<?php

namespace App\Rules;

use App\Support\SafeHtml;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class RichTextRequired implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || SafeHtml::plainText($value) === '') {
            $fail('validation.required')->translate();
        }
    }
}
```

- [ ] **Step 4: Run GREEN and format**

Run: `php artisan test tests/Feature/SafeHtmlTest.php`

Expected: 4 tests PASS, including all blank-HTML data cases.

Run: `vendor/bin/pint app/Support/SafeHtml.php app/Rules/RichTextRequired.php tests/Feature/SafeHtmlTest.php`

- [ ] **Step 5: Commit**

Run: `git add -- app/Support/SafeHtml.php app/Rules/RichTextRequired.php tests/Feature/SafeHtmlTest.php`

Run: `git commit -m "feat: validate readable rich text"`

---

### Task 2: Expand Storage and Validate Create/Update

**Files:**
- Create: `database/migrations/2026_07_20_000003_change_support_activity_name_to_text.php`
- Modify: `app/Http/Controllers/ReportStructureController.php`
- Modify: `tests/Feature/Report/SupportCriteriaTemplateTest.php`

**Interfaces:**
- Consumes: `RichTextRequired` from Task 1.
- Produces: `activity_name` as `TEXT`; create/update accept formatted HTML and reject markup-only values.

- [ ] **Step 1: Add failing schema, persistence, and blank-content tests**

Add inside `test_support_criteria_schema_exists()`:

```php
$this->assertSame('text', Schema::getColumnType('support_criterias', 'activity_name'));
```

Add before `payload()`:

```php
public function test_admin_can_store_long_formatted_support_criteria_text(): void
{
    $activity = '<p><strong>'.str_repeat('กิจกรรมประจำปี ', 30).'</strong></p>';
    $indicator = '<ul><li>ส่งงานตรงเวลา</li><li>ข้อมูลครบถ้วน</li></ul>';

    $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => $activity,
        'indicator' => $indicator,
        'target_value' => 100,
        'weight' => 100,
    ]]))->assertCreated();

    $this->assertDatabaseHas('support_criterias', [
        'activity_name' => $activity,
        'indicator' => $indicator,
    ]);
}

public function test_store_rejects_support_rich_text_without_readable_content(): void
{
    $this->postJson(route('report-structure.store'), $this->payload([[
        'sequence' => 1,
        'activity_name' => '<p><br></p>',
        'indicator' => '<p>&nbsp;</p>',
        'target_value' => 100,
        'weight' => 100,
    ]]))->assertUnprocessable()->assertJsonValidationErrors([
        'categories.0.evaluation_lists.0.support_criterias.0.activity_name',
        'categories.0.evaluation_lists.0.support_criterias.0.indicator',
    ]);
}
```

- [ ] **Step 2: Run RED**

Run: `php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php`

Expected: FAIL because the column is `varchar`, `activity_name` has `max:255`, and markup-only strings pass.

- [ ] **Step 3: Add the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_criterias', function (Blueprint $table) {
            $table->text('activity_name')->change();
        });
    }

    public function down(): void
    {
        Schema::table('support_criterias', function (Blueprint $table) {
            $table->string('activity_name')->change();
        });
    }
};
```

- [ ] **Step 4: Wire both controller validation arrays**

Import `use App\Rules\RichTextRequired;` and replace the activity/indicator rules in both store and update arrays with:

```php
'categories.*.evaluation_lists.*.support_criterias.*.activity_name' => ['required', 'string', new RichTextRequired],
'categories.*.evaluation_lists.*.support_criterias.*.indicator' => ['required', 'string', new RichTextRequired],
```

- [ ] **Step 5: Run GREEN, format, and commit**

Run: `php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php tests/Feature/SafeHtmlTest.php`

Expected: all tests PASS; long HTML persists unchanged and blank HTML returns 422.

Run: `vendor/bin/pint app/Http/Controllers/ReportStructureController.php database/migrations/2026_07_20_000003_change_support_activity_name_to_text.php tests/Feature/Report/SupportCriteriaTemplateTest.php`

Run: `git add -- app/Http/Controllers/ReportStructureController.php database/migrations/2026_07_20_000003_change_support_activity_name_to_text.php tests/Feature/Report/SupportCriteriaTemplateTest.php`

Run: `git commit -m "feat: store support criteria rich text"`

---

### Task 3: Share Summernote Configuration Across Create and Edit

**Files:**
- Create: `resources/views/criteria_config/partials/script-summernote-helpers.blade.php`
- Delete: `resources/views/criteria_config/partials/script-edit-summernote-helpers.blade.php`
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php`
- Modify: `resources/views/criteria_config/partials/create-script.blade.php`
- Modify: `resources/views/criteria_config/partials/edit-script.blade.php`
- Modify: `resources/views/criteria_config/partials/script-edit-data-helpers.blade.php`
- Modify: `tests/Feature/SupportCriteriaTemplateViewTest.php`

**Interfaces:**
- Produces: `getRichTextValue(element): string`, `richTextHasContent(html): boolean`, `initializeSummernote(container = null): void`, `destroySummernoteEditors(container = null): void`, and `resetSummernoteClone(container, clearContents = true): void`.
- Produces: shared `buildSummernoteOptions($editor)` with the approved toolbar and locale.

- [ ] **Step 1: Add failing markup and shared-helper tests**

Extend the first view test:

```php
expect($html)
    ->toContain('<textarea')
    ->toContain('support_activity_name richtext-editor')
    ->toContain('support_indicator richtext-editor');
```

Add this test:

```php
test('create and edit use one shared Summernote contract', function () {
    $helpers = file_get_contents(resource_path('views/criteria_config/partials/script-summernote-helpers.blade.php'));
    $create = file_get_contents(resource_path('views/criteria_config/partials/create-script.blade.php'));
    $edit = file_get_contents(resource_path('views/criteria_config/partials/edit-script.blade.php'));

    expect($create)->toContain("@include('criteria_config.partials.script-summernote-helpers')");
    expect($edit)->toContain("@include('criteria_config.partials.script-summernote-helpers')");
    expect($helpers)
        ->toContain("['insert', ['link', 'hr']]")
        ->toContain("lang: 'th-TH'")
        ->toContain('function getRichTextValue(element)')
        ->toContain('function richTextHasContent(html)')
        ->toContain('function resetSummernoteClone(container, clearContents = true)')
        ->toContain('function destroySummernoteEditors(container = null)');
});
```

Run: `php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php`

Expected: FAIL because both fields are inputs and the shared helper does not exist.

- [ ] **Step 2: Convert both support fields to textareas**

Replace the activity input with:

```blade
<textarea rows="8"
    class="support_activity_name richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
    placeholder="กิจกรรม/โครงการ/งาน"></textarea>
```

Replace the indicator input with:

```blade
<textarea rows="8"
    class="support_indicator richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
    placeholder="ตัวชี้วัด/เกณฑ์การประเมิน"></textarea>
```

- [ ] **Step 3: Create the shared Summernote API**

Create `script-summernote-helpers.blade.php`:

```javascript
        function richTextEditors(container = null) {
            if (!container) return $('.richtext-editor');

            const $container = $(container);
            return $container.is('.richtext-editor')
                ? $container.add($container.find('.richtext-editor'))
                : $container.find('.richtext-editor');
        }

        function getRichTextValue(element) {
            const $element = $(element);
            if ($element.next('.note-editor').length > 0 && typeof $element.summernote === 'function') {
                return ($element.summernote('code') || '').trim();
            }
            return ($element.val() || '').trim();
        }

        function richTextHasContent(html) {
            const parsed = new DOMParser().parseFromString(html || '', 'text/html');
            return parsed.body.textContent.replace(/\u00a0/g, ' ').trim() !== '';
        }

        function buildSummernoteOptions($editor) {
            let placeholder = 'กรุณาใส่คำอธิบายเพิ่มเติม...';
            if ($editor.hasClass('qual_sub_description')) placeholder = 'ใส่คำอธิบายการให้คะแนน';
            if ($editor.hasClass('support_activity_name')) placeholder = 'กิจกรรม/โครงการ/งาน';
            if ($editor.hasClass('support_indicator')) placeholder = 'ตัวชี้วัด/เกณฑ์การประเมิน';

            return {
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                placeholder,
                lang: 'th-TH',
                callbacks: {
                    onChange(contents) {
                        $editor.val(contents);
                        if (typeof markDirty === 'function') markDirty();
                    }
                }
            };
        }

        function initializeSummernote(container = null) {
            richTextEditors(container).each(function() {
                const $editor = $(this);
                if ($editor.next('.note-editor').length || $editor.data('summernoteInitialized')) return;
                $editor.summernote(buildSummernoteOptions($editor));
                $editor.data('summernoteInitialized', true);
            });
        }

        function destroySummernoteEditors(container = null) {
            richTextEditors(container).each(function() {
                const $editor = $(this);
                if ($editor.next('.note-editor').length && typeof $editor.summernote === 'function') {
                    $editor.summernote('destroy');
                }
                $editor.removeData('summernoteInitialized').removeAttr('style').show();
            });
        }

        function resetSummernoteClone(container, clearContents = true) {
            $(container).find('.note-editor').remove();
            richTextEditors(container).each(function() {
                $(this).removeData('summernoteInitialized').removeAttr('style').show();
                this.removeAttribute('id');
                if (clearContents) this.value = '';
            });
        }

        function cleanupSummernote() {
            destroySummernoteEditors();
        }

        function setupLazySummernote() {
            document.addEventListener('focusin', function(event) {
                const editor = event.target.closest('.richtext-editor');
                if (editor) initializeSummernote(editor.parentElement || editor);
            });
        }

        function observeVisibleSummernote() {
            const editors = document.querySelectorAll('.richtext-editor');
            if (!('IntersectionObserver' in window)) {
                editors.forEach((editor) => initializeSummernote(editor.parentElement || editor));
                return;
            }

            if (!window.richtextObserver) {
                window.richtextObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        initializeSummernote(entry.target.parentElement || entry.target);
                        window.richtextObserver.unobserve(entry.target);
                    });
                }, { rootMargin: '200px 0px', threshold: 0.01 });
            }

            editors.forEach((editor) => {
                if (!$(editor).data('summernoteInitialized') && !$(editor).next('.note-editor').length) {
                    window.richtextObserver.observe(editor);
                }
            });
        }
```

- [ ] **Step 4: Replace duplicate includes/functions**

In `create-script.blade.php`, replace its inline Summernote function block with:

```blade
        @include('criteria_config.partials.script-summernote-helpers')
```

Keep its existing document-ready call to `initializeSummernote()`.

In `edit-script.blade.php`, replace the old helper include with:

```blade
@include('criteria_config.partials.script-summernote-helpers')
```

Delete `script-edit-summernote-helpers.blade.php`. Remove the duplicate `getRichTextValue()` function from `script-edit-data-helpers.blade.php`.

- [ ] **Step 5: Run GREEN and commit**

Run: `php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php`

Expected: all tests PASS and create/edit expose one shared toolbar contract.

Run: `git add -- resources/views/criteria_config/partials/support-criteria-template.blade.php resources/views/criteria_config/partials/script-summernote-helpers.blade.php resources/views/criteria_config/partials/create-script.blade.php resources/views/criteria_config/partials/edit-script.blade.php resources/views/criteria_config/partials/script-edit-data-helpers.blade.php resources/views/criteria_config/partials/script-edit-summernote-helpers.blade.php tests/Feature/SupportCriteriaTemplateViewTest.php`

Run: `git commit -m "feat: share support criteria editor setup"`
