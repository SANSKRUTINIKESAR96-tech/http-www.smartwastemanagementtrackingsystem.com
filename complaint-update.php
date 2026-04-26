<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$id     = (int)($_POST['id']     ?? 0);
$status = $_POST['status']       ?? '';
$reply  = trim($_POST['admin_reply'] ?? '');

if ($id <= 0) fail('Invalid complaint.');
if (!in_array($status, ['Open','In Progress','Resolved'], true)) fail('Invalid status.');

$exists = db_one('SELECT id FROM complaints WHERE id = ?', 'i', [$id]);
if (!$exists) fail('Complaint not found.', 404);

db_exec(
    'UPDATE complaints SET status = ?, admin_reply = ? WHERE id = ?',
    'ssi', [$status, $reply === '' ? null : $reply, $id]
);
ok('Complaint updated.');
