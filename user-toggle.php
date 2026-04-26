<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) fail('Invalid user.');
if ($id === current_user()['id']) fail('You cannot change your own status.');

$u = db_one('SELECT status FROM users WHERE id = ?', 'i', [$id]);
if (!$u) fail('User not found.', 404);

$new = $u['status'] === 'active' ? 'inactive' : 'active';
db_exec('UPDATE users SET status = ? WHERE id = ?', 'si', [$new, $id]);
ok('User is now ' . $new);
