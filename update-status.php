<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('collector');

$id     = (int)($_POST['id']     ?? 0);
$status = trim((string)($_POST['status'] ?? ''));
$cid    = current_user()['id'];

if ($id <= 0) fail('Invalid request id.');
if (!in_array($status, ['Assigned','Collected'], true)) fail('Invalid status.');

// Ensure this collector actually owns the assignment.
$own = db_one(
    'SELECT ca.id, wc.user_id, wc.waste_type FROM collection_assignment ca
       JOIN waste_collection wc ON wc.id = ca.request_id
      WHERE ca.request_id = ? AND ca.collector_id = ?',
    'ii', [$id, $cid]
);
if (!$own) fail('You are not assigned to this request.', 403);

$residentId = (int)$own['user_id'];
$wasteType  = $own['waste_type'];

db_exec('UPDATE waste_collection SET status = ? WHERE id = ?', 'si', [$status, $id]);

if ($status === 'Collected') {
    db_exec(
        'UPDATE collection_assignment SET completed_at = NOW() WHERE request_id = ? AND collector_id = ?',
        'ii', [$id, $cid]
    );
    notify($residentId, "Great news! Your $wasteType waste (#$id) has been collected.");
}
ok('Status updated to ' . $status);
