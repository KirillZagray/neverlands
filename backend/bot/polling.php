<?php
/**
 * Telegram Bot Polling (getUpdates)
 * Для локального тестирования без webhook
 */

require_once __DIR__ . '/../config/config.php';

$token = TELEGRAM_BOT_TOKEN;
$offset = 0;

echo "🤖 Бот запущен! Ожидаю команды...\n";
echo "Нажмите Ctrl+C для остановки\n\n";

while (true) {
    // Get updates
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=30";

    $updates = @file_get_contents($url);
    if (!$updates) {
        sleep(1);
        continue;
    }

    $data = json_decode($updates, true);

    if (!$data['ok'] || empty($data['result'])) {
        sleep(1);
        continue;
    }

    foreach ($data['result'] as $update) {
        $offset = $update['update_id'] + 1;

        // Process message
        $message = $update['message'] ?? null;
        if (!$message) continue;

        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];

        echo "[" . date('H:i:s') . "] Сообщение от {$from['first_name']} ({$chatId}): {$text}\n";

        // Handle commands
        if ($text === '/start') {
            sendMessageWithButton($chatId, "🎮 Добро пожаловать в NeverLands!\n\nНажмите кнопку ниже, чтобы начать игру!");
            echo "  → Отправлен ответ /start с кнопкой\n";
        }
        elseif ($text === '/help') {
            sendMessage($chatId, "📖 Помощь:\n\n/start - Начать игру\n/help - Помощь\n\nИспользуйте кнопку 'Играть 🎮' для запуска игры!");
            echo "  → Отправлен ответ /help\n";
        }
        else {
            echo "  → Неизвестная команда\n";
        }
    }
}

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
    @file_get_contents($url, false, $context);
}

function sendMessageWithButton($chatId, $text) {
    $token = TELEGRAM_BOT_TOKEN;
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $webAppUrl = "https://full-suns-search.loca.lt";

    $data = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => [
            'inline_keyboard' => [[
                [
                    'text' => '🎮 Начать игру',
                    'web_app' => ['url' => $webAppUrl]
                ]
            ]]
        ]
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    @file_get_contents($url, false, $context);
}
