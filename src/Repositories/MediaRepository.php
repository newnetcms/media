<?php

namespace Newnet\Media\Repositories;

use Newnet\Core\Repositories\BaseRepository;

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
        return $this->model->orderByDesc('id')->paginate($itemOnPage);
    }
}
