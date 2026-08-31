<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Contracts;

interface CustomerRepositoryInterface
{
    public function findRemoteId(int $userId): ?string;

    public function saveRemoteId(int $userId, string $remoteId): void;
}
