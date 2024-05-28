<?php

declare(strict_types=1);

namespace Mmopane\Support;

use Closure;

readonly class ClosureWrapper
{
    /**
     * @param mixed $target Wrapable target.
     * @param Collection $attributes Additional attributes.
     */
    public function __construct(
        public mixed      $target,
        public Collection $attributes = new Collection(),
    ) {}

    /**
     * Wraps the target in a closure.
     * If the target is a callable, return a closure.
     * If the target is an array where the first element is a class and the second is a method, the instantiation will create and return the method's closure.
     * Otherwise, it will return a closure whose call result is the target.
     *
     * @param array $parameters Instance constructor parameters.
     * @return Closure
     */
    public function wrap(array $parameters = []): Closure
    {
        if ($this->target instanceof Closure)
            return $this->target;
        if (is_array($this->target) and isset($this->target[0], $this->target[1])) {
            $class = strval($this->target[0]);
            if (class_exists($class)) {
                try {
                    $reflection = new \ReflectionClass($this->target[0]);
                    $method     = strval($this->target[1]);

                    if ($reflection->hasMethod($method)) {
                        if ($reflection->getMethod($method)->isStatic())
                            return ($this->target)(...);
                        else
                            return (new $this->target[0](...$parameters))->{$this->target[1]}(...);
                    }
                } catch (\ReflectionException) {
                    return fn() => $this->target;
                }
            }
        }
        return fn() => $this->target;
    }
}