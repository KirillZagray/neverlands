<?php
/**
 * Telegram Bot Webhook Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get incoming update from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Log update for debugging
error_log("Telegram Update: " . print_r($update, true));

if (!$update) {
    exit;
}

// Extract message data
$message = $update['message'] ?? null;
$chatId = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';
$from = $message['from'] ?? null;

if (!$chatId) {
    exit;
}

// Handle commands
if ($text === '/start') {
    sendMessage($chatId, "🎮 Добро пожаловать в NeverLands!\n\nНажмите кнопку MENU внизу, чтобы начать игру!");
    exit;
}

if ($text === '/help') {
    sendMessage($chatId, "📖 Помощь:\n\n/start - Начать\n/help - Помощь\n\nИспользуйте кнопку MENU для запуска игры!");
    exit;
}

// Send message function
function sendMessage($chatId, $text) {
    $token = TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML'
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

    return json_decode($result, true);
}
