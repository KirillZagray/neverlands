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
    sendMessageWithButton($chatId, "🎮 Добро пожаловать в NeverLands!\n\nНажмите кнопку ниже, чтобы начать игру!");
    exit;
}

if ($text === '/help') {
    sendMessageWithButton($chatId, "📖 Помощь:\n\n/start - Начать игру\n/help - Помощь\n\nНажмите кнопку, чтобы запустить игру!");
    exit;
}

// Отправить текстовое сообщение
function sendMessage($chatId, $text) {
    $token = TELEGRAM_BOT_TOKEN;
    $url   = "https://api.telegram.org/bot{$token}/sendMessage";
    $data  = ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML'];
    $opts  = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode($data)]];
    return json_decode(file_get_contents($url, false, stream_context_create($opts)), true);
}

// Отправить сообщение с инлайн-кнопкой открытия Mini App
function sendMessageWithButton($chatId, $text) {
    $token     = TELEGRAM_BOT_TOKEN;
    $url       = "https://api.telegram.org/bot{$token}/sendMessage";
    $webAppUrl = "https://neverlands-three.vercel.app";
    $data = [
        'chat_id'      => $chatId,
        'text'         => $text,
        'parse_mode'   => 'HTML',
        'reply_markup' => [
            'inline_keyboard' => [[
                ['text' => '🎮 Начать игру', 'web_app' => ['url' => $webAppUrl]]
            ]]
        ]
    ];
    $opts = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode($data)]];
    return json_decode(file_get_contents($url, false, stream_context_create($opts)), true);
}
