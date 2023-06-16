<?php

namespace Newnet\Media\Facades;

use Illuminate\Support\Facades\Facade;

class Img extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'newnet.media.img';
    }
}
