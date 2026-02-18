<?php
/**
 * Player API
 *
 * GET  /api/player?user_id=X            - данные персонажа
 * PUT  /api/player                      - обновить статы (распределить очки)
 * POST /api/player  action=rest         - отдохнуть (восстановить HP за золото)
 * POST /api/player  action=upgrade_stat - вложить свободное очко в стат
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    // Получить данные персонажа
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        jsonError('user_id обязателен');
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
        jsonError('Игрок не найден', 404);
    }

    $player = $result->fetch_assoc();
    $player['login'] = from_win($player['login']);

    jsonSuccess(['player' => $player], 'Данные персонажа получены');

} elseif ($request_method === 'PUT') {
    // Прямое обновление статов (для внутреннего использования)
    $input  = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $stats  = $input['stats']   ?? null;

    if (!$userId || !$stats) {
        jsonError('user_id и stats обязательны');
    }

    $updates = [];
    $types   = '';
    $values  = [];

    foreach ($stats as $stat => $value) {
        $allowed = ['sila', 'lovk', 'uda4a', 'zdorov', 'znan', 'mudr'];
        if (in_array($stat, $allowed)) {
            $updates[] = "$stat = ?";
            $types    .= 'i';
            $values[]  = intval($value);
        }
    }

    if (empty($updates)) {
        jsonError('Нет допустимых статов для обновления');
    }

    $types   .= 'i';
    $values[] = $userId;

    $sql  = "UPDATE user SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        jsonSuccess(null, 'Статы обновлены');
    } else {
        jsonError('Не удалось обновить статы');
    }

} elseif ($request_method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $action = $input['action']  ?? null;

    if (!$userId) {
        jsonError('user_id обязателен');
    }

    // Получить данные игрока
    $stmt = $db->prepare("
        SELECT id, hp, hp_all, nv, sila, lovk, uda4a, zdorov, znan, mudr, level, free_stat
        FROM user WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $player = $stmt->get_result()->fetch_assoc();

    if (!$player) {
        jsonError('Игрок не найден', 404);
    }

    if ($action === 'rest') {
        // --- Отдых: восстановить HP ---
        $hp    = intval($player['hp']);
        $hpAll = intval($player['hp_all'] ?: max(50, intval($player['zdorov']) * 5));
        $nv    = intval($player['nv']);

        if ($hp >= $hpAll) {
            jsonSuccess(['hp' => $hp, 'hp_max' => $hpAll], 'HP уже полное');
        }

        // Стоимость: 1 nv за каждые 5 HP недостающих (минимум 1)
        $missing = $hpAll - $hp;
        $cost    = max(1, (int)ceil($missing / 5));

        if ($nv < $cost) {
            jsonError("Недостаточно золота для отдыха. Нужно: {$cost} nv, есть: {$nv} nv");
        }

        $newNv = $nv - $cost;
        $stmt  = $db->prepare("UPDATE user SET hp = ?, nv = ? WHERE id = ?");
        $stmt->bind_param('iii', $hpAll, $newNv, $userId);
        $stmt->execute();

        jsonSuccess([
            'hp'           => $hpAll,
            'hp_max'       => $hpAll,
            'cost'         => $cost,
            'nv_remaining' => $newNv
        ], "Отдохнули! HP восстановлено до {$hpAll}. Потрачено {$cost} nv");

    } elseif ($action === 'upgrade_stat') {
        // --- Вложить свободное очко в стат ---
        $stat      = $input['stat'] ?? null;
        $allowed   = ['sila', 'lovk', 'uda4a', 'zdorov', 'znan', 'mudr'];
        $freeStat  = intval($player['free_stat'] ?? 0);

        if (!$stat || !in_array($stat, $allowed)) {
            jsonError('Неверный стат. Допустимые: ' . implode(', ', $allowed));
        }

        if ($freeStat <= 0) {
            jsonError('Нет свободных очков умений');
        }

        $currentVal = intval($player[$stat]);
        $newVal     = $currentVal + 1;
        $newFree    = $freeStat - 1;

        $stmt = $db->prepare("UPDATE user SET $stat = ?, free_stat = ? WHERE id = ?");
        $stmt->bind_param('iii', $newVal, $newFree, $userId);

        if ($stmt->execute()) {
            $statNames = [
                'sila'   => 'Сила',
                'lovk'   => 'Ловкость',
                'uda4a'  => 'Удача',
                'zdorov' => 'Здоровье',
                'znan'   => 'Знания',
                'mudr'   => 'Мудрость',
            ];
            jsonSuccess([
                'stat'       => $stat,
                'stat_name'  => $statNames[$stat],
                'new_value'  => $newVal,
                'free_stat'  => $newFree
            ], "{$statNames[$stat]} повышена до {$newVal}");
        } else {
            jsonError('Не удалось обновить стат');
        }

    } else {
        jsonError('Неверное действие. Допустимые: rest, upgrade_stat');
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
