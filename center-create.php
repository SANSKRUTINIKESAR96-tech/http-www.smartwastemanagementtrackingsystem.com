<?php
require_once __DIR__ . '/../_bootstrap.php';
api_require_post(); api_require_csrf();
api_require_role('admin');

$name     = trim($_POST['name']     ?? '');
$location = trim($_POST['location'] ?? '');
$cap      = (int)($_POST['capacity_kg'] ?? 0);
$contact  = trim($_POST['contact']  ?? '');
$types    = trim($_POST['accepted_types'] ?? 'dry,wet,recyclable');

if ($name === '' || $location === '') fail('Name and location are required.');

db_exec(
    'INSERT INTO recycling_centers (name, location, capacity_kg, contact, accepted_types) VALUES (?,?,?,?,?)',
    'ssiss', [$name, $location, $cap, $contact, $types]
);
ok('Recycling center added.');
