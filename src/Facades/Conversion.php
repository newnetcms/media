<?php

namespace Newnet\Media\Facades;

use Illuminate\Support\Facades\Facade;
use Newnet\Media\ConversionRegistry;

class Conversion extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConversionRegistry::class;
    }
}
