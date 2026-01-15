#!/bin/bash

echo "🚀 Настройка NeverLands для Telegram"
echo "===================================="
echo ""

# Проверяем, запущен ли frontend
if ! curl -s http://localhost:3000 > /dev/null 2>&1; then
    echo "❌ Frontend не запущен на порту 3000"
    echo "Сначала запустите: cd /Applications/MAMP/htdocs/NLTv1/frontend && npm start"
    exit 1
fi

echo "✅ Frontend запущен"
echo ""

# Проверяем ngrok
if command -v ngrok &> /dev/null; then
    echo "✅ ngrok установлен"
    echo ""
    echo "Запускаю ngrok туннель..."
    echo "После запуска скопируйте URL вида: https://xxxx.ngrok-free.app"
    echo ""
    ngrok http 3000
else
    echo "⚠️  ngrok не установлен"
    echo ""
    echo "Установите одним из способов:"
    echo ""
    echo "1. Через Homebrew (если установлен):"
    echo "   brew install ngrok"
    echo ""
    echo "2. Скачать с сайта:"
    echo "   https://ngrok.com/download"
    echo "   Распакуйте и переместите в /usr/local/bin/"
    echo ""
    echo "3. Используйте альтернативу - localtunnel:"
    echo "   npm install -g localtunnel"
    echo "   lt --port 3000"
    echo ""
fi
