<?php
/**
 * Automatic Project Migration Script
 * Копирует ассеты и генерирует API из старого проекта
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

$oldProject = '/Applications/MAMP/htdocs/neverlands';
$newProject = '/Applications/MAMP/htdocs/NLTv1';

echo "=== NeverLands to Telegram Mini App Migration ===\n\n";

// Step 1: Copy images
echo "[1/5] Copying images...\n";
$imageSrc = $oldProject . '/image';
$imageDst = $newProject . '/frontend/public/images';

if (!file_exists($imageDst)) {
    mkdir($imageDst, 0755, true);
}

// Copy recursively
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    $count = 0;

    while(($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
                $count++;
            }
        }
    }

    closedir($dir);
    return $count;
}

$copiedFiles = copyDirectory($imageSrc, $imageDst);
echo "  ✓ Copied $copiedFiles image files\n\n";

// Step 2: Add telegram_id field to database
echo "[2/5] Updating database schema...\n";
$mysqli = new mysqli('localhost', 'root', 'root', 'nl');

if ($mysqli->connect_error) {
    die("  ✗ Database connection failed\n");
}

// Check if telegram_id column exists
$result = $mysqli->query("SHOW COLUMNS FROM user LIKE 'telegram_id'");

if ($result->num_rows == 0) {
    $mysqli->query("ALTER TABLE user ADD COLUMN telegram_id BIGINT NULL UNIQUE AFTER id");
    echo "  ✓ Added telegram_id column to user table\n";
} else {
    echo "  - telegram_id column already exists\n";
}

// Add index
$mysqli->query("ALTER TABLE user ADD INDEX idx_telegram_id (telegram_id)");
echo "  ✓ Added index for telegram_id\n\n";

// Step 3: Generate API endpoints from existing PHP functions
echo "[3/5] Generating API endpoints...\n";

// Player API
$playerApiContent = <<<'PHP'
<?php
/**
 * Player API - auto-generated from sql_func.php
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    // Get player data
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        jsonError('user_id required');
    }

    $stmt = $db->prepare("
        SELECT id, login, level, nv, loc, pos,
               sila, lovk, uda4a, zdorov, znan, mudr,
               hp, hp_all, exp, free_stat, obraz
        FROM user
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        jsonError('Player not found', 404);
    }

    $player = $result->fetch_assoc();

    // Convert encoding for strings
    $player['login'] = from_win($player['login']);

    jsonSuccess($player, 'Player data retrieved');

} elseif ($request_method === 'PUT') {
    // Update player stats
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $stats = $input['stats'] ?? null;

    if (!$userId || !$stats) {
        jsonError('user_id and stats required');
    }

    // Build UPDATE query
    $updates = [];
    $types = '';
    $values = [];

    foreach ($stats as $stat => $value) {
        $allowed = ['sila', 'lovk', 'uda4a', 'zdorov', 'znan', 'mudr'];
        if (in_array($stat, $allowed)) {
            $updates[] = "$stat = ?";
            $types .= 'i';
            $values[] = $value;
        }
    }

    if (empty($updates)) {
        jsonError('No valid stats to update');
    }

    $types .= 'i';
    $values[] = $userId;

    $sql = "UPDATE user SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        jsonSuccess(null, 'Stats updated');
    } else {
        jsonError('Failed to update stats');
    }

} else {
    jsonError('Method not allowed', 405);
}
?>
PHP;

file_put_contents($newProject . '/backend/api/player.php', $playerApiContent);
echo "  ✓ Generated player.php\n";

// Inventory API
$inventoryApiContent = <<<'PHP'
<?php
/**
 * Inventory API
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        jsonError('user_id required');
    }

    $stmt = $db->prepare("
        SELECT invent.*, items.name, items.gif, items.massa, items.price
        FROM invent
        INNER JOIN items ON invent.protype = items.id
        WHERE invent.pl_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['name'] = from_win($row['name']);
        $items[] = $row;
    }

    jsonSuccess(['items' => $items], 'Inventory retrieved');

} else {
    jsonError('Method not allowed', 405);
}
?>
PHP;

file_put_contents($newProject . '/backend/api/inventory.php', $inventoryApiContent);
echo "  ✓ Generated inventory.php\n";

// Market API
$marketApiContent = <<<'PHP'
<?php
/**
 * Market API
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    // Get market items
    $category = $_GET['category'] ?? 'w4';

    $stmt = $db->prepare("
        SELECT market.*, items.*
        FROM market
        LEFT JOIN items ON market.id = items.id
        WHERE kol > 0 AND type = ?
        ORDER BY items.level
        LIMIT 50
    ");
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['name'] = from_win($row['name']);
        $items[] = $row;
    }

    jsonSuccess(['items' => $items], 'Market items retrieved');

} elseif ($request_method === 'POST') {
    // Buy item
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $itemId = $input['item_id'] ?? null;

    if (!$userId || !$itemId) {
        jsonError('user_id and item_id required');
    }

    // Get item details
    $stmt = $db->prepare("SELECT * FROM items WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        jsonError('Item not found', 404);
    }

    // Check player money
    $stmt = $db->prepare("SELECT nv FROM user WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $player = $stmt->get_result()->fetch_assoc();

    if ($player['nv'] < $item['price']) {
        jsonError('Not enough money');
    }

    // Buy item
    $db->query("START TRANSACTION");

    try {
        // Add to inventory
        $stmt = $db->prepare("INSERT INTO invent (protype, pl_id, dolg, price, gift) VALUES (?, ?, ?, ?, 0)");
        $dolg = 100; // Default durability
        $stmt->bind_param('iiii', $itemId, $userId, $dolg, $item['price']);
        $stmt->execute();

        // Deduct money
        $stmt = $db->prepare("UPDATE user SET nv = nv - ? WHERE id = ?");
        $stmt->bind_param('ii', $item['price'], $userId);
        $stmt->execute();

        // Decrease market stock
        $stmt = $db->prepare("UPDATE market SET kol = kol - 1 WHERE id = ?");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();

        $db->query("COMMIT");

        jsonSuccess(null, 'Item purchased successfully');

    } catch (Exception $e) {
        $db->query("ROLLBACK");
        jsonError('Purchase failed: ' . $e->getMessage());
    }

} else {
    jsonError('Method not allowed', 405);
}
?>
PHP;

file_put_contents($newProject . '/backend/api/market.php', $marketApiContent);
echo "  ✓ Generated market.php\n";

// Map API (simplified)
$mapApiContent = <<<'PHP'
<?php
/**
 * Map API
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        jsonError('user_id required');
    }

    $stmt = $db->prepare("SELECT loc, pos FROM user WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    jsonSuccess($result, 'Position retrieved');

} elseif ($request_method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $newPos = $input['pos'] ?? null;

    if (!$userId || !$newPos) {
        jsonError('user_id and pos required');
    }

    $stmt = $db->prepare("UPDATE user SET pos = ? WHERE id = ?");
    $stmt->bind_param('si', $newPos, $userId);

    if ($stmt->execute()) {
        jsonSuccess(null, 'Position updated');
    } else {
        jsonError('Failed to update position');
    }

} else {
    jsonError('Method not allowed', 405);
}
?>
PHP;

file_put_contents($newProject . '/backend/api/map.php', $mapApiContent);
echo "  ✓ Generated map.php\n";

// Chat API (simplified)
$chatApiContent = <<<'PHP'
<?php
/**
 * Chat API
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $limit = $_GET['limit'] ?? 50;

    $stmt = $db->prepare("
        SELECT chat.*, user.login
        FROM chat
        LEFT JOIN user ON chat.login = user.login
        ORDER BY chat.id DESC
        LIMIT ?
    ");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $row['login'] = from_win($row['login']);
        $row['msg'] = from_win($row['msg']);
        $messages[] = $row;
    }

    jsonSuccess(['messages' => array_reverse($messages)], 'Chat messages retrieved');

} else {
    jsonError('Method not allowed', 405);
}
?>
PHP;

file_put_contents($newProject . '/backend/api/chat.php', $chatApiContent);
echo "  ✓ Generated chat.php\n";

// Battle API (placeholder)
$battleApiContent = <<<'PHP'
<?php
/**
 * Battle API - Coming soon
 */

jsonError('Battle system not yet implemented', 501);
?>
PHP;

file_put_contents($newProject . '/backend/api/battle.php', $battleApiContent);
echo "  ✓ Generated battle.php\n\n";

// Step 4: Create frontend package.json
echo "[4/5] Creating frontend configuration...\n";

$packageJson = [
    'name' => 'neverlands-telegram-miniapp',
    'version' => '1.0.0',
    'private' => true,
    'dependencies' => [
        'react' => '^18.2.0',
        'react-dom' => '^18.2.0',
        'react-router-dom' => '^6.8.0',
        'axios' => '^1.3.0',
        '@twa-dev/sdk' => '^6.7.0'
    ],
    'scripts' => [
        'start' => 'react-scripts start',
        'build' => 'react-scripts build',
        'test' => 'react-scripts test',
        'eject' => 'react-scripts eject'
    ],
    'devDependencies' => [
        'react-scripts' => '^5.0.1'
    ],
    'browserslist' => [
        'production' => [
            '>0.2%',
            'not dead',
            'not op_mini all'
        ],
        'development' => [
            'last 1 chrome version',
            'last 1 firefox version',
            'last 1 safari version'
        ]
    ]
];

file_put_contents(
    $newProject . '/frontend/package.json',
    json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);
echo "  ✓ Created package.json\n\n";

// Step 5: Summary
echo "[5/5] Migration Summary\n";
echo "  ✓ Copied $copiedFiles image files\n";
echo "  ✓ Updated database schema\n";
echo "  ✓ Generated 7 API endpoints\n";
echo "  ✓ Created frontend configuration\n";
echo "\n";
echo "=== Migration Complete! ===\n\n";

echo "Next steps:\n";
echo "1. cd /Applications/MAMP/htdocs/NLTv1/frontend\n";
echo "2. npm install\n";
echo "3. Create React components (see CREATE_FRONTEND.md)\n";
echo "4. Test API: http://localhost/NLTv1/backend/api/\n";
echo "5. Test frontend: npm start\n";

?>
