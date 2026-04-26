<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? APP_NAME;
$role      = current_role();
$user      = current_user();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script src="<?= e(url('assets/js/main.js')) ?>"></script>
</head>
<body class="<?= $user ? 'has-sidebar' : 'no-sidebar' ?>">

<?php if ($user): ?>
<aside class="sidebar">
    <div class="brand">
        <span class="logo">♻️</span>
        <div>
            <strong>EcoTrack</strong>
            <small>Waste Mgmt</small>
        </div>
    </div>
    <nav>
        <?php if ($role === 'user'): ?>
            <a href="<?= e(url('user/dashboard.php')) ?>">📊 Dashboard</a>
            <a href="<?= e(url('user/waste-entry.php')) ?>">🗑️ Request Pickup</a>
            <a href="<?= e(url('user/my-requests.php')) ?>">📋 My Requests</a>
            <a href="<?= e(url('user/complaints.php')) ?>">📣 Complaints</a>
            <a href="<?= e(url('user/reports.php')) ?>">📈 Reports</a>
        <?php elseif ($role === 'collector'): ?>
            <a href="<?= e(url('collector/dashboard.php')) ?>">📊 Dashboard</a>
            <a href="<?= e(url('collector/tasks.php')) ?>">🚛 My Tasks</a>
            <a href="<?= e(url('collector/history.php')) ?>">📜 History</a>
        <?php elseif ($role === 'admin'): ?>
            <a href="<?= e(url('admin/dashboard.php')) ?>">📊 Dashboard</a>
            <a href="<?= e(url('admin/users.php')) ?>">👥 Users</a>
            <a href="<?= e(url('admin/assignments.php')) ?>">🚛 Assignments</a>
            <a href="<?= e(url('admin/complaints.php')) ?>">📣 Complaints</a>
            <a href="<?= e(url('admin/vehicles.php')) ?>">🚚 Vehicles</a>
            <a href="<?= e(url('admin/centers.php')) ?>">🏭 Recycling Centers</a>
            <a href="<?= e(url('admin/reports.php')) ?>">📈 Reports</a>
        <?php endif; ?>
    </nav>
    <div class="side-user">
        <div class="avatar"><?= e(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?></div>
        <div class="side-user-info">
            <strong><?= e($user['name']) ?></strong>
            <small><?= e(ucfirst($user['role'])) ?></small>
        </div>
        <a class="logout-btn" href="<?= e(url('logout.php')) ?>" title="Logout">⎋</a>
    </div>
</aside>
<?php endif; ?>

<main class="main">
    <?php if (!$user): ?>
    <header class="topbar">
        <a class="topbar-brand" href="<?= e(url('index.php')) ?>">
            <span class="logo">♻️</span> <strong>EcoTrack</strong>
        </a>
        <nav class="topbar-nav">
            <a href="<?= e(url('index.php')) ?>#features">Features</a>
            <a href="<?= e(url('index.php')) ?>#how">How it works</a>
            <a class="btn btn-ghost" href="<?= e(url('login.php')) ?>">Login</a>
            <a class="btn btn-primary" href="<?= e(url('register.php')) ?>">Sign up</a>
        </nav>
    </header>
    <?php else: ?>
    <header class="topbar topbar-app">
        <h1 class="page-title"><?= e($pageTitle) ?></h1>
        <div class="topbar-actions">
            <?php
            $uCount = unread_count($user['id']);
            $notifUrl = url('user/notifications.php'); // General notifications page
            ?>
            <a href="<?= e($notifUrl) ?>" class="notif-bell <?= $uCount > 0 ? 'has-unread' : '' ?>" title="Notifications">
                🔔 <?php if ($uCount > 0): ?><span class="badge"><?= $uCount ?></span><?php endif; ?>
            </a>
            <span class="hello">Hi, <strong><?= e(explode(' ', $user['name'])[0]) ?></strong></span>
        </div>
    </header>
    <?php endif; ?>

    <div class="content">
    <?php render_flashes(); ?>
