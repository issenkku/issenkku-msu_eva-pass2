<?php

use App\Support\SafeHtml;

it('converts rich text to decoded plain text', function () {
    expect(SafeHtml::plainText('<p><strong>A</strong> &amp; B</p>'))->toBe('A & B');
});
