# 🚀 БЫСТРЫЙ ДЕПЛОЙ - ИНСТРУКЦИЯ

## Проблема с локальным backend:
Ваш IP динамический, поэтому нужен стабильный туннель для backend API.

## ✅ РЕШЕНИЕ (2 шага):

### Шаг 1: Создайте бесплатный аккаунт ngrok

1. Откройте: https://dashboard.ngrok.com/signup
2. Зарегистрируйтесь (через Google/GitHub)
3. Получите authtoken на: https://dashboard.ngrok.com/get-started/your-authtoken
4. Скопируйте команду типа:
   ```
   ngrok config add-authtoken ВАШТОКЕН
   ```

### Шаг 2: Запустите ngrok для backend API

```bash
# Скачайте ngrok с https://ngrok.com/download
# Или через brew:
brew install ngrok

# Добавьте токен (из шага 1)
ngrok config add-authtoken ВАШ_ТОКЕН

# Запустите туннель для backend API
ngrok http 8888
```

Вы получите URL типа:
```
https://abc-123-def.ngrok-free.app
```

### Шаг 3: Деплой на Vercel

```bash
cd /Applications/MAMP/htdocs/NLTv1/frontend

# Создайте .env.production
echo "REACT_APP_API_URL=https://abc-123-def.ngrok-free.app/NLTv1/backend/api" > .env.production

# Деплой на Vercel
npx vercel --prod

# Следуйте инструкциям:
# 1. Login (откроется браузер)
# 2. Setup project? Y
# 3. Link to existing project? N
# 4. Project name? neverlands
# 5. Override settings? N
```

### Шаг 4: Обновите бота

После деплоя получите URL типа: `https://neverlands-xxx.vercel.app`

```bash
# Обновите бота с новым URL
sed -i '' 's|full-suns-search.loca.lt|neverlands-xxx.vercel.app|g' /Applications/MAMP/htdocs/NLTv1/backend/bot/polling.php

# Перезапустите бота
pkill -f polling.php
cd /Applications/MAMP/htdocs/NLTv1/backend/bot && nohup /Applications/MAMP/bin/php/php8.2.0/bin/php polling.php > /tmp/bot.log 2>&1 &
```

---

## 🎯 ИЛИ ПРОЩЕ - ВСЁ НА RAILWAY:

Если не хотите возиться с ngrok, давайте деплоим ВСЁ на Railway!

Нужно:
1. Создать аккаунт на railway.app
2. Загрузить проект на GitHub
3. Railway автоматически задеплоит

Выбирайте! Помогу с любым вариантом! 🚀
