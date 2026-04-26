<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$rows = db_all(
    "SELECT v.*, u.name AS driver_name
       FROM vehicles v
  LEFT JOIN users u ON u.id = v.driver_id
   ORDER BY v.created_at DESC"
);
$drivers = db_all("SELECT id, name FROM users WHERE role = 'collector' AND status = 'active' ORDER BY name");

$pageTitle = 'Vehicles';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header"><h3>Fleet (<?= count($rows) ?>)</h3></div>

    <?php if (!$rows): ?>
        <div class="empty">No vehicles yet.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Vehicle No.</th><th>Type</th><th>Capacity</th><th>Driver</th><th>Status</th><th class="right">Action</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $v): ?>
            <tr>
                <td>#<?= (int)$v['id'] ?></td>
                <td><b><?= e($v['vehicle_no']) ?></b></td>
                <td><?= e($v['type']) ?></td>
                <td><?= e((string)$v['capacity_kg']) ?> kg</td>
                <td>
                    <form class="js-ajax" method="post" action="<?= e(url('api/admin/vehicle-update.php')) ?>" data-reload="true" style="display:flex;gap:6px;align-items:center">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                        <select name="driver_id">
                            <option value="">— none —</option>
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= (int)$d['id'] ?>" <?= (int)$d['id'] === (int)$v['driver_id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status">
                            <?php foreach (['available','on_duty','maintenance'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= $v['status'] === $s ? 'selected':'' ?>><?= e(ucwords(str_replace('_',' ',$s))) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
                <td><?= status_badge($v['status']) ?></td>
                <td class="right">
                    <button class="btn btn-sm btn-danger" data-confirm="Delete vehicle?" onclick="del(<?= (int)$v['id'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Add a vehicle</h3>
    <form class="form js-ajax" method="post" action="<?= e(url('api/admin/vehicle-create.php')) ?>" data-reload="true">
        <?= csrf_field() ?>
        <div class="form-row">
            <div><label>Vehicle No.</label><input type="text" name="vehicle_no" required placeholder="WM-XX-TYP-000"></div>
            <div><label>Type</label>
                <select name="type">
                    <option>Truck</option><option>Van</option><option>Compactor</option><option>Mini Tipper</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div><label>Capacity (kg)</label><input type="number" name="capacity_kg" value="1000" min="100"></div>
            <div><label>Driver</label>
                <select name="driver_id">
                    <option value="">— none —</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button class="btn btn-primary">Add vehicle</button>
    </form>
</div>

<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<script>
async function del(id) {
    const res = await ecoRequest('<?= e(url('api/admin/vehicle-delete.php')) ?>', { id });
    if (res.ok) location.reload(); else alert(res.message || 'Error');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
