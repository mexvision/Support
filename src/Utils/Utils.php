<?php

declare(strict_types=1);

namespace Mmopane\Support\Utils;

use Closure;

class Utils
{
    /**
     * Get the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$parameters
     * @return mixed
     */
    public static function toDefault(mixed $value, mixed ...$parameters): mixed
    {
        return $value instanceof Closure ? $value(...$parameters) : $value;
    }

    /**
     * Get the structured information about a given variable in a string type.
     *
     * @param mixed $value
     * @param bool $beautify
     * @return string
     */
    public static function toString(mixed $value, bool $beautify = false): string
    {
        if(is_array($value))
            return ArrayUtils::toString($value, $beautify);
        if(is_object($value))
            return $value::class . '::class';
        return var_export($value, true);
    }
}