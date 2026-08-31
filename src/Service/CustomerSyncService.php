<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Service;

use Showcase\ApiIntegration\Contracts\CustomerApiInterface;
use Showcase\ApiIntegration\Contracts\CustomerRepositoryInterface;
use Showcase\ApiIntegration\DTO\CustomerData;

final class CustomerSyncService
{
    public function __construct(
        private readonly CustomerApiInterface $client,
        private readonly CustomerRepositoryInterface $repository
    ) {
    }

    public function sync(CustomerData $customer): string
    {
        $remoteId = $this->repository->findRemoteId($customer->userId);

        if ($remoteId === null) {
            $remoteId = $this->client->createCustomer($customer);
            $this->repository->saveRemoteId($customer->userId, $remoteId);
            return $remoteId;
        }

        $this->client->updateCustomer($remoteId, $customer);
        return $remoteId;
    }
}
