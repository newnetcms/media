<?php

use Newnet\Media\Http\Controllers\Web\CropImageController;

/*
 * ImgSupport::url() chỉ sinh ra đúng 3 dạng: "w{width}", "w{width}h{height}"
 * hoặc "oh{height}" (width rỗng), có thể kèm "q{quality 1-100}". Giới hạn
 * route theo đúng ngữ pháp này (không chỉ theo charset [owhq0-9]+) để chặn
 * luôn các chuỗi rác/size bất thường ở tầng routing, trước khi vào controller.
 */
$mediaSizePattern = '(?:w[1-9][0-9]{0,3}(?:h[1-9][0-9]{0,3})?|oh[1-9][0-9]{0,3})(?:q(?:100|[1-9][0-9]?))?';

Route::get('images/size/{size}/{file}', CropImageController::class)
    ->name('media.web.cropimage')
    ->where([
        'size' => $mediaSizePattern,
        'file' => '.*',
    ]);

Route::get('images/webp/{size}/{file_path}/{file_name}', [CropImageController::class, 'webp'])
    ->name('media.web.cropimage.webp')
    ->where([
        'size' => $mediaSizePattern,
        'file_path' => '[a-zA-Z0-9=]+',
        'file_name' => '.*',
    ]);
