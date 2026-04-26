<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$filter = $_GET['status'] ?? 'all';

$where = '1=1';
$types = '';
$params = [];
if (in_array($filter, ['Pending','Assigned','Collected','Cancelled'], true)) {
    $where .= ' AND wc.status = ?'; $types .= 's'; $params[] = $filter;
}

$rows = db_all(
    "SELECT wc.*, u.name AS resident_name, u.phone,
            ca.id AS assign_id, ca.collector_id, ca.vehicle_id,
            c.name AS collector_name, v.vehicle_no
       FROM waste_collection wc
       JOIN users u ON u.id = wc.user_id
  LEFT JOIN collection_assignment ca ON ca.request_id = wc.id
  LEFT JOIN users c ON c.id = ca.collector_id
  LEFT JOIN vehicles v ON v.id = ca.vehicle_id
      WHERE $where
   ORDER BY wc.created_at DESC",
    $types, $params
);

$collectors = db_all("SELECT id, name FROM users WHERE role = 'collector' AND status = 'active' ORDER BY name");
$vehicles   = db_all("SELECT id, vehicle_no FROM vehicles WHERE status <> 'maintenance' ORDER BY vehicle_no");

$pageTitle = 'Assignments';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header"><h3>Pickup requests (<?= count($rows) ?>)</h3></div>
    <div class="row-actions mb-2">
        <?php foreach (['all','Pending','Assigned','Collected','Cancelled'] as $f): ?>
            <a class="btn btn-sm <?= $filter === $f ? 'btn-primary' : 'btn-ghost' ?>" href="?status=<?= e($f) ?>"><?= e(ucfirst($f)) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!$rows): ?>
        <div class="empty">No requests.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr>
            <th>#</th><th>Resident</th><th>Type</th><th>Kg</th><th>Location</th>
            <th>Pickup</th><th>Status</th><th>Collector</th><th>Vehicle</th><th class="right">Assign</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td><?= e($r['resident_name']) ?><br><small class="muted"><?= e($r['phone'] ?? '') ?></small></td>
                <td><?= e(ucfirst($r['waste_type'])) ?></td>
                <td><?= e((string)$r['quantity']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><?= e($r['pickup_date'] ?? '—') ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td><?= e($r['collector_name'] ?? '—') ?></td>
                <td><?= e($r['vehicle_no'] ?? '—') ?></td>
                <td class="right">
                    <?php if (in_array($r['status'], ['Pending','Assigned'], true)): ?>
                    <form class="js-ajax" method="post"
                          action="<?= e(url('api/admin/assign.php')) ?>"
                          data-reload="true"
                          style="display:flex;gap:6px;justify-content:flex-end;align-items:center;flex-wrap:wrap">
                        <?= csrf_field() ?>
                        <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                        <select name="collector_id" required>
                            <option value="">Collector…</option>
                            <?php foreach ($collectors as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$r['collector_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="vehicle_id">
                            <option value="">Vehicle…</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= (int)$v['id'] ?>" <?= (int)$v['id'] === (int)$r['vehicle_id'] ? 'selected':'' ?>><?= e($v['vehicle_no']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-primary">Assign</button>
                    </form>
                    <?php elseif ($r['status'] === 'Collected'): ?>
                        <form class="js-ajax" method="post" action="<?= e(url('api/admin/add-instructions.php')) ?>" data-reload="true">
                            <?= csrf_field() ?>
                            <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                            <div style="display:flex; flex-direction:column; gap:4px; align-items:flex-end">
                                <textarea name="instructions" placeholder="Recycling advice..." style="width:150px; font-size:12px; height:40px;"><?= e($r['recycling_instructions'] ?? '') ?></textarea>
                                <button class="btn btn-sm btn-ok">Send Advice</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <span class="muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
