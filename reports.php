<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$uid  = current_user()['id'];
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$rows = db_all(
    "SELECT id, waste_type, quantity, location, pickup_date, status, created_at
       FROM waste_collection
      WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
      ORDER BY created_at DESC",
    'iss', [$uid, $from, $to]
);

$totalKg  = array_sum(array_map(fn($r) => (float)$r['quantity'], $rows));
$collected= array_sum(array_map(fn($r) => $r['status'] === 'Collected' ? 1 : 0, $rows));

$daily = db_all(
    "SELECT DATE(created_at) d, COALESCE(SUM(quantity),0) qty
       FROM waste_collection
      WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?
      GROUP BY DATE(created_at) ORDER BY d",
    'iss', [$uid, $from, $to]
);

$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <form method="get" class="space-between">
        <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
            <div><label>From</label><input type="date" name="from" value="<?= e($from) ?>"></div>
            <div><label>To</label>  <input type="date" name="to"   value="<?= e($to) ?>"></div>
            <button class="btn btn-primary">Apply</button>
        </div>
        <a class="btn btn-ghost" href="javascript:window.print()">🖨️ Print / Export PDF</a>
    </form>
</div>

<div class="stats">
    <div class="stat"><div class="stat-icon">📦</div><div><div class="stat-label">Requests</div><div class="stat-value"><?= count($rows) ?></div></div></div>
    <div class="stat"><div class="stat-icon">✅</div><div><div class="stat-label">Collected</div><div class="stat-value"><?= $collected ?></div></div></div>
    <div class="stat"><div class="stat-icon">⚖️</div><div><div class="stat-label">Total kg</div><div class="stat-value"><?= number_format($totalKg, 1) ?></div></div></div>
    <div class="stat"><div class="stat-icon">📅</div><div><div class="stat-label">Range</div><div class="stat-value" style="font-size:15px"><?= e($from) ?><br>→<br><?= e($to) ?></div></div></div>
</div>

<div class="card">
    <h3>Daily volume</h3>
    <canvas id="chartDaily" height="120"></canvas>
</div>

<div class="card">
    <h3>Detailed requests</h3>
    <?php if (!$rows): ?>
        <div class="empty">No records in this range.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Kg</th><th>Location</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
                <td><?= e(ucfirst($r['waste_type'])) ?></td>
                <td><?= e((string)$r['quantity']) ?></td>
                <td><?= e($r['location']) ?></td>
                <td><?= status_badge($r['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<script>
ecoChart('chartDaily', {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($daily, 'd')) ?>,
        datasets: [{
            label: 'Kg',
            data: <?= json_encode(array_map(fn($r) => (float)$r['qty'], $daily)) ?>,
            borderColor: '#2f855a',
            backgroundColor: 'rgba(47,133,90,0.15)',
            fill: true, tension: 0.3
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
