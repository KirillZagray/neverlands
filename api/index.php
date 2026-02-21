<?php
/**
 * API Router - Minimal version for debugging
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($path, '/');

$segments = explode('/', $path);
$endpoint = $segments[0] ?? '';

$response = [
    'path' => $path,
    'endpoint' => $endpoint,
    'segments' => $segments,
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
];

echo json_encode($response);
?>
