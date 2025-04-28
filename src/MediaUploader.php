<?php

namespace Newnet\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Newnet\Media\Events\MediaUploadedEvent;
use Newnet\Media\Exceptions\UnsupportedFileExtensionException;
use Newnet\Media\Models\Media;
use Symfony\Component\HttpFoundation\File\File;

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
            $media->getDirectory(),
            $this->file,
            $this->fileName,
            [
                'visibility' => 'public',
            ]
        );

        event(new MediaUploadedEvent($media));

        return $media->fresh();
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
        if (!in_array(Str::lower($this->ext), config('cms.media.accept_upload_extension'))) {
            throw new UnsupportedFileExtensionException();
        }
    }
}
