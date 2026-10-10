<?php

namespace Newnet\Media\Repositories;

use Newnet\Core\Repositories\BaseRepository;
use Newnet\Media\Models\Media;
use Newnet\Media\Models\Mediable;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    protected $sortableFields = ['id', 'created_at', 'size', 'name'];

    public function getByCondition($array)
    {
        return $this->model->where($array);
    }

    public function paginate($itemOnPage)
    {
        return $this->notIgnoredQuery()
            ->orderByDesc('id')
            ->paginate($itemOnPage);
    }

    public function paginateAll($itemOnPage)
    {
        return $this->model->orderByDesc('id')->paginate($itemOnPage);
    }

    /**
     * Lọc media cho trang Danh sách admin: kết hợp tên, loại file, model gắn vào
     * và khoảng ngày upload trên cùng một query, thay cho search()/sort() cũ chỉ
     * nhận được một field mỗi lần (field sau đè field trước).
     */
    public function filter(array $filters, int $itemOnPage)
    {
        $query = $this->buildFilterQuery($filters)->with(['author', 'mediables.mediable']);

        $sortField = in_array($filters['sort_field'] ?? null, $this->sortableFields, true)
            ? $filters['sort_field']
            : 'id';
        $sortDir = ($filters['sort_dir'] ?? null) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortField, $sortDir);

        return $query->paginate($itemOnPage)->appends($filters);
    }

    /**
     * Tổng số file + tổng dung lượng khớp với đúng bộ lọc hiện tại (không phân
     * trang) — dùng cho thanh thống kê bên dưới danh sách. Dùng chung điều kiện
     * WHERE với filter() (buildFilterQuery()) để không bị lệch số với danh sách
     * đang hiển thị.
     */
    public function filterStats(array $filters): array
    {
        $row = $this->buildFilterQuery($filters)
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(size), 0) as total_size')
            ->first();

        return [
            'count' => (int) ($row->total_count ?? 0),
            'totalSize' => (int) ($row->total_size ?? 0),
        ];
    }

    /**
     * Điều kiện WHERE dùng chung cho filter() (có phân trang) và filterStats()
     * (chỉ đếm/tổng, không phân trang) — tách riêng để số liệu thống kê luôn
     * khớp đúng với danh sách đang hiển thị, không bị lệch do viết trùng logic.
     */
    protected function buildFilterQuery(array $filters)
    {
        $query = $this->notIgnoredQuery();

        if ($name = trim((string) ($filters['name'] ?? ''))) {
            // Tìm theo cả tên hiển thị lẫn tên file gốc trên đĩa — admin thường
            // nhớ tên file thật (có đuôi, có khi kèm hậu tố) hơn là tên hiển thị
            // đã qua chỉnh sửa.
            $query->where(function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                    ->orWhere('file_name', 'like', '%' . $name . '%');
            });
        }

        if (($type = $filters['type'] ?? 'all') && $type !== 'all') {
            if ($type === 'document') {
                $query->where('mime_type', 'not like', 'image/%')
                    ->where('mime_type', 'not like', 'video/%')
                    ->where('mime_type', 'not like', 'audio/%');
            } else {
                $query->where('mime_type', 'like', $type . '/%');
            }
        }

        if (($model = $filters['model'] ?? 'all') && $model !== 'all') {
            $query->whereHas('mediables', function ($q) use ($model) {
                $q->where('mediable_type', $model);
            });
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (!empty($filters['unattached'])) {
            $query->doesntHave('mediables');
        }

        return $query;
    }

    /**
     * Số liệu cho sidebar trang Danh sách: đếm theo loại file, theo tháng upload,
     * theo model gắn vào, media chưa gắn vào đâu, và tổng dung lượng đã dùng.
     */
    public function sidebarStats(): array
    {
        $ignoreModels = config('cms.media.ignore_models');

        $typeCounts = $this->notIgnoredQuery()
            ->selectRaw("SUBSTRING_INDEX(mime_type, '/', 1) as type_key, COUNT(*) as total")
            ->groupBy('type_key')
            ->pluck('total', 'type_key');

        $unattachedCount = $this->notIgnoredQuery()->doesntHave('mediables')->count();

        $monthCounts = $this->notIgnoredQuery()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->pluck('total', 'month_key');

        $modelCounts = Mediable::whereNotIn('mediable_type', $ignoreModels)
            ->selectRaw('mediable_type, COUNT(DISTINCT media_id) as total')
            ->groupBy('mediable_type')
            ->pluck('total', 'mediable_type');

        $totalSize = (int) $this->notIgnoredQuery()->sum('size');

        return [
            'typeCounts' => $typeCounts,
            'unattachedCount' => $unattachedCount,
            'monthCounts' => $monthCounts,
            'modelCounts' => $modelCounts,
            'totalSize' => $totalSize,
        ];
    }

    protected function notIgnoredQuery()
    {
        $ignoreModels = config('cms.media.ignore_models');

        return Media::where(function ($q) use ($ignoreModels) {
            $q->whereHas('mediables', function ($q) use ($ignoreModels) {
                $q->whereNotIn('mediable_type', $ignoreModels);
            });
            $q->orDoesntHave('mediables');
        });
    }
}
