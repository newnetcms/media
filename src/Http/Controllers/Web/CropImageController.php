<?php

namespace Newnet\Media\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class CropImageController extends Controller
{
    public function __invoke($size, $file)
    {
        if ($this->detectInternalImage($file)) {
            $image = Image::make($file);

            $cropedPath = $this->getCropedPath($file, $size);

            list($width, $height, $quality) = $this->getImageSizeOptions($size);

            $image->fit($width, $height, function (Constraint $constraint) {
                $constraint->upsize();
            })->save($cropedPath, $quality);

            return $image->response();
        }

        return response('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')
            ->withHeaders([
                'Content-Type' => 'image/png'
            ]);
    }

    protected function getCropedPath($file, $size): string
    {
        $cropedPath = "images/size/{$size}/{$file}";
        $folder = dirname($cropedPath);

        if (!File::isDirectory($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        return $cropedPath;
    }

    protected function detectInternalImage($file): bool
    {
        return File::exists($file);
    }

    protected function getImageSizeOptions($size): array
    {
        preg_match('/w([0-9]+)/', $size, $matchesWidth);
        preg_match('/h([0-9]+)/', $size, $matchesHeight);
        preg_match('/q([0-9]+)/', $size, $matchesQuality);

        return [
            $matchesWidth[1] ?? null,
            $matchesHeight[1] ?? null,
            $matchesQuality[1] ?? 70,
        ];
    }
}
