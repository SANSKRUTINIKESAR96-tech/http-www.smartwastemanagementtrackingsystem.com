<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$uid = current_user()['id'];

$totals = db_one(
    "SELECT
        COUNT(*)                                                          AS total,
        SUM(status = 'Pending')                                            AS pending,
        SUM(status = 'Assigned')                                           AS assigned,
        SUM(status = 'Collected')                                          AS collected,
        COALESCE(SUM(CASE WHEN status = 'Collected' THEN quantity END),0) AS kg_collected
     FROM waste_collection WHERE user_id = ?",
    'i', [$uid]
);

$openComplaints = db_one(
    "SELECT COUNT(*) c FROM complaints WHERE user_id = ? AND status <> 'Resolved'",
    'i', [$uid]
);

// Waste by type (last 90 days)
$byType = db_all(
    "SELECT waste_type, COALESCE(SUM(quantity),0) qty
       FROM waste_collection
      WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
      GROUP BY waste_type",
    'i', [$uid]
);

// Monthly totals (last 6 months)
$byMonth = db_all(
    "SELECT DATE_FORMAT(created_at, '%b %Y') label,
            COALESCE(SUM(quantity),0) qty
       FROM waste_collection
      WHERE user_id = ?
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
      ORDER BY MIN(created_at)",
    'i', [$uid]
);

$recent = db_all(
    "SELECT id, waste_type, quantity, location, pickup_date, status, created_at
       FROM waste_collection
      WHERE user_id = ?
      ORDER BY created_at DESC
      LIMIT 5",
    'i', [$uid]
);

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="stats">
    <div class="stat"><div class="stat-icon">🗑️</div><div><div class="stat-label">Total Requests</div><div class="stat-value"><?= (int)$totals['total'] ?></div></div></div>
    <div class="stat"><div class="stat-icon">⏳</div><div><div class="stat-label">Pending</div><div class="stat-value"><?= (int)$totals['pending'] ?></div></div></div>
    <div class="stat"><div class="stat-icon">✅</div><div><div class="stat-label">Collected</div><div class="stat-value"><?= (int)$totals['collected'] ?></div></div></div>
    <div class="stat"><div class="stat-icon">⚖️</div><div><div class="stat-label">Kg Collected</div><div class="stat-value"><?= number_format((float)$totals['kg_collected'], 1) ?></div></div></div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><h3>Waste by type (last 90 days)</h3></div>
        <canvas id="chartByType" height="200"></canvas>
    </div>
    <div class="card">
        <div class="card-header"><h3>Monthly volume (kg)</h3></div>
        <canvas id="chartByMonth" height="200"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent requests</h3>
        <a class="btn btn-primary btn-sm" href="<?= e(url('user/waste-entry.php')) ?>">+ New pickup</a>
    </div>
    <?php if (!$recent): ?>
        <div class="empty">No requests yet. <a href="<?= e(url('user/waste-entry.php')) ?>">Create one →</a></div>
    <?php else: ?>
        <div class="table-wrap">
        <table class="data">
            <thead><tr><th>#</th><th>Type</th><th>Qty (kg)</th><th>Location</th><th>Pickup date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td><?= e(ucfirst($r['waste_type'])) ?></td>
                    <td><?= e((string)$r['quantity']) ?></td>
                    <td><?= e($r['location']) ?></td>
                    <td><?= e($r['pickup_date'] ?? '—') ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header"><h3>Complaints</h3><a class="btn btn-outline btn-sm" href="<?= e(url('user/complaints.php')) ?>">Manage →</a></div>
    <p class="muted">You have <b><?= (int)$openComplaints['c'] ?></b> unresolved complaint(s).</p>
</div>

<script>
const typeLabels = <?= json_encode(array_map(fn($r) => ucfirst($r['waste_type']), $byType)) ?>;
const typeData   = <?= json_encode(array_map(fn($r) => (float)$r['qty'], $byType)) ?>;
const monthLabels= <?= json_encode(array_column($byMonth, 'label')) ?>;
const monthData  = <?= json_encode(array_map(fn($r) => (float)$r['qty'], $byMonth)) ?>;

ecoChart('chartByType', {
    type: 'doughnut',
    data: { labels: typeLabels.length ? typeLabels : ['No data'],
            datasets: [{ data: typeData.length ? typeData : [1],
                         backgroundColor: ['#2f855a','#38b2ac','#ed8936','#c53030','#805ad5','#2b6cb0'] }]},
    options: { plugins: { legend: { position: 'bottom' } } }
});
ecoChart('chartByMonth', {
    type: 'bar',
    data: { labels: monthLabels,
            datasets: [{ label: 'Kg', data: monthData, backgroundColor: '#2f855a', borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } },
               scales: { y: { beginAtZero: true } } }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
