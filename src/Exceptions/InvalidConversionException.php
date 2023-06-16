<?php

namespace Newnet\Media\Exceptions;

use Exception;

class InvalidConversionException extends Exception
{
    /**
     * @param string $name
     * @return InvalidConversionException
     */
    public static function doesNotExist($name)
    {
        return new static("Conversion `{$name}` does not exist");
    }
}
