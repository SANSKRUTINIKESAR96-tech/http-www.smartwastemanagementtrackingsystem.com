<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$rows = db_all("SELECT * FROM recycling_centers ORDER BY created_at DESC");

$pageTitle = 'Recycling Centers';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header"><h3>Recycling centers (<?= count($rows) ?>)</h3></div>
    <?php if (!$rows): ?><div class="empty">No centers yet.</div><?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Name</th><th>Location</th><th>Capacity</th><th>Accepts</th><th>Contact</th><th class="right">Action</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $c): ?>
            <tr>
                <td>#<?= (int)$c['id'] ?></td>
                <td><b><?= e($c['name']) ?></b></td>
                <td><?= e($c['location']) ?></td>
                <td><?= e((string)$c['capacity_kg']) ?> kg</td>
                <td><?= e($c['accepted_types']) ?></td>
                <td><?= e($c['contact'] ?? '—') ?></td>
                <td class="right">
                    <button class="btn btn-sm btn-danger" data-confirm="Delete center?" onclick="del(<?= (int)$c['id'] ?>)">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Add recycling center</h3>
    <form class="form js-ajax" method="post" action="<?= e(url('api/admin/center-create.php')) ?>" data-reload="true">
        <?= csrf_field() ?>
        <div class="form-row">
            <div><label>Name</label>    <input type="text" name="name" required></div>
            <div><label>Location</label><input type="text" name="location" required></div>
        </div>
        <div class="form-row">
            <div><label>Capacity (kg)</label><input type="number" name="capacity_kg" min="0" value="10000"></div>
            <div><label>Contact</label>      <input type="text" name="contact"></div>
        </div>
        <div>
            <label>Accepted types</label>
            <input type="text" name="accepted_types" placeholder="dry,wet,recyclable" value="dry,wet,recyclable">
            <small class="form-hint">Comma separated: dry, wet, recyclable, hazardous, e-waste</small>
        </div>
        <button class="btn btn-primary">Add center</button>
    </form>
</div>

<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<script>
async function del(id) {
    const res = await ecoRequest('<?= e(url('api/admin/center-delete.php')) ?>', { id });
    if (res.ok) location.reload(); else alert(res.message || 'Error');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
