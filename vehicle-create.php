<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$no  = trim($_POST['vehicle_no']  ?? '');
$typ = trim($_POST['type']        ?? 'Truck');
$cap = (int)($_POST['capacity_kg']?? 1000);
$drv = (int)($_POST['driver_id']  ?? 0);
$drv = $drv > 0 ? $drv : null;

if ($no === '') fail('Vehicle number is required.');

$dup = db_one('SELECT id FROM vehicles WHERE vehicle_no = ?', 's', [$no]);
if ($dup) fail('That vehicle number already exists.');

if ($drv === null) {
    db_exec('INSERT INTO vehicles (vehicle_no, type, capacity_kg) VALUES (?, ?, ?)',
        'ssi', [$no, $typ, $cap]);
} else {
    db_exec('INSERT INTO vehicles (vehicle_no, type, capacity_kg, driver_id) VALUES (?, ?, ?, ?)',
        'ssii', [$no, $typ, $cap, $drv]);
}
ok('Vehicle added.');
