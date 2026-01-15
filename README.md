# NeverLands Telegram Mini App v1

Telegram Mini App версия браузерной MMORPG "NeverLands"

## Архитектура

- **Frontend**: React + Telegram WebApp SDK
- **Backend**: PHP REST API
- **Database**: MySQL (nl database)
- **Assets**: 46MB изображений и ресурсов

## Структура проекта

```
NLTv1/
├── backend/           # PHP REST API
│   ├── api/          # API endpoints
│   ├── config/       # Конфигурация
│   ├── models/       # Модели данных
│   └── utils/        # Утилиты
├── frontend/         # React приложение
│   ├── src/
│   │   ├── components/  # React компоненты
│   │   ├── pages/       # Страницы
│   │   ├── services/    # API сервисы
│   │   ├── styles/      # Стили
│   │   └── utils/       # Утилиты
│   └── public/
│       ├── assets/      # Статические файлы
│       └── images/      # Изображения
└── database/         # SQL скрипты

## Запуск локально

1. Backend: PHP 8.2 + MySQL
2. Frontend: `npm start`
3. Telegram Bot: ngrok для тестирования

## Основной функционал

- ✅ Авторизация через Telegram
- ✅ Персонаж (характеристики, уровень)
- ✅ Инвентарь
- ✅ Магазин (покупка/продажа)
- ✅ Карта (перемещение)
- ✅ Боты/NPC
- ✅ Чат
- ✅ Бои

## Технологии

- React 18
- Telegram WebApp SDK
- PHP 8.2
- MySQL 8.0
- REST API
# neverlands
