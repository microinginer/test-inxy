## Сборка образа

```bash
docker compose build
```

## Установка зависимостей

```bash
docker compose run --rm composer install
```

## Запуск проекта

```bash
docker compose up -d nginx
```

## Генерация ключа приложения

```bash
docker compose run --rm artisan key:generate
```

## Запуск миграций

```bash
docker compose run --rm artisan migrate
```
