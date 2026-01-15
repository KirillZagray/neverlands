# 🚀 Деплой NeverLands на внешний сервер

## Варианты (от простого к сложному):

### ✅ Вариант 1: Railway.app (РЕКОМЕНДУЕТСЯ - 5 минут)
**Плюсы:** Бесплатно, автодеплой из GitHub, SSL из коробки
**Минусы:** Лимит 500 часов/месяц бесплатно

### ✅ Вариант 2: Vercel (для frontend)
**Плюсы:** Бесплатно навсегда, очень быстро
**Минусы:** Нужен отдельный backend

### ✅ Вариант 3: Яндекс Cloud
**Плюсы:** Российский сервер, полный контроль
**Минусы:** Нужна настройка, платно (но есть бесплатный триал)

---

## 🚂 ВАРИАНТ 1: Railway.app (БЫСТРЫЙ СТАРТ)

### Шаг 1: Подготовка проекта

Создадим файлы для деплоя:

1. **Dockerfile для backend:**
```dockerfile
FROM php:8.2-apache

# Install mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy backend files
COPY backend/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
```

2. **package.json для frontend (уже есть)**

### Шаг 2: Загрузка на GitHub

```bash
cd /Applications/MAMP/htdocs/NLTv1

# Инициализация git (если еще не сделано)
git init
git add .
git commit -m "Initial commit - NeverLands Telegram Mini App"

# Создайте репозиторий на GitHub.com
# Затем:
git remote add origin https://github.com/ВАШ_USERNAME/neverlands.git
git push -u origin main
```

### Шаг 3: Деплой на Railway

1. Зайдите на https://railway.app
2. Войдите через GitHub
3. New Project → Deploy from GitHub repo
4. Выберите репозиторий neverlands
5. Railway автоматически задеплоит!
6. Получите URL типа: https://neverlands-production.up.railway.app

### Шаг 4: Настройка базы данных

Railway предоставит PostgreSQL, но у нас MySQL. Нужно:
1. Add service → Database → MySQL
2. Скопируйте credentials
3. Импортируйте дамп базы

---

## ⚡ ВАРИАНТ 2: Vercel (САМЫЙ БЫСТРЫЙ)

### Для frontend:

```bash
cd /Applications/MAMP/htdocs/NLTv1/frontend

# Установите Vercel CLI
npm install -g vercel

# Деплой
vercel

# Следуйте инструкциям
# Получите URL типа: https://neverlands.vercel.app
```

### Для backend:
Используйте Railway или другой PHP хостинг для backend.

---

## ☁️ ВАРИАНТ 3: Яндекс Cloud (ПОЛНЫЙ КОНТРОЛЬ)

### Стоимость:
- ~500₽/месяц за минимальную VM
- Первые 60 дней - грант 4000₽

### Быстрая настройка:

1. **Создайте VM:**
   - OS: Ubuntu 22.04
   - vCPU: 2
   - RAM: 2GB
   - Диск: 10GB

2. **Подключитесь по SSH:**
   ```bash
   ssh ubuntu@ВАШ_IP
   ```

3. **Установите окружение:**
   ```bash
   # Обновление
   sudo apt update && sudo apt upgrade -y

   # LAMP stack
   sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-curl php8.1-mbstring -y

   # Node.js
   curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
   sudo apt install nodejs -y

   # Копирование файлов
   # Используйте scp или git clone
   ```

4. **Настройте Apache:**
   ```bash
   sudo nano /etc/apache2/sites-available/neverlands.conf
   ```

5. **SSL через Let's Encrypt:**
   ```bash
   sudo apt install certbot python3-certbot-apache -y
   sudo certbot --apache -d yourdomain.com
   ```

---

## 🎯 МОЯ РЕКОМЕНДАЦИЯ:

**Используйте Railway для быстрого старта!**

Потом можно мигрировать на Яндекс Cloud если нужен полный контроль.

---

Какой вариант выбираете? Я помогу настроить!
