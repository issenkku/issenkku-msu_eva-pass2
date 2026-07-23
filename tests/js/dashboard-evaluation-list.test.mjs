import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const scriptSource = readFileSync(new URL('../../resources/views/dashboard/partials/index-script.blade.php', import.meta.url), 'utf8')
    .match(/<script>\s*([\s\S]*?)<\/script>\s*$/)[1]
    .replace('const overviewChart = @json($overviewChart);', 'const overviewChart = {"id":"overallCompletionChart"};');

const clearScriptSource = readFileSync(new URL('../../resources/views/components/search-bar-script.blade.php', import.meta.url), 'utf8').match(
    /<script>\s*([\s\S]*?)<\/script>\s*$/,
)[1];

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

        eventPhases: for (const capture of [true, false]) {
            for (const entry of listeners.filter((item) => item.capture === capture)) {
                if (event.immediatePropagationStopped) {
                    break eventPhases;
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
            return submittedForm.fields.map(([key, value]) => [key, key === 'search' ? input.value : value]);
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
