<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$req       = (int)($_POST['request_id']   ?? 0);
$collector = (int)($_POST['collector_id'] ?? 0);
$vehicle   = (int)($_POST['vehicle_id']   ?? 0);

if ($req <= 0 || $collector <= 0) fail('Request and collector are required.');

$exists = db_one('SELECT user_id, waste_type, status FROM waste_collection WHERE id = ?', 'i', [$req]);
if (!$exists) fail('Request not found.', 404);
if (!in_array($exists['status'], ['Pending','Assigned'], true)) fail('This request is already finalized.');

$residentId = (int)$exists['user_id'];
$wasteType  = $exists['waste_type'];

$col = db_one("SELECT id, name FROM users WHERE id = ? AND role = 'collector' AND status = 'active'", 'i', [$collector]);
if (!$col) fail('Invalid collector.');

$collectorName = $col['name'];

$vehicleId = $vehicle > 0 ? $vehicle : null;
if ($vehicleId !== null) {
    $veh = db_one("SELECT id FROM vehicles WHERE id = ?", 'i', [$vehicleId]);
    if (!$veh) fail('Invalid vehicle.');
}

$conn = db();
$conn->begin_transaction();
try {
    $existing = db_one('SELECT id FROM collection_assignment WHERE request_id = ?', 'i', [$req]);
    if ($existing) {
        if ($vehicleId === null) {
            db_exec('UPDATE collection_assignment SET collector_id = ?, vehicle_id = NULL WHERE id = ?',
                'ii', [$collector, (int)$existing['id']]);
        } else {
            db_exec('UPDATE collection_assignment SET collector_id = ?, vehicle_id = ? WHERE id = ?',
                'iii', [$collector, $vehicleId, (int)$existing['id']]);
        }
    } else {
        if ($vehicleId === null) {
            db_exec('INSERT INTO collection_assignment (request_id, collector_id) VALUES (?, ?)', 'ii', [$req, $collector]);
        } else {
            db_exec('INSERT INTO collection_assignment (request_id, collector_id, vehicle_id) VALUES (?, ?, ?)', 'iii', [$req, $collector, $vehicleId]);
        }
    }
    db_exec("UPDATE waste_collection SET status = 'Assigned' WHERE id = ?", 'i', [$req]);

    // Send Notifications
    notify($residentId, "Your $wasteType pickup request (#$req) has been assigned to $collectorName.");
    notify($collector, "New assignment: Pick up $wasteType waste from resident #$residentId.");

    $conn->commit();
    ok('Request assigned successfully.');
} catch (Throwable $t) {
    $conn->rollback();
    fail('Could not assign: ' . $t->getMessage(), 500);
}
