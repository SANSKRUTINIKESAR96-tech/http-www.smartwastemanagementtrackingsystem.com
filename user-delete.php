<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) fail('Invalid user.');
if ($id === current_user()['id']) fail('You cannot delete your own account.');

$u = db_one('SELECT role FROM users WHERE id = ?', 'i', [$id]);
if (!$u) fail('User not found.', 404);
if ($u['role'] === 'admin') fail('Admin accounts cannot be deleted from here.');

db_exec('DELETE FROM users WHERE id = ?', 'i', [$id]);
ok('User deleted.');
