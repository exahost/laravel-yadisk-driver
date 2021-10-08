<?php

namespace ITPolice\YandexDisk;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ITPolice\YandexDisk\Jobs\YandexDiskDriverFileUpload;
use function Complex\sec;

class Helper
{
    /**
     * Загрузка файла
     * @param string $path - путь
     * @param string $disk - диск
     * @param string $queue - приоритет очереди
     * @param int $delay - задержка в секундах
     */
    public static function upload(string $path, string $disk = 'public', string $queue = 'low', int $delay = 0) {
        if(config('filesystems.disks.yandex-disk.on')) {
            $job = new YandexDiskDriverFileUpload($path);
            dispatch($job->onQueue($queue)->delay(now()->addSeconds($delay)));
        }
    }

    /**
     * Получаем содержимое файла
     * @param string $path - путь
     * @return mixed|string|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function getFile(string $path) {
        if(Storage::disk('public')->has($path)) {
            return Storage::disk('public')->get($path);
        }

        if(config('filesystems.disks.yandex-disk.on')) {
            $cacheTime = config('filesystems.disks.yandex-disk.cacheTime');
            return Cache::remember('ya-disk-storage_data_' . $path, $cacheTime, function () use ($path) {
                return Storage::disk('yandex-disk')->get($path);
            });
        }

        return null;
    }

    /**
     * Получаем метаданные файла
     * @param string $path - путь
     * @param string $prop
     * @return mixed|null
     */
    protected static function getFileMeta(string $path, string $prop) {
        if(Storage::disk('public')->has($path)) {
            return Storage::disk('public')->{$prop}($path);
        }

        if(config('filesystems.disks.yandex-disk.on')) {
            $cacheTime = config('filesystems.disks.yandex-disk.cacheTime');
            return Cache::remember('ya-disk-storage_'.$prop.'_' . $path, $cacheTime, function () use ($path, $prop) {
                return Storage::disk('yandex-disk')->{$prop}($path);
            });
        }

        return null;
    }

    /**
     * Получаем название диска на котором размещен файл
     * @param string $path - путь
     * @return string|null
     */
    public static function getFileDisk(string $path) {
        if(Storage::disk('public')->has($path)) return 'public';
        if(Storage::disk('yandex-disk')->has($path)) return 'yandex-disk';
        return null;
    }

    /**
     * Получаем mime-type файла
     * @param string $path - путь
     * @return mixed|null
     */
    public static function getFileMimeType(string $path) {
        return self::getFileMeta($path, 'mimeType');
    }

    /**
     * Получаем размер файла
     * @param string $path - путь
     * @return mixed|null
     */
    public static function getFileSize(string $path) {
        return self::getFileMeta($path, 'size');
    }

    /**
     * Получаем дату последнего изменения файла
     * @param string $path - путь
     * @return mixed|null
     */
    public static function getFileLastModified(string $path) {
        return self::getFileMeta($path, 'lastModified');
    }

    /**
     * Отдаём файл на скачивание
     * @param string $path - путь
     * @param string $name - название
     * @return \Illuminate\Http\Response|void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function output(string $path, string $name) {
        $fileData = self::getFile($path);
        if ($fileData) {
            $mimeType = self::getFileMimeType($path);
            $response = Response::make($fileData, 200);
            $response->header('Cache-Control', "public");
            $response->header('Content-Description', "File Transfer");
            $response->header('Content-Disposition', "attachment; filename={$name}");
            $response->header('Content-Transfer-Encoding', "binary");
            $response->header('Content-Type', $mimeType);
            return $response;
        }
    }
}
