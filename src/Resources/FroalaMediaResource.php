<?php

namespace Newnet\Media\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Newnet\Media\Facades\Img;
use Newnet\Media\Models\Media;

/**
 * Class FroalaMediaResource
 *
 * @package Newnet\Media\Resources
 * @mixin Media
 */
class FroalaMediaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'media-id' => $this->id,
            'name'     => $this->name,
            'url'      => $this->getUrl(),
            'thumb'    => Img::url($this->getUrl(), 300, 300),
        ];
    }
}
