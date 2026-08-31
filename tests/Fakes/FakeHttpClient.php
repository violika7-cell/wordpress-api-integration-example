<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Tests\Fakes;

use Showcase\ApiIntegration\Contracts\HttpClientInterface;
use Showcase\ApiIntegration\Contracts\HttpResponse;

final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<array{method:string,url:string,options:array<string,mixed>}> */
    public array $requests = [];

    public function __construct(private HttpResponse $response)
    {
    }

    public function request(string $method, string $url, array $options = []): HttpResponse
    {
        $this->requests[] = compact('method', 'url', 'options');
        return $this->response;
    }
}
