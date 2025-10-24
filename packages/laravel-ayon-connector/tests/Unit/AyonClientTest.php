<?php

namespace Benjamin\AyonConnector\Tests\Unit;

use Benjamin\AyonConnector\Exceptions\AyonSyncException;
use Benjamin\AyonConnector\Services\AyonClient;
use Benjamin\AyonConnector\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AyonClientTest extends TestCase
{
    private AyonClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new AyonClient();
    }

    public function test_it_creates_a_user_successfully(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->createUser(['name' => 'John'], 'john');

        $this->assertSame(200, $response->status());
        $this->assertTrue($response->json('success'));
    }

    public function test_it_updates_a_user_successfully(): void
    {
        Http::fake([
            '*' => Http::response(['updated' => true], 200),
        ]);

        $response = $this->client->updateUser(['name' => 'John'], 'john');

        $this->assertSame(200, $response->status());
        $this->assertTrue($response->json('updated'));
    }

    public function test_it_deletes_a_user_successfully(): void
    {
        Http::fake([
            '*' => Http::response(['deleted' => true], 200),
        ]);

        $response = $this->client->deleteUser('john');

        $this->assertSame(200, $response->status());
        $this->assertTrue($response->json('deleted'));
    }

    public function test_it_deactivates_a_user_successfully(): void
    {
        Http::fake([
            '*' => Http::response(['deactivated' => true], 200),
        ]);

        $response = $this->client->deactivateUser('john');

        $this->assertSame(200, $response->status());
        $this->assertTrue($response->json('deactivated'));
    }

    public function test_it_throws_exception_on_failed_request(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'bad request'], 400),
        ]);

        $this->expectException(AyonSyncException::class);
        $this->expectExceptionMessage('AYON API Error: HTTP 400');

        $this->client->createUser(['name' => 'John'], 'john');
    }

    public function test_it_logs_exception_when_http_client_throws(): void
    {
        Http::fake([
            '*' => fn() => throw new \Exception('Network error'),
        ]);

        Log::spy();

        $this->expectException(AyonSyncException::class);
        $this->expectExceptionMessage('AYON API Exception: Network error');

        try {
            $this->client->createUser(['name' => 'John'], 'john');
        } catch (AyonSyncException $e) {
            Log::shouldHaveReceived('error')
                ->once()
                ->withArgs(fn($message, $context) =>
                    str_contains($message, 'AYON API exception')
                    && str_contains($context['message'], 'Network error')
                );
            throw $e;
        }
    }
}
