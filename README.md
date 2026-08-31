# WordPress API Integration Example

[![CI](https://github.com/violika7-cell/wordpress-api-integration-example/actions/workflows/ci.yml/badge.svg)](https://github.com/violika7-cell/wordpress-api-integration-example/actions/workflows/ci.yml)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777bb4)

A small, production-minded WordPress plugin that demonstrates how I structure a maintainable third-party REST API integration without putting business logic into theme files or procedural callbacks.

> **Showcase note:** this repository contains synthetic demo code only. It does not contain client code, credentials, production URLs, or proprietary business logic.

## Problem

A WordPress application needs to synchronize a local user with an external customer service. The integration should be testable, observable, secure, and resilient to remote API failures.

A common implementation would place `wp_remote_post()` directly inside a hook and mix validation, HTTP transport, mapping, persistence, and error handling in one function. That works initially, but it becomes difficult to test and risky to extend.

This example separates those concerns.

## Architecture

```text
REST endpoint / WordPress event
        |
        v
CustomerSyncService
   |           |
   v           v
ExampleClient  CustomerRepository
   |           |
   v           v
HttpClient     WordPress user meta
```

Key components:

- **CustomerSyncService** — application workflow and orchestration.
- **CustomerApiInterface / ExampleClient** — remote API boundary and concrete adapter.
- **HttpClientInterface** — transport abstraction; production uses WordPress HTTP API.
- **CustomerRepositoryInterface** — persistence abstraction.
- **UserMetaCustomerRepository** — WordPress adapter using user meta.
- **SyncController** — authenticated REST endpoint for manually triggering a sync.

## Why this structure

- Business logic is independent from the transport layer.
- Remote API code can be unit tested without WordPress or real HTTP requests.
- Persistence can be replaced without rewriting the service.
- Errors are normalized before reaching controllers/UI.
- Credentials never appear in source code.
- The service can later be triggered by cron, webhooks, admin actions, or queues.

## Installation

Requirements:

- PHP 8.1+
- WordPress 6.x+
- Composer for development/testing

```bash
composer install
```

Copy the repository into:

```text
wp-content/plugins/showcase-api-integration
```

Activate **Showcase API Integration** in WordPress.

Configure constants outside version control, for example in `wp-config.php`:

```php
define('SHOWCASE_API_BASE_URL', 'https://api.example.test');
define('SHOWCASE_API_TOKEN', 'replace-with-a-secret-from-your-environment');
```

## Example request

With an authenticated WordPress session and REST nonce:

```http
POST /wp-json/showcase/v1/users/42/sync
X-WP-Nonce: <nonce>
```

The controller checks capability before the service is called.

## Decisions

### Dependency injection instead of globals

The plugin bootstrap wires concrete WordPress adapters to application services. The core service receives interfaces rather than calling global APIs directly.

### WordPress HTTP API behind an adapter

`wp_remote_request()` remains useful and production-proven, but wrapping it gives the application a clean boundary and makes tests deterministic.

### Explicit DTOs

`CustomerData` carries validated data between layers. This avoids passing loosely structured arrays through the application.

### Idempotent local persistence

The remote customer ID is stored locally and reused on subsequent updates. Customer creation also sends a deterministic idempotency key, so a retry after a local persistence failure does not need to create a duplicate remote customer when the upstream API supports idempotency.

## Error handling

Remote failures are converted into explicit exceptions:

- transport/network failure;
- malformed JSON;
- non-2xx response;
- missing required response fields.

The REST layer converts application exceptions into safe API responses while logging technical context server-side.

No remote response body containing potentially sensitive data is returned directly to the browser.

## Security considerations

- No API secrets in the repository.
- Capability checks on REST endpoints.
- REST routes define `permission_callback`.
- User IDs are validated and resolved server-side.
- Remote URLs are constructed from a configured trusted base URL.
- Input is sanitized before leaving the application boundary.
- Errors returned to clients are intentionally generic.
- Server logs should avoid tokens and unnecessary personal data.

## Testing

The application service is unit tested with fake adapters:

```bash
composer test
```

The test suite covers:

- creating a remote customer when no mapping exists;
- updating an existing remote customer;
- persistence of the returned remote identifier;
- propagation of controlled API failures.

CI runs the unit tests on every push and pull request.

## Production extensions I would normally consider

Depending on scale and business requirements:

- retry/backoff policy for transient failures;
- asynchronous processing for slow remote APIs;
- webhook signature validation;
- structured logging/correlation IDs;
- rate-limit handling;
- dead-letter/recovery workflow;
- encrypted secret management;
- sync status/last-error admin visibility;
- integration tests against a sandbox API.

## Repository purpose

This is intentionally a compact showcase. It demonstrates coding style, boundaries, error handling, security thinking, and testability without exposing any client implementation.

## License

MIT
