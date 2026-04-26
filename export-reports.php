<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_role('admin');

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$rows = db_all(
    "SELECT wc.id, u.name as resident, wc.waste_type, wc.quantity, wc.location, wc.status, wc.pickup_date, wc.created_at, wc.recycling_instructions
       FROM waste_collection wc
       JOIN users u ON u.id = wc.user_id
      WHERE DATE(wc.created_at) BETWEEN ? AND ?
   ORDER BY wc.created_at DESC",
    'ss', [$from, $to]
);

$filename = "waste_report_" . $from . "_to_" . $to . ".csv";

// Headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['ID', 'Resident', 'Waste Type', 'Qty (kg)', 'Location', 'Status', 'Pickup Date', 'Requested At', 'Recycling Instructions']);

foreach ($rows as $r) {
    fputcsv($output, [
        $r['id'],
        $r['resident'],
        ucfirst($r['waste_type']),
        $r['quantity'],
        $r['location'],
        $r['status'],
        $r['pickup_date'] ?? 'N/A',
        $r['created_at'],
        $r['recycling_instructions'] ?? 'None'
    ]);
}

fclose($output);
exit;
