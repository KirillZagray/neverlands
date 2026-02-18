<?php
/**
 * Inventory API
 *
 * GET    /api/inventory?user_id=X               - список предметов
 * POST   /api/inventory  action=sell            - продать предмет { invent_id }
 * DELETE /api/inventory?user_id=X&invent_id=Y  - выбросить предмет
 */

$db = Database::getInstance()->getConnection();

// Убедиться что колонка equipped есть (создаётся также в equipment.php)
$colCheck = $db->query("SHOW COLUMNS FROM `invent` LIKE 'equipped'");
if ($colCheck && $colCheck->num_rows === 0) {
    $db->query("ALTER TABLE `invent` ADD COLUMN `equipped` TINYINT(1) NOT NULL DEFAULT 0");
}

if ($request_method === 'GET') {
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) jsonError('user_id обязателен');

    $stmt = $db->prepare("
        SELECT invent.id AS invent_id,
               invent.protype,
               COALESCE(invent.equipped, 0) AS equipped,
               items.name,
               items.gif,
               items.massa,
               items.price,
               items.level AS req_level
        FROM invent
        INNER JOIN items ON invent.protype = items.id
        WHERE invent.pl_id = ?
        ORDER BY invent.id DESC
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['name'] = from_win($row['name']);
        $items[] = $row;
    }

    jsonSuccess(['items' => $items, 'count' => count($items)], 'Инвентарь получен');

} elseif ($request_method === 'POST') {
    // Продать предмет
    $input    = json_decode(file_get_contents('php://input'), true);
    $userId   = $input['user_id']   ?? null;
    $inventId = intval($input['invent_id'] ?? 0);
    $action   = $input['action']    ?? 'sell';

    if (!$userId || !$inventId) jsonError('user_id и invent_id обязательны');
    if ($action !== 'sell') jsonError('Неверное действие. Допустимые: sell');

    // Найти предмет в инвентаре
    $stmt = $db->prepare("
        SELECT invent.id, invent.protype,
               COALESCE(invent.equipped, 0) AS equipped,
               items.name, items.price
        FROM invent
        INNER JOIN items ON invent.protype = items.id
        WHERE invent.id = ? AND invent.pl_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('ii', $inventId, $userId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) jsonError('Предмет не найден в вашем инвентаре');
    if ($item['equipped']) jsonError('Снимите предмет перед продажей');

    // Цена продажи — 50% от рыночной стоимости
    $sellPrice = max(1, (int)floor(intval($item['price']) * 0.5));
    $itemName  = from_win($item['name']);

    // Удалить из инвентаря
    $stmt = $db->prepare("DELETE FROM invent WHERE id = ? AND pl_id = ?");
    $stmt->bind_param('ii', $inventId, $userId);
    if (!$stmt->execute() || $stmt->affected_rows === 0) {
        jsonError('Не удалось продать предмет');
    }

    // Начислить золото игроку
    $stmt = $db->prepare("UPDATE user SET nv = nv + ? WHERE id = ?");
    $stmt->bind_param('ii', $sellPrice, $userId);
    $stmt->execute();

    // Вернуть 1 единицу на маркет (если запись есть)
    $stmt = $db->prepare("UPDATE market SET kol = kol + 1 WHERE id = ?");
    $stmt->bind_param('i', $item['protype']);
    $stmt->execute(); // Ошибку игнорируем

    // Актуальный баланс
    $balStmt = $db->prepare("SELECT nv FROM user WHERE id = ? LIMIT 1");
    $balStmt->bind_param('i', $userId);
    $balStmt->execute();
    $balance = $balStmt->get_result()->fetch_assoc();

    jsonSuccess([
        'item_name'  => $itemName,
        'sell_price' => $sellPrice,
        'nv_balance' => $balance['nv'] ?? 0,
    ], "Продан: {$itemName} за {$sellPrice} nv");

} elseif ($request_method === 'DELETE') {
    // Выбросить предмет (без возмещения)
    $userId   = $_GET['user_id']   ?? null;
    $inventId = intval($_GET['invent_id'] ?? 0);

    if (!$userId || !$inventId) {
        jsonError('user_id и invent_id обязательны (query параметры)');
    }

    // Проверить, не надет ли предмет
    $stmt = $db->prepare("
        SELECT invent.id, COALESCE(invent.equipped, 0) AS equipped, items.name
        FROM invent
        INNER JOIN items ON invent.protype = items.id
        WHERE invent.id = ? AND invent.pl_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('ii', $inventId, $userId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) jsonError('Предмет не найден в вашем инвентаре');
    if ($item['equipped']) jsonError('Снимите предмет перед тем как выбросить');

    $stmt = $db->prepare("DELETE FROM invent WHERE id = ? AND pl_id = ?");
    $stmt->bind_param('ii', $inventId, $userId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        jsonSuccess(['invent_id' => $inventId], 'Выброшен: ' . from_win($item['name']));
    } else {
        jsonError('Не удалось выбросить предмет');
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
