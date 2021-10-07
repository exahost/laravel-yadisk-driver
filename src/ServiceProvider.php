<?php

namespace ITPolice\YandexDisk;

use ITPolice\YandexDisk\YandexDiskAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Arhitector\Yandex\Disk;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('yandex-disk', function ($app, $config) {
            $client = new Disk($config['token']);
            return new Filesystem(new YandexDiskAdapter($client, $config['prefix']));
        });

        app()->config['filesystems.disks.yandex-disk'] = [
            'driver' => 'yandex-disk',
            'token' => env('YANDEX_DISK_OAUTH_TOKEN'),
            'cacheTime' => env('YANDEX_DISK_CACHE_TIME', 900),
            'on' => env('YANDEX_DISK_ON', 'true'),
            'prefix' => '/'.env('YANDEX_DISK_BASE_PATH', 'storage/'),
        ];

        if(config('filesystems.disks.yandex-disk.on')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }
    }
}
