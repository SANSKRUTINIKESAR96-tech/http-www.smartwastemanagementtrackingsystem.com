<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('user');

$id  = (int)($_POST['id'] ?? 0);
$uid = current_user()['id'];
if ($id <= 0) fail('Invalid request id.');

$row = db_one('SELECT status FROM waste_collection WHERE id = ? AND user_id = ?', 'ii', [$id, $uid]);
if (!$row) fail('Request not found.', 404);
if ($row['status'] !== 'Pending') fail('Only pending requests can be cancelled.');

db_exec("UPDATE waste_collection SET status = 'Cancelled' WHERE id = ? AND user_id = ?", 'ii', [$id, $uid]);
ok('Request cancelled.');
