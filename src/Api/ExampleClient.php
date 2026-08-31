<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Api;

use Showcase\ApiIntegration\Contracts\CustomerApiInterface;
use Showcase\ApiIntegration\Contracts\HttpClientInterface;
use Showcase\ApiIntegration\DTO\CustomerData;
use Showcase\ApiIntegration\Exception\RemoteApiException;

final class ExampleClient implements CustomerApiInterface
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly string $baseUrl,
        private readonly string $token
    ) {
        if ($baseUrl === '' || $token === '') {
            throw new \InvalidArgumentException('API configuration is incomplete.');
        }
    }

    public function createCustomer(CustomerData $customer): string
    {
        $data = $this->send('POST', '/customers', $customer->toApiPayload(), [
            'Idempotency-Key' => 'wp-user-' . $customer->userId,
        ]);
        $id = $data['id'] ?? null;

        if (!is_string($id) || $id === '') {
            throw new RemoteApiException('Remote API returned an invalid customer identifier.');
        }

        return $id;
    }

    public function updateCustomer(string $remoteId, CustomerData $customer): void
    {
        $this->send('PATCH', '/customers/' . rawurlencode($remoteId), $customer->toApiPayload());
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $payload, array $extraHeaders = []): array
    {
        $response = $this->http->request($method, rtrim($this->baseUrl, '/') . $path, [
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ], $extraHeaders),
            'body' => json_encode($payload, JSON_THROW_ON_ERROR),
        ]);

        if ($response->status < 200 || $response->status >= 300) {
            throw new RemoteApiException(sprintf('Remote API returned HTTP %d.', $response->status));
        }

        if ($response->body === '') {
            return [];
        }

        try {
            $decoded = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RemoteApiException('Remote API returned malformed JSON.', 0, $e);
        }

        if (!is_array($decoded)) {
            throw new RemoteApiException('Remote API returned an unexpected response format.');
        }

        return $decoded;
    }
}
