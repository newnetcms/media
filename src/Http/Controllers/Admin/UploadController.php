<?php

namespace Newnet\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Newnet\Media\MediaUploader;
use Newnet\Media\Resources\MediaResource;

class UploadController extends Controller
{
    /**
     * @var MediaUploader
     */
    private $mediaUploader;

    public function __construct(MediaUploader $mediaUploader)
    {
        $this->mediaUploader = $mediaUploader;
    }

    public function __invoke(Request $request)
    {
        $medias = new Collection();
        $file = $request->file('file');
        if (is_array($file)) {
            foreach ($file as $item) {
                if ($media = $this->handleUploadMedia($item)) {
                    $medias->push($media);
                }
            }
        } else {
            if ($media = $this->handleUploadMedia($file)) {
                $medias->push($media);
            }
        }

        switch ($request->input('response')) {
            case 'froala':
                return $this->renderFroala($medias);
                break;

            case 'tinymce':
                return $this->renderTinymce($medias);
                break;

            default:
                return $this->renderDefault($medias);
        }
    }

    public function renderDefault(Collection $medias)
    {
        return response()->json([
            'success' => true,
            'files'   => MediaResource::collection($medias),
        ]);
    }

    public function renderFroala(Collection $medias)
    {
        return response()->json([
            'media-id' => $medias->first()->id,
            'name'     => $medias->first()->name,
            'link'     => $medias->first()->getUrl(),
            'thumb'    => $medias->first()->thumb,
        ]);
    }

    public function renderTinymce(Collection $medias)
    {
        return response()->json([
            'media-id' => $medias->first()->id,
            'name'     => $medias->first()->name,
            'link'     => $medias->first()->getUrl(),
            'thumb'    => $medias->first()->thumb,
        ]);
    }

    protected function handleUploadMedia(UploadedFile $item)
    {
        return $this->mediaUploader->setFile($item)->upload();
    }
}
