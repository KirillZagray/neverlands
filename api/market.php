<?php
/**
 * Market API
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $category = $_GET['category'] ?? 'w4';
    $stmt = $db->prepare("SELECT market.*, items.name, items.gif, items.level, items.price FROM market LEFT JOIN items ON market.id = items.id WHERE kol > 0 AND type = ? ORDER BY items.level LIMIT 50");
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['name'] = from_win($row['name']);
        $items[] = $row;
    }
    jsonSuccess(['items' => $items], 'Market items retrieved');
} else {
    jsonError('Method not allowed', 405);
}
