<?php
/**
 * Setup Telegram Webhook
 */

require_once __DIR__ . '/../config/config.php';

$token = TELEGRAM_BOT_TOKEN;

// Webhook URL — Railway backend
$webhookUrl = getenv('WEBHOOK_URL') ?: "https://neverlands-production.up.railway.app/bot/webhook.php";

echo "Настройка webhook...\n";
echo "URL: {$webhookUrl}\n\n";

// Set webhook
$url = "https://api.telegram.org/bot{$token}/setWebhook";

$data = [
    'url' => $webhookUrl,
    'allowed_updates' => ['message', 'callback_query']
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
    echo "✅ Webhook успешно настроен!\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\nТеперь бот будет отвечать на команды /start и /help\n";
} else {
    echo "❌ Ошибка настройки webhook:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// Check webhook status
echo "\n\n=== Проверка webhook ===\n";
$infoUrl = "https://api.telegram.org/bot{$token}/getWebhookInfo";
$info = file_get_contents($infoUrl);
$infoData = json_decode($info, true);
echo json_encode($infoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
