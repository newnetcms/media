<?php

namespace Newnet\Media;

use Intervention\Image\ImageManager;
use Newnet\Media\Models\Media;

class ImageManipulator
{
    /** @var ConversionRegistry */
    protected $conversionRegistry;

    /** @var ImageManager */
    protected $imageManager;

    /**
     * Create a new manipulator instance.
     * @param ConversionRegistry $conversionRegistry
     * @param ImageManager       $imageManager
     * @return void
     */
    public function __construct(ConversionRegistry $conversionRegistry, ImageManager $imageManager)
    {
        $this->conversionRegistry = $conversionRegistry;

        $this->imageManager = $imageManager;
    }

    /**
     * Perform the specified conversions on the given media item.
     * @param Media $media
     * @param array $conversions
     * @param bool  $onlyIfMissing
     * @return void
     * @throws Exceptions\InvalidConversionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function manipulate(Media $media, array $conversions, $onlyIfMissing = true)
    {
        if (!$media->isOfType('image')) {
            return;
        }

        foreach ($conversions as $conversion) {
            $path = $media->getPath($conversion);

            $filesystem = $media->filesystem();

            if ($onlyIfMissing && $filesystem->exists($path)) {
                continue;
            }

            $converter = $this->conversionRegistry->get($conversion);

            $image = $converter($this->imageManager->make(
                $filesystem->readStream($media->getPath())
            ));

            $filesystem->put($path, $image->stream(), [
                'visibility' => 'public'
            ]);
        }
    }
}
