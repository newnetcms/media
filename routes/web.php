<?php

use Newnet\Media\Http\Controllers\Web\CropImageController;

Route::get('images/size/{size}/{file}', CropImageController::class)
    ->name('media.web.cropimage')
    ->where([
        'size' => '[whq0-9]+',
        'file' => '.*',
    ]);
