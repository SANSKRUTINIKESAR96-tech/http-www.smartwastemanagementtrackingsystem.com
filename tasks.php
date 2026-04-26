<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('collector');

$cid    = current_user()['id'];
$filter = $_GET['status'] ?? 'Assigned';

$where  = 'ca.collector_id = ?';
$types  = 'i';
$params = [$cid];
if (in_array($filter, ['Assigned','Collected','Cancelled'], true)) {
    $where .= ' AND wc.status = ?';
    $types .= 's';
    $params[] = $filter;
}

$rows = db_all(
    "SELECT wc.*, u.name AS resident_name, u.phone AS resident_phone,
            v.vehicle_no, ca.completed_at, ca.id AS assignment_id
       FROM collection_assignment ca
       JOIN waste_collection wc ON wc.id = ca.request_id
       JOIN users u             ON u.id = wc.user_id
  LEFT JOIN vehicles v          ON v.id = ca.vehicle_id
      WHERE $where
   ORDER BY wc.pickup_date DESC, wc.id DESC",
    $types, $params
);

$pageTitle = 'My Tasks';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Assigned tasks</h3>
    </div>
    <div class="row-actions mb-2">
        <?php foreach (['Assigned','Collected','Cancelled','all'] as $f): ?>
            <a class="btn btn-sm <?= $filter === $f ? 'btn-primary' : 'btn-ghost' ?>"
               href="?status=<?= e($f) ?>"><?= e(ucfirst($f)) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$rows): ?>
        <div class="empty">Nothing here.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr>
            <th>#</th><th>Resident</th><th>Type</th><th>Kg</th><th>Location</th>
            <th>Pickup</th><th>Vehicle</th><th>Status</th><th class="right">Action</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td><?= e($r['resident_name']) ?><br><small class="muted"><?= e($r['resident_phone'] ?? '') ?></small></td>
                <td><?= e(ucfirst($r['waste_type'])) ?></td>
                <td><?= e((string)$r['quantity']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><?= e($r['pickup_date'] ?? '—') ?></td>
                <td><?= e($r['vehicle_no'] ?? '—') ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td class="right">
                    <?php if ($r['status'] === 'Assigned'): ?>
                        <button class="btn btn-sm btn-primary" onclick="setStatus(<?= (int)$r['id'] ?>, 'Collected')">Mark Collected</button>
                    <?php elseif ($r['status'] === 'Collected'): ?>
                        <span class="muted">Done <?= $r['completed_at'] ? e(date('M j, Y', strtotime($r['completed_at']))) : '' ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<script>
async function setStatus(id, status) {
    if (!confirm('Update #' + id + ' to ' + status + '?')) return;
    const res = await ecoRequest('<?= e(url('api/collector/update-status.php')) ?>', { id, status });
    if (res.ok) location.reload(); else alert(res.message || 'Error');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
