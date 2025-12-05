<?php

namespace Newnet\Media\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Newnet\Media\Facades\Img;
use Newnet\Media\Jobs\PerformConversions;
use Newnet\Media\MediaGroup;
use Newnet\Media\MediaWrapper;
use Newnet\Media\Models\Media;

/**
 * Trait HasMediaTrait
 *
 * @package Newnet\Media\Traits
 *
 * @property boolean $forceDeleteMedia
 */
trait HasMediaTrait
{
    /** @var MediaGroup[] */
    protected $mediaGroups = [];

    protected $mediaAttributes = [];

    protected static function bootHasMediaTrait()
    {
        static::deleting(function (self $model) {
            if ($model->forceDeleteMedia()) {
                foreach ($model->media as $media) {
                    $media->delete();
                }
            } else {
                $model->media()->detach();
            }
        });

        static::saved(function (self $model) {
            foreach ($model->mediaAttributes as $key => $value) {
                $model->syncMedia($value, $key);
            }
        });
    }

//    public function initializeHasMediaTrait()
//    {
//        $this->with[] = 'media';
//    }

    /**
     * Get the "media" relationship.
     * @return MorphToMany
     */
    public function media()
    {
        return $this
            ->morphToMany(config('cms.media.model'), 'mediable')
            ->withPivot('group', 'id')
            ->orderBy('pivot_id');
    }

    /**
     * Determine if there is any media in the specified group.
     * @param string $group
     * @return mixed
     */
    public function hasMedia(string $group = 'default')
    {
        return $this->getMedia($group)->isNotEmpty();
    }

    /**
     * Get all the media in the specified group.
     * @param string $group
     * @return mixed
     */
    public function getMedia(string $group = 'default')
    {
        return $this->media->where('pivot.group', $group);
    }

    /**
     * Get the first media item in the specified group.
     * @param string $group
     * @return mixed
     */
    public function getFirstMedia(string $group = 'default')
    {
        if (!config('cms.media.enable_cache')) {
            return $this->getMedia($group)->first();
        }

        $class = $this::class;
        $id    = $this->getKey();
        $cacheKey = "media:{$class}:{$id}:{$group}";

        // Nếu relation đã được eager-loaded thì dùng collection hiện có (không thêm query)
        if ($this->relationLoaded('media')) {
            $media = $this->media->where('pivot.group', $group)->first();
            return new MediaWrapper($media, null, $cacheKey);
        }

        // Nếu chưa loaded -> tạo loader (callable) để query only-when-needed
        $loader = function () use ($group) {
            // Sử dụng relation builder (không dùng $this->media property)
            // Sử dụng wherePivot để lọc theo pivot column
            return $this->media()
                ->wherePivot('group', $group)
                // nếu relation định nghĩa orderBy('pivot_id'), bạn có thể lặp lại:
                ->orderBy('pivot_id')
                ->first();
        };

        return new MediaWrapper(null, $loader, $cacheKey);
    }

    /**
     * Get the url of the first media item in the specified group.
     * @param string $group
     * @param string $conversion
     * @return string
     */
    public function getFirstMediaUrl(string $group = 'default', string $conversion = '')
    {
        if (!$media = $this->getFirstMedia($group)) {
            return '';
        }

        return $media->getUrl($conversion);
    }

    /**
     * Get the url of the first media item in the specified group.
     * @param string $group
     * @param string $conversion
     * @return string
     */
    public function getFirstMediaThumb(string $group = 'default')
    {
        if (!$media = $this->getFirstMedia($group)) {
            return '';
        }

        return Img::url($media->getUrl(), 300, 300);
    }

    /**
     * Attach media to the specified group.
     * @param mixed  $media
     * @param string $group
     * @param array  $conversions
     * @return void
     */
    public function attachMedia($media, string $group = 'default', array $conversions = [])
    {
        $this->registerMediaGroups();

        $ids = $this->parseMediaIds($media);

        $mediaGroup = $this->getMediaGroup($group);

        if ($mediaGroup && $mediaGroup->hasConversions()) {
            $conversions = array_merge(
                $conversions, $mediaGroup->getConversions()
            );
        }

        if (!empty($conversions)) {
            $model = config('cms.media.model');

            /** @var Media $media */
            $media = $model::findMany($ids);

            $media->each(function ($media) use ($conversions) {
                PerformConversions::dispatch(
                    $media, $conversions
                );
            });
        }

        $this->media()->attach($ids, [
            'group' => $group,
        ]);
    }

    /**
     * Sync media to the specified group.
     *
     * @param $media
     * @param  string  $group
     * @param  array  $conversions
     */
    public function syncMedia($media, string $group = 'default', array $conversions = [])
    {
        $this->clearMediaGroup($group);
        $this->attachMedia($media, $group, $conversions);
    }

    /**
     * Register all the model's media groups.
     * @return void
     */
    public function registerMediaGroups()
    {
        //
    }

    /**
     * Get the media group with the specified name.
     * @param string $name
     * @return MediaGroup|null
     */
    public function getMediaGroup(string $name)
    {
        return $this->mediaGroups[$name] ?? null;
    }

    /**
     * Detach the specified media.
     * @param mixed $media
     * @return void
     */
    public function detachMedia($media = null)
    {
        $this->media()->detach($media);
    }

    /**
     * Detach all the media in the specified group.
     * @param string $group
     * @return void
     */
    public function clearMediaGroup(string $group = 'default')
    {
        $this->media()->wherePivot('group', $group)->detach();
    }

    /**
     * Parse the media id's from the mixed input.
     * @param mixed $media
     * @return array
     */
    protected function parseMediaIds($media)
    {
        if ($media instanceof Collection) {
            return $media->modelKeys();
        }

        if ($media instanceof Media) {
            return [$media->getKey()];
        }

        return (array)$media;
    }

    /**
     * Register a new media group.
     * @param string $name
     * @return MediaGroup
     */
    protected function addMediaGroup(string $name)
    {
        $group = new MediaGroup();

        $this->mediaGroups[$name] = $group;

        return $group;
    }

    protected function forceDeleteMedia()
    {
        return property_exists($this, 'forceDeleteMedia') ? $this->forceDeleteMedia : false;
    }
}
