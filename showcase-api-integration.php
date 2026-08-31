<?php
/**
 * Plugin Name: Showcase API Integration
 * Description: Synthetic example of a maintainable third-party API integration.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Author: Victoria Bondar
 * License: MIT
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_readable($autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>Showcase API Integration requires Composer dependencies.</p></div>';
    });
    return;
}

require $autoload;

use Showcase\ApiIntegration\Api\ExampleClient;
use Showcase\ApiIntegration\Http\WpHttpClient;
use Showcase\ApiIntegration\Repository\UserMetaCustomerRepository;
use Showcase\ApiIntegration\Rest\SyncController;
use Showcase\ApiIntegration\Service\CustomerSyncService;

add_action('rest_api_init', static function (): void {
    $http = new WpHttpClient();
    $client = new ExampleClient(
        $http,
        defined('SHOWCASE_API_BASE_URL') ? (string) SHOWCASE_API_BASE_URL : '',
        defined('SHOWCASE_API_TOKEN') ? (string) SHOWCASE_API_TOKEN : ''
    );
    $repository = new UserMetaCustomerRepository();
    $service = new CustomerSyncService($client, $repository);

    (new SyncController($service))->registerRoutes();
});
