<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Http;

use Showcase\ApiIntegration\Contracts\HttpClientInterface;
use Showcase\ApiIntegration\Contracts\HttpResponse;
use Showcase\ApiIntegration\Exception\RemoteApiException;

final class WpHttpClient implements HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): HttpResponse
    {
        $response = wp_remote_request($url, array_merge([
            'method' => strtoupper($method),
            'timeout' => 10,
        ], $options));

        if (is_wp_error($response)) {
            throw new RemoteApiException('Remote API transport failure.');
        }

        return new HttpResponse(
            (int) wp_remote_retrieve_response_code($response),
            (string) wp_remote_retrieve_body($response)
        );
    }
}
