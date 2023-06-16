<?php

namespace Newnet\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Newnet\Media\Models\MediaTag;

class MediaTagController extends Controller
{
    public function __invoke(Request $request)
    {
        $media_id = $request->input('media_id');

        $mediaTag = MediaTag::whereMediaId($media_id)->first();
        if (!$mediaTag) {
            $mediaTag = MediaTag::create([
                'media_id' => $media_id,
            ]);
        }

        $mediaTag->update($request->only([
            'label',
            'title',
        ]));

        return [
            'success' => true,
        ];
    }
}
