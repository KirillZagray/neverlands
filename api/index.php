<?php
/**
 * API Router - Main entry point
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Parse path properly
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');

// If path is empty or just /, show index
if (empty($path) || $path === 'api') {
    jsonSuccess([
        'api' => 'NeverLands Telegram Mini App',
        'version' => API_VERSION,
        'endpoints' => [
            '/auth' => 'Authentication',
            '/player' => 'Player data and actions',
            '/inventory' => 'Inventory management',
            '/market' => 'Market/shop',
            '/map' => 'Map and locations',
            '/chat' => 'Chat messages',
            '/battle' => 'Боевая система (PvE)',
            '/professions' => 'Профессии',
            '/equipment' => 'Экипировка'
        ]
    ], 'API is running');
}

// Get endpoint (first segment)
$segments = explode('/', $path);
$endpoint = $segments[0];

// Route to appropriate handler
try {
    switch ($endpoint) {
        case 'auth':
            require_once __DIR__ . '/auth.php';
            break;
        case 'player':
            require_once __DIR__ . '/player.php';
            break;
        case 'inventory':
            require_once __DIR__ . '/inventory.php';
            break;
        case 'market':
            require_once __DIR__ . '/market.php';
            break;
        case 'map':
            require_once __DIR__ . '/map.php';
            break;
        case 'chat':
            require_once __DIR__ . '/chat.php';
            break;
        case 'battle':
            require_once __DIR__ . '/battle.php';
            break;
        case 'professions':
        case 'profession':
            require_once __DIR__ . '/professions.php';
            break;
        case 'equipment':
        case 'equip':
            require_once __DIR__ . '/equipment.php';
            break;
        case 'debug':
            require_once __DIR__ . '/debug.php';
            break;
        default:
            jsonError('Endpoint not found: ' . $endpoint, 404);
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    jsonError('Internal server error: ' . $e->getMessage(), 500);
}
?>
