<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$userId = current_user()['id'];

// Mark all as read if requested
if (isset($_POST['mark_all_read'])) {
    if (csrf_check()) {
        db_exec('UPDATE notifications SET is_read = 1 WHERE user_id = ?', 'i', [$userId]);
        flash('notif', 'All notifications marked as read.', 'success');
        redirect('user/notifications.php');
    }
}

$notifications = db_all(
    'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50',
    'i', [$userId]
);

$pageTitle = 'Notifications';
require __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:800px;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3>Your Notifications</h3>
        <?php if ($notifications): ?>
        <form method="post">
            <?= csrf_field() ?>
            <button type="submit" name="mark_all_read" class="btn btn-sm btn-ghost">Mark all as read</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (!$notifications): ?>
        <div class="empty">You have no notifications yet.</div>
    <?php else: ?>
        <div class="notification-list">
            <?php foreach ($notifications as $n): ?>
                <div class="notif-item <?= $n['is_read'] ? 'read' : 'unread' ?>" style="padding:16px; border-bottom:1px solid #e5e7eb; position:relative">
                    <div class="notif-msg"><?= e($n['message']) ?></div>
                    <small class="muted"><?= date('M j, Y g:i A', strtotime($n['created_at'])) ?></small>
                    <?php if (!$n['is_read']): ?>
                        <span style="position:absolute; right:16px; top:50%; transform:translateY(-50%); width:8px; height:8px; background:#2563eb; border-radius:50%"></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php
        // Mark these as read now that they've been seen? 
        // Or keep them unread until explicit click? Usually seeing the list marks them.
        db_exec('UPDATE notifications SET is_read = 1 WHERE user_id = ?', 'i', [$userId]);
        ?>
    <?php endif; ?>
</div>

<style>
.notif-item.unread { background: #f0f9ff; }
.notif-bell { text-decoration: none; position: relative; font-size: 1.2rem; margin-right: 15px; }
.notif-bell .badge { 
    position: absolute; top: -5px; right: -5px; 
    background: #ef4444; color: white; 
    font-size: 0.7rem; padding: 2px 5px; border-radius: 10px; 
}
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>
