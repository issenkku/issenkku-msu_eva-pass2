import test from 'node:test';
import assert from 'node:assert/strict';

import {
    activityEvidenceFieldName,
    activityEvidenceGroups,
    activityEntryFieldName,
    activityEntryIdsKeepOriginalOrder,
    activityHtmlHasVisibleText,
    groupActivityEntries,
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
            { id: 41, evidence_links: ['https://example.com/one'] },
            { id: 42, evidence_links: [] },
            { id: null, evidence_links: ['https://example.com/new'] },
        ]),
        [
            {
                id: 41,
                label: 'รายการ 1',
                links: ['https://example.com/one'],
            },
            {
                id: 'new-2',
                label: 'รายการ 3',
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
