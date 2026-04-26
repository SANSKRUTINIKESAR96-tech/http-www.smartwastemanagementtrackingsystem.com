<?php
/**
 * Shared bootstrap for every JSON API endpoint.
 * - Forces POST (except where explicitly overridden).
 * - Enforces CSRF.
 * - Provides ok()/fail() helpers.
 */

require_once __DIR__ . '/../includes/auth.php';

function api_require_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'message' => 'Method not allowed'], 405);
    }
}

function api_require_csrf(): void {
    if (!csrf_check()) {
        json_response(['ok' => false, 'message' => 'Invalid session. Please reload and try again.'], 419);
    }
}

function api_require_login(): void {
    if (!is_logged_in()) {
        json_response(['ok' => false, 'message' => 'Not logged in'], 401);
    }
}

function api_require_role($roles): void {
    api_require_login();
    $roles = (array)$roles;
    if (!in_array(current_role(), $roles, true)) {
        json_response(['ok' => false, 'message' => 'Forbidden'], 403);
    }
}

function ok(string $message = 'Success', array $extra = []): void {
    json_response(array_merge(['ok' => true, 'message' => $message], $extra));
}

function fail(string $message, int $status = 400, array $extra = []): void {
    json_response(array_merge(['ok' => false, 'message' => $message], $extra), $status);
}
