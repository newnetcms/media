<?php

namespace Newnet\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kra8\Snowflake\Snowflake;
use Newnet\Core\Support\Traits\CacheableTrait;

/**
 * Newnet\Media\Models\Media
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $file_name
 * @property string|null $disk
 * @property string|null $ext
 * @property string|null $mime_type
 * @property int|null $size
 * @property string|null $author_type
 * @property int|null $author_id
 * @property string|null $attrs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $author
 * @property-read string $extension
 * @property-read mixed $thumb
 * @property-read string|null $type
 * @property-read mixed $url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Newnet\Media\Models\Mediable> $mediables
 * @property-read int|null $mediables_count
 * @method static \Illuminate\Database\Eloquent\Builder|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereAttrs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereAuthorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereExt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Media extends Model
{
    use CacheableTrait;

    protected $table = 'media';

    protected $fillable = [
        'name',
        'file_name',
        'disk',
        'ext',
        'mime_type',
        'size',
        'attrs',
    ];

    protected $casts = [
        'attrs' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($media) {
            $prefix = app(Snowflake::class)->next();
            $media->file_name = $prefix . '-' . $media->file_name;
            $media->attrs = array_merge($media->attrs ?? [], ['ver2' => true]);
        });

        self::deleting(function (Media $model) {
            if ($model->isVer2()) {
                $model->filesystem()->delete($model->getPath());
            } else {
                $model->filesystem()->deleteDirectory(
                    $model->getDirectory()
                );
            }
        });
    }

    public function author()
    {
        return $this->morphTo();
    }

    /**
     * Get the file type.
     *
     * @return string|null
     */
    public function getTypeAttribute()
    {
        return Str::before($this->mime_type, '/') ?? null;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Determine if the file is of the specified type.
     *
     * @param  string  $type
     * @return bool
     */
    public function isOfType(string $type)
    {
        return $this->type === $type;
    }

    /**
     * Ảnh có hiển thị được trực tiếp qua thẻ <img> trên trình duyệt hay không.
     * Không chỉ dựa vào mime_type (có thể bị lưu sai khi upload — ví dụ 1 file
     * .dat vẫn có thể có mime_type kiểu "image/..." — nên phải khớp luôn đuôi
     * file nằm trong danh sách định dạng raster/vector trình duyệt hiển thị
     * được). HEIC/HEIF tuy mime_type là image/* nhưng phần lớn trình duyệt
     * (trừ Safari) không render được qua <img src>, nên cũng loại khỏi đây.
     */
    public function isDisplayableImage(): bool
    {
        $displayableExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        return $this->isOfType('image') && in_array(Str::lower($this->extension), $displayableExtensions, true);
    }

    /**
     * Get the url to the file.
     *
     * @param  string  $conversion
     * @return mixed
     */
    public function getUrl(string $conversion = '')
    {
        $path = $this->getPath($conversion);
        $url  = $this->filesystem()->url($path);

        if (!config('cms.media.use_cdn')) {
            return $url;
        }

        $origin = rtrim(config('app.url'), '/');
        $cdn    = rtrim(config('cms.media.cdn_url'), '/');

        return str_replace($origin, $cdn, $url);
    }

    /**
     * Get the full path to the file.
     *
     * @param  string  $conversion
     * @return mixed
     */
    public function getFullPath(string $conversion = '')
    {
        return $this->filesystem()->path(
            $this->getPath($conversion)
        );
    }

    /**
     * Get the path to the file on disk.
     *
     * @param  string  $conversion
     * @return string
     */
    public function getPath(string $conversion = '')
    {
        if ($this->isVer2()) {
            return $this->created_at->format('Y/m') . DIRECTORY_SEPARATOR . $this->file_name;
        }

        $directory = $this->getDirectory();

//        if ($conversion) {
//            $directory .= '/conversions/'.$conversion;
//        }

        return $directory.'/'.$this->file_name;
    }

    /**
     * Get the directory for files on disk.
     *
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->created_at->format('Y/m').'/'.$this->getKey();
    }

    /**
     * Get the filesystem where the associated file is stored.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     */
    public function filesystem()
    {
        return Storage::disk($this->disk);
    }

    public function mediables()
    {
        return $this->hasMany(Mediable::class);
    }

    public function getThumbAttribute()
    {
        $thumbSize = config('cms.media.thumbsize', [300, 300]);

        return $this->crop($thumbSize[0], $thumbSize[1]);
    }

    public function getUrlAttribute()
    {
        return $this->getUrl();
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function crop($width, $height, $format = 'jpg', $quality = 80){
        if (config('cms.media.imageproxy.enable') === true){
            $urlCdn = config('cms.media.imageproxy.server');
            return "{$urlCdn}/{$width}x{$height},q{$quality},{$format}/".$this->getUrl();
        }else{
            return $this->getUrl();
        }
    }

    public function isVer2()
    {
        if (!$this->exists) {
            return true;
        }

        return $this->attrs['ver2'] ?? false;
    }

    /**
     * Kích thước file dạng người đọc được (KB/MB/GB).
     */
    public function getHumanSizeAttribute()
    {
        return static::humanSize((int) $this->size);
    }

    public static function humanSize(int $size): string
    {
        if ($size <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($size, 1024)), count($units) - 1);

        return round($size / (1024 ** $power), $power > 0 ? 1 : 0) . ' ' . $units[$power];
    }

    /**
     * Kích thước ảnh [width, height], null nếu không phải ảnh hoặc không đọc được
     * (vd: disk không hỗ trợ đọc trực tiếp từ path như S3).
     */
    public function getDimensionsAttribute()
    {
        if (!$this->isOfType('image')) {
            return null;
        }

        try {
            $size = getimagesize($this->getFullPath());
        } catch (\Throwable $exception) {
            return null;
        }

        return $size ? ['width' => $size[0], 'height' => $size[1]] : null;
    }

    public function getAltAttribute()
    {
        return $this->attrs['alt'] ?? '';
    }

    public function getCaptionAttribute()
    {
        return $this->attrs['caption'] ?? '';
    }
}
