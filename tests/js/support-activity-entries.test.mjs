import test from 'node:test';
import assert from 'node:assert/strict';

import {
    activityEntryFieldName,
    activityEntryIdsKeepOriginalOrder,
    activityHtmlHasVisibleText,
} from '../../resources/js/support-activity-entries.js';

test('builds nested support activity entry field names after add or delete', () => {
    assert.equal(
        activityEntryFieldName(7, 2, 'content'),
        'support_list[7][activity_entries][2][content]',
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
