<?php
/**
 * CLI script: register Telegram webhook on Railway startup
 * Usage: php bot/register-webhook.php
 */

$token = getenv('TELEGRAM_BOT_TOKEN');
$host  = getenv('RAILWAY_PUBLIC_DOMAIN');

if (!$token) {
    echo "[webhook] TELEGRAM_BOT_TOKEN not set — skipping registration\n";
    exit(0);
}

if (!$host) {
    echo "[webhook] RAILWAY_PUBLIC_DOMAIN not set — skipping registration\n";
    exit(0);
}

$webhookUrl = "https://{$host}/bot/webhook.php";
echo "[webhook] Registering webhook: {$webhookUrl}\n";

$payload = json_encode([
    'url'             => $webhookUrl,
    'allowed_updates' => ['message', 'callback_query'],
]);

$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
        'content' => $payload,
        'timeout' => 10,
    ],
]);

$url    = "https://api.telegram.org/bot{$token}/setWebhook";
$result = @file_get_contents($url, false, $ctx);

if ($result === false) {
    echo "[webhook] Failed to reach Telegram API\n";
    exit(0);
}

$response = json_decode($result, true);
if (!empty($response['ok'])) {
    echo "[webhook] Webhook registered successfully\n";
} else {
    echo "[webhook] Warning: " . ($response['description'] ?? 'unknown error') . "\n";
}
