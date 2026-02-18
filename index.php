<?php
/**
 * Router for Railway deployment
 */

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Telegram-Init-Data');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$path = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Route /api/* → api/index.php
if (strpos($path, 'api') === 0) {
    $_SERVER['REQUEST_URI'] = '/' . substr($path, strlen('api'));
    if (empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/') {
        $_SERVER['REQUEST_URI'] = '/index';
    }
    if (!empty($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
    require __DIR__ . '/api/index.php';
    exit;
}

// Route /bot/* → bot files
if (strpos($path, 'bot/') === 0) {
    $botFile = __DIR__ . '/' . $path;
    if (file_exists($botFile) && !is_dir($botFile)) {
        require $botFile;
        exit;
    }
}

// Serve static frontend assets (referenced as /assets/... in index.html)
if (strpos($path, 'assets/') === 0 || $path === 'favicon.ico' || $path === 'robots.txt') {
    $staticFile = __DIR__ . '/frontend/build/' . $path;
    if (file_exists($staticFile) && !is_dir($staticFile)) {
        serveFile($staticFile);
        exit;
    }
}

// SPA fallback — serve frontend index.html for all other routes
$indexFile = __DIR__ . '/frontend/build/index.html';
if (file_exists($indexFile)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($indexFile);
} else {
    http_response_code(503);
    echo json_encode(['error' => 'Frontend not built']);
}

function serveFile($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'js'   => 'application/javascript',
        'css'  => 'text/css',
        'html' => 'text/html; charset=utf-8',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'webp' => 'image/webp',
    ];
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($filePath);
}
