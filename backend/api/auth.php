<?php
/**
 * Authentication API
 * Handles Telegram WebApp authentication
 */

$db = Database::getInstance();

if ($request_method === 'POST') {
    // Get Telegram init data
    $input = json_decode(file_get_contents('php://input'), true);
    $initData = $input['initData'] ?? '';
    $userData = $input['user'] ?? null;

    if (!$userData) {
        jsonError('No user data provided');
    }

    // For local testing, skip Telegram validation
    // In production, validate init data against bot token
    $telegramId = $userData['id'];
    $username = $userData['username'] ?? 'User' . $telegramId;
    $firstName = $userData['first_name'] ?? '';
    $lastName = $userData['last_name'] ?? '';

    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM user WHERE telegram_id = ? LIMIT 1");
    $stmt->bind_param('i', $telegramId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Existing user - update last login
        $user = $result->fetch_assoc();

        $updateStmt = $db->prepare("UPDATE user SET last = UNIX_TIMESTAMP() WHERE id = ?");
        $updateStmt->bind_param('i', $user['id']);
        $updateStmt->execute();

        jsonSuccess([
            'user' => [
                'id' => $user['id'],
                'telegram_id' => $telegramId,
                'login' => from_win($user['login']),
                'level' => $user['level'],
                'nv' => $user['nv']
            ],
            'isNew' => false
        ], 'Login successful');

    } else {
        // New user - create account
        $login = t($username);
        $defaultPass = '';
        $defaultEmail = '';
        $defaultEmpty = '';

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
        $insertStmt->bind_param('is', $telegramId, $login);

        if ($insertStmt->execute()) {
            $newUserId = $db->getLastInsertId();

            jsonSuccess([
                'user' => [
                    'id' => $newUserId,
                    'telegram_id' => $telegramId,
                    'login' => $username,
                    'level' => 0,
                    'nv' => 100
                ],
                'isNew' => true
            ], 'Registration successful');
        } else {
            $error = $insertStmt->error;
            error_log("Insert error: " . $error);
            jsonError('Failed to create user: ' . $error);
        }
    }

} else {
    jsonError('Method not allowed', 405);
}
?>
