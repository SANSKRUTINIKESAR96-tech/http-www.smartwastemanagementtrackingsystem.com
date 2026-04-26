<?php
/**
 * One-time installer: seeds sample users, complaints, and pickup requests
 * with properly bcrypt-hashed passwords.
 *
 * Run AFTER importing database/schema.sql.
 * Open in your browser:  http://localhost/WastManagementSystem/install.php
 *
 * For security, delete this file after seeding.
 */

$ALLOW_INSTALL = false; 
if (!$ALLOW_INSTALL) {
    die("<h2 style='color:red'>Installation Locked</h2><p>For security, you must edit <code>install.php</code> and set <code>\$ALLOW_INSTALL = true;</code> to run the seeder.</p>");
}

require_once __DIR__ . '/includes/functions.php';

$log = [];
$err = null;

try {
    $conn = db();

    // Abort if already seeded.
    $count = db_one('SELECT COUNT(*) c FROM users');
    if ((int)$count['c'] > 0) {
        $log[] = '⚠️  Users table already has data — skipping user seed.';
    } else {
        $hash = password_hash('Password@123', PASSWORD_BCRYPT);

        $users = [
            ['System Admin',   'admin@wms.test',     '9000000001', 'HQ Office',       'admin'],
            ['Ravi Collector', 'collector@wms.test', '9000000002', 'Depot 1',         'collector'],
            ['Meera Driver',   'collector2@wms.test','9000000003', 'Depot 2',         'collector'],
            ['Asha Sharma',    'user@wms.test',      '9000000010', '12 Green Street', 'user'],
            ['Vikram Patel',   'user2@wms.test',     '9000000011', '45 Eco Avenue',   'user'],
        ];
        foreach ($users as $u) {
            db_exec(
                'INSERT INTO users (name, email, password, phone, address, role)
                 VALUES (?, ?, ?, ?, ?, ?)',
                'ssssss',
                [$u[0], $u[1], $hash, $u[2], $u[3], $u[4]]
            );
        }
        $log[] = '✅ Seeded ' . count($users) . ' sample users (password: Password@123).';

        // Map collectors to vehicles (if present).
        $vehicles = db_all('SELECT id FROM vehicles ORDER BY id');
        $collectors = db_all("SELECT id FROM users WHERE role = 'collector' ORDER BY id");
        if ($vehicles && $collectors) {
            db_exec('UPDATE vehicles SET driver_id = ? WHERE id = ?', 'ii', [$collectors[0]['id'], $vehicles[0]['id']]);
            if (isset($vehicles[1], $collectors[1])) {
                db_exec('UPDATE vehicles SET driver_id = ? WHERE id = ?', 'ii', [$collectors[1]['id'], $vehicles[1]['id']]);
            }
            $log[] = '✅ Assigned drivers to vehicles.';
        }

        // Seed pickup requests.
        $resident1 = db_one("SELECT id FROM users WHERE email = 'user@wms.test'")['id']  ?? null;
        $resident2 = db_one("SELECT id FROM users WHERE email = 'user2@wms.test'")['id'] ?? null;
        if ($resident1 && $resident2) {
            $pickups = [
                [$resident1, 'dry',        5.5, '12 Green Street', 'Weekly pickup',         date('Y-m-d'),                                      'Pending'],
                [$resident1, 'wet',        3.0, '12 Green Street', 'Kitchen waste',         date('Y-m-d'),                                      'Assigned'],
                [$resident2, 'recyclable', 8.0, '45 Eco Avenue',   'Cardboard and plastic', date('Y-m-d', strtotime('-1 day')),                 'Collected'],
                [$resident2, 'e-waste',    2.0, '45 Eco Avenue',   'Old laptop and cables', date('Y-m-d', strtotime('-3 day')),                 'Collected'],
                [$resident1, 'hazardous',  1.2, '12 Green Street', 'Used batteries',        date('Y-m-d', strtotime('-7 day')),                 'Collected'],
            ];
            foreach ($pickups as $p) {
                db_exec(
                    'INSERT INTO waste_collection (user_id, waste_type, quantity, location, notes, pickup_date, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    'isdssss',
                    $p
                );
            }
            $log[] = '✅ Seeded ' . count($pickups) . ' sample pickup requests.';

            // Seed complaints.
            $complaints = [
                [$resident1, 'Missed pickup',   'The truck did not come on scheduled day.', '12 Green Street', 'Open',        null],
                [$resident2, 'Overflowing bin', 'Public bin near park is overflowing.',     'Park Road',       'Resolved',    'Cleaned and scheduled daily pickup.'],
                [$resident1, 'Bad smell',       'Bad odor from collection point.',          '12 Green Street', 'In Progress', 'Team dispatched to inspect.'],
            ];
            foreach ($complaints as $c) {
                db_exec(
                    'INSERT INTO complaints (user_id, subject, description, location, status, admin_reply)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    'isssss',
                    $c
                );
            }
            $log[] = '✅ Seeded ' . count($complaints) . ' sample complaints.';

            // Seed assignments for the pickups that are not Pending.
            $col1 = $collectors[0]['id'] ?? null;
            $col2 = $collectors[1]['id'] ?? $col1;
            $veh1 = $vehicles[0]['id']   ?? null;
            $veh2 = $vehicles[1]['id']   ?? $veh1;
            $pickupsRows = db_all('SELECT id, status FROM waste_collection ORDER BY id');
            foreach ($pickupsRows as $i => $row) {
                if ($row['status'] === 'Pending') continue;
                $collector = ($i % 2 === 0) ? $col1 : $col2;
                $vehicle   = ($i % 2 === 0) ? $veh1 : $veh2;
                $completed = $row['status'] === 'Collected' ? date('Y-m-d H:i:s', strtotime('-' . ($i + 1) . ' day')) : null;
                db_exec(
                    'INSERT INTO collection_assignment (request_id, collector_id, vehicle_id, completed_at, remarks)
                     VALUES (?, ?, ?, ?, ?)',
                    'iiiss',
                    [$row['id'], $collector, $vehicle, $completed, 'Auto-seeded']
                );
            }
            $log[] = '✅ Seeded collection assignments.';

            // Seed processing rows for collected pickups.
            $centers = db_all('SELECT id FROM recycling_centers ORDER BY id');
            $collectedRows = db_all("SELECT id, quantity FROM waste_collection WHERE status = 'Collected'");
            foreach ($collectedRows as $i => $row) {
                $center = $centers[$i % max(count($centers), 1)]['id'] ?? null;
                db_exec(
                    'INSERT INTO waste_processing (collection_id, center_id, processed_qty, method)
                     VALUES (?, ?, ?, ?)',
                    'iids',
                    [$row['id'], $center, $row['quantity'], 'recycled']
                );
            }
            $log[] = '✅ Seeded waste processing records.';
        }
    }
} catch (Throwable $t) {
    $err = $t->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Install — EcoTrack</title>
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="no-sidebar">
<main class="main">
    <div class="content">
        <div class="card" style="max-width:680px;margin:60px auto;">
            <h1>♻️ EcoTrack – Installer</h1>
            <p class="muted">This script seeds sample users, pickups, complaints, and assignments.</p>

            <?php if ($err): ?>
                <div class="alert alert-error"><b>Error:</b> <?= e($err) ?><br>
                    Did you import <code>database/schema.sql</code> first?
                </div>
            <?php else: ?>
                <div class="alert alert-success">Installation ran successfully.</div>
                <ul style="line-height:1.8">
                    <?php foreach ($log as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
                </ul>
                <h3 class="mt-3">Demo accounts</h3>
                <table class="data" style="width:100%">
                    <tr><th>Role</th><th>Email</th><th>Password</th></tr>
                    <tr><td>Admin</td>     <td>admin@wms.test</td>     <td>Password@123</td></tr>
                    <tr><td>Collector</td> <td>collector@wms.test</td> <td>Password@123</td></tr>
                    <tr><td>User</td>      <td>user@wms.test</td>      <td>Password@123</td></tr>
                </table>
                <div class="alert alert-warn mt-3">
                    ⚠️  Delete <code>install.php</code> after setup for security.
                </div>
                <a class="btn btn-primary" href="<?= e(url('login.php')) ?>">Go to Login →</a>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
