<?php

namespace Newnet\Media\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Newnet\Media\Facades\Img;
use Newnet\Media\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'file_name' => $this->file_name,
            'url'       => $this->getUrl(),
            'thumb'     => $this->isOfType('image') ? Img::url($this->getUrl(), 300, 300) : null,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'is_image'  => $this->isOfType('image'),
        ];
    }
}
