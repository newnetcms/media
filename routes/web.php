<?php

use Newnet\Media\Http\Controllers\Web\CropImageController;

Route::get('images/size/{size}/{file}', CropImageController::class)
    ->name('media.web.cropimage')
    ->where([
        'size' => '[owhq0-9]+',
        'file' => '.*',
    ]);

Route::get('images/webp/{size}/{file_path}/{file_name}', [CropImageController::class, 'webp'])
    ->name('media.web.cropimage.webp')
    ->where([
        'size' => '[owhq0-9]+',
        'file_path' => '[a-zA-Z0-9=]+',
        'file_name' => '.*',
    ]);
