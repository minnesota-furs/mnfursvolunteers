<?php

use App\Models\ApplicationSetting;
use App\Services\ConcatService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

beforeEach(function () {
    ApplicationSetting::set('concat_api_base_url', 'https://fm-test.concat.app', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_id', 'client-id-123', 'string', null, 'integrations');
    ApplicationSetting::set('concat_client_secret', 'super-secret', 'encrypted', null, 'integrations');
});

function makeConcatServiceWithMockedHttp(array $responses): array
{
    // An ArrayObject (not a plain array) so the history middleware's mutations
    // stay visible to the caller after this function returns — plain arrays
    // are copied by value on return, which would leave the caller with an
    // empty snapshot taken before any requests were made.
    $container = new ArrayObject;
    $history = Middleware::history($container);

    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $stack->push($history);

    $client = new Client(['handler' => $stack]);

    return [new ConcatService($client), $container];
}

it('does not reuse a token past the lifetime ConCat actually reported', function () {
    [$service, $requests] = makeConcatServiceWithMockedHttp([
        new Response(200, [], json_encode(['access_token' => 'token-1', 'expires_in' => 120])),
        new Response(200, [], json_encode(['hasMore' => false, 'data' => []])),
        new Response(200, [], json_encode(['access_token' => 'token-2', 'expires_in' => 120])),
        new Response(200, [], json_encode(['hasMore' => false, 'data' => []])),
    ]);

    $service->getRoles();
    expect($requests)->toHaveCount(2);

    // Old bug: token was cached for a hardcoded 50 minutes no matter what ConCat
    // said. 90s from now is past this token's real (120s - 60s buffer = 60s) life,
    // but nowhere near the old hardcoded cache duration.
    $this->travel(90)->seconds();

    $service->getRoles();
    expect($requests)->toHaveCount(4);
    expect((string) $requests[2]['request']->getUri())->toContain('/api/oauth/token');
});

it('retries once with a fresh token when ConCat responds 401 to an expired token', function () {
    [$service, $requests] = makeConcatServiceWithMockedHttp([
        new Response(200, [], json_encode(['access_token' => 'stale-token', 'expires_in' => 3600])),
        new Response(401, [], json_encode(['errors' => ['authentication' => ['code' => 2001, 'message' => 'Token expired.']]])),
        new Response(200, [], json_encode(['access_token' => 'fresh-token', 'expires_in' => 3600])),
        new Response(200, [], json_encode(['hasMore' => false, 'data' => [['id' => 'role-1', 'name' => 'Staff']]])),
    ]);

    $roles = $service->getRoles();

    expect($roles)->toBe([['id' => 'role-1', 'name' => 'Staff']]);
    expect($requests)->toHaveCount(4);
    expect($requests[3]['request']->getHeaderLine('Authorization'))->toBe('Bearer fresh-token');
});
