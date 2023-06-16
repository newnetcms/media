<?php
namespace Newnet\Media\Services;

use Newnet\Media\Models\MediaTag;

class StorageTagService
{
    public function storeTagMedia($request, $dataId, $res)
    {

        if (isset($request['media_ids'])) {
            foreach ($request['media_ids'] as $key => $item) {
                if (isset($item)) {
                    $data = [
                        'media_id' => $item,
                        'label' => $request['media_labels'][$key],
                        'title' => $request['media_titles'][$key],
                    ];
                    $check = MediaTag::where('media_id', $item)->first();

                    if (isset($check)) {
                        $check->update($data);

                    } else {
                        $model = new MediaTag($data);
                        $model->MediaTags()->associate($res);
                        $model->save();
                    }
                }
            }
        }
    }


}
