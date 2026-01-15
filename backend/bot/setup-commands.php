<?php
/**
 * Setup Bot Commands
 */

require_once __DIR__ . '/../config/config.php';

$token = TELEGRAM_BOT_TOKEN;

// Set bot commands
$commands = [
    ['command' => 'start', 'description' => 'Начать игру'],
    ['command' => 'help', 'description' => 'Помощь']
];

$url = "https://api.telegram.org/bot{$token}/setMyCommands";

$data = [
    'commands' => $commands
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$response = json_decode($result, true);

if ($response['ok']) {
    echo "✅ Команды бота успешно настроены!\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo "❌ Ошибка настройки команд:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
