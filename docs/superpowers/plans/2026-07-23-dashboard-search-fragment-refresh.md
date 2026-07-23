# Dashboard Search Fragment Refresh Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make dashboard evaluation-list Search and Clear replace only the evaluation-list fragment while preserving the viewport and browser history.

**Architecture:** Keep the existing server-rendered fragment loader in the dashboard Blade script. Add dashboard-owned delegated handling for Search and Clear, with Clear intercepted during capture so the shared search-bar listener cannot submit the form first. Exercise the actual Blade script with the repository's Node test runner and a focused fake DOM; no new dependency is required.

**Tech Stack:** Laravel 11, Blade, browser Fetch/History APIs, Node.js built-in test runner, Pest 3

## Global Constraints

- Replace only `[data-evaluation-list]`; do not refresh overview cards, charts, or the top-level filter panel.
- Search and Clear must preserve the current scroll position.
- Search and Clear must remove `page`; Clear must also omit `search`.
- Preserve all other active dashboard query parameters.
- Keep normal GET form behaviour when JavaScript is unavailable.
- Keep status, pagination, and Back/Forward behaviour unchanged.
- Add no frontend framework or runtime dependency.

---

### Task 1: Regression-test and fix Search/Clear fragment navigation

**Files:**
- Create: `tests/js/dashboard-evaluation-list.test.mjs`
- Modify: `resources/views/dashboard/partials/index-script.blade.php:97-189`
- Modify: `tests/Feature/DashboardTest.php:55-100`

**Interfaces:**
- Consumes: the existing `loadEvaluationList(url, { push, focus })` closure and the fragment contract `X-Dashboard-Fragment: evaluation-list`.
- Produces: delegated Search and Clear handlers that call `loadEvaluationList` with a URL string and no focus target; the existing status and pagination handlers remain consumers of the same loader.

- [ ] **Step 1: Write the failing browser-behaviour regression test**

Create `tests/js/dashboard-evaluation-list.test.mjs`:

```js
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const scriptSource = readFileSync(
    new URL('../../resources/views/dashboard/partials/index-script.blade.php', import.meta.url),
    'utf8',
)
    .match(/<script>\s*([\s\S]*?)<\/script>\s*$/)[1]
    .replace(
        'const overviewChart = @json($overviewChart);',
        'const overviewChart = {"id":"overallCompletionChart"};',
    );

const clearScriptSource = readFileSync(
    new URL('../../resources/views/components/search-bar-script.blade.php', import.meta.url),
    'utf8',
).match(/<script>\s*([\s\S]*?)<\/script>\s*$/)[1];

const flush = () => new Promise((resolve) => setImmediate(resolve));

function createHarness({ search, page = '3', scrollY = 480 }) {
    const documentListeners = new Map();
    const windowListeners = new Map();
    const fetchUrls = [];
    const pushedUrls = [];
    let currentList;
    let replacements = 0;

    const addListener = (store, type, listener, options = {}) => {
        const listeners = store.get(type) ?? [];
        listeners.push({
            listener,
            capture: options === true || options?.capture === true,
        });
        store.set(type, listeners);
    };

    const dispatch = async (store, type, event) => {
        const listeners = store.get(type) ?? [];

        for (const capture of [true, false]) {
            for (const entry of listeners.filter((item) => item.capture === capture)) {
                if (event.immediatePropagationStopped) {
                    return;
                }
                entry.listener(event);
            }
        }

        await flush();
    };

    const createList = () => ({
        setAttribute() {},
        querySelectorAll() {
            return [];
        },
        querySelector() {
            return null;
        },
        replaceWith(nextList) {
            currentList = nextList;
            replacements += 1;
        },
    });

    currentList = createList();

    const heading = {
        focus() {
            sandbox.window.scrollY = 0;
        },
    };

    const form = {
        action: 'https://example.test/dashboard',
        fields: [
            ['search', search],
            ['year', '2026'],
            ['page', page],
        ],
        querySelector(selector) {
            return selector === '[data-auto-search-input]' ? input : null;
        },
        async requestSubmit() {
            await dispatch(documentListeners, 'submit', createEvent(form));
        },
    };

    const input = { value: search };
    const listMarker = {};
    const clearButton = {
        closest(selector) {
            if (selector === '[data-auto-search-clear]') {
                return clearButton;
            }
            if (selector === '[data-evaluation-list]') {
                return listMarker;
            }
            if (selector === '[data-auto-search-form]') {
                return form;
            }
            return null;
        },
    };

    function createEvent(target) {
        return {
            target: {
                closest(selector) {
                    if (target === form && selector === '[data-evaluation-list] [data-auto-search-form]') {
                        return form;
                    }
                    if (target === clearButton) {
                        return clearButton.closest(selector);
                    }
                    return null;
                },
            },
            button: 0,
            defaultPrevented: false,
            immediatePropagationStopped: false,
            preventDefault() {
                this.defaultPrevented = true;
            },
            stopImmediatePropagation() {
                this.immediatePropagationStopped = true;
            },
        };
    }

    class FakeFormData {
        constructor(submittedForm) {
            return submittedForm.fields.map(([key, value]) => [
                key,
                key === 'search' ? input.value : value,
            ]);
        }
    }

    const document = {
        addEventListener(type, listener, options) {
            addListener(documentListeners, type, listener, options);
        },
        querySelector(selector) {
            return selector === '[data-evaluation-list]' ? currentList : null;
        },
        querySelectorAll() {
            return [];
        },
        getElementById(id) {
            return id === 'evaluation-list-heading' ? heading : null;
        },
        createElement() {
            return {
                set innerHTML(value) {
                    this.value = value;
                },
                querySelector(selector) {
                    return selector === '[data-evaluation-list]' ? createList() : null;
                },
            };
        },
    };

    const window = {
        scrollY,
        location: {
            href: `https://example.test/dashboard?search=${encodeURIComponent(search)}&year=2026&page=${page}`,
        },
        history: {
            pushState(state, title, url) {
                pushedUrls.push(String(url));
                window.location.href = String(url);
            },
        },
        addEventListener(type, listener, options) {
            addListener(windowListeners, type, listener, options);
        },
    };

    const sandbox = {
        AbortController,
        Chart: class {},
        CSS: {
            escape(value) {
                return value;
            },
        },
        FormData: FakeFormData,
        URL,
        URLSearchParams,
        console,
        document,
        fetch: async (url) => {
            fetchUrls.push(String(url));
            return {
                ok: true,
                headers: {
                    get(name) {
                        return name === 'X-Dashboard-Fragment' ? 'evaluation-list' : null;
                    },
                },
                async text() {
                    return '<section data-evaluation-list></section>';
                },
            };
        },
        window,
    };

    vm.runInNewContext(clearScriptSource, sandbox);
    vm.runInNewContext(scriptSource, sandbox);

    const domReady = documentListeners.get('DOMContentLoaded')[0].listener;
    domReady();

    return {
        clearButton,
        createEvent,
        dispatchDocument: (type, event) => dispatch(documentListeners, type, event),
        dispatchWindow: (type, event) => dispatch(windowListeners, type, event),
        fetchUrls,
        form,
        get replacements() {
            return replacements;
        },
        pushedUrls,
        window,
    };
}

test('search replaces only the evaluation list and preserves the viewport', async () => {
    const harness = createHarness({ search: 'Alice' });
    const event = harness.createEvent(harness.form);

    await harness.dispatchDocument('submit', event);

    assert.equal(event.defaultPrevented, true);
    assert.equal(harness.replacements, 1);
    assert.equal(harness.window.scrollY, 480);
    assert.equal(harness.fetchUrls.length, 1);

    const url = new URL(harness.fetchUrls[0]);
    assert.equal(url.searchParams.get('search'), 'Alice');
    assert.equal(url.searchParams.get('year'), '2026');
    assert.equal(url.searchParams.has('page'), false);
    assert.equal(harness.pushedUrls[0], harness.fetchUrls[0]);
});

test('clear removes search and page without document navigation or scrolling', async () => {
    const harness = createHarness({ search: 'Alice' });
    const event = harness.createEvent(harness.clearButton);

    await harness.dispatchDocument('click', event);

    assert.equal(event.defaultPrevented, true);
    assert.equal(event.immediatePropagationStopped, true);
    assert.equal(harness.replacements, 1);
    assert.equal(harness.window.scrollY, 480);
    assert.equal(harness.fetchUrls.length, 1);

    const url = new URL(harness.fetchUrls[0]);
    assert.equal(url.searchParams.has('search'), false);
    assert.equal(url.searchParams.get('year'), '2026');
    assert.equal(url.searchParams.has('page'), false);
    assert.equal(harness.pushedUrls[0], harness.fetchUrls[0]);
});

test('Back and Forward restore the fragment without adding history', async () => {
    const harness = createHarness({ search: 'Alice', page: '1' });

    await harness.dispatchWindow('popstate', {});

    assert.equal(harness.replacements, 1);
    assert.equal(harness.fetchUrls[0], harness.window.location.href);
    assert.deepEqual(harness.pushedUrls, []);
});
```

- [ ] **Step 2: Run the regression test and verify RED**

Run:

```powershell
node --test tests/js/dashboard-evaluation-list.test.mjs
```

Expected: the Search and Clear tests fail against the current code. Search moves the fake
viewport from `480` to `0`; Clear either leaves `search=` in the request URL or
also moves the viewport to `0`. The Back/Forward test already passes and protects
the existing history behaviour during the fix.

- [ ] **Step 3: Add the minimal dashboard-owned Search/Clear behaviour**

In `resources/views/dashboard/partials/index-script.blade.php`, immediately
after `loadEvaluationList`, add:

```js
        const buildEvaluationSearchUrl = (form, { clearSearch = false } = {}) => {
            const url = new URL(form.action, window.location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            url.searchParams.delete('page');

            if (clearSearch || !(url.searchParams.get('search') || '').trim()) {
                url.searchParams.delete('search');
            }

            return url.toString();
        };

        document.addEventListener('click', (event) => {
            const clearButton = event.target.closest('[data-auto-search-clear]');
            if (!clearButton || !clearButton.closest('[data-evaluation-list]')) {
                return;
            }

            const form = clearButton.closest('[data-auto-search-form]');
            const input = form?.querySelector('[data-auto-search-input]');
            if (!form || !input) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            input.value = '';

            loadEvaluationList(buildEvaluationSearchUrl(form, { clearSearch: true }));
        }, true);
```

Replace the existing evaluation-list submit handler body with:

```js
        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-evaluation-list] [data-auto-search-form]');
            if (!form) {
                return;
            }

            event.preventDefault();
            loadEvaluationList(buildEvaluationSearchUrl(form));
        });
```

Do not change the shared `components/search-bar-script.blade.php`; its bubble
listener remains the fallback for pages outside the dashboard.

- [ ] **Step 4: Strengthen the server-rendered integration contract**

In `tests/Feature/DashboardTest.php`, extend
`dashboard table rows expose searchable report metadata and filter hooks` with:

```php
        ->assertSee('buildEvaluationSearchUrl', false)
        ->assertSee('event.stopImmediatePropagation()', false)
        ->assertSee('clearSearch: true', false)
        ->assertDontSee("loadEvaluationList(url.toString(), { focus: 'heading' });", false)
```

These assertions ensure the full dashboard response ships the browser-tested
handler and does not reintroduce heading focus for Search/Clear.

- [ ] **Step 5: Run focused tests and verify GREEN**

Run:

```powershell
node --test tests/js/dashboard-evaluation-list.test.mjs
php artisan test tests/Feature/DashboardTest.php
```

Expected: the two JavaScript tests pass; every `DashboardTest` case passes with
zero failures.

- [ ] **Step 6: Run the complete JavaScript suite, formatter check, PHP suite, and production build**

Run:

```powershell
npm run test:js
npm run format:check
php artisan test
npm run build
```

Expected: every command exits `0`; Node and Pest report zero failing tests;
Prettier reports all matched files use its code style; Vite completes a
production build.

- [ ] **Step 7: Review the scoped diff and commit**

Run:

```powershell
git diff --check -- resources/views/dashboard/partials/index-script.blade.php tests/js/dashboard-evaluation-list.test.mjs tests/Feature/DashboardTest.php
git diff -- resources/views/dashboard/partials/index-script.blade.php tests/js/dashboard-evaluation-list.test.mjs tests/Feature/DashboardTest.php
git add -- resources/views/dashboard/partials/index-script.blade.php tests/js/dashboard-evaluation-list.test.mjs tests/Feature/DashboardTest.php
git commit -m "fix: refresh dashboard search results in place"
```

Expected: `git diff --check` prints nothing; the displayed diff contains only
the dashboard Search/Clear handler and its regression coverage; the commit
includes exactly the three listed files.
