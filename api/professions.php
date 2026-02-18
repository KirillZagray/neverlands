<?php
/**
 * Professions API
 *
 * GET  /api/professions?user_id=X       - текущая профессия + список всех
 * POST /api/professions  action=choose  - выбрать профессию  { profession }
 * POST /api/professions  action=work    - поработать (получить nv + exp)
 */

$db = Database::getInstance()->getConnection();

// Добавить колонку last_work если ещё не существует
$colCheck = $db->query("SHOW COLUMNS FROM `user` LIKE 'last_work'");
if ($colCheck && $colCheck->num_rows === 0) {
    $db->query("ALTER TABLE `user` ADD COLUMN `last_work` INT NOT NULL DEFAULT 0");
}

// -----------------------------------------------------------------------
// Профессии: ключ — код (хранится в user.umen), значения — параметры
// -----------------------------------------------------------------------
$PROFESSIONS = [
    'miner' => [
        'name'        => 'Шахтёр',
        'icon'        => '⛏',
        'desc'        => 'Добывает руду и камень в шахтах. Доход растёт с уровнем.',
        'work_nv'     => 15,   // базовое золото за одну работу
        'work_exp'    => 5,    // базовый опыт
        'cooldown'    => 60,   // секунд между работами
        'stat_bonus'  => 'sila', // повышает этот стат при длительной работе
    ],
    'fisher' => [
        'name'        => 'Рыбак',
        'icon'        => '🎣',
        'desc'        => 'Ловит рыбу у рек и озёр. Тихое, но прибыльное занятие.',
        'work_nv'     => 10,
        'work_exp'    => 4,
        'cooldown'    => 45,
        'stat_bonus'  => 'lovk',
    ],
    'healer' => [
        'name'        => 'Лекарь',
        'icon'        => '⚕',
        'desc'        => 'Варит зелья и помогает другим игрокам. Много опыта, меньше золота.',
        'work_nv'     => 12,
        'work_exp'    => 10,
        'cooldown'    => 50,
        'stat_bonus'  => 'mudr',
    ],
    'jeweler' => [
        'name'        => 'Ювелир',
        'icon'        => '💎',
        'desc'        => 'Создаёт украшения из руды. Высокий доход, долгий перерыв.',
        'work_nv'     => 30,
        'work_exp'    => 8,
        'cooldown'    => 120,
        'stat_bonus'  => 'znan',
    ],
    'merchant' => [
        'name'        => 'Торговец',
        'icon'        => '🏪',
        'desc'        => 'Торгует на рынке. Стабильный доход + скидка 10% при покупках.',
        'work_nv'     => 20,
        'work_exp'    => 3,
        'cooldown'    => 90,
        'stat_bonus'  => 'uda4a',
    ],
];

// -----------------------------------------------------------------------

if ($request_method === 'GET') {
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) jsonError('user_id обязателен');

    $stmt = $db->prepare("SELECT umen, last_work, level FROM user WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $player = $stmt->get_result()->fetch_assoc();
    if (!$player) jsonError('Игрок не найден', 404);

    $currentProf = $player['umen']      ?? '';
    $lastWork    = intval($player['last_work'] ?? 0);
    $now         = time();

    $list = [];
    foreach ($PROFESSIONS as $key => $prof) {
        $isActive     = ($currentProf === $key);
        $cooldownLeft = 0;
        if ($isActive && $lastWork > 0) {
            $cooldownLeft = max(0, $prof['cooldown'] - ($now - $lastWork));
        }
        $list[$key] = array_merge($prof, [
            'id'            => $key,
            'is_active'     => $isActive,
            'cooldown_left' => $cooldownLeft,
            'can_work'      => ($isActive && $cooldownLeft === 0),
        ]);
    }

    jsonSuccess([
        'current_profession' => $currentProf ?: null,
        'professions'        => $list,
    ], 'Список профессий получен');

} elseif ($request_method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $action = $input['action']  ?? 'choose';

    if (!$userId) jsonError('user_id обязателен');

    // ---- ВЫБРАТЬ ПРОФЕССИЮ ----
    if ($action === 'choose') {
        $profession = $input['profession'] ?? null;

        if (!$profession || !isset($PROFESSIONS[$profession])) {
            jsonError('Неверная профессия. Доступные: ' . implode(', ', array_keys($PROFESSIONS)));
        }

        // Проверить что игрок существует
        $stmt = $db->prepare("SELECT id, umen FROM user WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $player = $stmt->get_result()->fetch_assoc();
        if (!$player) jsonError('Игрок не найден', 404);

        $stmt = $db->prepare("UPDATE user SET umen = ?, last_work = 0 WHERE id = ?");
        $stmt->bind_param('si', $profession, $userId);

        if ($stmt->execute()) {
            $prof = $PROFESSIONS[$profession];
            jsonSuccess([
                'profession' => $profession,
                'name'       => $prof['name'],
                'cooldown'   => $prof['cooldown'],
            ], "Профессия выбрана: {$prof['icon']} {$prof['name']}");
        } else {
            jsonError('Не удалось сохранить профессию');
        }

    // ---- РАБОТАТЬ ----
    } elseif ($action === 'work') {
        $stmt = $db->prepare("
            SELECT umen, last_work, level, nv, exp, free_stat
            FROM user WHERE id = ? LIMIT 1
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $player = $stmt->get_result()->fetch_assoc();
        if (!$player) jsonError('Игрок не найден', 404);

        $currentProf = $player['umen'] ?? '';
        if (!$currentProf || !isset($PROFESSIONS[$currentProf])) {
            jsonError('Сначала выберите профессию (action=choose)');
        }

        $prof     = $PROFESSIONS[$currentProf];
        $lastWork = intval($player['last_work'] ?? 0);
        $now      = time();
        $elapsed  = $now - $lastWork;

        if ($lastWork > 0 && $elapsed < $prof['cooldown']) {
            $left = $prof['cooldown'] - $elapsed;
            jsonError("Рано! Нужно подождать ещё {$left} сек. (кулдаун профессии: {$prof['cooldown']} сек.)");
        }

        // Доход масштабируется с уровнем: +10% за каждый уровень
        $level     = max(1, intval($player['level']));
        $bonusMult = 1.0 + ($level * 0.1);
        $earned    = intval($prof['work_nv']  * $bonusMult);
        $expGained = intval($prof['work_exp'] * $bonusMult);

        $newNv  = intval($player['nv'])  + $earned;
        $newExp = intval($player['exp']) + $expGained;

        // Проверка level up (порог: 100 * уровень)
        $newLevel    = $level;
        $levelUp     = false;
        $threshold   = max(100, $level * 100);
        if ($newExp >= $threshold && $newLevel < 99) {
            $newLevel++;
            $levelUp = true;
        }

        if ($levelUp) {
            $newFree = intval($player['free_stat'] ?? 0) + 3;
            $stmt = $db->prepare("UPDATE user SET nv = ?, exp = ?, last_work = ?, level = ?, free_stat = ? WHERE id = ?");
            $stmt->bind_param('iiiiii', $newNv, $newExp, $now, $newLevel, $newFree, $userId);
        } else {
            $stmt = $db->prepare("UPDATE user SET nv = ?, exp = ?, last_work = ? WHERE id = ?");
            $stmt->bind_param('iiii', $newNv, $newExp, $now, $userId);
        }

        if ($stmt->execute()) {
            $result = [
                'profession'  => $currentProf,
                'prof_name'   => $prof['name'],
                'earned_nv'   => $earned,
                'earned_exp'  => $expGained,
                'nv_total'    => $newNv,
                'next_work_in' => $prof['cooldown'],
                'level_up'    => $levelUp,
            ];
            if ($levelUp) {
                $result['new_level'] = $newLevel;
            }
            jsonSuccess($result, "{$prof['icon']} {$prof['name']}: +{$earned} золота, +{$expGained} опыта");
        } else {
            jsonError('Ошибка при работе: ' . $db->error);
        }

    } else {
        jsonError('Неверное действие. Допустимые: choose, work');
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
