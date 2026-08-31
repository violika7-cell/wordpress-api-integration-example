<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Tests\Fakes;

use Showcase\ApiIntegration\Contracts\CustomerRepositoryInterface;

final class InMemoryCustomerRepository implements CustomerRepositoryInterface
{
    /** @var array<int,string> */
    private array $items = [];

    public function findRemoteId(int $userId): ?string
    {
        return $this->items[$userId] ?? null;
    }

    public function saveRemoteId(int $userId, string $remoteId): void
    {
        $this->items[$userId] = $remoteId;
    }
}
