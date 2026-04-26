<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('collector');

$cid  = current_user()['id'];
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$rows = db_all(
    "SELECT wc.id, wc.waste_type, wc.quantity, wc.location, wc.status,
            u.name AS resident_name, ca.completed_at, v.vehicle_no
       FROM collection_assignment ca
       JOIN waste_collection wc ON wc.id = ca.request_id
       JOIN users u             ON u.id = wc.user_id
  LEFT JOIN vehicles v          ON v.id = ca.vehicle_id
      WHERE ca.collector_id = ?
        AND DATE(ca.assigned_at) BETWEEN ? AND ?
   ORDER BY ca.assigned_at DESC",
    'iss', [$cid, $from, $to]
);

$pageTitle = 'History';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <form method="get" class="space-between">
        <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
            <div><label>From</label><input type="date" name="from" value="<?= e($from) ?>"></div>
            <div><label>To</label>  <input type="date" name="to"   value="<?= e($to) ?>"></div>
            <button class="btn btn-primary">Apply</button>
        </div>
        <a class="btn btn-ghost" href="javascript:window.print()">🖨️ Print</a>
    </form>
</div>

<div class="card">
    <h3>Assignment history</h3>
    <?php if (!$rows): ?>
        <div class="empty">No history in this range.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Resident</th><th>Type</th><th>Kg</th><th>Location</th><th>Vehicle</th><th>Status</th><th>Completed</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td><?= e($r['resident_name']) ?></td>
                <td><?= e(ucfirst($r['waste_type'])) ?></td>
                <td><?= e((string)$r['quantity']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><?= e($r['vehicle_no'] ?? '—') ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td><?= $r['completed_at'] ? e(date('M j, Y H:i', strtotime($r['completed_at']))) : '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
