# 🚀 Быстрый деплой NeverLands

## ⚡ САМЫЙ БЫСТРЫЙ СПОСОБ (5 минут):

### Используем Vercel для frontend + Railway для backend

---

## 📦 FRONTEND на Vercel:

```bash
cd /Applications/MAMP/htdocs/NLTv1/frontend

# 1. Обновите URL API в .env
echo "REACT_APP_API_URL=https://YOUR-BACKEND.railway.app/api" > .env.production

# 2. Установите Vercel CLI
npm install -g vercel

# 3. Деплой
vercel --prod

# Получите URL: https://neverlands-xxx.vercel.app
```

---

## 🔧 BACKEND на Railway:

### Вариант A: Через интерфейс (проще)

1. **Зайдите:** https://railway.app
2. **Login** через GitHub
3. **New Project** → **Deploy from GitHub repo**
4. **Connect repo** или **Deploy from local**
5. **Add variables:**
   - `DB_HOST` = ваш MySQL хост
   - `DB_USER` = root
   - `DB_PASS` = пароль
   - `DB_NAME` = nl

### Вариант B: Через CLI

```bash
cd /Applications/MAMP/htdocs/NLTv1/backend

# Установка Railway CLI
npm install -g @railway/cli

# Login
railway login

# Создание проекта
railway init

# Деплой
railway up

# Получите URL
railway domain
```

---

## 🎯 АЛЬТЕРНАТИВА: Все в одном на Heroku

```bash
cd /Applications/MAMP/htdocs/NLTv1

# Установка Heroku CLI
# https://devcenter.heroku.com/articles/heroku-cli

# Login
heroku login

# Создание приложения
heroku create neverlands-game

# Деплой
git push heroku main

# Настройка БД
heroku addons:create jawsdb:kitefin

# URL: https://neverlands-game.herokuapp.com
```

---

## ✅ ПОСЛЕ ДЕПЛОЯ:

1. **Обновите бота:**
```bash
# В polling.php измените:
$webAppUrl = "https://ваш-frontend.vercel.app";
```

2. **Перезапустите бота**

3. **Готово!** Игра работает 24/7

---

Какой способ выбираете? Помогу настроить!
