<?php

namespace Newnet\Media;

use Intervention\Image\ImageManager;

/**
 * Keeps the package working with both intervention/image 2.x and 4.x.
 */
class ImageProcessor
{
    /**
     * Default quality of intervention/image 2.x (4.x defaults to 75).
     */
    const DEFAULT_QUALITY = 90;

    /**
     * Determine if intervention/image 2.x is installed.
     */
    public static function isLegacy(): bool
    {
        return class_exists(\Intervention\Image\Constraint::class);
    }

    /**
     * Create an image manager for intervention/image 4.x.
     * Auto orientation and animation decoding are disabled to match 2.x output.
     */
    public static function createManager(): ImageManager
    {
        $driver = config('image.driver') === 'imagick'
            ? \Intervention\Image\Drivers\Imagick\Driver::class
            : \Intervention\Image\Drivers\Gd\Driver::class;

        return new ImageManager($driver, autoOrientation: false, decodeAnimation: false);
    }

    /**
     * Resize a local file, store the result at the given path and return it as a response.
     * Images are never upsized. The format is taken from the path unless it is given.
     */
    public static function crop($file, $path, $width = null, $height = null, $quality = null, $format = null)
    {
        if (static::isLegacy()) {
            $image = \Intervention\Image\Facades\Image::make($file);

            if ($width && $height) {
                $image->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
            } elseif ($width) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->upsize();
                    $constraint->aspectRatio();
                });
            }

            $image->save($path, $quality, $format);

            return $image->response();
        }

        $image = static::createManager()->decodePath($file);

        if ($width && $height) {
            $image->coverDown((int) $width, (int) $height);
        } elseif ($width) {
            $image->scaleDown(width: (int) $width);
        }

        $encode = function ($quality) use ($image, $path, $format) {
            return $format
                ? $image->encodeUsingFileExtension($format, quality: $quality)
                : $image->encodeUsingPath($path, quality: $quality);
        };

        $encode((int) ($quality ?: static::DEFAULT_QUALITY))->save($path);

        $encoded = $encode(static::DEFAULT_QUALITY);

        return response($encoded->toString())->withHeaders([
            'Content-Type' => $encoded->mediaType(),
            'Content-Length' => $encoded->size(),
        ]);
    }

    /**
     * Read an image from a stream.
     */
    public static function read(ImageManager $manager, $stream)
    {
        return static::isLegacy() ? $manager->make($stream) : $manager->decodeStream($stream);
    }

    /**
     * Get the encoded contents of a converted image, keeping the given mime type.
     */
    public static function contents($image, $mimeType = null)
    {
        if (static::isLegacy()) {
            return $image->stream();
        }

        if ($image instanceof \Intervention\Image\Interfaces\EncodedImageInterface) {
            return $image->toString();
        }

        return $image->encodeUsingMediaType($mimeType, quality: static::DEFAULT_QUALITY)->toString();
    }
}
