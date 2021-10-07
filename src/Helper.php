<?php

namespace ITPolice\YandexDisk;

use ITPolice\YandexDisk\Jobs\YandexDiskDriverFileUpload;

class Helper
{
    /**
     * Загрузка файла
     * @param string $path - путь
     * @param string $disk - диск
     * @param string $queue - приоритет очереди
     */
    public static function upload($path, $disk = 'public', $queue = 'low') {
        if(config('filesystems.disks.yandex-disk.on')) {
            $job = new YandexDiskDriverFileUpload($path);
            dispatch($job->onQueue($queue));
        }
    }
}
