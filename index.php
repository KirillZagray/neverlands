<?php
/**
 * Router for Railway deployment
 * Redirects /api/* requests to api/ folder
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

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// Route /bot/* to bot PHP files
if (strpos($path, 'bot/') === 0) {
    $botFile = __DIR__ . '/' . $path;
    if (file_exists($botFile) && !is_dir($botFile)) {
        require $botFile;
        exit;
    }
    http_response_code(404);
    exit;
}

// If path starts with 'api', route to api/index.php
if (strpos($path, 'api') === 0 || strpos($path, 'api/') === 0) {
    // Modify REQUEST_URI to remove /api prefix for the API router
    $_SERVER['REQUEST_URI'] = '/' . substr($path, 4); // Remove 'api/' or 'api'
    if (empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/') {
        $_SERVER['REQUEST_URI'] = '/index';
    }
    // Add back query string
    if (!empty($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
    require __DIR__ . '/api/index.php';
    exit;
}

// Default response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'NeverLands Backend API',
    'version' => '1.0',
    'endpoints' => [
        '/api/auth',
        '/api/player',
        '/api/inventory',
        '/api/market',
        '/api/map',
        '/api/chat',
        '/api/battle'
    ]
]);
