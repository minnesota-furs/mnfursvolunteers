<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class ConcatService
{
    private ?string $baseUrl;

    private ?string $clientId;

    private ?string $clientSecret;

    public function __construct(private Client $http = new Client)
    {
        $this->baseUrl = rtrim((string) ApplicationSetting::get('concat_api_base_url'), '/') ?: null;
        $this->clientId = ApplicationSetting::get('concat_client_id');
        $this->clientSecret = ApplicationSetting::get('concat_client_secret');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    /**
     * Verify the configured credentials work by requesting a token and making
     * a cheap authenticated call.
     */
    public function testConnection(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return $this->getAccessToken() !== null && $this->getRoles() !== null;
    }

    /**
     * @return array<int, array{id: string, name: string}>|null
     */
    public function getRoles(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $roles = [];
            $nextPage = null;

            do {
                $query = ['limit' => 1000];
                if ($nextPage) {
                    $query['nextPage'] = $nextPage;
                }

                $response = $this->request('GET', '/api/v0/roles', ['query' => $query]);
                $roles = array_merge($roles, $response['data'] ?? []);
                $nextPage = ($response['hasMore'] ?? false) ? ($response['nextPage'] ?? null) : null;
            } while ($nextPage);

            return $roles;
        } catch (\Throwable $e) {
            \Log::warning('ConCat getRoles failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Look up a ConCat user by email. Returns the first match, or null if
     * nobody in ConCat has that email.
     */
    public function findUserByEmail(string $email): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->request('POST', '/api/v0/users/search', [
                'json' => ['filter' => ['email' => $email], 'limit' => 1],
            ]);

            return $response['data'][0] ?? null;
        } catch (\Throwable $e) {
            \Log::warning('ConCat findUserByEmail failed', ['email' => $email, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Look up a ConCat user by their ConCat user ID. Used for manually
     * linking an account whose ConCat email doesn't match this app's records.
     */
    public function getUserById(string $concatUserId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            return $this->request('GET', "/api/v0/users/{$concatUserId}");
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            \Log::warning('ConCat getUserById failed', ['concat_user_id' => $concatUserId, 'error' => $e->getMessage()]);

            return null;
        } catch (\Throwable $e) {
            \Log::warning('ConCat getUserById failed', ['concat_user_id' => $concatUserId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Look up registrations for a batch of ConCat user IDs (chunked to
     * ConCat's 100-per-search limit). Used to report which linked staff have
     * an event registration on ConCat.
     *
     * @param  string[]  $concatUserIds
     * @return array<int, array{uuid: string, user: array{id: string}}>
     */
    public function searchRegistrationsByUserIds(array $concatUserIds): array
    {
        if (! $this->isConfigured() || empty($concatUserIds)) {
            return [];
        }

        try {
            $registrations = [];

            foreach (array_chunk(array_values(array_unique($concatUserIds)), 100) as $chunk) {
                $nextPage = null;

                do {
                    $body = ['filter' => ['userIds' => $chunk], 'limit' => 100];
                    if ($nextPage) {
                        $body['nextPage'] = $nextPage;
                    }

                    $response = $this->request('POST', '/api/v0/registration/search', ['json' => $body]);
                    $registrations = array_merge($registrations, $response['data'] ?? []);
                    $nextPage = ($response['hasMore'] ?? false) ? ($response['nextPage'] ?? null) : null;
                } while ($nextPage);
            }

            return $registrations;
        } catch (\Throwable $e) {
            \Log::warning('ConCat searchRegistrationsByUserIds failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function grantRole(string $concatUserId, string $roleId, string $scope = 'convention'): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $this->request('PUT', "/api/v0/users/{$concatUserId}/roles/{$roleId}", [
                'json' => ['scope' => $scope],
            ]);

            return true;
        } catch (\Throwable $e) {
            \Log::warning('ConCat grantRole failed', [
                'concat_user_id' => $concatUserId,
                'role_id' => $roleId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Revoke a role from a ConCat user. Treats "already gone" (404) as
     * success. A 400 means the role is managed by an automated process on
     * ConCat's side and can't be removed via the API — logged, not fatal.
     */
    public function revokeRole(string $concatUserId, string $roleId): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $this->request('DELETE', "/api/v0/users/{$concatUserId}/roles/{$roleId}");

            return true;
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();

            if ($status === 404) {
                return true;
            }

            \Log::warning('ConCat revokeRole could not be completed', [
                'concat_user_id' => $concatUserId,
                'role_id' => $roleId,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Throwable $e) {
            \Log::warning('ConCat revokeRole failed', [
                'concat_user_id' => $concatUserId,
                'role_id' => $roleId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function tokenCacheKey(): string
    {
        return 'concat_access_token_'.md5($this->baseUrl.$this->clientId);
    }

    private function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $token = Cache::get($this->tokenCacheKey());

        return $token ?: $this->requestAccessToken();
    }

    /**
     * Request a fresh token from ConCat and cache it for however long it's
     * actually valid (via `expires_in`), so the cache doesn't outlive the
     * token itself — reusing an expired token surfaces as confusing 401s on
     * every subsequent call (e.g. "ConCat returned no roles" on the Manage
     * Concat page, when the real problem is an expired cached token).
     */
    private function requestAccessToken(): ?string
    {
        try {
            $response = $this->http->post("{$this->baseUrl}/api/oauth/token", [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'user:read user:roles:update registration:read',
                ],
                'timeout' => 10,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $token = $body['access_token'] ?? null;

            if ($token) {
                // Leave a 60s safety buffer before the token's real expiry; fall back to
                // a conservative 5 minutes if ConCat doesn't report expires_in.
                $expiresIn = (int) ($body['expires_in'] ?? 300);
                Cache::put($this->tokenCacheKey(), $token, max(60, $expiresIn - 60));
            }

            return $token;
        } catch (GuzzleException $e) {
            \Log::warning('ConCat token request failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @throws GuzzleException|\RuntimeException
     */
    private function request(string $method, string $path, array $options = [], bool $retryOn401 = true): array
    {
        $token = $this->getAccessToken();

        if (! $token) {
            throw new \RuntimeException('ConCat access token unavailable.');
        }

        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $options['timeout'] = 10;

        try {
            $response = $this->http->request($method, "{$this->baseUrl}{$path}", $options);
        } catch (ClientException $e) {
            // The cached token may have outlived ConCat's actual expiry (clock skew,
            // an inaccurate expires_in, etc.) — forget it and retry once with a fresh one.
            if ($retryOn401 && $e->getResponse()->getStatusCode() === 401) {
                Cache::forget($this->tokenCacheKey());

                return $this->request($method, $path, $options, retryOn401: false);
            }

            throw $e;
        }

        return json_decode($response->getBody()->getContents(), true) ?? [];
    }
}
