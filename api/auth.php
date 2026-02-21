<?php
/**
 * Authentication API
 * Handles Telegram WebApp authentication with HMAC-SHA256 validation
 *
 * POST /api/auth  { initData, user }
 */

$db = Database::getInstance();

/**
 * Проверяет подпись initData от Telegram WebApp.
 * https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
 */
function validateTelegramInitData(string $initData, string $botToken): bool
{
    if (!$initData || !$botToken) return false;

    // Разобрать строку как URL query string
    $params = [];
    foreach (explode('&', $initData) as $chunk) {
        $pair = explode('=', $chunk, 2);
        if (count($pair) === 2) {
            $params[urldecode($pair[0])] = urldecode($pair[1]);
        }
    }

    $receivedHash = $params['hash'] ?? '';
    if (!$receivedHash) return false;
    unset($params['hash']);

    // Отсортировать по ключу и собрать строку проверки
    ksort($params);
    $checkParts = [];
    foreach ($params as $key => $value) {
        $checkParts[] = "$key=$value";
    }
    $checkString = implode("\n", $checkParts);

    // HMAC-SHA256: секрет = HMAC("WebAppData", bot_token)
    $secretKey    = hash_hmac('sha256', $botToken, 'WebAppData', true);
    $expectedHash = hash_hmac('sha256', $checkString, $secretKey);

    return hash_equals($expectedHash, $receivedHash);
}

if ($request_method === 'POST') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $initData = $input['initData'] ?? '';
    $userData = $input['user']     ?? null;

    if (!$userData) {
        jsonError('Не переданы данные пользователя');
    }

    // Проверка подписи Telegram (пропускается только если токен не задан — dev-режим)
    $botToken = TELEGRAM_BOT_TOKEN;
    if ($botToken) {
        if (!$initData) {
            jsonError('initData обязателен в production', 403);
        }
        if (!validateTelegramInitData($initData, $botToken)) {
            jsonError('Неверная подпись Telegram. Откройте приложение заново.', 403);
        }
        // Проверка срока действия (24 часа)
        $params   = [];
        foreach (explode('&', $initData) as $chunk) {
            $pair = explode('=', $chunk, 2);
            if (count($pair) === 2) $params[urldecode($pair[0])] = urldecode($pair[1]);
        }
        $authDate = intval($params['auth_date'] ?? 0);
        if ($authDate && (time() - $authDate) > 86400) {
            jsonError('Данные авторизации устарели. Перезапустите приложение.', 403);
        }
    }

    $telegramId = $userData['id'];
    $username   = $userData['username']   ?? 'User' . $telegramId;
    $firstName  = $userData['first_name'] ?? '';
    $lastName   = $userData['last_name']  ?? '';

    // Проверить существующего пользователя
    $stmt = $db->prepare("SELECT * FROM user WHERE telegram_id = ? LIMIT 1");
    if (!$stmt) {
        jsonError('DB prepare error: ' . $db->getConnection()->error, 500);
    }
    $stmt->bind_param('i', $telegramId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $updateStmt = $db->prepare("UPDATE user SET last = UNIX_TIMESTAMP() WHERE id = ?");
        $updateStmt->bind_param('i', $user['id']);
        $updateStmt->execute();

        jsonSuccess([
            'user' => [
                'id'          => $user['id'],
                'telegram_id' => $telegramId,
                'login'       => from_win($user['login']),
                'level'       => $user['level'],
                'nv'          => $user['nv']
            ],
            'isNew' => false
        ], 'Вход выполнен');

    } else {
        // Новый пользователь
        $login = t($username);

        $insertStmt = $db->prepare("
            INSERT INTO user (
                telegram_id, login, type, level, nv, loc, pos, last,
                pass, email, icq, name, country, city, bday, url, sex,
                thotem, bdaypers, ip, pcid, chcolor, obraz, f_obraz, st,
                affect, umen, block, about, addon, licens, options
            ) VALUES (
                ?, ?, 1, 0, 100, 1, '1000_1000', UNIX_TIMESTAMP(),
                '', '', '', '', '', '', '', '', '',
                '', '', '', '', '000000', '', '', '',
                '', '', '', '', '', '', ''
            )
        ");
        if (!$insertStmt) {
            jsonError('DB prepare error (insert): ' . $db->getConnection()->error, 500);
        }
        $insertStmt->bind_param('is', $telegramId, $login);

        if ($insertStmt->execute()) {
            $newUserId = $db->getLastInsertId();

            jsonSuccess([
                'user' => [
                    'id'          => $newUserId,
                    'telegram_id' => $telegramId,
                    'login'       => $username,
                    'level'       => 0,
                    'nv'          => 100
                ],
                'isNew' => true
            ], 'Регистрация успешна');
        } else {
            jsonError('Ошибка создания аккаунта: ' . $insertStmt->error);
        }
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
