<?php

namespace Benjamin\AyonConnector\Services;

use Benjamin\AyonConnector\Contracts\AyonClientInterface;
use Benjamin\AyonConnector\Exceptions\AyonSyncException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class AyonClient
 *
 * @package Benjamin\AyonConnector\Services
 */
class AyonClient implements AyonClientInterface
{
    /** @var string Base URL of the AYON API */
    protected string $baseUrl;

    /** @var string API key for authentication */
    protected string $apiKey;

    /**
     * AyonClient constructor.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(config('ayon.api_url'), '/');
        $this->apiKey = config('ayon.api_key');
    }

    /**
     * Send a request to the AYON API safely.
     *
     * @param string $method HTTP method (get, post, put, patch, delete)
     * @param string $endpoint API endpoint, starting with /
     * @param array<string, mixed> $data Request payload
     * @return Response
     *
     * @throws AyonSyncException
     */
    protected function request(string $method, string $endpoint, array $data = []): Response
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->{$method}("{$this->baseUrl}{$endpoint}", $data);

            if ($response->failed()) {
                $status = $response->status();
                $body   = $response->body();

                Log::error("AYON API request failed", [
                    'url' => "{$this->baseUrl}{$endpoint}",
                    'status' => $status,
                    'response' => $body,
                ]);

                throw new AyonSyncException("AYON API Error: HTTP {$status}");
            }

            Log::info("AYON API request succeeded", ['method' => $method, 'endpoint' => $endpoint]);

            return $response;

        } catch (Throwable $e) {
            Log::error("AYON API exception", [
                'url' => "{$this->baseUrl}{$endpoint}",
                'message' => $e->getMessage(),
            ]);

            throw new AyonSyncException("AYON API Exception: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Create a user in AYON.
     *
     * @param array<string, mixed> $userData
     * @param string $ayonName
     * @return Response
     *
     * @throws AyonSyncException
     */
    public function createUser(array $userData, string $ayonName): Response
    {
        return $this->request('put', "/api/users/{$ayonName}", $userData);
    }

    /**
     * Update a user in AYON.
     *
     * @param array<string, mixed> $userData
     * @param string $ayonName
     * @return Response
     *
     * @throws AyonSyncException
     */
    public function updateUser(array $userData, string $ayonName): Response
    {
        return $this->request('patch', "/api/users/{$ayonName}", array_merge($userData, ['active' => true]));
    }

    /**
     * Delete a user from AYON.
     *
     * @param string $ayonName
     * @return Response
     *
     * @throws AyonSyncException
     */
    public function deleteUser(string $ayonName): Response
    {
        return $this->request('delete', "/api/users/{$ayonName}");
    }

    /**
     * Deactivate a user in AYON.
     *
     * @param string $ayonName
     * @return Response
     *
     * @throws AyonSyncException
     */
    public function deactivateUser(string $ayonName): Response
    {
        return $this->request('patch', "/api/users/{$ayonName}", ['active' => false]);
    }
}
