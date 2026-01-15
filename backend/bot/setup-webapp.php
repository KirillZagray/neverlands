<?php
/**
 * Автоматическая настройка Web App и Menu Button
 */

require_once __DIR__ . '/../config/config.php';

$token = TELEGRAM_BOT_TOKEN;
$webAppUrl = "https://happy-places-design.loca.lt";

echo "🔧 Настройка Telegram Web App\n";
echo "==============================\n\n";

// 1. Set menu button with web app
echo "1. Настройка кнопки MENU...\n";

$menuData = [
    'menu_button' => [
        'type' => 'web_app',
        'text' => 'Играть 🎮',
        'web_app' => [
            'url' => $webAppUrl
        ]
    ]
];

$menuUrl = "https://api.telegram.org/bot{$token}/setChatMenuButton";

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($menuData)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($menuUrl, false, $context);
$response = json_decode($result, true);

if ($response['ok']) {
    echo "   ✅ Кнопка MENU настроена!\n";
} else {
    echo "   ❌ Ошибка: " . ($response['description'] ?? 'Unknown') . "\n";
}

// 2. Set bot description
echo "\n2. Установка описания бота...\n";

$descUrl = "https://api.telegram.org/bot{$token}/setMyDescription";
$descData = [
    'description' => 'Браузерная MMORPG NeverLands. Нажмите кнопку MENU для запуска игры!'
];

$options['http']['content'] = json_encode($descData);
$context = stream_context_create($options);
$result = file_get_contents($descUrl, false, $context);
$response = json_decode($result, true);

if ($response['ok']) {
    echo "   ✅ Описание установлено!\n";
} else {
    echo "   ❌ Ошибка: " . ($response['description'] ?? 'Unknown') . "\n";
}

// 3. Set short description
echo "\n3. Установка краткого описания...\n";

$shortDescUrl = "https://api.telegram.org/bot{$token}/setMyShortDescription";
$shortDescData = [
    'short_description' => '🎮 Браузерная MMORPG'
];

$options['http']['content'] = json_encode($shortDescData);
$context = stream_context_create($options);
$result = file_get_contents($shortDescUrl, false, $context);
$response = json_decode($result, true);

if ($response['ok']) {
    echo "   ✅ Краткое описание установлено!\n";
} else {
    echo "   ❌ Ошибка: " . ($response['description'] ?? 'Unknown') . "\n";
}

echo "\n==============================\n";
echo "✅ ГОТОВО!\n\n";
echo "📱 Что делать дальше:\n";
echo "1. Откройте бота в Telegram\n";
echo "2. Внизу слева должна появиться кнопка 'Играть 🎮'\n";
echo "3. Нажмите на неё - игра откроется!\n\n";
echo "🌐 Web App URL: {$webAppUrl}\n";
