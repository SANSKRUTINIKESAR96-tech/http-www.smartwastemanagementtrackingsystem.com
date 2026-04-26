<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$role = $_GET['role'] ?? 'all';
$q    = trim($_GET['q'] ?? '');

$where = '1=1';
$types = '';
$params = [];
if (in_array($role, ['user','collector','admin'], true)) {
    $where .= ' AND role = ?'; $types .= 's'; $params[] = $role;
}
if ($q !== '') {
    $where .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $types .= 'sss';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}

$rows = db_all("SELECT * FROM users WHERE $where ORDER BY created_at DESC", $types, $params);

$pageTitle = 'Users';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>All users (<?= count($rows) ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('newUser').scrollIntoView({behavior:'smooth'})">+ Add user</button>
    </div>
    <form method="get" class="row-actions mb-2">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, phone…" style="max-width:280px">
        <select name="role">
            <?php foreach (['all'=>'All roles','user'=>'Users','collector'=>'Collectors','admin'=>'Admins'] as $k=>$v): ?>
                <option value="<?= e($k) ?>" <?= $role === $k ? 'selected':'' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost">Filter</button>
    </form>

    <div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th class="right">Action</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $u): ?>
            <tr>
                <td>#<?= (int)$u['id'] ?></td>
                <td><b><?= e($u['name']) ?></b><br><small class="muted"><?= e($u['address'] ?? '') ?></small></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['phone'] ?? '—') ?></td>
                <td><span class="tag"><?= e(ucfirst($u['role'])) ?></span></td>
                <td><?= status_badge($u['status']) ?></td>
                <td><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                <td class="right">
                    <?php if ((int)$u['id'] !== current_user()['id']): ?>
                        <button class="btn btn-sm btn-ghost" onclick="toggleStatus(<?= (int)$u['id'] ?>)">
                            <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                        <?php if ($u['role'] !== 'admin'): ?>
                        <button class="btn btn-sm btn-danger" data-confirm="Delete this user and all their data?"
                            onclick="deleteUser(<?= (int)$u['id'] ?>)">Delete</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="muted">— you —</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card" id="newUser">
    <h3>Add a new user / collector / admin</h3>
    <form class="form js-ajax" method="post" action="<?= e(url('api/admin/user-create.php')) ?>" data-reload="true">
        <?= csrf_field() ?>
        <div class="form-row">
            <div><label>Name</label> <input type="text" name="name" required></div>
            <div><label>Email</label><input type="email" name="email" required></div>
        </div>
        <div class="form-row">
            <div><label>Phone</label>   <input type="tel"  name="phone"></div>
            <div><label>Password</label><input type="password" name="password" required minlength="6"></div>
        </div>
        <div class="form-row">
            <div><label>Role</label>
                <select name="role">
                    <option value="user">User / Resident</option>
                    <option value="collector">Collector</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div><label>Address</label><input type="text" name="address"></div>
        </div>
        <button class="btn btn-primary" type="submit">Create user</button>
    </form>
</div>

<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<script>
async function toggleStatus(id) {
    const res = await ecoRequest('<?= e(url('api/admin/user-toggle.php')) ?>', { id });
    if (res.ok) location.reload(); else alert(res.message || 'Error');
}
async function deleteUser(id) {
    const res = await ecoRequest('<?= e(url('api/admin/user-delete.php')) ?>', { id });
    if (res.ok) location.reload(); else alert(res.message || 'Error');
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
