
# Использование

## 1. Зарегистрировать приложение в Yandex

> включить Яндекс.Диск REST API

https://oauth.yandex.ru

## 2. Получить токен
`https://oauth.yandex.ru/authorize?response_type=token&client_id={ID приложения}`

## 3. Прописать токен в .env 
`YANDEX_DISK_OAUTH_TOKEN=`

## 4. Примеры использования

```
Storage::disk('yandex-disk')->exists('path/to/file.txt');
Storage::disk('yandex-disk')->get('path/to/file.txt');
Storage::disk('yandex-disk')->put('path/to/file.txt', 'file content ...');
```
