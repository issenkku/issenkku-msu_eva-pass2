<?php

use Tests\TestCase;

uses(TestCase::class);

test('test environment uses an isolated sqlite database', function () {
    expect(app()->environment())->toBe('testing')
        ->and(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toEndWith('database/testing.sqlite')
        ->and(config('database.connections.sqlite.database'))->not->toContain('msu_eva');
});
