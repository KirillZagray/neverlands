<?php
/**
 * Map API
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        jsonError('user_id required');
    }

    $stmt = $db->prepare("SELECT loc, pos FROM user WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    jsonSuccess($result, 'Position retrieved');

} elseif ($request_method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $newPos = $input['pos'] ?? null;

    if (!$userId || !$newPos) {
        jsonError('user_id and pos required');
    }

    $stmt = $db->prepare("UPDATE user SET pos = ? WHERE id = ?");
    $stmt->bind_param('si', $newPos, $userId);

    if ($stmt->execute()) {
        jsonSuccess(null, 'Position updated');
    } else {
        jsonError('Failed to update position');
    }

} else {
    jsonError('Method not allowed', 405);
}
?>