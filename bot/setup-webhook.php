<?php
/**
 * Setup Telegram Webhook
 * Run this once after deploying to Railway to register the webhook URL.
 * Access via: https://your-railway-url.up.railway.app/bot/setup-webhook.php
 */

require_once __DIR__ . '/../config/config.php';

$token = TELEGRAM_BOT_TOKEN;

// Auto-detect Railway URL
$host = getenv('RAILWAY_PUBLIC_DOMAIN') ?: ($_SERVER['HTTP_HOST'] ?? null);
if (!$host) {
    die("❌ Cannot detect host. Set RAILWAY_PUBLIC_DOMAIN env var or access via HTTP.\n");
}

$webhookUrl = "https://{$host}/bot/webhook.php";

echo "Настройка webhook...\n";
echo "URL: {$webhookUrl}\n\n";

$url = "https://api.telegram.org/bot{$token}/setWebhook";
$data = ['url' => $webhookUrl, 'allowed_updates' => ['message', 'callback_query']];
$options = ['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode($data)]];

$result   = file_get_contents($url, false, stream_context_create($options));
$response = json_decode($result, true);

if ($response['ok'] ?? false) {
    echo "✅ Webhook успешно настроен!\n";
    echo "URL: {$webhookUrl}\n";
} else {
    echo "❌ Ошибка:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

echo "\n\n=== Статус webhook ===\n";
$info = file_get_contents("https://api.telegram.org/bot{$token}/getWebhookInfo");
echo json_encode(json_decode($info, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
