<?php

use App\Support\SafeHtml;

it('converts rich text to decoded plain text', function () {
    expect(SafeHtml::plainText('<p><strong>A</strong> &amp; B</p>'))->toBe('A & B');
});

it('omits script and style content from plain text', function () {
    expect(SafeHtml::plainText('<p>A</p><script>alert(1)</script><style>.x{}</style>'))->toBe('A');
});
