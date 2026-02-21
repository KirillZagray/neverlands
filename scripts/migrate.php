<?php
/**
 * Database migration script for NeverLands
 * Run: php scripts/migrate.php
 * Safe to run multiple times — uses CREATE TABLE IF NOT EXISTS
 */

// Load config (defines DB_* constants and helpers)
// We need a minimal bootstrap without headers
$host    = getenv('DB_HOST') ?: 'localhost';
$name    = getenv('DB_NAME') ?: 'railway';
$user    = getenv('DB_USER') ?: 'root';
$pass    = getenv('DB_PASS') ?: 'root';
$port    = (int)(getenv('DB_PORT') ?: 3306);

echo "[migrate] Connecting to {$host}:{$port}/{$name} ...\n";

// Retry up to 10 times with 3-second pauses (MySQL may still be starting)
$db      = null;
$retries = 10;
for ($i = 1; $i <= $retries; $i++) {
    $db = new mysqli($host, $user, $pass, $name, $port);
    if (!$db->connect_error) {
        break;
    }
    echo "[migrate] Attempt {$i}/{$retries} failed: " . $db->connect_error . "\n";
    if ($i < $retries) {
        sleep(3);
    }
}

if ($db->connect_error) {
    echo "[migrate] Could not connect after {$retries} attempts — skipping migration.\n";
    exit(0); // не роняем деплой, сервер всё равно стартует
}
$db->set_charset('utf8mb4');
echo "[migrate] Connected OK\n";

// Helper
function run(mysqli $db, string $sql, string $label): void {
    if ($db->query($sql)) {
        echo "[migrate] OK: $label\n";
    } else {
        echo "[migrate] WARN: $label — " . $db->error . "\n";
    }
}

// ═══════════════════════════════════════════════════════════
//  TABLE: user
// ═══════════════════════════════════════════════════════════
run($db, "
CREATE TABLE IF NOT EXISTS `user` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `telegram_id` BIGINT       NOT NULL DEFAULT 0,
  `login`       VARCHAR(64)  NOT NULL DEFAULT '',
  `type`        TINYINT      NOT NULL DEFAULT 1,
  `level`       INT          NOT NULL DEFAULT 0,
  `exp`         INT          NOT NULL DEFAULT 0,
  `nv`          INT          NOT NULL DEFAULT 100,
  `hp`          INT          NOT NULL DEFAULT 50,
  `hp_all`      INT          NOT NULL DEFAULT 50,
  `sila`        INT          NOT NULL DEFAULT 5,
  `lovk`        INT          NOT NULL DEFAULT 5,
  `uda4a`       INT          NOT NULL DEFAULT 5,
  `zdorov`      INT          NOT NULL DEFAULT 5,
  `znan`        INT          NOT NULL DEFAULT 5,
  `mudr`        INT          NOT NULL DEFAULT 5,
  `free_stat`   INT          NOT NULL DEFAULT 0,
  `loc`         INT          NOT NULL DEFAULT 1,
  `pos`         VARCHAR(20)  NOT NULL DEFAULT '1000_1000',
  `last`        INT          NOT NULL DEFAULT 0,
  `last_battle` INT          NOT NULL DEFAULT 0,
  `last_work`   INT          NOT NULL DEFAULT 0,
  `umen`        VARCHAR(50)  NOT NULL DEFAULT '',
  `obraz`       VARCHAR(255) NOT NULL DEFAULT '',
  `f_obraz`     VARCHAR(255) NOT NULL DEFAULT '',
  `chcolor`     VARCHAR(10)  NOT NULL DEFAULT '000000',
  `st`          TINYINT      NOT NULL DEFAULT 0,
  `affect`      VARCHAR(255) NOT NULL DEFAULT '',
  `block`       TINYINT      NOT NULL DEFAULT 0,
  `pass`        VARCHAR(255) NOT NULL DEFAULT '',
  `email`       VARCHAR(100) NOT NULL DEFAULT '',
  `icq`         VARCHAR(30)  NOT NULL DEFAULT '',
  `name`        VARCHAR(100) NOT NULL DEFAULT '',
  `country`     VARCHAR(50)  NOT NULL DEFAULT '',
  `city`        VARCHAR(50)  NOT NULL DEFAULT '',
  `bday`        VARCHAR(20)  NOT NULL DEFAULT '',
  `url`         VARCHAR(255) NOT NULL DEFAULT '',
  `sex`         VARCHAR(5)   NOT NULL DEFAULT '',
  `thotem`      VARCHAR(50)  NOT NULL DEFAULT '',
  `bdaypers`    VARCHAR(50)  NOT NULL DEFAULT '',
  `ip`          VARCHAR(45)  NOT NULL DEFAULT '',
  `pcid`        VARCHAR(100) NOT NULL DEFAULT '',
  `about`       TEXT,
  `addon`       TEXT,
  `licens`      TINYINT      NOT NULL DEFAULT 0,
  `options`     TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegram_id` (`telegram_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "CREATE TABLE user");

// ═══════════════════════════════════════════════════════════
//  TABLE: items
// ═══════════════════════════════════════════════════════════
run($db, "
CREATE TABLE IF NOT EXISTS `items` (
  `id`     INT          NOT NULL AUTO_INCREMENT,
  `name`   VARCHAR(100) NOT NULL DEFAULT '',
  `gif`    VARCHAR(255) NOT NULL DEFAULT '',
  `massa`  INT          NOT NULL DEFAULT 1,
  `price`  INT          NOT NULL DEFAULT 10,
  `level`  INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "CREATE TABLE items");

// ═══════════════════════════════════════════════════════════
//  TABLE: market
// ═══════════════════════════════════════════════════════════
run($db, "
CREATE TABLE IF NOT EXISTS `market` (
  `id`   INT         NOT NULL,
  `kol`  INT         NOT NULL DEFAULT 99,
  `type` VARCHAR(10) NOT NULL DEFAULT 'w4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "CREATE TABLE market");

// ═══════════════════════════════════════════════════════════
//  TABLE: invent
// ═══════════════════════════════════════════════════════════
run($db, "
CREATE TABLE IF NOT EXISTS `invent` (
  `id`       INT     NOT NULL AUTO_INCREMENT,
  `pl_id`    INT     NOT NULL DEFAULT 0,
  `protype`  INT     NOT NULL DEFAULT 0,
  `equipped` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `pl_id` (`pl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "CREATE TABLE invent");

// ═══════════════════════════════════════════════════════════
//  TABLE: chat
// ═══════════════════════════════════════════════════════════
run($db, "
CREATE TABLE IF NOT EXISTS `chat` (
  `id`     INT          NOT NULL AUTO_INCREMENT,
  `pl_id`  INT          NOT NULL DEFAULT 0,
  `login`  VARCHAR(64)  NOT NULL DEFAULT '',
  `msg`    TEXT,
  `time`   INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `pl_id` (`pl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "CREATE TABLE chat");

// ═══════════════════════════════════════════════════════════
//  SEED: items + market (only if empty)
// ═══════════════════════════════════════════════════════════
$count = $db->query("SELECT COUNT(*) AS n FROM items")->fetch_assoc()['n'];
if ($count == 0) {
    echo "[migrate] Seeding items and market...\n";

    $items = [
        // [name, gif, massa, price, level, market_type]
        // ── Оружие (w4) ──────────────────────────
        ['Деревянный меч',      'items/sword_wood.gif',    2,   20,  0,  'w4'],
        ['Железный меч',        'items/sword_iron.gif',    4,   80,  2,  'w4'],
        ['Стальной меч',        'items/sword_steel.gif',   5,  200,  5,  'w4'],
        ['Боевой топор',        'items/axe.gif',           7,  350,  8,  'w4'],
        ['Рыцарский меч',       'items/sword_knight.gif',  6,  600, 12,  'w4'],
        ['Дракенблейд',         'items/sword_dragon.gif',  8, 1500, 20,  'w4'],

        // ── Броня (a) ────────────────────────────
        ['Кожаная броня',       'items/armor_leather.gif', 3,   60,  0,  'a'],
        ['Кольчуга',            'items/armor_chain.gif',   8,  180,  3,  'a'],
        ['Пластинчатая броня',  'items/armor_plate.gif',  12,  500,  7,  'a'],
        ['Рыцарские доспехи',   'items/armor_knight.gif', 15, 1000, 15,  'a'],

        // ── Шлем (h) ────────────────────────────
        ['Кожаный шлем',        'items/helm_leather.gif',  2,   40,  0,  'h'],
        ['Железный шлем',       'items/helm_iron.gif',     4,  120,  3,  'h'],
        ['Стальной шлем',       'items/helm_steel.gif',    5,  300,  8,  'h'],
        ['Рыцарский шлем',      'items/helm_knight.gif',   6,  700, 15,  'h'],

        // ── Щит (s) ─────────────────────────────
        ['Деревянный щит',      'items/shield_wood.gif',   4,   50,  0,  's'],
        ['Железный щит',        'items/shield_iron.gif',   7,  150,  3,  's'],
        ['Стальной щит',        'items/shield_steel.gif',  9,  400,  8,  's'],

        // ── Сапоги (b) ──────────────────────────
        ['Кожаные сапоги',      'items/boots_leather.gif', 2,   35,  0,  'b'],
        ['Железные сапоги',     'items/boots_iron.gif',    4,  110,  4,  'b'],
        ['Стальные сапоги',     'items/boots_steel.gif',   5,  280,  9,  'b'],

        // ── Кольцо (r) ──────────────────────────
        ['Медное кольцо',       'items/ring_copper.gif',   1,   45,  0,  'r'],
        ['Серебряное кольцо',   'items/ring_silver.gif',   1,  130,  4,  'r'],
        ['Золотое кольцо',      'items/ring_gold.gif',     1,  350, 10,  'r'],

        // ── Амулет (am) ─────────────────────────
        ['Амулет силы',         'items/amulet_str.gif',    1,   80,  2,  'am'],
        ['Амулет ловкости',     'items/amulet_dex.gif',    1,  120,  5,  'am'],
        ['Амулет мудрости',     'items/amulet_wis.gif',    1,  200, 10,  'am'],
    ];

    $stmt = $db->prepare("INSERT INTO items (name, gif, massa, price, level) VALUES (?,?,?,?,?)");
    $mStmt = $db->prepare("INSERT INTO market (id, kol, type) VALUES (?,99,?)");

    foreach ($items as $item) {
        [$name, $gif, $massa, $price, $level, $mtype] = $item;
        $stmt->bind_param('ssiii', $name, $gif, $massa, $price, $level);
        $stmt->execute();
        $newId = $db->insert_id;
        $mStmt->bind_param('is', $newId, $mtype);
        $mStmt->execute();
    }

    echo "[migrate] Seeded " . count($items) . " items\n";
} else {
    echo "[migrate] Items already seeded ($count rows), skipping\n";
}

echo "[migrate] Done!\n";
$db->close();
