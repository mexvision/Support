<?php

declare(strict_types=1);

namespace Mmopane\Support;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;

/**
 * @template TKey of array-key
 * @template-covariant TValue
 * @implements ArrayAccess<TKey, TValue>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(
        protected array $items = []
    ) {}

    /**
     * Add an item to the collection.
     *
     * @param TValue $value
     * @return static<TKey, TValue>
     */
    public function add(mixed $value): static
    {
        $this->items[] = $value;
        return $this;
    }

    /**
     * Add an items to the collection.
     *
     * @param array<TKey, TValue>|static<TKey, TValue> $values
     * @return static<TKey, TValue>
     */
    public function addAll(array|self $values): static
    {
        foreach ($values as $value)
            $this->add($value);
        return $this;
    }

    /**
     * Set an item in the collection by key.
     *
     * @param TKey $key
     * @param TValue $value
     * @return static<TKey, TValue>
     */
    public function set(mixed $key, mixed $value): static
    {
        $this->items[$key] = $value;
        return $this;
    }

    /**
     * Set an items in the collection by key.
     *
     * @param array<TKey, TValue>|static<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function setAll(array|self $items): static
    {
        foreach ($items as $key => $value)
            $this->set($key, $value);
        return $this;
    }

    /**
     * Get an item from the collection by key.
     *
     * @template TValueDefault
     * @param TKey $key
     * @param TValueDefault|callable(): TValueDefault $default
     * @return TValue|TValueDefault
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        if(array_key_exists($key, $this->items))
            return $this->items[$key];
        return $default instanceof Closure ? $default() : $default;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param TKey $key
     * @return bool
     */
    public function contains(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Determine if an items exists in the collection by keys.
     *
     * @param list<TKey>|self<array-key, TKey> $keys
     * @return bool
     */
    public function containsAll(array|self $keys): bool
    {
        foreach ($keys as $key)
            if(!$this->contains($key))
                return false;
        return true;
    }

    /**
     * Determine if an items exists in the collection by value.
     *
     * @param TValue $value
     * @param bool $strict
     * @return bool
     */
    public function in(mixed $value, bool $strict = true): bool
    {
        return in_array($value, $this->items, $strict);
    }

    /**
     * Determine if an items exists in the collection by values.
     *
     * @param list<TValue>|self<array-key, TValue> $values
     * @return bool
     */
    public function inAll(array|self $values): bool
    {
        foreach ($values as $value)
            if(!$this->in($value))
                return false;
        return true;
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param TKey $key
     * @return bool
     */
    public function remove(mixed $key): bool
    {
        if(!$this->contains($key))
            return false;
        unset($this->items[$key]);
        return true;
    }

    /**
     * Remove an items from the collection by keys.
     *
     * @param list<TKey>|self<array-key, TKey> $keys
     * @return bool
     */
    public function removeAll(array|self $keys): bool
    {
        $result = false;
        foreach ($keys as $key)
            $result = $this->remove($key);
        return $result;
    }

    /**
     * Remove an item from the collection by value.
     *
     * @param TValue $value
     * @param bool $strict
     * @return bool
     */
    public function removeItem(mixed $value, bool $strict = true): bool
    {
        $values = [$value];
        $result = false;
        foreach ($this->items as $key => $value)
        {
            if(!in_array($value, $values, $strict))
                continue;
            unset($this->items[$key]);
            $result = true;
        }
        return $result;
    }

    /**
     * Remove an items from the collection by values.
     *
     * @param list<TValue>|self<array-key, TValue> $values
     * @param bool $strict
     * @return bool
     */
    public function removeItemAll(array|self $values, bool $strict = true): bool
    {
        $result = false;
        foreach ($this->items as $key => $value)
        {
            if(!in_array($value, $values, $strict))
                continue;
            unset($this->items[$key]);
            $result = true;
        }
        return $result;
    }

    /**
     * Get all the items in the collection.
     *
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Remove all the items in the collection.
     *
     * @return static<TKey, TValue>
     */
    public function clear(): static
    {
        $this->items = [];
        return $this;
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() <= 0;
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Run a filter over each of the items.
     *
     * @param (callable(TValue, TKey): bool) $callback
     * @return static<TKey, TValue>
     */
    public function filter(callable $callback): static
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     * @param callable(TValue, TKey): TMapValue $callback
     * @return static<TKey, TMapValue>
     */
    public function map(callable $callback): static
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }

    /**
     * Get the first item from the collection.
     *
     * @template TValueDefault
     * @param TValueDefault|callable(): TValueDefault $default
     * @return TValue|TValueDefault
     */
    public function first(mixed $default = null): mixed
    {
        $key = array_key_first($this->items);
        if(is_null($key))
            return $default instanceof Closure ? $default() : $default;
        return $this->items[$key];
    }

    /**
     * Get the last item from the collection.
     *
     * @template TValueDefault
     * @param TValueDefault|callable(): TValueDefault $default
     * @return TValue|TValueDefault
     */
    public function last(mixed $default = null): mixed
    {
        $key = array_key_last($this->items);
        if(is_null($key))
            return $default instanceof Closure ? $default() : $default;
        return $this->items[$key];
    }

    /**
     * Get and remove the first item from the collection.
     *
     * @template TValueDefault
     * @param TValueDefault|callable(): TValueDefault $default
     * @return TValue|TValueDefault
     */
    public function shift(mixed $default = null): mixed
    {
        $item = array_shift($this->items);
        if(is_null($item))
            return $default instanceof Closure ? $default() : $default;
        return $item;
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @template TValueDefault
     * @param TValueDefault|callable(): TValueDefault $default
     * @return TValue|TValueDefault
     */
    public function pop(mixed $default = null): mixed
    {
        $item = array_pop($this->items);
        if(is_null($item))
            return $default instanceof Closure ? $default() : $default;
        return $item;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return self<array-key, TKey>
     */
    public function keys(): self
    {
        return new self(array_keys($this->items));
    }

    /**
     * Get the values of the collection items.
     *
     * @return self<array-key, TValue>
     */
    public function values(): self
    {
        return new self(array_values($this->items));
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param static<TKey, TValue>|array<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function merge(array|self $items): static
    {
        return new static(array_merge($this->items, is_array($items) ? $items : $items->all()));
    }

    /**
     * Slice the underlying collection array.
     *
     * @param int $offset
     * @param int|null $length
     * @return static<TKey, TValue>
     */
    public function slice(int $offset, int $length = null): static
    {
        return new static(array_slice($this->items, $offset, $length));
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param TKey $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param TKey $offset
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * Set the item at a given offset.
     *
     * @param TKey|null $offset
     * @param TValue $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset))
            $this->items[] = $value;
        else
            $this->items[$offset] = $value;
    }

    /**
     * Unset the item at a given offset.
     *
     * @param TKey $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}