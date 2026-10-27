<?php

namespace Newnet\Media\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Newnet\Media\ImageProcessor;

class CropImageController extends Controller
{
    public function __invoke($size, $file)
    {
        if ($this->detectInternalImage($file)) {
            $cropedPath = $this->getCropedPath($file, $size);

            list($width, $height, $quality) = $this->getImageSizeOptions($size);

            return ImageProcessor::crop($file, $cropedPath, $width, $height, $quality);
        }

        return response('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=')
            ->withHeaders([
                'Content-Type' => 'image/png'
            ]);
    }

    public function webp($size, $file_path, $file_name)
    {
        $file = base64_decode($file_path);

        if ($this->isSafePath($file_name) && $this->detectInternalImage($file)) {
            $cropedPath = $this->getWebpPath($size, $file_path, $file_name);

            list($width, $height) = $this->getImageSizeOptions($size);

            return ImageProcessor::crop($file, $cropedPath, $width, $height, null, 'webp');
        }

        return response('data:image/webp;base64,UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==')
            ->withHeaders([
                'Content-Type' => 'image/webp'
            ]);
    }

    /**
     * $file/$file_name đến trực tiếp từ URL công khai, không qua xác thực nào.
     * Chặn ".." và đường dẫn tuyệt đối để request không thể đọc/viết ra ngoài
     * thư mục public (path traversal - CWE-22), vì $size (đã bị route ->where()
     * giới hạn ký tự) không đủ để chặn việc này khi $file/$file_name còn mở.
     */
    protected function isSafePath($path): bool
    {
        if (!is_string($path) || $path === '' || str_contains($path, "\0")) {
            return false;
        }

        $normalized = str_replace('\\', '/', $path);

        return $normalized[0] !== '/' && !preg_match('#(^|/)\.\.(/|$)#', $normalized);
    }

    protected function getWebpPath($size, $file_path, $file_name): string
    {
        $cropedPath = "images/webp/{$size}/{$file_path}/{$file_name}";
        $folder = dirname($cropedPath);

        if (!File::isDirectory($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        return $cropedPath;
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
        return $this->isSafePath($file) && File::exists($file);
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
