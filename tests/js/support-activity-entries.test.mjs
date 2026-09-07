import test from 'node:test';
import assert from 'node:assert/strict';

import {
    activityEvidenceFieldName,
    activityEvidenceGroups,
    activityEntryFieldName,
    activityEntryIdsKeepOriginalOrder,
    activityHtmlHasVisibleText,
    groupActivityEntries,
    reconcileActivityEntryRows,
} from '../../resources/js/support-activity-entries.js';
import * as supportActivityEntries from '../../resources/js/support-activity-entries.js';

class FakeElement {
    constructor(tagName) {
        this.tagName = tagName;
        this.children = [];
        this.className = '';
        this.dataset = {};
        this.textContent = '';
    }

    append(...children) {
        this.children.push(...children);
    }

    appendChild(child) {
        this.children.push(child);
    }

    replaceChildren(...children) {
        this.children = children;
    }
}

class FakeRow {
    constructor(name) {
        this.name = name;
        this.removed = false;
    }

    remove() {
        this.removed = true;
    }
}

test('builds nested support activity entry field names after add or delete', () => {
    assert.equal(
        activityEntryFieldName(7, 2, 'content'),
        'support_list[7][activity_entries][2][content]',
    );
    assert.equal(
        activityEntryFieldName(7, 0, 'support_indicator_item_id'),
        'support_list[7][activity_entries][0][support_indicator_item_id]',
    );
    assert.equal(
        activityEvidenceFieldName(7, 2),
        'support_list[7][activity_entries][2][evidence_links][]',
    );
});

test('groups projects under each ordered indicator item without duplication', () => {
    const groups = groupActivityEntries(
        [{ id: 11, code: '2.1' }, { id: 12, code: '2.2' }, { id: 13, code: '2.3' }],
        [
            { id: 41, support_indicator_item_id: 11 },
            { id: 42, support_indicator_item_id: 11 },
            { id: 43, support_indicator_item_id: 12 },
        ],
    );

    assert.deepEqual(groups.map((group) => group.activity_entries.map((entry) => entry.id)), [
        [41, 42],
        [43],
        [],
    ]);
});

test('keeps evidence grouped by its owning activity entry', () => {
    assert.deepEqual(
        activityEvidenceGroups([
            { id: 41, content: '<p>กิจกรรมหนึ่ง</p>', evidence_links: ['https://example.com/one'] },
            { id: 42, content: '<p>กิจกรรมสอง</p>', evidence_links: [] },
            { id: null, content: '<p>กิจกรรมใหม่</p>', evidence_links: ['https://example.com/new'] },
        ]),
        [
            {
                id: 41,
                label: 'รายการ 1 · กิจกรรมหนึ่ง',
                links: ['https://example.com/one'],
            },
            {
                id: 'new-2',
                label: 'รายการ 3 · กิจกรรมใหม่',
                links: ['https://example.com/new'],
            },
        ],
    );
});

test('rejects visually empty rich text while accepting visible content', () => {
    assert.equal(activityHtmlHasVisibleText('<p><br></p>'), false);
    assert.equal(activityHtmlHasVisibleText('<div>&nbsp;</div>'), false);
    assert.equal(activityHtmlHasVisibleText('<p>โครงการประจำเดือน</p>'), true);
});

test('detects reviewer attempts to create delete or reorder entries', () => {
    assert.equal(activityEntryIdsKeepOriginalOrder([1, 2], [1, 2]), true);
    assert.equal(activityEntryIdsKeepOriginalOrder([2, 1], [1, 2]), false);
    assert.equal(activityEntryIdsKeepOriginalOrder([1], [1, 2]), false);
    assert.equal(activityEntryIdsKeepOriginalOrder([1, 2, null], [1, 2]), false);
});

test('renders live activity updates with the same circular numbering as the server view', () => {
    const previousDocument = globalThis.document;
    globalThis.document = {
        createElement: (tagName) => new FakeElement(tagName),
    };

    try {
        const target = new FakeElement('div');

        supportActivityEntries.renderActivityEntryList(target, [
            { html: '<p>โครงการแรก</p>' },
            { html: '<p>โครงการที่สอง</p>' },
        ]);

        const list = target.children[0];
        assert.equal(list.tagName, 'div');
        assert.equal(list.className, 'space-y-2');
        assert.equal(list.children.length, 2);
        assert.equal(list.children[0].children[0].textContent, '1');
        assert.equal(list.children[0].children[1].textContent, 'โครงการแรก');
        assert.match(list.children[0].children[0].className, /rounded-full/);
        assert.doesNotMatch(list.className, /list-decimal/);
    } finally {
        globalThis.document = previousDocument;
    }
});

test('reconciles live desktop rows when entries are added and removed', () => {
    const base = new FakeRow('base');
    const created = [];
    const expanded = reconcileActivityEntryRows([base], 2, (index) => {
        const row = new FakeRow(`row-${index}`);
        created.push(row);
        return row;
    });

    assert.equal(expanded.rows.length, 2);
    assert.equal(expanded.rowCount, 2);
    assert.equal(expanded.hasEntries, true);
    assert.deepEqual(expanded.rows.map((row) => row.name), ['base', 'row-1']);

    const reduced = reconcileActivityEntryRows(expanded.rows, 0, () => {
        throw new Error('must not create a row while reducing');
    });

    assert.equal(reduced.rows.length, 1);
    assert.equal(reduced.rowCount, 1);
    assert.equal(reduced.hasEntries, false);
    assert.equal(created[0].removed, true);
});
