<?php

use Newnet\Media\Http\Controllers\Admin\UploadController;
use Newnet\Media\Http\Controllers\Admin\MediaController;

Route::name('media.admin.')
    ->middleware('admin.acl')
    ->group(function () {
        Route::resource('media', MediaController::class);

        // Thao tác của trang Danh sách media (cần cùng quyền media.admin.media.*
        // như các action resource phía trên). Tên route không lặp lại tiền tố
        // 'media.admin.' vì group ->name('media.admin.') ở trên đã tự prepend.
        Route::post('media/bulk-destroy', [MediaController::class, 'destroy'])
            ->name('media.bulk_destroy');
        Route::post('media/bulk-download', [MediaController::class, 'bulkDownload'])
            ->name('media.bulk_download');
    });

Route::post('media/upload', UploadController::class)
    ->name('media.admin.upload')
    ->middleware('admin.can:media.admin.media.create');

// Các route dưới đây phục vụ widget chọn ảnh dùng chung (@mediamanager, Froala
// editor) ở hàng chục form của những module khác — không áp quyền media.admin.media.*
// riêng để tránh gãy việc chọn ảnh ở các role chưa được cấp quyền này.
Route::prefix('media')->group(function () {
    Route::get('/froala-load-images', [MediaController::class, 'froalaLoadImages'])
        ->name('media.admin.media.froala_load_images');

    // ajax media
    Route::get('ajax/media-list', [MediaController::class, 'ajaxMedia'])
        ->name('media.admin.media.ajaxMedia');
    Route::post('/store-ajax', [MediaController::class, 'storeAjax'])
        ->name('media.admin.media.storeAjax');
});
