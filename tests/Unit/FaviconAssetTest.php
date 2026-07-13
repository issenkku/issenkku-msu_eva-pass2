<?php

test('public favicon does not use the Laravel starter icon', function () {
    $faviconPath = dirname(__DIR__, 2).'/public/favicon.ico';
    $laravelStarterIconHash = '4606a56e6ef3f5ec39201497f57069d5457ce9cea25227134d0ba378788e9070';

    expect(is_file($faviconPath))->toBeTrue();
    expect(hash_file('sha256', $faviconPath))->not->toBe($laravelStarterIconHash);
});
