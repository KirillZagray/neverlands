<?php
/**
 * Chat API
 *
 * GET  /api/chat?limit=50   - получить последние сообщения
 * POST /api/chat            - отправить сообщение
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $limit = min(100, max(1, intval($_GET['limit'] ?? 50)));
    $result = $db->query("SELECT * FROM chat ORDER BY id DESC LIMIT $limit");
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $row['msg'] = from_win($row['msg'] ?? '');
        if (isset($row['login'])) {
            $row['login'] = from_win($row['login']);
        }
        $messages[] = $row;
    }
    jsonSuccess(['messages' => array_reverse($messages)], 'Сообщения получены');

} elseif ($request_method === 'POST') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $userId  = $input['user_id']  ?? null;
    $message = trim($input['message'] ?? '');

    if (!$userId) {
        jsonError('user_id обязателен');
    }

    if (empty($message)) {
        jsonError('Сообщение не может быть пустым');
    }

    if (mb_strlen($message, 'UTF-8') > 500) {
        jsonError('Сообщение слишком длинное (максимум 500 символов)');
    }

    // Получить login игрока
    $stmt = $db->prepare("SELECT login FROM user WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        jsonError('Игрок не найден', 404);
    }

    $msg   = t($message);
    $login = $user['login'];
    $time  = time();

    // Вставка сообщения (пробуем с полем time, потом без)
    $stmt = $db->prepare("INSERT INTO chat (pl_id, login, msg, time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('issi', $userId, $login, $msg, $time);

    if ($stmt->execute()) {
        jsonSuccess(['id' => $db->insert_id], 'Сообщение отправлено');
    } else {
        // Попробовать без поля time
        $stmt2 = $db->prepare("INSERT INTO chat (pl_id, login, msg) VALUES (?, ?, ?)");
        $stmt2->bind_param('iss', $userId, $login, $msg);
        if ($stmt2->execute()) {
            jsonSuccess(['id' => $db->insert_id], 'Сообщение отправлено');
        } else {
            jsonError('Не удалось отправить сообщение: ' . $db->error);
        }
    }

} else {
    jsonError('Метод не поддерживается', 405);
}
?>
