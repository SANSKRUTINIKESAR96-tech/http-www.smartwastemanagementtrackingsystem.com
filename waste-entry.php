<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$errors = [];
$old = ['waste_type' => 'dry', 'quantity' => '', 'location' => current_user()['address'] ?? '', 'pickup_date' => date('Y-m-d'), 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Invalid session.';
    } else {
        $old['waste_type']  = $_POST['waste_type']  ?? 'dry';
        $old['quantity']    = $_POST['quantity']    ?? '';
        $old['location']    = trim($_POST['location']    ?? '');
        $old['pickup_date'] = $_POST['pickup_date'] ?? '';
        $old['notes']       = trim($_POST['notes']  ?? '');

        if (!in_array($old['waste_type'], ['dry','wet','recyclable','hazardous','e-waste'], true)) $errors[] = 'Choose a valid waste type.';
        if (!is_numeric($old['quantity']) || (float)$old['quantity'] <= 0) $errors[] = 'Quantity must be a positive number.';
        if ($old['location'] === '')                                       $errors[] = 'Location is required.';
        if ($old['pickup_date'] === '')                                    $errors[] = 'Pickup date is required.';

        if (!$errors) {
            $res = db_exec(
                'INSERT INTO waste_collection (user_id, waste_type, quantity, location, notes, pickup_date, status)
                 VALUES (?, ?, ?, ?, ?, ?, "Pending")',
                'isdsss',
                [current_user()['id'], $old['waste_type'], (float)$old['quantity'], $old['location'], $old['notes'], $old['pickup_date']]
            );
            if ($res['insert_id']) {
                flash('auth', 'Pickup request submitted successfully!', 'success');
                redirect('user/my-requests.php');
            } else {
                $errors[] = 'Could not save request.';
            }
        }
    }
}

$pageTitle = 'Request Pickup';
require __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:680px;">
    <h3>New waste pickup request</h3>
    <p class="card-sub">Tell us what to pick up and when — we'll assign a collector.</p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form" style="max-width:none">
        <?= csrf_field() ?>
        <div class="form-row">
            <div>
                <label>Waste type</label>
                <select name="waste_type" required>
                    <?php foreach (['dry','wet','recyclable','hazardous','e-waste'] as $t): ?>
                        <option value="<?= e($t) ?>" <?= $old['waste_type'] === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Quantity (kg)</label>
                <input type="number" step="0.1" min="0.1" name="quantity" value="<?= e((string)$old['quantity']) ?>" required placeholder="e.g. 5.5">
            </div>
        </div>
        <div>
            <label>Pickup location</label>
            <input type="text" name="location" value="<?= e($old['location']) ?>" required placeholder="Street, area, city">
        </div>
        <div>
            <label>Preferred pickup date</label>
            <input type="date" name="pickup_date" value="<?= e($old['pickup_date']) ?>" min="<?= e(date('Y-m-d')) ?>" required>
        </div>
        <div>
            <label>Notes (optional)</label>
            <textarea name="notes" placeholder="Any special instructions…"><?= e($old['notes']) ?></textarea>
        </div>
        <div class="row-actions">
            <button class="btn btn-primary" type="submit">Submit request</button>
            <a class="btn btn-ghost" href="<?= e(url('user/dashboard.php')) ?>">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
