<?php

use App\Cloud;

it('adds a scheme to the base URL when one is missing', function (string $baseUrl, string $expected) {
    config(['cloud.base_url' => $baseUrl]);

    expect(Cloud::baseUrl())->toBe($expected);
})->with([
    'no scheme' => ['cloud.ugarit.test', 'https://cloud.ugarit.test'],
    'https' => ['https://cloud.ugarit.test', 'https://cloud.ugarit.test'],
    'http' => ['http://cloud.ugarit.test', 'http://cloud.ugarit.test'],
    'no scheme with port' => ['cloud.ugarit.test:8080', 'https://cloud.ugarit.test:8080'],
    'default' => ['https://cloud.ugarit.com', 'https://cloud.ugarit.com'],
]);
