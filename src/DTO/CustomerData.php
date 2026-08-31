<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\DTO;

final class CustomerData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $displayName
    ) {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User ID must be positive.');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('A valid email is required.');
        }
    }

    /** @return array<string, string> */
    public function toApiPayload(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->displayName,
        ];
    }
}
