<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use ITPolice\YandexDisk\Helper;

Route::get('storage/{path}', function ($filePath) {
    $fileData = Helper::getFile($filePath);
    if(!$fileData) abort(404);

    $mimeType = Helper::getFileMimeType($filePath);

    $response = Response::make($fileData, 200);
    $response->header('Content-Type', $mimeType);
    return $response;
})->where(['path' => '.*', 'type' => '(fonts|img)']);
