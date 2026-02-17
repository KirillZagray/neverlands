<?php
/**
 * Battle API - Turn-based combat system
 *
 * GET  /api/battle?zone=1       - получить список монстров в зоне
 * POST /api/battle              - атаковать монстра
 */

$db = Database::getInstance()->getConnection();

// Кулдаун между боями (секунды)
define('BATTLE_COOLDOWN', 10);

// Добавить колонку last_battle если нет
$colCheck = $db->query("SHOW COLUMNS FROM `user` LIKE 'last_battle'");
if ($colCheck && $colCheck->num_rows === 0) {
    $db->query("ALTER TABLE `user` ADD COLUMN `last_battle` INT NOT NULL DEFAULT 0");
}

// Шаблоны монстров по зонам (можно перенести в БД)
$MONSTERS = [
    1 => ['name' => 'Волк',      'zone' => 1, 'hp' => 25,  'sila' => 4,  'lovk' => 3, 'exp' => 8,   'gold' => 3],
    2 => ['name' => 'Кабан',     'zone' => 1, 'hp' => 35,  'sila' => 6,  'lovk' => 2, 'exp' => 12,  'gold' => 5],
    3 => ['name' => 'Медведь',   'zone' => 2, 'hp' => 60,  'sila' => 10, 'lovk' => 3, 'exp' => 25,  'gold' => 12],
    4 => ['name' => 'Огр',       'zone' => 2, 'hp' => 80,  'sila' => 12, 'lovk' => 2, 'exp' => 35,  'gold' => 20],
    5 => ['name' => 'Тролль',    'zone' => 3, 'hp' => 120, 'sila' => 18, 'lovk' => 4, 'exp' => 60,  'gold' => 35],
    6 => ['name' => 'Великан',   'zone' => 3, 'hp' => 150, 'sila' => 22, 'lovk' => 3, 'exp' => 80,  'gold' => 50],
    7 => ['name' => 'Дракон',    'zone' => 4, 'hp' => 250, 'sila' => 30, 'lovk' => 8, 'exp' => 200, 'gold' => 150],
    8 => ['name' => 'Лич',       'zone' => 4, 'hp' => 200, 'sila' => 25, 'lovk' => 10,'exp' => 180, 'gold' => 130],
];

if ($request_method === 'GET') {
    // Получить монстров в зоне
    $zone = intval($_GET['zone'] ?? 1);
    if ($zone < 1) $zone = 1;
    if ($zone > 4) $zone = 4;

    $available = [];
    foreach ($MONSTERS as $id => $monster) {
        if ($monster['zone'] <= $zone) {
            $available[$id] = $monster;
        }
    }

    jsonSuccess([
        'zone'     => $zone,
        'monsters' => $available
    ], 'Монстры получены');

} elseif ($request_method === 'POST') {
    // Начать бой
    $input = json_decode(file_get_contents('php://input'), true);
    $userId    = $input['user_id']    ?? null;
    $monsterId = intval($input['monster_id'] ?? 0);

    if (!$userId) {
        jsonError('user_id обязателен');
    }

    if (!isset($MONSTERS[$monsterId])) {
        jsonError('Неверный монстр');
    }

    // Получить данные персонажа
    $stmt = $db->prepare("
        SELECT id, hp, hp_all, sila, lovk, uda4a, zdorov, znan, mudr, exp, level, nv,
               COALESCE(last_battle, 0) AS last_battle
        FROM user WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $player = $stmt->get_result()->fetch_assoc();

    if (!$player) {
        jsonError('Игрок не найден', 404);
    }

    // Проверить кулдаун между боями
    $lastBattle = intval($player['last_battle']);
    $elapsed    = time() - $lastBattle;
    if ($lastBattle > 0 && $elapsed < BATTLE_COOLDOWN) {
        $wait = BATTLE_COOLDOWN - $elapsed;
        jsonError("Подождите ещё {$wait} сек. перед следующим боем");
    }

    // Проверить HP
    $currentHp = intval($player['hp']);
    if ($currentHp <= 0) {
        jsonError('Ваш персонаж мёртв. Сначала отдохните (/api/player, action=rest).');
    }

    $monster   = $MONSTERS[$monsterId];
    $playerHp  = $currentHp;
    $monsterHp = $monster['hp'];
    $log       = [];
    $round     = 0;

    // Статы игрока
    $pSila  = intval($player['sila']);
    $pLovk  = intval($player['lovk']);
    $pUda4a = intval($player['uda4a']);

    // Симуляция боя (максимум 20 раундов)
    while ($playerHp > 0 && $monsterHp > 0 && $round < 20) {
        $round++;

        // --- Удар игрока ---
        // Базовый урон: сила + случайная добавка от ловкости
        $dmg = max(1, $pSila + rand(0, max(1, (int)($pLovk / 2))));
        // Критический удар (шанс = удача / 100)
        $crit = (rand(1, 100) <= $pUda4a);
        if ($crit) {
            $dmg = (int)($dmg * 1.5);
        }
        $monsterHp -= $dmg;
        $critMark   = $crit ? ' [крит!]' : '';
        $log[] = "Раунд $round: Вы наносите {$dmg}{$critMark} урона. {$monster['name']} HP: " . max(0, $monsterHp);

        if ($monsterHp <= 0) break;

        // --- Удар монстра ---
        // Ловкость игрока даёт шанс уклониться
        $dodge = (rand(1, 100) <= max(0, $pLovk - $monster['lovk']));
        if ($dodge) {
            $log[] = "{$monster['name']} промахивается! Вы уклонились.";
        } else {
            $mDmg      = max(1, $monster['sila'] + rand(0, max(1, (int)($monster['lovk'] / 2))));
            $playerHp -= $mDmg;
            $log[] = "{$monster['name']} наносит {$mDmg} урона. Ваше HP: " . max(0, $playerHp);
        }
    }

    $victory    = ($monsterHp <= 0);
    $newHp      = max(0, $playerHp);
    $expGained  = 0;
    $goldGained = 0;

    if ($victory) {
        $expGained  = $monster['exp'];
        $goldGained = $monster['gold'] + rand(0, (int)($monster['gold'] / 2));
        $log[]      = "=== Победа! Получено: {$expGained} опыта, {$goldGained} золота ===";

        $newExp = intval($player['exp']) + $expGained;
        $newNv  = intval($player['nv'])  + $goldGained;

        // Проверка повышения уровня (каждые 100 * level опыта)
        $newLevel    = intval($player['level']);
        $levelThreshold = max(100, $newLevel * 100);
        $levelUp     = false;
        if ($newExp >= $levelThreshold && $newLevel < 99) {
            $newLevel++;
            $levelUp = true;
            $log[] = "*** Уровень повышен до {$newLevel}! ***";
        }

        $now = time();
        if ($levelUp) {
            $freeStat = intval($player['free_stat'] ?? 0) + 3;
            $stmt = $db->prepare("UPDATE user SET hp = ?, exp = ?, nv = ?, level = ?, free_stat = ?, last_battle = ? WHERE id = ?");
            $stmt->bind_param('iiiiiii', $newHp, $newExp, $newNv, $newLevel, $freeStat, $now, $userId);
        } else {
            $stmt = $db->prepare("UPDATE user SET hp = ?, exp = ?, nv = ?, last_battle = ? WHERE id = ?");
            $stmt->bind_param('iiiii', $newHp, $newExp, $newNv, $now, $userId);
        }
        $stmt->execute();

        jsonSuccess([
            'victory'     => true,
            'player_hp'   => $newHp,
            'exp_gained'  => $expGained,
            'gold_gained' => $goldGained,
            'level_up'    => $levelUp,
            'new_level'   => $levelUp ? $newLevel : intval($player['level']),
            'rounds'      => $round,
            'log'         => $log
        ], 'Победа!');
    } else {
        $log[] = "=== Поражение! {$monster['name']} победил. ===";

        $now  = time();
        $stmt = $db->prepare("UPDATE user SET hp = ?, last_battle = ? WHERE id = ?");
        $stmt->bind_param('iii', $newHp, $now, $userId);
        $stmt->execute();

        jsonSuccess([
            'victory'     => false,
            'player_hp'   => $newHp,
            'exp_gained'  => 0,
            'gold_gained' => 0,
            'level_up'    => false,
            'rounds'      => $round,
            'log'         => $log
        ], 'Поражение');
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
