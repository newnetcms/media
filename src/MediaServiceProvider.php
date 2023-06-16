<?php

namespace Newnet\Media;

use Illuminate\Support\Facades\Blade;
use Newnet\Media\Models\Media;
use Newnet\Media\Models\Mediable;
use Newnet\Media\Models\MediaTag;
use Newnet\Media\Repositories\MediableRepository;
use Newnet\Media\Repositories\MediableRepositoryInterace;
use Newnet\Media\Repositories\MediaRepository;
use Newnet\Media\Repositories\MediaRepositoryInterface;
use Newnet\Media\Repositories\MediaTagRepositoryInterface;
use Newnet\Module\Support\BaseModuleServiceProvider;

class MediaServiceProvider extends BaseModuleServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(ConversionRegistry::class);
        $this->app->singleton(MediaUploader::class);

        $this->app->singleton(MediaRepositoryInterface::class, function () {
            return new MediaRepository(new Media());
        });

        $this->app->singleton(MediableRepositoryInterace::class, function () {
            return new MediableRepository(new Mediable());
        });
        $this->app->singleton(MediaTagRepositoryInterface::class, function () {
            return new MediaRepository(new MediaTag());
        });

        $this->app->singleton('newnet.media.img', function () {
            return new ImgSupport();
        });

        $this->app->singleton('newnet.media.uploader', function () {
            return new MediaUploader();
        });
    }

    public function boot()
    {
        parent::boot();

        $this->registerPermissions();
        $this->registerBlade();
    }

    public function registerBlade(){
        Blade::include('media::admin.modals.tag-modal', 'modalMedia');
        Blade::include('media::form.media', 'mediamanager');
    }
}
