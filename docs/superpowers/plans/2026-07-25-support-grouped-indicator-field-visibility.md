# Support Grouped Indicator Field Visibility Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hide the legacy support indicator field, rather than the required activity field, when an administrator groups projects by sub-indicator.

**Architecture:** Keep the existing create/edit toggle functions and server-side conditional validation. Correct the semantic marker in the shared Blade template so both editor flows hide the intended label, and protect the behavior with a DOM-based view regression test.

**Tech Stack:** Laravel Blade, PHP 8, Pest, DOMDocument/DOMXPath

## Global Constraints

- `กิจกรรม/โครงการ/งาน` remains visible and required in grouped and non-grouped modes.
- `ตัวชี้วัด/เกณฑ์การประเมิน` is shown and required only in non-grouped mode.
- Grouped mode submits and persists `indicator` as null and validates the existing sub-indicator list.
- Do not change database, model, controller, API, scoring, or evaluation-flow behavior.
- Preserve unrelated working-tree changes.

---

### Task 1: Correct the Shared Field Visibility Contract

**Files:**
- Modify: `tests/Feature/SupportCriteriaTemplateViewTest.php`
- Modify: `resources/views/criteria_config/partials/support-criteria-template.blade.php:30-41`

**Interfaces:**
- Consumes: The existing `toggleSupportIndicatorMode(block)` functions in the create and edit scripts, which toggle `hidden` on `[data-support-legacy-indicator]`.
- Produces: Exactly one marked label in each rendered editor template; it contains `.support_indicator` and does not contain `.support_activity_name`.

- [ ] **Step 1: Write the failing regression test**

Add this Pest test to `tests/Feature/SupportCriteriaTemplateViewTest.php`:

```php
test('grouped support mode marks only the legacy indicator field for hiding', function (string $view) {
    $html = view($view)->render();
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);

    $document->loadHTML(
        '<!doctype html><html><body>'.$html.'</body></html>',
        LIBXML_NOERROR | LIBXML_NOWARNING
    );

    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);
    $markedLabels = $xpath->query('//label[@data-support-legacy-indicator]');

    expect($markedLabels)->not->toBeFalse()
        ->and($markedLabels->length)->toBe(1);

    $markedLabel = $markedLabels->item(0);
    $indicatorFields = $xpath->query(
        './/textarea[contains(concat(" ", normalize-space(@class), " "), " support_indicator ")]',
        $markedLabel
    );
    $activityFields = $xpath->query(
        './/textarea[contains(concat(" ", normalize-space(@class), " "), " support_activity_name ")]',
        $markedLabel
    );

    expect($indicatorFields)->not->toBeFalse()
        ->and($indicatorFields->length)->toBe(1)
        ->and($activityFields)->not->toBeFalse()
        ->and($activityFields->length)->toBe(0);
})->with([
    'create editor' => 'criteria_config.partials.create-evaluation-template',
    'edit editor' => 'criteria_config.partials.edit-evaluation-template',
]);
```

This test catches the production mutation where
`data-support-legacy-indicator` is placed on the activity label or on more than
one label.

- [ ] **Step 2: Run the test and verify RED**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php --filter="grouped support mode marks only the legacy indicator field for hiding"
```

Expected: two failing datasets because the marked label contains
`.support_activity_name` and does not contain `.support_indicator`.

- [ ] **Step 3: Apply the minimal template correction**

Change the two labels in
`resources/views/criteria_config/partials/support-criteria-template.blade.php`
from:

```blade
<label class="text-sm font-medium text-gray-700" data-support-legacy-indicator>
    กิจกรรม/โครงการ/งาน <span class="text-red-500">*</span>
    <textarea rows="8"
        class="support_activity_name richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
        placeholder="กิจกรรม/โครงการ/งาน"></textarea>
</label>
<label class="text-sm font-medium text-gray-700">
    ตัวชี้วัด/เกณฑ์การประเมิน <span class="text-red-500">*</span>
```

to:

```blade
<label class="text-sm font-medium text-gray-700">
    กิจกรรม/โครงการ/งาน <span class="text-red-500">*</span>
    <textarea rows="8"
        class="support_activity_name richtext-editor mt-2 block w-full rounded-lg border border-gray-300 p-2.5"
        placeholder="กิจกรรม/โครงการ/งาน"></textarea>
</label>
<label class="text-sm font-medium text-gray-700" data-support-legacy-indicator>
    ตัวชี้วัด/เกณฑ์การประเมิน <span class="text-red-500">*</span>
```

Do not modify the toggle functions: both already hide the marked label only
when grouped mode is selected.

- [ ] **Step 4: Run focused tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php
php artisan test tests/Feature/Report/SupportCriteriaTemplateTest.php --filter="grouped|legacy indicator"
```

Expected: all focused tests pass with zero failures.

- [ ] **Step 5: Run the complete affected test files**

Run:

```powershell
php artisan test tests/Feature/SupportCriteriaTemplateViewTest.php tests/Feature/Report/SupportCriteriaTemplateTest.php
```

Expected: both test files pass with zero failures and no new warnings.

- [ ] **Step 6: Review the isolated diff**

Run:

```powershell
git diff -- tests/Feature/SupportCriteriaTemplateViewTest.php resources/views/criteria_config/partials/support-criteria-template.blade.php
git status --short
```

Confirm that the production diff only moves
`data-support-legacy-indicator`, the test asserts create and edit behavior, and
all unrelated working-tree changes remain untouched.

- [ ] **Step 7: Commit the implementation**

```powershell
git add -- tests/Feature/SupportCriteriaTemplateViewTest.php resources/views/criteria_config/partials/support-criteria-template.blade.php
git commit -m "fix: hide support indicator in grouped mode"
```
