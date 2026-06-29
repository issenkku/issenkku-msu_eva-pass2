<?php

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

test('quality scores create view renders data hooks for dynamic controls', function () {
    $errors = new ViewErrorBag();
    $errors->put('default', new MessageBag());

    $html = view('quality-scores.create', [
        'reportDatas' => collect(),
        'users' => collect(),
        'selectedReport' => null,
        'errors' => $errors,
    ])->render();

    expect($html)
        ->toContain('data-quality-score-remove-criteria')
        ->toContain('data-quality-score-remove-user')
        ->toContain('data-quality-score-input')
        ->toContain('window.__qualityScoresCreateHooksBound')
        ->not->toContain('onclick="removeCriteria(')
        ->not->toContain('onclick="removeUser(')
        ->not->toContain('onchange="updateSubmitButton()"');
});
