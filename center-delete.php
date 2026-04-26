<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) fail('Invalid center.');

db_exec('DELETE FROM recycling_centers WHERE id = ?', 'i', [$id]);
ok('Center deleted.');
