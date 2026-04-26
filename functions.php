<?php
/**
 * Common helpers used across the application.
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output for safe HTML rendering. */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Build an absolute URL inside the app. */
function url(string $path = ''): string {
    $base = rtrim(APP_URL, '/');
    return $base . '/' . ltrim($path, '/');
}

/** Redirect and stop. */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

/** Store / retrieve a one-time flash message. */
function flash(string $key, ?string $message = null, string $type = 'info') {
    if ($message !== null) {
        $_SESSION['_flash'][$key] = ['msg' => $message, 'type' => $type];
        return null;
    }
    if (!empty($_SESSION['_flash'][$key])) {
        $f = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $f;
    }
    return null;
}

/** Render any pending flash messages as styled alerts. */
function render_flashes(): void {
    if (empty($_SESSION['_flash'])) return;
    foreach ($_SESSION['_flash'] as $key => $f) {
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
    $_SESSION['_flash'] = [];
}

/** CSRF token helpers (simple per-session token). */
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): bool {
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($t) && hash_equals(csrf_token(), $t);
}

/** Convenience: run a prepared SELECT and return all rows. */
function db_all(string $sql, string $types = '', array $params = []): array {
    $stmt = db()->prepare($sql);
    if ($stmt === false) {
        throw new RuntimeException('SQL prepare failed: ' . db()->error);
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

/** Return a single row (or null). */
function db_one(string $sql, string $types = '', array $params = []): ?array {
    $rows = db_all($sql, $types, $params);
    return $rows[0] ?? null;
}

/** Run an INSERT / UPDATE / DELETE. Returns affected rows + insert id. */
function db_exec(string $sql, string $types = '', array $params = []): array {
    $stmt = db()->prepare($sql);
    if ($stmt === false) {
        throw new RuntimeException('SQL prepare failed: ' . db()->error);
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = [
        'affected'  => $stmt->affected_rows,
        'insert_id' => $stmt->insert_id,
    ];
    $stmt->close();
    return $result;
}

/** Send a JSON response and exit (for AJAX APIs). */
function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/** Pretty-print a status as a colored pill. */
function status_badge(string $status): string {
    $map = [
        'Pending'     => 'warn',
        'Assigned'    => 'info',
        'Collected'   => 'ok',
        'Cancelled'   => 'mute',
        'Open'        => 'warn',
        'In Progress' => 'info',
        'Resolved'    => 'ok',
        'active'      => 'ok',
        'inactive'    => 'mute',
        'available'   => 'ok',
        'on_duty'     => 'info',
        'maintenance' => 'warn',
    ];
    $cls = $map[$status] ?? 'mute';
    return '<span class="pill pill-' . e($cls) . '">' . e($status) . '</span>';
}

/** Add a persistent notification for a user. */
function notify(int $userId, string $message): bool {
    $res = db_exec('INSERT INTO notifications (user_id, message) VALUES (?, ?)', 'is', [$userId, $message]);
    return $res['affected'] > 0;
}

/** Get unread notification count for a user. */
function unread_count(int $userId): int {
    $res = db_one('SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0', 'i', [$userId]);
    return (int)($res['cnt'] ?? 0);
}
