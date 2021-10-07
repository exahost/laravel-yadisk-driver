<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('storage/{path}', function ($filePath) {
    //Cache::forget('ya-disk-storage_data_' . $filePath);
    //Cache::forget('ya-disk-storage_mimetype_' . $filePath);
    $cacheTime = config('filesystems.disks.yandex-disk.cacheTime');

    $fileData = null;
    $mimeType = null;

    if(config('filesystems.disks.yandex-disk.on')) {
        $fileData = Cache::remember('ya-disk-storage_data_' . $filePath, $cacheTime, function () use ($filePath) {
            return Storage::disk('yandex-disk')->get($filePath);
        });
    }

    if($fileData) {
        $mimeType = Cache::remember('ya-disk-storage_mimetype_' . $filePath, $cacheTime, function () use ($filePath) {
            return Storage::disk('yandex-disk')->mimeType($filePath);
        });
    } elseif(Storage::disk('public')->has($filePath)) {
        $mimeType = Storage::disk('public')->mimeType($filePath);
        $fileData = Storage::disk('public')->get($filePath);
    } else {
        abort(404);
    }

    $response = Response::make($fileData, 200);
    $response->header('Content-Type', $mimeType);
    return $response;
})->where(['path' => '.*', 'type' => '(fonts|img)']);
