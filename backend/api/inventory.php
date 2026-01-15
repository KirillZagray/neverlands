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