<?php

declare(strict_types=1);

namespace Mmopane\Support\Utils;

class PathUtils
{
    /**
     * Prepare path.
     * Removes extra symbols at the end of the path.
     *
     * @param string $path
     * @return string
     */
    public static function prepare(string $path): string
    {
        return rtrim($path, "\n\r\t\v\0\/ ");
    }

    /**
     * Join path.
     * Combines parts of paths into a single one.
     *
     * @param string $path
     * @param string ...$paths
     * @return string
     */
    public static function join(string $path, string ...$paths): string
    {
        $path = PathUtils::prepare($path);
        foreach ($paths as $index => $item) {
            if (empty($item) or $item === DIRECTORY_SEPARATOR)
                unset($paths[$index]);
            else
                $paths[$index] = DIRECTORY_SEPARATOR . trim($item, "\n\r\t\v\0\/ ");
        }
        return $path . implode('', $paths);
    }

    /**
     * Extract the regular expression from a path template.
     * By default, dynamic path parts have the following pattern: ([A-Za-z0-9-]+).
     * You can redefine your patterns for individual dynamic parameters of the path in the conditions array.
     *
     * @param string $pathTemplate
     * @param array<string, string> $conditions
     * @return string
     */
    public static function extractRegex(string $pathTemplate, array $conditions = []): string
    {
        $pathTemplate = static::prepare($pathTemplate);
        $segments     = [];
        foreach (explode('/', $pathTemplate) as $segment) {
            if (preg_match('^\{(\w+)}$^', $segment)) {
                $segment = trim($segment, '{}');
                if (isset($conditions[$segment]))
                    $segments[] = '(' . $conditions[$segment] . ')';
                else
                    $segments[] = '([A-Za-z0-9-]+)';
                continue;
            }
            $segments[] = $segment;
        }
        return '/^' . implode('\/', $segments) . '$/';
    }

    /**
     * Extract the names of the dynamic parameters of the path template.
     * The path template can contain dynamic parameters: /path/{parameter}.
     *
     * @param string $pathTemplate
     * @return list<string>
     */
    public static function extractParametersNames(string $pathTemplate): array
    {
        $pathTemplate = static::prepare($pathTemplate);
        $matches      = [];
        preg_match_all("({\w+})", $pathTemplate, $matches);
        if (empty($matches) or empty($matches[0]))
            return [];
        return array_map(fn(string $value) => trim($value, '{}'), $matches[0]);
    }

    /**
     * Extract the values of the dynamic parameters of the path template regex.
     *
     * @param string $path
     * @param string $regex
     * @return array
     */
    public static function extractParametersValues(string $path, string $regex): array
    {
        $path    = static::prepare($path);
        $matches = [];
        preg_match($regex, $path, $matches);
        return array_slice($matches, 1);
    }

    /**
     * Inject the dynamic parameters in the path template.
     *
     * @param string $pathTemplate
     * @param array $parameters
     * @return string
     */
    public function injectParameters(string $pathTemplate, array $parameters = []): string
    {
        $pathTemplate = static::prepare($pathTemplate);
        if (empty($parameters))
            return $pathTemplate;
        $keys = array_map(fn($key) => '{' . $key . '}', array_keys($parameters));
        return str_replace($keys, $parameters, $pathTemplate);
    }
}