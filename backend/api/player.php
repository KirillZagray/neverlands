<?php
/**
 * Player API - auto-generated from sql_func.php
 */

$db = Database::getInstance()->getConnection();

if ($request_method === 'GET') {
    // Get player data
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        jsonError('user_id required');
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
        jsonError('Player not found', 404);
    }

    $player = $result->fetch_assoc();

    // Convert encoding for strings
    $player['login'] = from_win($player['login']);

    jsonSuccess($player, 'Player data retrieved');

} elseif ($request_method === 'PUT') {
    // Update player stats
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? null;
    $stats = $input['stats'] ?? null;

    if (!$userId || !$stats) {
        jsonError('user_id and stats required');
    }

    // Build UPDATE query
    $updates = [];
    $types = '';
    $values = [];

    foreach ($stats as $stat => $value) {
        $allowed = ['sila', 'lovk', 'uda4a', 'zdorov', 'znan', 'mudr'];
        if (in_array($stat, $allowed)) {
            $updates[] = "$stat = ?";
            $types .= 'i';
            $values[] = $value;
        }
    }

    if (empty($updates)) {
        jsonError('No valid stats to update');
    }

    $types .= 'i';
    $values[] = $userId;

    $sql = "UPDATE user SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        jsonSuccess(null, 'Stats updated');
    } else {
        jsonError('Failed to update stats');
    }

} else {
    jsonError('Method not allowed', 405);
}
?>