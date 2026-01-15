<?php
/**
 * Test Bot - Send message
 */

require_once __DIR__ . '/../config/config.php';

$token = TELEGRAM_BOT_TOKEN;

// Your chat ID (вставьте свой chat_id после первого /start)
$chatId = isset($argv[1]) ? $argv[1] : null;

if (!$chatId) {
    echo "Использование: php test-bot.php YOUR_CHAT_ID\n";
    echo "\nЧтобы узнать свой chat_id:\n";
    echo "1. Откройте бота в Telegram\n";
    echo "2. Отправьте /start\n";
    echo "3. Проверьте логи: tail -f /Applications/MAMP/logs/php_error.log\n";
    exit;
}

// Send test message
$url = "https://api.telegram.org/bot{$token}/sendMessage";

$data = [
    'chat_id' => $chatId,
    'text' => "🎮 Тестовое сообщение от NeverLands!\n\nБот работает! Нажмите кнопку MENU для запуска игры."
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
    echo "✅ Сообщение отправлено!\n";
} else {
    echo "❌ Ошибка:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
