<?php

declare(strict_types=1);

namespace Mmopane\Support\Utils;

class ArrayUtils
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param mixed $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(array $array, mixed $key, mixed $default = null): mixed
    {
        if(is_null($key))
            return Utils::toDefault($default);
        if(static::containsKey($array, $key))
            return $array[$key];
        if(!str_contains($key, '.'))
            return $array[$key] ?? Utils::toDefault($default);
        foreach (explode('.', $key) as $segment)
        {
            if (is_array($array) and static::containsKey($array, $segment))
                $array = $array[$segment];
            else
                return Utils::toDefault($default);
        }
        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $value
     * @return array
     */
    public static function set(array &$array, mixed $key, mixed $value): array
    {
        if(is_null($key))
            return $array = $value;
        $keys = explode('.', $key);
        foreach($keys as $index => $key)
        {
            if(count($keys) === 1)
                break;
            unset($keys[$index]);
            if(!isset($array[$key]) or !is_array($array[$key]))
                $array[$key] = [];
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param array $array
     * @param mixed $keys
     * @return bool
     */
    public static function contains(array $array, mixed $keys): bool
    {
        $keys = (array) $keys;
        if(empty($array) || empty($keys))
            return false;
        foreach ($keys as $key)
        {
            $subKeyArray = $array;
            if(static::containsKey($array, $key))
                continue;
            foreach (explode('.', $key) as $segment)
            {
                if(is_array($subKeyArray) && static::containsKey($subKeyArray, $segment))
                    $subKeyArray = $subKeyArray[$segment];
                else
                    return false;
            }
        }
        return true;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param mixed $keys
     * @return void
     */
    public static function remove(array &$array, mixed $keys): void
    {
        $original = &$array;
        $keys = (array) $keys;
        if(empty($keys))
            return;
        foreach ($keys as $key)
        {
            if(static::containsKey($array, $key))
            {
                unset($array[$key]);
                continue;
            }
            $parts = explode('.', $key);
            $array = &$original;
            while(count($parts) > 1)
            {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part]))
                    $array = &$array[$part];
                else
                    continue 2;
            }
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array $array
     * @param mixed $key
     * @return bool
     */
    public static function containsKey(array $array, mixed $key): bool
    {
        if(is_float($key))
            $key = (string) $key;
        return array_key_exists($key, $array);
    }

    /**
     * Get the first item of the array.
     *
     * @param array $array
     * @param mixed|null $default
     * @return mixed
     */
    public static function first(array $array, mixed $default = null): mixed
    {
        $key = array_key_first($array);
        if(is_null($key))
            return Utils::toDefault($default);
        return $array[$key];
    }

    /**
     * Get the last item of the array.
     *
     * @param array $array
     * @param mixed|null $default
     * @return mixed
     */
    public static function last(array $array, mixed $default = null): mixed
    {
        $key = array_key_last($array);
        if(is_null($key))
            return Utils::toDefault($default);
        return $array[$key];
    }

    /**
     * Get the structured information about a given array in a string type.
     *
     * @param array $array
     * @param bool $beautify
     * @param int $depth
     * @return string
     */
    public static function toString(array $array, bool $beautify = false, int $depth = 0): string
    {
        if(empty($array))
            return "[]";
        $tab = $beautify ? str_repeat("\t", $depth) : "";
        $result = $beautify ? "[\n" : "[";
        $count = count($array);
        foreach ($array as $key => $val)
        {
            $count--;
            $result .= $beautify ? ($tab . "\t") : "";
            $result .= Utils::toString($key, $beautify);
            $result .= $beautify ? " => " : "=>";
            if(is_array($val))
                $result .= static::toString($val, $beautify, $depth + 1);
            else
                $result .= Utils::toString($val, $beautify);
            $result .= ($count > 0) ? "," : "";
            $result .= $beautify ? "\n" : "";
        }
        return $beautify ? $result . $tab . "]" : $result . "]";
    }
}