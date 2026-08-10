<?php

use App\Support\FriendlyErrorPage;
use Symfony\Component\HttpFoundation\Response;

test('a follow-up error response keeps its metadata but sends no duplicate body', function () {
    $response = new Response('duplicate error document', 500, [
        'X-Request-Id' => 'request-123',
    ]);

    $result = FriendlyErrorPage::withoutContent($response);

    expect($result)->toBe($response)
        ->and($result->getContent())->toBeEmpty()
        ->and($result->getStatusCode())->toBe(500)
        ->and($result->headers->get('X-Request-Id'))->toBe('request-123');
});
