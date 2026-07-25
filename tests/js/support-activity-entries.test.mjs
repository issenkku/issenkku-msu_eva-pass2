import test from 'node:test';
import assert from 'node:assert/strict';

import {
    activityEvidenceFieldName,
    activityEntryFieldName,
    activityEntryIdsKeepOriginalOrder,
    activityHtmlHasVisibleText,
    groupActivityEntries,
} from '../../resources/js/support-activity-entries.js';

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
