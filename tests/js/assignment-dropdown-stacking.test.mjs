import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const styles = readFileSync(
    new URL('../../resources/views/assignment-data/partials/index-styles.blade.php', import.meta.url),
    'utf8',
);
const rowTemplate = readFileSync(
    new URL('../../resources/views/assignment-data/partials/index-table-row.blade.php', import.meta.url),
    'utf8',
);

test('raises the assignment row while its action dropdown is open', () => {
    assert.match(rowTemplate, /<tr class="assignment-data-row /);

    const openRowRule = styles.match(
        /\.assignment-data-row:has\(\.dropdown-menu\.show\)\s*\{([^}]*)\}/,
    );

    assert.ok(openRowRule, 'expected an open-dropdown row stacking rule');
    assert.match(openRowRule[1], /position:\s*relative/);
    assert.match(openRowRule[1], /z-index:\s*[1-9]\d*/);
});
