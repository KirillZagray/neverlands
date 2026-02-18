<?php
/**
 * Equipment API — управление надетыми предметами
 *
 * GET  /api/equipment?user_id=X             - список надетых предметов
 * POST /api/equipment  action=equip         - надеть предмет  { invent_id }
 * POST /api/equipment  action=unequip       - снять предмет   { invent_id }
 * GET  /api/equipment?user_id=X&stats=1     - бонусы от экипировки к статам
 */

$db = Database::getInstance()->getConnection();

// Добавить колонку equipped в invent если нет
$check = $db->query("SHOW COLUMNS FROM `invent` LIKE 'equipped'");
if ($check && $check->num_rows === 0) {
    $db->query("ALTER TABLE `invent` ADD COLUMN `equipped` TINYINT(1) NOT NULL DEFAULT 0");
}

// -----------------------------------------------------------------------
// Определяем слот экипировки по коду типа предмета (market.type / items.type)
// Коды из оригинального Neverlands: w=оружие, a=броня, h=шлем, r=кольцо и т.д.
// -----------------------------------------------------------------------
function resolveSlot(?string $type): string
{
    if (!$type) return 'misc';
    $t = strtolower(trim($type));
    // Упорядочено от длинного к короткому чтобы 'am' не совпал с 'a'
    if (str_starts_with($t, 'am')) return 'amulet';
    if (str_starts_with($t, 'w'))  return 'weapon';
    if (str_starts_with($t, 'a'))  return 'armor';
    if (str_starts_with($t, 'h'))  return 'helmet';
    if (str_starts_with($t, 'b'))  return 'boots';
    if (str_starts_with($t, 'r'))  return 'ring';
    if (str_starts_with($t, 's'))  return 'shield';
    return $t; // неизвестный тип — используем как есть
}

// -----------------------------------------------------------------------

if ($request_method === 'GET') {
    $userId    = $_GET['user_id'] ?? null;
    $withStats = !empty($_GET['stats']);
    if (!$userId) jsonError('user_id обязателен');

    $stmt = $db->prepare("
        SELECT invent.id    AS invent_id,
               invent.protype,
               invent.equipped,
               items.name,
               items.gif,
               items.price,
               items.level  AS req_level,
               market.type  AS item_type
        FROM invent
        INNER JOIN items ON invent.protype = items.id
        LEFT JOIN market ON market.id = items.id
        WHERE invent.pl_id = ? AND invent.equipped = 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = $stmt->get_result();

    $equipped   = [];
    $statBonuses = ['sila' => 0, 'lovk' => 0, 'uda4a' => 0, 'zdorov' => 0, 'znan' => 0, 'mudr' => 0];

    while ($row = $rows->fetch_assoc()) {
        $row['name'] = from_win($row['name']);
        $row['slot'] = resolveSlot($row['item_type']);
        $equipped[]  = $row;
    }

    $response = ['equipped' => $equipped, 'count' => count($equipped)];
    if ($withStats) {
        $response['stat_bonuses'] = $statBonuses;
    }

    jsonSuccess($response, 'Надетые предметы получены');

} elseif ($request_method === 'POST') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $userId   = $input['user_id']   ?? null;
    $inventId = intval($input['invent_id'] ?? 0);
    $action   = $input['action']    ?? 'equip';

    if (!$userId || !$inventId) {
        jsonError('user_id и invent_id обязательны');
    }

    // Проверить что предмет принадлежит игроку
    $stmt = $db->prepare("
        SELECT invent.id, invent.protype, invent.equipped,
               items.name,
               items.level AS req_level,
               market.type AS item_type
        FROM invent
        INNER JOIN items ON invent.protype = items.id
        LEFT JOIN market ON market.id = items.id
        WHERE invent.id = ? AND invent.pl_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('ii', $inventId, $userId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        jsonError('Предмет не найден в вашем инвентаре');
    }

    $itemName = from_win($item['name']);
    $slot     = resolveSlot($item['item_type']);

    if ($action === 'equip') {
        if ($item['equipped']) {
            jsonError("Предмет «{$itemName}» уже надет");
        }

        // Проверить уровень игрока
        if ($item['req_level'] > 0) {
            $lvlStmt = $db->prepare("SELECT level FROM user WHERE id = ? LIMIT 1");
            $lvlStmt->bind_param('i', $userId);
            $lvlStmt->execute();
            $lvlRow = $lvlStmt->get_result()->fetch_assoc();
            if (intval($lvlRow['level'] ?? 0) < intval($item['req_level'])) {
                jsonError("Нужен уровень {$item['req_level']} для этого предмета");
            }
        }

        // Снять уже надетый предмет того же слота
        if ($slot !== 'misc' && $item['item_type']) {
            $typeFirst = substr($item['item_type'], 0, 2);
            $pattern   = $typeFirst . '%';
            // Снимаем через подзапрос предметов из того же слота
            $unStmt = $db->prepare("
                UPDATE invent
                INNER JOIN items  ON invent.protype = items.id
                LEFT  JOIN market ON market.id = items.id
                SET invent.equipped = 0
                WHERE invent.pl_id = ? AND invent.equipped = 1 AND market.type LIKE ?
            ");
            $unStmt->bind_param('is', $userId, $pattern);
            $unStmt->execute();
        }

        // Надеть
        $stmt = $db->prepare("UPDATE invent SET equipped = 1 WHERE id = ?");
        $stmt->bind_param('i', $inventId);
        if ($stmt->execute()) {
            jsonSuccess([
                'invent_id' => $inventId,
                'item_name' => $itemName,
                'slot'      => $slot,
            ], "Надет: {$itemName} [{$slot}]");
        } else {
            jsonError('Не удалось надеть предмет');
        }

    } elseif ($action === 'unequip') {
        if (!$item['equipped']) {
            jsonError("Предмет «{$itemName}» не надет");
        }

        $stmt = $db->prepare("UPDATE invent SET equipped = 0 WHERE id = ?");
        $stmt->bind_param('i', $inventId);
        if ($stmt->execute()) {
            jsonSuccess([
                'invent_id' => $inventId,
                'item_name' => $itemName,
            ], "Снят: {$itemName}");
        } else {
            jsonError('Не удалось снять предмет');
        }

    } else {
        jsonError('Неверное действие. Допустимые: equip, unequip');
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
