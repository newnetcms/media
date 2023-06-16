<?php

use Newnet\Media\Http\Controllers\Admin\MediaTagController;
use Newnet\Media\Http\Controllers\Admin\UploadController;
use Newnet\Media\Http\Controllers\Admin\MediaController;

Route::name('media.admin.')
    ->middleware('admin.acl')
    ->group(function () {
        Route::resource('media', MediaController::class);
    });

Route::post('media/upload', UploadController::class)
    ->name('media.admin.upload')
    ->middleware('admin.can:media.admin.media.create');

Route::post('media/media-tag', MediaTagController::class)
    ->name('media.admin.media-tag')
    ->middleware('admin.can:media.admin.media.create');

Route::prefix('media')->group(function () {
    Route::get('/search', [MediaController::class, 'search'])
        ->name('media.admin.media.search');
    Route::get('/sort', [MediaController::class, 'sort'])
        ->name('media.admin.media.sort');
    Route::delete('/delete', [MediaController::class, 'delete'])
        ->name('media.admin.media.delete');
    Route::get('/froala-load-images', [MediaController::class, 'froalaLoadImages'])
        ->name('media.admin.media.froala_load_images');

    // ajax media
    Route::get('ajax/media-list', [MediaController::class, 'ajaxMedia'])
        ->name('media.admin.media.ajaxMedia');
    Route::post('/store-ajax', [MediaController::class, 'storeAjax'])
        ->name('media.admin.media.storeAjax');
});
