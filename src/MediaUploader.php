<?php

namespace Newnet\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Newnet\Media\Events\MediaUploadedEvent;
use Newnet\Media\Exceptions\SuspiciousContentException;
use Newnet\Media\Exceptions\UnsupportedFileExtensionException;
use Newnet\Media\Models\Media;
use Symfony\Component\HttpFoundation\File\File;
use Throwable;

class MediaUploader
{
    /** @var UploadedFile */
    protected $file;

    /** @var string */
    protected $name;

    /** @var string */
    protected $fileName;

    protected $mimeType;

    protected $size;

    protected $ext;

    protected $disk;

    /** @var array */
    protected $attributes = [];

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $author;

    protected $needVerifyExtension = true;

    /** Đường dẫn file tạm sau khi sanitize nội dung (nếu có), để dọn dẹp khi upload() xong. */
    protected $sanitizedTempPath;

    /** Ext ảnh raster mà GD/Intervention trên server này chắc chắn decode/encode lại được. */
    protected array $rasterImageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];

    /**
     * Set the file to be uploaded.
     * @param UploadedFile|string $file
     * @return MediaUploader
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $fileName = basename($file);
            $file = new File($file);
            $this->mimeType = $file->getMimeType();
            $this->size = $file->getSize();
            $this->ext = $file->getExtension();
        } else {
            $fileName = $file->getClientOriginalName();
            $this->mimeType = $file->getMimeType();
            $this->size = $file->getSize();
            $this->ext = $file->getClientOriginalExtension();
        }

        $this->file = $file;

        $name = pathinfo($fileName, PATHINFO_FILENAME);

        $this->setName($name);
        $this->setFileName($fileName);

        return $this;
    }

    /**
     * Set the name of the media item.
     * @param string $name
     * @return MediaUploader
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Alias of method setFileName
     *
     * @param string $realName
     * @return $this
     */
    public function setRealName(string $realName)
    {
        return $this->setFileName($realName);
    }

    /**
     * Set the name of the file.
     * @param string $fileName
     * @return MediaUploader
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $this->sanitiseFileName($fileName);

        return $this;
    }

    /**
     * Sanitise the file name.
     * @param string $fileName
     * @return string
     */
    protected function sanitiseFileName(string $fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        return Str::lower(Str::slug($name) . '.' . $ext);
    }

    /**
     * Set any custom attributes to be saved to the media item.
     * @param array $attributes
     * @return MediaUploader
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param array $properties
     * @return MediaUploader
     */
    public function withProperties(array $properties)
    {
        return $this->withAttributes($properties);
    }

    /**
     * Upload the file to the specified disk.
     * @return Media
     */
    public function upload()
    {
        if ($this->needVerifyExtension) {
            $this->verifyExtension();
        }

        // Luôn chạy, không phụ thuộc needVerifyExtension: làm sạch nội dung khỏi payload
        // nhúng (polyglot/EXIF-XSS...) trước khi file được lưu thật lên disk.
        $this->sanitizeContent(Str::lower($this->ext));

        try {
            $model = config('cms.media.model');

            /** @var Media $media */
            $media = new $model();

            $media->name = $this->name;
            $media->file_name = $this->fileName;
            $media->disk = $this->disk ?: config('cms.media.disk');
            $media->mime_type = $this->mimeType;
            $media->size = $this->size;
            $media->ext = $this->ext;

            if ($auth = $this->getAuthor()) {
                $media->author()->associate($auth);
            }

            $media->forceFill($this->attributes);

            $media->save();

            $media->filesystem()->putFileAs(
                dirname($media->getPath()),
                $this->file,
                $media->file_name,
                [
                    'visibility' => 'public',
                ]
            );

            event(new MediaUploadedEvent($media));

            return $media->fresh();
        } finally {
            if ($this->sanitizedTempPath && file_exists($this->sanitizedTempPath)) {
                @unlink($this->sanitizedTempPath);
            }
        }
    }

    public function uploadFromUrl($url, $realName = null)
    {
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'newnet_download_');

        $res = Http::get($url);
        if ($res->failed()) {
            throw $res->toException();
        }

        $content = $res->body();
        \File::put($tmpFilePath, $content);
        $realName = $realName ?: basename($url);
        $name = pathinfo($realName, PATHINFO_FILENAME);
        $ext = pathinfo($realName, PATHINFO_EXTENSION);

        $this->setFile($tmpFilePath);
        $this->setRealName($realName);
        $this->setName($name);
        $this->ext = $ext;

        $media = $this->upload();

        \File::delete($tmpFilePath);

        return $media;
    }

    protected function getAuthor()
    {
        if ($this->author) {
            return $this->author;
        }

        $guard = config('cms.media.guard');

        return \Auth::guard($guard)->user();
    }

    public function setAuthor($user)
    {
        $this->author = $user;

        return $this;
    }

    public function setDisk($disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function setVerifyExtension($value)
    {
        $this->needVerifyExtension = $value;

        return $this;
    }

    protected function verifyExtension()
    {
        $ext = Str::lower($this->ext);

        if (!in_array($ext, config('cms.media.accept_upload_extension'))) {
            throw new UnsupportedFileExtensionException();
        }

        $this->verifyContentMatchesExtension($ext);
    }

    /**
     * Ext nằm trong allowlist chưa đủ: file đổi tên ext (ví dụ đổi thành .jpg) vẫn phải có
     * nội dung thật đúng loại đó, không chỉ dựa vào tên file.
     */
    protected function verifyContentMatchesExtension(string $ext)
    {
        $path = $this->file->getPathname();

        if (in_array($ext, $this->rasterImageExtensions) && @getimagesize($path) === false) {
            throw new UnsupportedFileExtensionException();
        }

        if ($ext === 'pdf' && substr((string) file_get_contents($path, false, null, 0, 5), 0, 5) !== '%PDF-') {
            throw new UnsupportedFileExtensionException();
        }
    }

    /**
     * Làm sạch nội dung file theo loại, vì ext/mime hợp lệ không đảm bảo file không giấu payload:
     * - Ảnh raster: decode rồi encode lại bằng Intervention — chỉ còn pixel thật, loại bỏ MỌI
     *   vùng metadata (EXIF, JFIF comment, ICC, XMP...) bất kể payload giấu kiểu gì, không cần biết
     *   trước signature của nó (khác hẳn cách chặn theo danh sách mẫu, vốn luôn có thể bị né).
     * - SVG: không rasterize được (là vector/XML), nên chỉ lọc bỏ <script>, thuộc tính on*=, và
     *   href="javascript:..." — các vector XSS nằm NGAY TRONG cú pháp SVG hợp lệ, không phải lỗi.
     * - Loại còn lại (pdf, zip, doc, mp4...): không tái cấu trúc được an toàn bằng code ở đây, nên
     *   chỉ quét chữ ký script/exec phổ biến và từ chối thẳng nếu khớp (chặn được các mẫu đã biết,
     *   không chắc chặn được biến thể hoàn toàn mới — yếu hơn 2 nhánh trên).
     */
    protected function sanitizeContent(string $ext)
    {
        if (in_array($ext, $this->rasterImageExtensions)) {
            // cms.media.reencode_on_upload: mặc định TẮT (xem comment trong lib/media/config/media.php
            // lý do default false). Khi tắt, ảnh vẫn được quét signature như nhánh else bên dưới,
            // chỉ là không decode/encode lại nên không tốn thêm CPU và không mất metadata thật.
            if (config('cms.media.reencode_on_upload')) {
                $this->reencodeImage();
            } else {
                $this->rejectIfContainsPayload();
            }
        } elseif ($ext === 'svg') {
            $this->sanitizeSvg();
        } else {
            $this->rejectIfContainsPayload();
        }
    }

    protected function reencodeImage()
    {
        try {
            // Package này khai báo "intervention/image": "^2.5|^4.0" (xem lib/media/composer.json)
            // — project nào cài 2.5 vẫn phải chạy được, nên không gọi thẳng API 4.x.
            // ImageProcessor::isLegacy() detect bằng cách check class Intervention\Image\Constraint
            // (chỉ tồn tại ở 2.x, bị bỏ ở 4.x) để biết đang chạy trên version nào, cùng cách
            // MediaServiceProvider.php đã dùng để quyết định có bind ImageManager (4.x) hay không.
            if (ImageProcessor::isLegacy()) {
                // Nhánh intervention/image 2.5: Image::make()->encode($format, $quality) rồi
                // ép (string) để lấy binary đã encode — đây là API 2.x chính thống, giống cách
                // ImageProcessor::crop() ở trên đã dùng Facades\Image::make() cho nhánh legacy.
                $image = \Intervention\Image\Facades\Image::make($this->file->getPathname());

                // QUAN TRỌNG: ảnh điện thoại thường lưu pixel THEO CHIỀU NGANG kèm thẻ EXIF
                // Orientation báo "xoay X độ khi hiển thị" (ví dụ ảnh chụp dọc vẫn lưu pixel ngang
                // + Orientation=6), chứ không xoay sẵn pixel. Bước encode lại bên dưới xoá MỌI EXIF
                // (kể cả Orientation) để diệt payload ẩn — nên phải orientate() để xoay/lật pixel
                // thật theo đúng Orientation TRƯỚC khi xoá, nếu không ảnh đúng chiều sẽ bị lưu lại
                // thành ảnh nằm sai chiều vĩnh viễn (đã test thực tế thấy lỗi này trước khi thêm dòng này).
                $image->orientate();

                $contents = (string) $image->encode($this->ext, ImageProcessor::DEFAULT_QUALITY);
            } else {
                // Nhánh intervention/image 4.x: API mới (ImageManager::decodePath(),
                // encodeUsingMediaType(), named argument quality: — cú pháp PHP 8.0+). Chỉ chạy
                // khi chắc chắn 4.x đang được cài, nên không bao giờ gọi nhầm sang method không
                // tồn tại ở 2.5.
                $image = ImageProcessor::createManager()->decodePath($this->file->getPathname());

                // Tương tự nhánh legacy ở trên (xem comment đầy đủ phía trên): bake EXIF Orientation
                // vào pixel thật trước khi encode lại, vì encodeUsingMediaType() sẽ xoá mọi metadata.
                // 4.x gọi method này là orient() (không phải orientate() như 2.x).
                $image->orient();

                $contents = $image->encodeUsingMediaType($this->mimeType, quality: ImageProcessor::DEFAULT_QUALITY)->toString();
            }
        } catch (Throwable $e) {
            // Ảnh đã qua verifyContentMatchesExtension (getimagesize đọc được) nhưng Intervention
            // không decode/encode lại được — coi như không đủ tin cậy, từ chối thay vì lưu nguyên bản.
            throw new UnsupportedFileExtensionException();
        }

        $this->replaceFileContents($contents);
    }

    protected function sanitizeSvg()
    {
        $contents = file_get_contents($this->file->getPathname());

        if ($contents === false) {
            throw new UnsupportedFileExtensionException();
        }

        $contents = preg_replace('#<script\b.*?</script>#is', '', $contents);
        $contents = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $contents);
        $contents = preg_replace('#(href|xlink:href)(\s*=\s*)(["\'])\s*javascript:[^"\']*\3#i', '', $contents);

        $this->replaceFileContents($contents);
    }

    /** Chữ ký script/exec hay gặp trong payload giấu vào file upload (polyglot, EXIF-XSS, PDF-JS...). */
    protected function rejectIfContainsPayload()
    {
        $content = @file_get_contents($this->file->getPathname(), false, null, 0, 2 * 1024 * 1024);

        if (!is_string($content) || $content === '') {
            return;
        }

        $patterns = [
            '/<\?php\b/i',
            '/<script[\s>]/i',
            '/<svg\b/i',
            '/\bon(load|error|click|mouseover|focus|mouseenter)\s*=/i',
            '/javascript\s*:/i',
            '/\b(eval|system|shell_exec|passthru|exec|proc_open)\s*\(/i',
            '#/(JavaScript|JS|OpenAction)\b#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new SuspiciousContentException();
            }
        }
    }

    /** Ghi nội dung đã sanitize ra file tạm và thay $this->file bằng file đó trước khi lưu thật. */
    protected function replaceFileContents(string $contents)
    {
        $path = tempnam(sys_get_temp_dir(), 'newnet_media_sanitized_');
        file_put_contents($path, $contents);

        $this->file = new File($path);
        $this->size = filesize($path);
        $this->sanitizedTempPath = $path;
    }
}
