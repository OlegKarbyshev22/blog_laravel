# «Точка зрения» — учебный блог на Laravel 12

Проект выполняет требования лабораторной работы: публичный просмотр тем, регистрация и авторизация, комментарии с премодерацией, оценки 1–5, запись пользователей на темы, CRUD и защищённая панель администратора. На одну тему могут записаться не более трёх человек.

## Соответствие заданию

- Laravel 12 — требование «Laravel 10 и выше» выполнено;
- Laravel UI 4 и Bootstrap 5 — авторизация и Blade-интерфейс;
- администратор создаёт, просматривает, редактирует и удаляет публикации и категории;
- администратор одобряет, отклоняет и удаляет комментарии;
- администратор просматривает, подтверждает, отменяет и удаляет записи на темы;
- пользователь просматривает публикации, комментирует, оценивает и записывается на тему;
- лимит в три участника проверяется на сервере внутри транзакции;
- SQLite используется по умолчанию, MySQL/MariaDB через XAMPP также поддерживается;
- миграции, сидеры и автоматические тесты входят в проект.

## Развёртывание из GitHub

Склонируйте репозиторий и перейдите в каталог проекта:

```powershell
git clone https://github.com/OlegKarbyshev22/blog_laravel.git
cd blog_laravel
```

Установите PHP 8.2+ с расширениями `fileinfo`, `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, Composer и Node.js 20+. На Windows запустите `setup.bat`, затем сервер:

```powershell
.\setup.bat
php artisan serve
```

На Linux или macOS выполните те же шаги вручную:

```bash
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

Сайт откроется по адресу <http://127.0.0.1:8000>. Веб-серверу нужны права на запись в `storage` и `bootstrap/cache`. Для размещения на хостинге укажите `public` как корневой каталог сайта, задайте `APP_URL`, отключите отладку (`APP_DEBUG=false`) и замените пароли демонстрационных учётных записей.

## Быстрый запуск на другом Windows-компьютере

Установите:

1. PHP 8.2+ или XAMPP;
2. Composer;
3. Node.js 20+;
4. Git — если проект переносится через репозиторий.

В `php.ini` должны быть включены расширения:

```ini
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=pdo_mysql
```

Скопируйте проект и дважды запустите `setup.bat` либо выполните:

```powershell
.\setup.ps1
php artisan serve
```

Скрипт сам установит зависимости, создаст `.env` и SQLite-файл, применит миграции, добавит демонстрационные данные и соберёт frontend. После запуска откройте <http://127.0.0.1:8000>.

Тестовые аккаунты:

| Роль | Email | Пароль |
|---|---|---|
| Администратор | `admin@blog.test` | `password` |
| Пользователь | `user@blog.test` | `password` |

## 1. Выбор темы и проектирование

Тема приложения — блог с обсуждением и записью читателей на опубликованные темы. Публикация в предметной области одновременно является материалом для чтения и темой, на которую пользователь может записаться.

### 1.1 Таблицы, поля и типы

| Таблица | Основные поля | Назначение |
|---|---|---|
| `users` | `id bigint`, `name varchar(255)`, `email varchar(255) unique`, `password varchar(255)`, `role varchar(20)`, timestamps | Аккаунты и роли |
| `categories` | `id bigint`, `name varchar(100)`, `slug varchar(120) unique`, `description text nullable`, timestamps | Категории тем |
| `posts` | `id bigint`, `user_id bigint FK`, `category_id bigint FK`, `title varchar(200)`, `slug varchar(220) unique`, `excerpt text`, `body longtext`, `image_url varchar nullable`, `status varchar(20)`, `published_at timestamp nullable`, timestamps | Темы/публикации |
| `comments` | `id bigint`, `post_id bigint FK`, `user_id bigint FK`, `body text`, `status varchar(20)`, `moderated_by bigint FK nullable`, `moderated_at timestamp nullable`, timestamps | Комментарии и модерация |
| `ratings` | `id bigint`, `post_id bigint FK`, `user_id bigint FK`, `value tinyint`, timestamps, unique(`post_id`,`user_id`) | Оценки 1–5 |
| `enrollments` | `id bigint`, `post_id bigint FK`, `user_id bigint FK`, `status varchar(20)`, timestamps, unique(`post_id`,`user_id`) | Записи пользователей на темы |
| `sessions` | `id varchar`, `user_id bigint nullable`, `payload longtext`, `last_activity int` | Сессии авторизации |
| `password_reset_tokens` | `email varchar PK`, `token varchar`, `created_at timestamp` | Сброс пароля |
| `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | служебные поля Laravel | Кеш и очереди |

Статусы публикации: `draft`, `published`. Статусы комментария: `pending`, `approved`, `rejected`. Статусы записи: `confirmed`, `cancelled`.

### 1.2 Связи между таблицами

```text
Category 1 ─── N Post
User     1 ─── N Post
User     1 ─── N Comment N ─── 1 Post
User     1 ─── N Rating  N ─── 1 Post
User     1 ─── N Enrollment N ─── 1 Post
User     1 ─── N Comment (moderated_by — необязательная связь с модератором)
```

При удалении публикации каскадно удаляются комментарии, оценки и записи. Категорию, содержащую публикации, удалить нельзя. Один пользователь может поставить публикации только одну оценку и создать только одну запись на тему.

### 1.3 Карта экранов

```text
Публичная часть
├── Главная / — список, поиск, фильтр по категории, пагинация
├── Публикация /posts/{slug}
│   ├── чтение материала
│   ├── запись/отмена записи на тему и счётчик свободных мест
│   ├── оценка 1–5
│   └── форма и список одобренных комментариев
├── Вход /login
├── Регистрация /register
└── Восстановление пароля /password/*

Панель администратора /admin
├── Сводка
├── Публикации — список, просмотр, создание, изменение, удаление
├── Категории — список, создание, изменение, удаление
├── Комментарии — фильтр, одобрение, отклонение, удаление
└── Записи на темы — просмотр, фильтр, подтверждение, отмена, удаление
```

### 1.4 Роли и сценарии

**Гость:** читает опубликованные материалы, ищет и фильтрует темы, видит оценки, комментарии и количество свободных мест. Для действий переходит к регистрации или входу.

**Пользователь:** получает возможности гостя, ставит или изменяет одну оценку, отправляет комментарий на модерацию, записывается на тему при наличии места и отменяет собственную запись.

**Администратор:** открывает `/admin`, управляет публикациями и категориями, модерирует комментарии, обрабатывает записи на темы. Доступ защищён middleware `auth` и `admin`.

### 1.5 Формы

- регистрация, вход, восстановление и сброс пароля;
- поиск и фильтр публикаций;
- комментарий;
- оценка 1–5;
- запись на тему и отмена записи;
- создание и редактирование публикации;
- создание и редактирование категории;
- модерация комментария;
- обработка записи пользователя.

Все изменяющие запросы защищены CSRF-токенами. Данные проверяются Laravel Validator, пароли хешируются, Blade экранирует обычный вывод.

## 2–10. Реализация этапов

1. Архитектура и предметная область описаны выше.
2. Окружение: PHP/XAMPP, Node.js, Git, Composer.
3. База: SQLite без дополнительного сервера или MySQL через phpMyAdmin.
4. Зависимости устанавливаются командой `composer install`.
5. Настройки находятся в `.env`, шаблон — `.env.example`.
6. Схема создаётся миграциями, демонстрационные данные — сидером.
7. Ресурсные контроллеры администратора находятся в `app/Http/Controllers/Admin`.
8. Интерфейс построен на Blade; Laravel UI предоставляет авторизацию.
9. Маршруты находятся в `routes/web.php`.
10. Реализована собственная административная панель `/admin` на Laravel UI/Bootstrap. Оригинальный `encore/laravel-admin` не добавлен, поскольку он рассчитан на старые поколения Laravel. Доступный пакет `laravel-admin/base` заявляет поддержку Laravel 12, но его автор отмечает alpha-статус; для учебного проекта выбран совместимый и контролируемый Blade CRUD без дублирования пользователей и конфликтующих миграций.

## Вариант с MySQL и phpMyAdmin

1. Запустите Apache и MySQL в XAMPP.
2. В <http://localhost/phpmyadmin> создайте БД `blog_laravel` с кодировкой `utf8mb4_unicode_ci`.
3. Укажите в `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_laravel
DB_USERNAME=root
DB_PASSWORD=
```

4. Выполните:

```powershell
php artisan config:clear
php artisan migrate --seed
```

## Структура кода

- `app/Models` — Eloquent-модели и связи;
- `app/Http/Controllers` — публичные действия;
- `app/Http/Controllers/Admin` — административный CRUD и модерация;
- `app/Http/Middleware/EnsureUserIsAdmin.php` — проверка роли;
- `database/migrations` — воспроизводимая схема БД;
- `database/seeders/DatabaseSeeder.php` — демонстрационные аккаунты и данные;
- `resources/views` — Blade-шаблоны;
- `resources/sass/app.scss` — стили Bootstrap/Sass;
- `routes/web.php` — маршрутизация;
- `tests/Feature/BlogTest.php` — функциональные тесты, включая лимит трёх участников;
- `setup.ps1`, `setup.bat` — автоматическое развёртывание на Windows.

## Основные команды

```powershell
php artisan migrate --seed
php artisan route:list
php artisan test
npm run build
php artisan optimize:clear
php artisan serve
```

Для полной пересборки только учебной базы можно использовать `php artisan migrate:fresh --seed`. Эта команда удаляет существующие таблицы и данные.
