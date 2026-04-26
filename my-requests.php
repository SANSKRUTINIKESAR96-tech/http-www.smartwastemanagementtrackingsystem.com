<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$uid    = current_user()['id'];
$filter = $_GET['status'] ?? 'all';

$where  = 'user_id = ?';
$types  = 'i';
$params = [$uid];
if (in_array($filter, ['Pending','Assigned','Collected','Cancelled'], true)) {
    $where  .= ' AND wc.status = ?';
    $types  .= 's';
    $params[] = $filter;
}

$rows = db_all(
    "SELECT wc.*, ca.collector_id,
            u.name AS collector_name, v.vehicle_no
       FROM waste_collection wc
  LEFT JOIN collection_assignment ca ON ca.request_id = wc.id
  LEFT JOIN users u                 ON u.id = ca.collector_id
  LEFT JOIN vehicles v              ON v.id = ca.vehicle_id
      WHERE $where
   ORDER BY wc.created_at DESC",
    $types, $params
);

$pageTitle = 'My Requests';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>My pickup requests</h3>
        <a class="btn btn-primary btn-sm" href="<?= e(url('user/waste-entry.php')) ?>">+ New pickup</a>
    </div>
    <div class="row-actions mb-2">
        <?php foreach (['all','Pending','Assigned','Collected','Cancelled'] as $f): ?>
            <a class="btn btn-sm <?= $filter === $f ? 'btn-primary' : 'btn-ghost' ?>"
               href="?status=<?= e($f) ?>"><?= e(ucfirst($f)) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$rows): ?>
        <div class="empty">No requests match that filter.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr>
            <th>#</th><th>Type</th><th>Qty</th><th>Location</th>
            <th>Pickup</th><th>Status</th><th>Collector</th><th>Vehicle</th><th class="right">Action</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td><?= e(ucfirst($r['waste_type'])) ?></td>
                <td><?= e((string)$r['quantity']) ?> kg</td>
                <td><?= e($r['location']) ?></td>
                <td><?= e($r['pickup_date'] ?? '—') ?></td>
                <td>
                    <?= status_badge($r['status']) ?>
                    <?php if ($r['recycling_instructions']): ?>
                        <div style="margin-top:8px; padding:8px; background:#ecfdf5; border:1px solid #10b981; border-radius:4px; font-size:0.85rem">
                            <strong>💡 Recycling Advice:</strong><br>
                            <?= e($r['recycling_instructions']) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?= e($r['collector_name'] ?? '—') ?></td>
                <td><?= e($r['vehicle_no']     ?? '—') ?></td>
                <td class="actions right">
                    <?php if ($r['status'] === 'Pending'): ?>
                        <button class="btn btn-sm btn-danger"
                            data-confirm="Cancel this pickup request?"
                            onclick="cancelRequest(<?= (int)$r['id'] ?>)">Cancel</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php csrf_field(); // ensure token is present ?>
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

<script>
async function cancelRequest(id) {
    const res = await ecoRequest('<?= e(url('api/waste/cancel.php')) ?>', { id });
    if (res.ok) location.reload();
    else alert(res.message || 'Could not cancel.');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
