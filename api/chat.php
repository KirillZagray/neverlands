<?php
$db = Database::getInstance()->getConnection();
if ($request_method === 'GET') {
    $limit = intval($_GET['limit'] ?? 50);
    $result = $db->query("SELECT * FROM chat ORDER BY id DESC LIMIT $limit");
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $row['msg'] = from_win($row['msg'] ?? '');
        $messages[] = $row;
    }
    jsonSuccess(['messages' => array_reverse($messages)], 'Chat messages retrieved');
} else {
    jsonError('Method not allowed', 405);
}
