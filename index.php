<?php
/**
 * Router for Railway deployment
 * - /api/*       → api/index.php
 * - /bot/*       → bot PHP files
 * - /assets/*, static files → frontend/build/
 * - everything else → frontend/build/index.html (SPA)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Telegram-Init-Data');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$path = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Route /bot/* → bot PHP files
if (strpos($path, 'bot/') === 0) {
    $botFile = __DIR__ . '/' . $path;
    if (file_exists($botFile) && !is_dir($botFile)) {
        require $botFile;
        exit;
    }
    http_response_code(404);
    exit;
}

// Route /api/* → api/index.php
if ($path === 'api' || strpos($path, 'api/') === 0) {
    $newPath = substr($path, 3); // remove 'api' prefix
    $newPath = ltrim($newPath, '/'); // remove leading slashes
    $newPath = '/' . $newPath; // add single leading slash
    if ($newPath === '/') {
        $newPath = '/index';
    }
    $_SERVER['REQUEST_URI'] = $newPath . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    
    // Debug
    error_log('Routing to api/index.php with URI: ' . $_SERVER['REQUEST_URI']);
    
    require __DIR__ . '/api/index.php';
    exit;
}

// Serve static files from frontend/build/
$staticFile = __DIR__ . '/frontend/build/' . $path;
if ($path !== '' && file_exists($staticFile) && !is_dir($staticFile)) {
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $mime = [
        'js'    => 'application/javascript',
        'css'   => 'text/css',
        'html'  => 'text/html; charset=utf-8',
        'json'  => 'application/json',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'webp'  => 'image/webp',
    ][$ext] ?? 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($staticFile);
    exit;
}

// SPA fallback — serve index.html for all routes
$indexFile = __DIR__ . '/frontend/build/index.html';
if (file_exists($indexFile)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($indexFile);
    exit;
}

http_response_code(503);
echo json_encode(['error' => 'Frontend not available']);
