<?php

namespace Newnet\Media\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Newnet\Media\Models\MediaTag
 *
 * @property-read Model|\Eloquent $MediaTags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Newnet\Media\Models\Media> $tagImage
 * @property-read int|null $tag_image_count
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaTag query()
 * @mixin \Eloquent
 */
class MediaTag extends Model
{
    protected $table = 'media_tags';
    protected $fillable = ['media_id', 'label', 'title', 'content', 'entity'];


    public function tagImage()
    {
        return $this->hasMany(Media::class, 'media_id');
    }

    public function MediaTags()
    {
        return $this->morphTo('tags');
    }

}
