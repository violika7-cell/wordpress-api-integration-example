<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Contracts;

interface HttpClientInterface
{
    /** @param array<string, mixed> $options */
    public function request(string $method, string $url, array $options = []): HttpResponse;
}
