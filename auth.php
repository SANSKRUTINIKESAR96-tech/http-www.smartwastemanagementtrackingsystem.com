<?php
/**
 * Session-based authentication + role-based access control.
 */

require_once __DIR__ . '/functions.php';

/** Is anyone logged in? */
function is_logged_in(): bool {
    return !empty($_SESSION['user']['id']);
}

/** Current logged-in user array (id, name, email, role...) or null. */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/** Current role or null. */
function current_role(): ?string {
    return $_SESSION['user']['role'] ?? null;
}

/** Establish a logged-in session. */
function login_user(array $user): void {
    // regenerate to prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'      => (int)$user['id'],
        'name'    => $user['name'],
        'email'   => $user['email'],
        'role'    => $user['role'],
        'phone'   => $user['phone']   ?? null,
        'address' => $user['address'] ?? null,
    ];
}

/** Destroy the session. */
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Require the visitor to be logged in (else redirect to login page). */
function require_login(): void {
    if (!is_logged_in()) {
        flash('auth', 'Please log in to continue.', 'warn');
        redirect('login.php');
    }
}

/**
 * Require one of the given roles. Redirects to the correct dashboard otherwise.
 *
 * Usage: require_role('admin')  or  require_role(['admin','collector']).
 */
function require_role($roles): void {
    require_login();
    $roles = (array)$roles;
    if (!in_array(current_role(), $roles, true)) {
        flash('auth', 'You do not have access to that page.', 'error');
        redirect(role_home(current_role()));
    }
}

/** The dashboard path for a given role. */
function role_home(?string $role): string {
    return match ($role) {
        'admin'     => 'admin/dashboard.php',
        'collector' => 'collector/dashboard.php',
        'user'      => 'user/dashboard.php',
        default     => 'login.php',
    };
}
