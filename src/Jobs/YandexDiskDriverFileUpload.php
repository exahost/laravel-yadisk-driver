<?php

namespace ITPolice\YandexDisk\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class YandexDiskDriverFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $path;
    public $disk;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, $disk = 'public')
    {
        $this->path = $path;
        $this->disk = $disk;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filePath = $this->path;

        if(!config('filesystems.disks.yandex-disk.on')) return;
        if(!Storage::disk('public')->exists($filePath)) return;

        $cacheTime = config('filesystems.disks.yandex-disk.cacheTime');

        $imageData = Storage::disk($this->disk)->get($filePath);
        $mimeType = Storage::disk($this->disk)->mimeType($filePath);

        Cache::put('ya-disk-storage_data_' . $filePath, $imageData, $cacheTime);
        Cache::put('ya-disk-storage_mimetype_' . $filePath, $mimeType, $cacheTime);

        if(Storage::disk('yandex-disk')->put($filePath, $imageData)) {
            Storage::disk($this->disk)->delete($filePath);
        }
    }
}
