<?php

use Newnet\Media\Repositories\MediaRepositoryInterface;

if (!function_exists('get_media')) {
    /**
     * Get Media
     *
     * @param $mediaId
     *
     * @return \Newnet\Media\Models\Media|null
     */
    function get_media($mediaId)
    {
        if ($mediaId) {
            return app(MediaRepositoryInterface::class)->find($mediaId);
        }

        return null;
    }

}

if (!function_exists('imageProxy')){
    function imageProxy($url, $width, $height = null, $format = 'jpg', $quality = 80)
    {
        if (config('cms.media.imageproxy.enable') === true) {
            $urlCdn = config('cms.media.imageproxy.server');
            if ($height) {
                return "{$urlCdn}/{$width}x{$height},q{$quality},{$format}/" . $url;
            } else {
                return "{$urlCdn}/{$width}x,q{$quality},{$format}/" . $url;
            }
        } else {
            return Img::url($url, $width, $height);
        }
    }
}
