<?php
session_start();
// Check login
if (!isset($_SESSION["login"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require 'function.php';

header('Content-Type: application/json');

$keyword = $_GET['q'] ?? '';
$keyword = mysqli_real_escape_string($conn, trim($keyword));

if (empty($keyword)) {
    echo json_encode([]);
    exit;
}

$results = cari($keyword);

// Limit results for live search
$results = array_slice($results, 0, 10);

// Format results for JSON
$output = array_map(function($r) {
    return [
        'idAnima' => $r['idAnima'],
        'name' => $r['name'],
        'slug' => $r['slug'],
        'image' => $r['image'],
        'habitat' => $r['habitat'],
        'status' => $r['status'],
        'describe' => substr($r['describe'], 0, 100) . (strlen($r['describe']) > 100 ? '...' : ''),
        'kingdom' => $r['kingdom'] ?? 'Animalia',
        'pylum' => $r['pylum'] ?? '',
        'class' => $r['class'] ?? '',
        'ordo' => $r['ordo'] ?? '',
        'famili' => $r['famili'] ?? '',
        'genus' => $r['genus'] ?? '',
        'nationalPlanting' => $r['nationalPlanting'] ?? '',
        'internationalPlanting' => $r['internationalPlanting'] ?? ''
    ];
}, $results);

echo json_encode($output);
