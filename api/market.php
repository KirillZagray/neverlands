<?php
/**
 * Market API
 *
 * GET  /api/market?category=w4  - список товаров в категории
 * POST /api/market              - купить предмет
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $category = $_GET['category'] ?? 'w4';
    $stmt = $db->prepare("
        SELECT market.*, items.name, items.gif, items.level, items.price
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
    jsonSuccess(['items' => $items], 'Товары получены');

} elseif ($request_method === 'POST') {
    // Купить предмет
    $input  = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $itemId = intval($input['item_id'] ?? 0);

    if (!$userId || !$itemId) {
        jsonError('user_id и item_id обязательны');
    }

    // Получить предмет из маркета
    $stmt = $db->prepare("
        SELECT market.id, market.kol, items.price, items.name, items.level
        FROM market
        LEFT JOIN items ON market.id = items.id
        WHERE market.id = ? AND kol > 0
        LIMIT 1
    ");
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        jsonError('Предмет недоступен или закончился');
    }

    // Получить данные игрока
    $stmt = $db->prepare("SELECT id, nv, level FROM user WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $player = $stmt->get_result()->fetch_assoc();

    if (!$player) {
        jsonError('Игрок не найден', 404);
    }

    // Проверить уровень
    if (intval($player['level']) < intval($item['level'] ?? 0)) {
        jsonError('Недостаточный уровень для этого предмета (требуется: ' . $item['level'] . ')');
    }

    // Проверить золото
    $price = intval($item['price']);
    if (intval($player['nv']) < $price) {
        jsonError('Недостаточно золота. Нужно: ' . $price . ' nv, есть: ' . $player['nv'] . ' nv');
    }

    // --- Транзакция ---

    // 1. Добавить в инвентарь
    $stmt = $db->prepare("INSERT INTO invent (pl_id, protype) VALUES (?, ?)");
    $stmt->bind_param('ii', $userId, $itemId);
    if (!$stmt->execute()) {
        jsonError('Не удалось добавить предмет в инвентарь');
    }

    // 2. Уменьшить количество в маркете
    $stmt = $db->prepare("UPDATE market SET kol = kol - 1 WHERE id = ?");
    $stmt->bind_param('i', $itemId);
    $stmt->execute();

    // 3. Списать золото у игрока
    $newNv = intval($player['nv']) - $price;
    $stmt  = $db->prepare("UPDATE user SET nv = ? WHERE id = ?");
    $stmt->bind_param('ii', $newNv, $userId);
    $stmt->execute();

    $itemName = from_win($item['name']);
    jsonSuccess([
        'item_name'    => $itemName,
        'price_paid'   => $price,
        'nv_remaining' => $newNv
    ], "Куплено: {$itemName}");

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
