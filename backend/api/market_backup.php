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
