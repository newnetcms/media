<?php

namespace Newnet\Media\Repositories;

use Newnet\Core\Repositories\BaseRepository;
use Newnet\Media\Models\Media;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    public function getByCondition($array)
    {
        return $this->model->where($array);
    }

    public function search($field, $key)
    {
        return $this->model->where($field, 'like', '%'.$key.'%')->get();
    }

    public function sort($field, $value)
    {
        return $this->model->orderBy($field, $value)->get();
    }

    public function paginate($itemOnPage)
    {
        $ignore_models = config('cms.media.ignore_models');

        return Media::where(function ($q) use ($ignore_models) {
                $q->whereHas('mediables', function ($q) use ($ignore_models) {
                    $q->whereNotIn('mediable_type', $ignore_models);
                });
                $q->orDoesntHave('mediables');
            })
            ->orderByDesc('id')
            ->paginate($itemOnPage);
    }

    public function paginateAll($itemOnPage)
    {
        return $this->model->orderByDesc('id')->paginate($itemOnPage);
    }
}
