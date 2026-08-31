<?php

declare(strict_types=1);

namespace Showcase\ApiIntegration\Rest;

use Showcase\ApiIntegration\DTO\CustomerData;
use Showcase\ApiIntegration\Exception\RemoteApiException;
use Showcase\ApiIntegration\Service\CustomerSyncService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class SyncController
{
    public function __construct(private readonly CustomerSyncService $service)
    {
    }

    public function registerRoutes(): void
    {
        register_rest_route('showcase/v1', '/users/(?P<id>\\d+)/sync', [
            'methods' => 'POST',
            'callback' => [$this, 'sync'],
            'permission_callback' => static fn (): bool => current_user_can('edit_users'),
            'args' => [
                'id' => [
                    'validate_callback' => static fn ($value): bool => is_numeric($value) && (int) $value > 0,
                ],
            ],
        ]);
    }

    public function sync(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user = get_user_by('id', (int) $request['id']);
        if ($user === false) {
            return new WP_Error('showcase_user_not_found', 'User not found.', ['status' => 404]);
        }

        try {
            $remoteId = $this->service->sync(new CustomerData(
                (int) $user->ID,
                (string) $user->user_email,
                (string) $user->display_name
            ));
        } catch (RemoteApiException|\InvalidArgumentException|\RuntimeException $e) {
            error_log('[showcase-api] ' . $e->getMessage());
            return new WP_Error('showcase_sync_failed', 'Customer sync failed.', ['status' => 502]);
        }

        return new WP_REST_Response([
            'success' => true,
            'remote_id' => $remoteId,
        ], 200);
    }
}
