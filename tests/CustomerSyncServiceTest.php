<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Tests;

use PHPUnit\Framework\TestCase;
use Showcase\ApiIntegration\Api\ExampleClient;
use Showcase\ApiIntegration\Contracts\HttpResponse;
use Showcase\ApiIntegration\DTO\CustomerData;
use Showcase\ApiIntegration\Service\CustomerSyncService;
use Showcase\ApiIntegration\Tests\Fakes\FakeHttpClient;
use Showcase\ApiIntegration\Tests\Fakes\InMemoryCustomerRepository;

final class CustomerSyncServiceTest extends TestCase
{
    public function testCreatesAndPersistsRemoteCustomerWhenMappingDoesNotExist(): void
    {
        $http = new FakeHttpClient(new HttpResponse(201, '{"id":"cus_123"}'));
        $repo = new InMemoryCustomerRepository();
        $service = new CustomerSyncService(
            new ExampleClient($http, 'https://api.example.test', 'test-token'),
            $repo
        );

        $id = $service->sync(new CustomerData(42, 'user@example.test', 'Test User'));

        self::assertSame('cus_123', $id);
        self::assertSame('cus_123', $repo->findRemoteId(42));
        self::assertSame('POST', $http->requests[0]['method']);
    }

    public function testUpdatesCustomerWhenMappingAlreadyExists(): void
    {
        $http = new FakeHttpClient(new HttpResponse(200, '{}'));
        $repo = new InMemoryCustomerRepository();
        $repo->saveRemoteId(42, 'cus_existing');

        $service = new CustomerSyncService(
            new ExampleClient($http, 'https://api.example.test', 'test-token'),
            $repo
        );

        $id = $service->sync(new CustomerData(42, 'user@example.test', 'Updated User'));

        self::assertSame('cus_existing', $id);
        self::assertSame('PATCH', $http->requests[0]['method']);
        self::assertStringContainsString('/customers/cus_existing', $http->requests[0]['url']);
    }
}
