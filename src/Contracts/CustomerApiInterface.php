<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Contracts;

use Showcase\ApiIntegration\DTO\CustomerData;

interface CustomerApiInterface
{
    public function createCustomer(CustomerData $customer): string;
    public function updateCustomer(string $remoteId, CustomerData $customer): void;
}
