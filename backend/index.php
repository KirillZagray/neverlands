<?php
/**
 * Router for Railway deployment
 * Redirects /api/* requests to api/ folder
 */

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// If path starts with 'api/', route to api folder
if (strpos($path, 'api/') === 0) {
    $apiPath = substr($path, 4); // Remove 'api/' prefix

    if (empty($apiPath)) {
        $apiPath = 'index.php';
    } else if (!preg_match('/\.php$/', $apiPath)) {
        $apiPath .= '.php';
    }

    $targetFile = __DIR__ . '/api/' . $apiPath;

    if (file_exists($targetFile)) {
        require $targetFile;
        exit;
    }
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
