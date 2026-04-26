<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$uid    = current_user()['id'];
$errors = [];
$old    = ['subject' => '', 'description' => '', 'location' => current_user()['address'] ?? ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Invalid session.';
    } else {
        $old['subject']     = trim($_POST['subject']     ?? '');
        $old['description'] = trim($_POST['description'] ?? '');
        $old['location']    = trim($_POST['location']    ?? '');

        if ($old['subject']     === '') $errors[] = 'Subject is required.';
        if ($old['description'] === '') $errors[] = 'Description is required.';
        if ($old['location']    === '') $errors[] = 'Location is required.';

        if (!$errors) {
            db_exec(
                'INSERT INTO complaints (user_id, subject, description, location) VALUES (?, ?, ?, ?)',
                'isss',
                [$uid, $old['subject'], $old['description'], $old['location']]
            );
            flash('auth', 'Complaint submitted successfully.', 'success');
            redirect('user/complaints.php');
        }
    }
}

$list = db_all(
    'SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC',
    'i', [$uid]
);

$pageTitle = 'Complaints';
require __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-2">
    <div class="card">
        <h3>Raise a complaint</h3>
        <p class="card-sub">Tell us what went wrong and we'll look into it.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form" style="max-width:none">
            <?= csrf_field() ?>
            <div>
                <label>Subject</label>
                <input type="text" name="subject" required value="<?= e($old['subject']) ?>" placeholder="e.g. Missed pickup">
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" required placeholder="Describe the issue…"><?= e($old['description']) ?></textarea>
            </div>
            <div>
                <label>Location</label>
                <input type="text" name="location" required value="<?= e($old['location']) ?>">
            </div>
            <button class="btn btn-primary" type="submit">Submit complaint</button>
        </form>
    </div>

    <div class="card">
        <h3>My complaints</h3>
        <?php if (!$list): ?>
            <div class="empty">You haven't filed any complaints.</div>
        <?php else: ?>
            <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Subject</th><th>Status</th><th>Date</th><th>Reply</th></tr></thead>
                <tbody>
                <?php foreach ($list as $c): ?>
                    <tr>
                        <td>
                            <b><?= e($c['subject'] ?? '—') ?></b><br>
                            <small class="muted"><?= e(mb_strimwidth($c['description'], 0, 80, '…')) ?></small>
                        </td>
                        <td><?= status_badge($c['status']) ?></td>
                        <td><?= e(date('M j, Y', strtotime($c['created_at']))) ?></td>
                        <td><?= $c['admin_reply'] ? e($c['admin_reply']) : '<span class="muted">—</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
