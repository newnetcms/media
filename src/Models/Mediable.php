<?php

namespace Newnet\Media\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Newnet\Media\Models\Mediable
 *
 * @property int $id
 * @property int $media_id
 * @property string|null $mediable_type
 * @property int|null $mediable_id
 * @property string|null $group
 * @property-read \Newnet\Media\Models\Media $media
 * @property-read Model|\Eloquent $mediable
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable whereMediableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mediable whereMediableType($value)
 * @mixin \Eloquent
 */
class Mediable extends Model
{
    protected $table = 'mediables';

    protected $fillable = [
        'media_id',
        'group',
    ];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id', 'id');
    }
}
