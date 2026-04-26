<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$id     = (int)($_POST['id']         ?? 0);
$drv    = (int)($_POST['driver_id']  ?? 0);
$status = $_POST['status']           ?? 'available';

if ($id <= 0) fail('Invalid vehicle.');
if (!in_array($status, ['available','on_duty','maintenance'], true)) fail('Invalid status.');

$drv = $drv > 0 ? $drv : null;

if ($drv === null) {
    db_exec('UPDATE vehicles SET driver_id = NULL, status = ? WHERE id = ?', 'si', [$status, $id]);
} else {
    db_exec('UPDATE vehicles SET driver_id = ?, status = ? WHERE id = ?', 'isi', [$drv, $status, $id]);
}
ok('Vehicle updated.');
