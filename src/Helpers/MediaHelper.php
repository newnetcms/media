<?php

namespace Newnet\Media\Helpers;

class MediaHelper
{
    public function handleRemoveDuplicate($colections, $field, $isFormat) {
        $arrayData = [];
        foreach ($colections as $colection) {
            if ($isFormat) {
                $value = $colection->$field->format('Y-m');
            } else {
                $value = $colection->$field;
            }
            array_push($arrayData, $value);
        }
        return array_unique($arrayData);
    }

}
