<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$reqID = (int)($_POST['request_id'] ?? 0);
$instr = trim($_POST['instructions'] ?? '');

if ($reqID <= 0) fail('Invalid request ID.');
if ($instr === '') fail('Instructions cannot be empty.');

$req = db_one('SELECT user_id, waste_type FROM waste_collection WHERE id = ?', 'i', [$reqID]);
if (!$req) fail('Request not found.');

$res = db_exec(
    'UPDATE waste_collection SET recycling_instructions = ? WHERE id = ?',
    'si', [$instr, $reqID]
);

if ($res['affected'] >= 0) { // 0 if no change, but still success
    notify((int)$req['user_id'], "Admin has added recycling advice for your " . $req['waste_type'] . " waste request (#$reqID). Check your requests for details!");
    ok('Instructions updated and user notified.');
} else {
    fail('Failed to update instructions.');
}
