<?php

namespace Newnet\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Newnet\Media\Helpers\MediaHelper;
use Newnet\Media\MediaUploader;
use Newnet\Media\Models\Media;
use Newnet\Media\Repositories\MediableRepositoryInterace;
use Newnet\Media\Repositories\MediaRepositoryInterface;
use Newnet\Media\Resources\FroalaMediaResource;

class MediaController extends Controller
{
    /**
     * @var MediaRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var MediableRepositoryInterace
     */
    private $mediableRepositoryInterace;

    public function __construct(MediaRepositoryInterface $mediaRepository, MediableRepositoryInterace $mediableRepositoryInterace)
    {
        $this->mediaRepository = $mediaRepository;
        $this->mediableRepositoryInterace = $mediableRepositoryInterace;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, MediaHelper $mediaHelper)
    {
        $medias = $this->mediaRepository->paginate(config('cms.media.itemOnPage'));
        $mediables = $this->mediableRepositoryInterace->getAll();
        $allMonths = $mediaHelper->handleRemoveDuplicate($medias, 'created_at', true);
        $mediables = $mediaHelper->handleRemoveDuplicate($mediables, 'mediable_type', false);

        if ($request->ajax()) {
            $mode = $request->mode;
            $view = view("media::admin.ajax-result",compact('medias', 'mode'))->render();
            return response()->json(['result' => $view, 'status' => 'C200']);
        }

        return view('media::admin.index', compact('medias', 'allMonths', 'mediables'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, MediaUploader $mediaUploader)
    {
        try {
//            DB::beginTransaction();
            if ($files = $request->file('image-upload')) {
                foreach($files as $file) {
                    $fileArray = array('image' => $file);
                    $rules = array(
                        'image' => config('cms.media.validatorImage')
                    );
                    $validator = \Validator::make($fileArray, $rules);
                    if ($validator->fails()) {
//                        DB::rollBack();
                        return redirect()->back()->with('errors', $validator->errors());
                    } else {
                        $media = $mediaUploader->setFile($file)->upload();
                    }
                }
            }
//            DB::commit();
            return redirect()->back()->with('success', 'Uploaded Successfully!');
        } catch(\Exception $exception) {
//            DB::rollBack();
            return response()->json(['error' => $exception->getMessage()]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        /** @var Media $file */
        $file = $this->mediaRepository->getById($id);
        $modelAttached = $file->mediables;
        if ($file->isOfType('image')) {
            $src = $file->getUrl();
        } else if ($file->type == 'video') {
            $src = asset('vendor/media/images/types/video.png');
        } else if ($file->type == 'audio') {
            $src = asset('vendor/media/images/types/mp3.png');
        } else {
            $src = asset('vendor/media/images/types/text.png');
        }

        return response()->json(['file' => $file, 'src' => $src, 'modelAttached' => $modelAttached]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function search(Request $request)
    {
        $mode = $request->mode;
        $value = $request->value;
        $field = $request->field;
        $medias = $this->mediaRepository->search($field, $value);
        if (count($medias) > 0) {
            $view = view("media::admin.ajax-result",compact('medias', 'mode'))->render();
            return response()->json(['result' => $view, 'status' => 'C200']);
        }
        return response()->json(['result' => [], 'status' => 'C404']);
    }

    public function sort(Request $request) {
        $mode = $request->mode;
        $data = $request->value;
        $data = explode('-', $data);
        $medias = $this->mediaRepository->sort($data[0], $data[1]);
        if (count($medias) > 0) {
            $view = view("media::admin.ajax-result",compact('medias', 'mode'))->render();
            return response()->json(['result' => $view, 'status' => 'C200']);
        }
        return response()->json(['result' => [], 'status' => 'C404']);
    }

    public function destroy(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $media = $this->mediaRepository->getById($id);
            $media->delete();
        }
        Session::flash('success', __('media::media.notification.deleted'));
        return response()->json(['success' => 200]);
    }

    public function froalaLoadImages(Request $request)
    {
        $items = $this->mediaRepository->all();

        FroalaMediaResource::withoutWrapping();
        $data = FroalaMediaResource::collection($items);

        return response()->json($data);
    }

    public function ajaxMedia(Request $request, MediaHelper $mediaHelper){
        $medias = $this->mediaRepository->paginate(20);
        $mediables = $this->mediableRepositoryInterace->getAll();
        $allMonths = $mediaHelper->handleRemoveDuplicate($medias, 'created_at', true);
        $mediables = $mediaHelper->handleRemoveDuplicate($mediables, 'mediable_type', false);

        $mode = $request->mode;
        $view = view("media::form.result",compact('medias', 'mode'))->render();
        return response()->json(['result' => $view, 'status' => 'C200']);

    }

    public function storeAjax(Request $request, MediaUploader $mediaUploader){
//        try {
//            DB::beginTransaction();
            if ($files = $request->file('image-upload')) {
                foreach($files as $file) {
                    $fileArray = array('image' => $file);
                    $rules = array(
                        'image' => config('cms.media.validatorImage')
                    );
                    $validator = \Validator::make($fileArray, $rules);
                    if ($validator->fails()) {
//                        DB::rollBack();
                        return redirect()->back()->with('errors', $validator->errors()->getMessages());
                    } else {
                        $media = $mediaUploader->setFile($file)->upload();
                        return [
                            'id' => $media,
                        ];
                    }
                }
            }
//            DB::commit();
            return true;
//        } catch(\Exception $exception) {
//            DB::rollBack();
//            return false;
//        }
    }
}
