<?php

namespace Newnet\Media;

use BadMethodCallException;
use Closure;
use Newnet\Media\Models\Media;

class MediaWrapper
{
    protected ?Media $media;
    protected ?Closure $loader;
    protected bool $loaded = false;
    protected ?string $cacheKey;
    protected ?string $cachedUrl = null;

    /**
     * @param Media|null $media  Nếu đã có model (relation đã được eager loaded)
     * @param callable|null $loader  Callable trả về Media|null khi cần load (deferred)
     */
    public function __construct(?Media $media = null, ?callable $loader = null, ?string $cacheKey = null)
    {
        $this->media = $media;
        $this->loaded = $media !== null;
        $this->loader = $loader;
        $this->cacheKey = $cacheKey;
    }

    /** Triggers load nếu chưa load */
    public function getMedia(): ?Media
    {
        if (! $this->loaded && $this->loader) {
            $this->media = ($this->loader)();
            $this->loaded = true;
        }
        return $this->media;
    }

    /** Lấy url (không bắt buộc gọi query nếu đã có) */
    public function getUrl(): ?string
    {
        if ($this->cacheKey && config('cms.media.enable_cache')) {
            // Cache persistent (Redis/file/array backend)
            return cache()->remember($this->cacheKey, 3600, function () {
                $media = $this->getMedia();
                if ($media) {
                    return $media->getUrl();
                }

                return '';
            });
        }

        // Chỉ cache trong instance
        if ($this->cachedUrl !== null) {
            return $this->cachedUrl;
        }

        $media = $this->getMedia();
        $this->cachedUrl = $media ? ($media->getUrl() ?? null) : null;

        return $this->cachedUrl;
    }

    public function delete()
    {
        $media = $this->getMedia();
        if ($media) {
            $media->delete();
        }
    }

    /** Khi bị ép thành string (echo, concat, (string)$obj) */
    public function __toString(): string
    {
        return $this->getUrl() ?: '';
    }

    /** Proxy method call to underlying Media model (triggers load) */
    public function __call($method, $arguments)
    {
        $media = $this->getMedia();
        if ($media && method_exists($media, $method)) {
            return $media->$method(...$arguments);
        }
        throw new BadMethodCallException("Call to undefined method {$method} on Media or media is null.");
    }

    /** Proxy property access to underlying Media model (triggers load) */
    public function __get($name)
    {
        $media = $this->getMedia();

        return $media?->$name;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }
}
