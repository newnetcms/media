<?php

namespace Newnet\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Newnet\Media\Exceptions\SuspiciousContentException;
use Newnet\Media\Exceptions\UnsupportedFileExtensionException;
use Newnet\Media\Facades\Img;
use Newnet\Media\MediaUploader;
use Newnet\Media\Models\Media;
use Newnet\Media\Models\Mediable;
use Newnet\Media\Repositories\MediableRepositoryInterace;
use Newnet\Media\Repositories\MediaRepositoryInterface;
use Newnet\Media\Resources\FroalaMediaResource;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

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
    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $medias = $this->mediaRepository->filter($filters, config('cms.media.itemOnPage'));
        $pagination = [
            'hasMore' => $medias->hasMorePages(),
            'nextPage' => $medias->currentPage() + 1,
        ];

        // Infinite scroll tải thêm trang: chỉ cần fragment item để nối vào
        // #mediaItemsWrapper hiện có, không cần build lại sidebar/chip (không
        // đổi khi chỉ tải thêm cùng 1 bộ lọc).
        if ($request->ajax() && $request->boolean('append')) {
            $mode = $filters['mode'];
            $itemsView = view('media::admin.partials.media-items-only', compact('medias', 'mode'))->render();

            return response()->json(array_merge($pagination, [
                'result' => $itemsView,
                'status' => 'C200',
            ]));
        }

        $sidebar = $this->buildSidebarData($filters);
        $activeChips = $this->activeFilterChips($sidebar, $filters);
        $stats = $this->mediaRepository->filterStats($filters);
        $loadedCount = $medias->count();
        $statsView = view('media::admin.partials.stats-bar', compact('stats', 'loadedCount'))->render();

        if ($request->ajax()) {
            $mode = $filters['mode'];
            $listView = view('media::admin.partials.media-grid', compact('medias', 'mode'))->render();
            $sidebarView = view('media::admin.partials.sidebar', compact('sidebar'))->render();

            return response()->json(array_merge($pagination, [
                'result' => $listView,
                'sidebar' => $sidebarView,
                'activeChips' => $activeChips,
                'stats' => $statsView,
                'status' => 'C200',
            ]));
        }

        return view('media::admin.index', [
            'medias' => $medias,
            'filters' => $filters,
            'sidebar' => $sidebar,
            'activeChips' => $activeChips,
            'statsView' => $statsView,
            'sortOptions' => $this->buildSortOptions(),
            'pagination' => $pagination,
        ]);
    }

    /**
     * Danh sách chip hiển thị MỌI chiều lọc đang bật cùng lúc (type + month +
     * model + unattached có thể kết hợp với nhau) để người dùng biết đang xem
     * gì và bỏ từng chiều riêng — tìm trong sidebar data đã build sẵn để không
     * phải tính lại.
     */
    protected function activeFilterChips(array $sidebar, array $filters): array
    {
        $chips = [];

        foreach (array_slice($sidebar['quickViews'], 1) as $view) {
            if ($view['active']) {
                // 'clearFilter' = chiều lọc cần bỏ khi bấm chip — picker modal
                // (form.media) dùng key này để tự reset đúng biến filter rồi
                // gọi lại AJAX, thay vì theo clearUrl (điều hướng cả trang).
                $chips[] = ['label' => $view['label'], 'clearUrl' => $this->filterUrl(['type' => 'all'], $filters), 'clearFilter' => 'type'];
            }
        }

        foreach ($sidebar['yearGroups'] as $group) {
            foreach ($group['months'] as $month) {
                if ($month['active']) {
                    $chips[] = [
                        'label' => $group['year'] . ' - ' . $month['label'],
                        'clearUrl' => $this->filterUrl(['month' => null], $filters),
                        'clearFilter' => 'month',
                    ];
                }
            }
        }

        foreach ($sidebar['modelList'] as $model) {
            if ($model['active']) {
                $chips[] = [
                    'label' => __('media::media.sidebar.by_usage') . ': ' . $model['label'],
                    'clearUrl' => $this->filterUrl(['model' => 'all'], $filters),
                    'clearFilter' => 'model',
                ];
            }
        }

        if ($sidebar['unattachedToggle']['active']) {
            $chips[] = [
                'label' => $sidebar['unattachedToggle']['label'],
                'clearUrl' => $this->filterUrl(['unattached' => null], $filters),
                'clearFilter' => 'unattached',
            ];
        }

        if (count($chips) > 1) {
            $chips[] = [
                'label' => __('media::media.filter.clear_all'),
                'clearUrl' => $this->clearAllFiltersUrl($filters),
                'isClearAll' => true,
                'clearFilter' => 'all',
            ];
        }

        return $chips;
    }

    /**
     * 4 lựa chọn sắp xếp cố định (field sortable chỉ có created_at/size, xem
     * MediaRepository::$sortableFields) — icon mũi tên xuống/lên cho desc/asc
     * để UI sort hiển thị dạng icon thay vì dropdown chữ.
     */
    protected function buildSortOptions(): array
    {
        return [
            ['value' => 'created_at-desc', 'label' => __('media::media.sort.created_at_desc'), 'icon' => 'fa-arrow-down'],
            ['value' => 'created_at-asc', 'label' => __('media::media.sort.created_at_asc'), 'icon' => 'fa-arrow-up'],
            ['value' => 'size-desc', 'label' => __('media::media.sort.size_desc'), 'icon' => 'fa-arrow-down'],
            ['value' => 'size-asc', 'label' => __('media::media.sort.size_asc'), 'icon' => 'fa-arrow-up'],
        ];
    }

    /**
     * Dữ liệu cho sidebar: thư viện nhanh (loại file), theo năm-tháng upload,
     * theo nơi sử dụng (+ toggle "chưa gắn vào đâu" riêng), và dung lượng đã
     * dùng. type/month/model/unattached giờ kết hợp được với nhau — mỗi click
     * chỉ toggle đúng 1 chiều, giữ nguyên các chiều khác (xem filterUrl()).
     */
    protected function buildSidebarData(array $filters): array
    {
        $stats = $this->mediaRepository->sidebarStats();
        $typeCounts = $stats['typeCounts'];
        $totalCount = (int) $typeCounts->sum();
        $documentCount = max(0, $totalCount - ($typeCounts['image'] ?? 0) - ($typeCounts['video'] ?? 0) - ($typeCounts['audio'] ?? 0));

        $typeDefs = [
            ['value' => 'all', 'label' => __('media::media.sidebar.all'), 'count' => $totalCount],
            ['value' => 'image', 'label' => __('media::media.sidebar.image'), 'count' => $typeCounts['image'] ?? 0],
            ['value' => 'video', 'label' => __('media::media.sidebar.video'), 'count' => $typeCounts['video'] ?? 0],
            ['value' => 'audio', 'label' => __('media::media.sidebar.audio'), 'count' => $typeCounts['audio'] ?? 0],
            ['value' => 'document', 'label' => __('media::media.sidebar.document'), 'count' => $documentCount],
        ];

        $quickViews = array_map(function ($def) use ($filters) {
            $active = $filters['type'] === $def['value'];
            // "Tất cả" luôn trỏ về type=all; các loại khác bấm lại để bỏ chọn (toggle).
            $toggledType = $def['value'] === 'all' ? 'all' : ($active ? 'all' : $def['value']);

            return [
                'label' => $def['label'],
                'count' => $def['count'],
                'active' => $def['value'] === 'all' ? $filters['type'] === 'all' : $active,
                // 'value' = type sẽ áp dụng nếu bấm vào mục này — picker modal
                // (form.media) dùng key này qua data-value thay cho href, vì
                // click trong modal chỉ nên cập nhật bộ lọc + gọi lại AJAX chứ
                // không điều hướng rời trang form đang sửa.
                'value' => $toggledType,
                'url' => $this->filterUrl(['type' => $toggledType], $filters),
            ];
        }, $typeDefs);

        $allMonthsLink = [
            'label' => __('media::media.sidebar.all'),
            'count' => $totalCount,
            'active' => !$filters['month'],
            'value' => null,
            'url' => $this->filterUrl(['month' => null], $filters),
        ];

        $yearGroups = [];
        foreach ($stats['monthCounts'] as $monthKey => $count) {
            [$year, $monthNum] = explode('-', $monthKey);
            if (!isset($yearGroups[$year])) {
                $yearGroups[$year] = ['year' => $year, 'months' => []];
            }
            $active = $filters['month'] === $monthKey;
            $toggledMonth = $active ? null : $monthKey;
            $yearGroups[$year]['months'][] = [
                'key' => $monthKey,
                'label' => __('media::media.sidebar.month') . ' ' . (int) $monthNum,
                'count' => $count,
                'active' => $active,
                'value' => $toggledMonth,
                'url' => $this->filterUrl(['month' => $toggledMonth], $filters),
            ];
        }

        // Năm gần nhất luôn mở sẵn; năm khác chỉ mở sẵn nếu đang có tháng được chọn trong đó
        // (còn lại thu gọn để sidebar không quá dài khi media trải qua nhiều năm).
        $firstYear = true;
        foreach ($yearGroups as $year => &$group) {
            $hasActiveMonth = collect($group['months'])->contains('active', true);
            $group['expanded'] = $firstYear || $hasActiveMonth;
            $firstYear = false;
        }
        unset($group);

        $unattachedActive = !empty($filters['unattached']);

        // "Tất cả" của nhóm Nơi sử dụng = bỏ luôn cả model lẫn unattached (2
        // chiều loại trừ nhau trong cùng nhóm này), không chỉ riêng model.
        $allModelsLink = [
            'label' => __('media::media.sidebar.all'),
            'count' => $totalCount,
            'active' => $filters['model'] === 'all' && !$unattachedActive,
            'value' => 'all',
            'url' => $this->filterUrl(['model' => 'all', 'unattached' => null], $filters),
        ];

        $modelList = [];
        foreach ($stats['modelCounts'] as $modelType => $count) {
            $active = $filters['model'] === $modelType;
            $toggledModel = $active ? 'all' : $modelType;
            $modelList[] = [
                'label' => class_basename($modelType),
                'count' => $count,
                'active' => $active,
                'value' => $toggledModel,
                // Model và "chưa gắn vào đâu" loại trừ nhau (đối nghịch về nghĩa),
                // nên chọn model thì luôn bỏ unattached, bất kể unattached có đang bật hay không.
                'url' => $this->filterUrl(['model' => $toggledModel, 'unattached' => null], $filters),
            ];
        }

        $unattachedToggle = [
            'label' => __('media::media.sidebar.unattached'),
            'count' => $stats['unattachedCount'],
            'active' => $unattachedActive,
            'value' => $unattachedActive ? 0 : 1,
            'url' => $this->filterUrl([
                'unattached' => $unattachedActive ? null : 1,
                'model' => 'all',
            ], $filters),
        ];

        return [
            'quickViews' => $quickViews,
            'allMonthsLink' => $allMonthsLink,
            'yearGroups' => array_values($yearGroups),
            'allModelsLink' => $allModelsLink,
            'modelList' => $modelList,
            'unattachedToggle' => $unattachedToggle,
            'totalSizeHuman' => Media::humanSize($stats['totalSize']),
        ];
    }

    /**
     * URL cho một liên kết sidebar: giữ nguyên TẤT CẢ chiều lọc hiện tại
     * (type/model/month/unattached đều kết hợp được với nhau) và chỉ đổi đúng
     * chiều được truyền vào $overrides — mỗi lần gọi là một lần toggle 1 chiều,
     * không ảnh hưởng các chiều khác.
     */
    protected function filterUrl(array $overrides, array $current): string
    {
        $params = array_merge([
            'name' => $current['name'] ?? null,
            'sort' => $current['sort'] ?? null,
            'mode' => $current['mode'] ?? null,
            'type' => $current['type'] ?? 'all',
            'model' => $current['model'] ?? 'all',
            'month' => $current['month'] ?? null,
            'unattached' => !empty($current['unattached']) ? 1 : null,
        ], $overrides);

        return route('media.admin.media.index', array_filter($params, fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Link "bỏ hết lọc" khi có ≥2 chip đang bật cùng lúc — tiện gộp xoá 1 lần
     * thay vì bấm từng chip.
     */
    protected function clearAllFiltersUrl(array $current): string
    {
        return $this->filterUrl(['type' => 'all', 'model' => 'all', 'month' => null, 'unattached' => null], $current);
    }

    /**
     * Chuẩn hoá các tham số lọc/sắp xếp từ request thành một mảng duy nhất,
     * dùng chung cho cả lần tải trang đầu và các lần gọi lại qua AJAX.
     */
    protected function parseFilters(Request $request): array
    {
        $sortValue = $request->input('sort') ?: 'id-desc';
        $sort = explode('-', $sortValue);

        $month = $request->input('month');
        [$dateFrom, $dateTo] = $month ? $this->monthBounds($month) : [null, null];

        return [
            'name' => $request->input('name'),
            'type' => $request->input('type') ?: 'all',
            'model' => $request->input('model') ?: 'all',
            'month' => $month,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'unattached' => $request->boolean('unattached'),
            'sort' => $sortValue,
            'sort_field' => $sort[0] ?? 'id',
            'sort_dir' => $sort[1] ?? 'desc',
            'mode' => $request->input('mode') ?: 'grid',
            'page' => $request->input('page'),
        ];
    }

    protected function monthBounds(string $month): array
    {
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

            return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
        } catch (\Throwable $exception) {
            return [null, null];
        }
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
        $file->load('mediables.mediable', 'author');

        if ($file->isDisplayableImage()) {
            $src = \Img::url($file->getUrl(), 600, 600);
        } else if ($file->type == 'video') {
            $src = asset('vendor/media/images/types/video.png');
        } else if ($file->type == 'audio') {
            $src = asset('vendor/media/images/types/mp3.png');
        } else {
            $src = asset('vendor/media/images/types/text.png');
        }

        return response()->json([
            // Chỉ thêm các field tính toán (url, human_size, dimensions, ...) ngay
            // tại response này, không thêm vào $appends của model vì Media được
            // serialize ở nhiều nơi khác (FroalaMediaResource, MediaResource, ...)
            // và dimensions phải đọc file trên disk nên không nên luôn kèm theo.
            'file' => array_merge($file->toArray(), [
                'type' => $file->type,
                'url' => $file->getUrl(),
                'human_size' => $file->human_size,
                'dimensions' => $file->dimensions,
                'alt' => $file->alt,
                'caption' => $file->caption,
                'created_at' => $file->created_at?->format('d/m/Y H:i:s'),
                'author' => $file->author ? ['name' => $file->author->name ?? null] : null,
            ]),
            'src' => $src,
            'usages' => $file->mediables->map(fn ($mediable) => $this->describeMediable($mediable)),
        ]);
    }

    /**
     * Mô tả ngắn một bản ghi mediable để hiển thị trong mục "Dùng ở đâu", kèm
     * link chỉnh sửa khi resolve được route theo convention {module}.admin.{model}.edit
     * (best-effort, không hardcode theo module cụ thể để lib/media không phải biết
     * về lib/cms, lib/banner-manager, ...).
     */
    protected function describeMediable(Mediable $mediable): array
    {
        $related = $mediable->mediable;

        if (!$related) {
            return [
                'type' => $mediable->mediable_type,
                'id' => $mediable->mediable_id,
                'label' => class_basename($mediable->mediable_type) . ' #' . $mediable->mediable_id . ' (đã xoá)',
                'editUrl' => null,
            ];
        }

        $segments = explode('\\', get_class($related));
        $module = Str::lower($segments[1] ?? '');
        $model = Str::lower(class_basename($related));

        $editUrl = null;
        try {
            $editUrl = route("{$module}.admin.{$model}.edit", $related->getKey());
        } catch (\Throwable $exception) {
            $editUrl = null;
        }

        return [
            'type' => class_basename($related),
            'id' => $related->getKey(),
            'label' => $related->name ?? $related->title ?? (class_basename($related) . ' #' . $related->getKey()),
            'editUrl' => $editUrl,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        /** @var Media $file */
        $file = $this->mediaRepository->getById($id);

        $file->name = $data['name'] ?: $file->name;
        $file->attrs = array_merge($file->attrs ?? [], [
            'alt' => $data['alt'] ?? '',
            'caption' => $data['caption'] ?? '',
        ]);
        $file->save();

        return response()->json(['success' => true, 'file' => $file]);
    }

    /**
     * Xoá nhiều media cùng lúc (bulk action từ trang Danh sách).
     */
    public function destroy(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array|min:1'])['ids'];

        foreach ($this->mediaRepository->findMany($ids) as $media) {
            $media->delete();
        }

        Session::flash('success', __('media::media.notification.deleted'));
        return response()->json(['success' => true]);
    }

    /**
     * Đóng gói nhiều media đã chọn thành một file zip để tải xuống cùng lúc.
     */
    public function bulkDownload(Request $request): StreamedResponse
    {
        $ids = $request->validate(['ids' => 'required|array|min:1'])['ids'];
        $medias = $this->mediaRepository->findMany($ids);

        $zipPath = tempnam(sys_get_temp_dir(), 'media_zip_');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);

        foreach ($medias as $media) {
            try {
                $zip->addFromString($media->file_name, $media->filesystem()->get($media->getPath()));
            } catch (\Throwable $exception) {
                continue;
            }
        }

        $zip->close();

        return response()->streamDownload(function () use ($zipPath) {
            echo file_get_contents($zipPath);
            unlink($zipPath);
        }, 'media-' . now()->format('Ymd-His') . '.zip');
    }

    public function froalaLoadImages(Request $request)
    {
        $items = $this->mediaRepository->all();

        FroalaMediaResource::withoutWrapping();
        $data = FroalaMediaResource::collection($items);

        return response()->json($data);
    }

    public function ajaxMedia(Request $request){
        // Dùng chung filter()/buildSidebarData() với trang Danh sách media, để
        // picker modal hỗ trợ search/sort/sidebar (loại file, theo tháng, theo
        // nơi sử dụng) mà không cần viết lại logic lọc/đếm.
        $month = $request->input('month');
        [$dateFrom, $dateTo] = $month ? $this->monthBounds($month) : [null, null];

        $filters = [
            'name' => $request->input('name'),
            'type' => $request->input('type') ?: 'all',
            'model' => $request->input('model') ?: 'all',
            'month' => $month,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'unattached' => $request->boolean('unattached'),
            'sort_field' => $request->input('sort_field'),
            'sort_dir' => $request->input('sort_dir'),
        ];
        $medias = $this->mediaRepository->filter($filters, 20);

        $mode = $request->mode;
        $view = view("media::form.result",compact('medias', 'mode'))->render();
        $response = [
            'result' => $view,
            'status' => 'C200',
            // Để JS biết còn trang kế tiếp hay không — vừa tránh gọi thêm trang
            // rỗng khi cuộn, vừa để tự tải thêm nếu trang đầu chưa đủ dài tạo
            // scrollbar (modal mở lần đầu, màn hình cao) — xem maybeFillViewport().
            'hasMore' => $medias->hasMorePages(),
            'nextPage' => $medias->currentPage() + 1,
        ];

        // Sidebar + chip row chỉ cần render lại khi đổi bộ lọc (không phải khi
        // cuộn tải thêm trang cùng 1 bộ lọc) — JS chỉ gửi with_sidebar=1 cho reloadImg().
        if ($request->boolean('with_sidebar')) {
            $sidebar = $this->buildSidebarData($filters);
            $response['sidebar'] = view('media::form.partials.picker-sidebar', compact('sidebar'))->render();

            $activeChips = $this->activeFilterChips($sidebar, $filters);
            $response['chips'] = view('media::form.partials.picker-chip-row', compact('activeChips'))->render();
        }

        return response()->json($response);

    }

    /**
     * Upload 1 file mỗi request (cả trang Danh sách media lẫn modal picker đều
     * gửi từng file một). Lỗi trả JSON 422 thay vì redirect — redirect bị XHR
     * tự đi theo thành 200 nên trước đây file sai định dạng/quá dung lượng vẫn
     * bị client coi là upload thành công. Trả kèm thông tin media để modal
     * picker tự chọn file vừa upload vào field đã mở modal.
     */
    public function storeAjax(Request $request, MediaUploader $mediaUploader){
        $files = $request->file('image-upload');
        $file = is_array($files) ? reset($files) : $files;

        if (!$file) {
            return response()->json(['message' => __('media::media.upload.error')], 422);
        }

        $validator = \Validator::make(['image' => $file], ['image' => config('cms.media.validatorImage')]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('image')], 422);
        }

        try {
            $media = $mediaUploader->setFile($file)->upload();
        } catch (UnsupportedFileExtensionException $exception) {
            return response()->json(['message' => __('media::media.upload.unsupported_type')], 422);
        } catch (SuspiciousContentException $exception) {
            return response()->json(['message' => __('media::media.upload.error')], 422);
        }

        $isImage = $media->isOfType('image');

        return response()->json([
            'status' => 'C200',
            'id' => $media->id,
            // Cùng định dạng với data-src/data-type/data-ext của form/result.blade.php
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'src' => $isImage ? Img::url($media->getUrl(), 300, 300) : '',
                'type' => $isImage ? 'image' : 'other',
                'ext' => strtoupper($media->extension),
                // url gốc + kind dùng khi chèn vào TinyMCE (src ở trên chỉ là thumbnail)
                'url' => $media->getUrl(),
                'kind' => $media->insertableKind(),
            ],
        ]);
    }
}
