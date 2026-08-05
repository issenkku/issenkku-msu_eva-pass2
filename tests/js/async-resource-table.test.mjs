import assert from 'node:assert/strict';
import test from 'node:test';

const {
    applyResourceMutation,
    refreshTableRegion,
} = await import('../../resources/js/async-resource-table.js');

function row(id) {
    return {
        dataset: { resourceId: String(id) },
        removed: false,
        remove() {
            this.removed = true;
        },
        replaceWith(replacement) {
            this.replacement = replacement;
        },
    };
}

function tableDocument(existingRows = []) {
    const rows = new Map(existingRows.map((item) => [item.dataset.resourceId, item]));
    const appended = [];
    const container = { append: (item) => appended.push(item) };

    return {
        appended,
        createElement(name) {
            assert.equal(name, 'template');
            return {
                content: {},
                set innerHTML(value) {
                    this.content.firstElementChild = { html: value.trim() };
                },
            };
        },
        querySelector(selector) {
            const id = selector.match(/data-resource-id="([^"]+)"/)?.[1];
            if (id) return rows.get(id) || null;
            if (selector === '[data-resource-rows]') return container;
            return null;
        },
        rows,
    };
}

test('applyResourceMutation replaces an existing row with the server fragment', () => {
    const existing = row(7);
    const documentRef = tableDocument([existing]);

    applyResourceMutation(documentRef, {
        html: { row: '<tr data-resource-row data-resource-id="7">updated</tr>' },
        state: { id: 7 },
        success: true,
    });

    assert.equal(existing.replacement.html, '<tr data-resource-row data-resource-id="7">updated</tr>');
    assert.equal(documentRef.appended.length, 0);
});

test('applyResourceMutation appends a new row and removes every deleted id', () => {
    const deletedOne = row(2);
    const deletedTwo = row(3);
    const documentRef = tableDocument([deletedOne, deletedTwo]);

    applyResourceMutation(documentRef, {
        html: { row: '<tr data-resource-row data-resource-id="9">new</tr>' },
        state: { deleted_ids: [2, 3], id: 9 },
        success: true,
    });

    assert.equal(documentRef.appended[0].html, '<tr data-resource-row data-resource-id="9">new</tr>');
    assert.equal(deletedOne.removed, true);
    assert.equal(deletedTwo.removed, true);
});

test('applyResourceMutation rejects a row contract without an id before changing the DOM', () => {
    const documentRef = tableDocument();

    assert.throws(
        () => applyResourceMutation(documentRef, { html: { row: '<tr>bad</tr>' }, state: {}, success: true }),
        /resource id/i,
    );
    assert.equal(documentRef.appended.length, 0);
});

test('refreshTableRegion replaces only the requested fragment', async () => {
    const originalParser = globalThis.DOMParser;
    const replacement = { id: 'replacement' };
    const current = {
        replaceWith(value) {
            this.replacement = value;
        },
    };
    globalThis.DOMParser = class {
        parseFromString(html, type) {
            assert.equal(html, '<main>server page</main>');
            assert.equal(type, 'text/html');
            return { querySelector: () => replacement };
        }
    };

    try {
        await refreshTableRegion(
            '/departments?page=2&search=science',
            '[data-async-table-region]',
            async (url, options) => {
                assert.equal(url, '/departments?page=2&search=science');
                assert.equal(options.headers['X-Requested-With'], 'XMLHttpRequest');
                return { ok: true, text: async () => '<main>server page</main>' };
            },
            { querySelector: () => current },
        );
    } finally {
        globalThis.DOMParser = originalParser;
    }

    assert.equal(current.replacement, replacement);
});

test('refreshTableRegion leaves the current fragment untouched after a failed response', async () => {
    const current = { replacement: null };

    await assert.rejects(
        refreshTableRegion(
            '/positions?page=1',
            '[data-async-table-region]',
            async () => ({ ok: false, status: 500 }),
            { querySelector: () => current },
        ),
        /500/,
    );
    assert.equal(current.replacement, null);
});
