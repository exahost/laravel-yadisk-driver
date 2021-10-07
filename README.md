
# Использование

## 1. Зарегистрировать приложение в Yandex

> включить Яндекс.Диск REST API

https://oauth.yandex.ru

## 2. Получить токен
`https://oauth.yandex.ru/authorize?response_type=token&client_id={ID приложения}`

## 3. Прописать настройки в .env 

**Обязательные**

```
YANDEX_DISK_OAUTH_TOKEN= - Токен яндекс диска 
```

**Не обязательные**

```
YANDEX_DISK_CACHE_TIME=900 - Время кеширования
YANDEX_DISK_ON=true - Вкл./откл. загрузки файлов в Яндекс диск
YANDEX_DISK_BASE_PATH=storage/ - Путь к корневой папке Яндекс диска
```

## 4. Примеры использования

```
Storage::disk('yandex-disk')->exists('path/to/file.txt');
Storage::disk('yandex-disk')->get('path/to/file.txt');
Storage::disk('yandex-disk')->put('path/to/file.txt', 'file content ...');
```
