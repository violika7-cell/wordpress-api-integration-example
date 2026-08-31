<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Repository;

use Showcase\ApiIntegration\Contracts\CustomerRepositoryInterface;

final class UserMetaCustomerRepository implements CustomerRepositoryInterface
{
    private const META_KEY = '_showcase_remote_customer_id';

    public function findRemoteId(int $userId): ?string
    {
        $value = get_user_meta($userId, self::META_KEY, true);
        return is_string($value) && $value !== '' ? $value : null;
    }

    public function saveRemoteId(int $userId, string $remoteId): void
    {
        $result = update_user_meta($userId, self::META_KEY, sanitize_text_field($remoteId));

        if ($result === false && get_user_meta($userId, self::META_KEY, true) !== $remoteId) {
            throw new \RuntimeException('Unable to persist remote customer mapping.');
        }
    }
}
