<?php

namespace Covaleski\Collection;

use Countable;

class Collection implements Countable
{
    /**
     * Create the collection instance.
     */
    public function __construct(protected array|object $values)
    {
    }

    /**
     * Get all stored values.
     */
    public function all(): array|object
    {
        return $this->values;
    }

    /**
     * Create a collection with values from each element at the specified key.
     */
    public function column(string $key): static
    {
        return $this->map(fn (mixed $item): mixed => match (true) {
            is_array($item) => $item[$key],
            is_object($item) => $item->$key,
        });
    }

    /**
     * Count stored values.
     */
    public function count(): int
    {
        if (is_countable($this->values)) {
            return count($this->values);
        } else {
            $count = 0;
            foreach ($this->values as $unused) $count++;
            return $count;
        }
    }

    /**
     * Create a collection with the values that pass the specified callback.
     */
    public function filter(callable $callback): static
    {
        $result = new static($this->clone());
        $this->walk(function ($value, $key) use ($callback, $result) {
            if (!call_user_func($callback, $value, $key)) {
                $result->unset($key);
            }
        });
        return $result;
    }

    /**
     * Get the first stored value.
     */
    public function first(): mixed
    {
        return $this->nth(0);
    }

    /**
     * Access the value at the specified key.
     */
    public function get(string $key): mixed
    {
        return is_array($this->values)
            ? $this->values[$key]
            : $this->values->$key;
    }

    /**
     * Create a collection containing all stored keys.
     */
    public function keys(): static
    {
        return $this->map(fn ($_, $k) => $k);
    }

    /**
     * Get the last stored value.
     */
    public function last(): mixed
    {
        return $this->nth(-1);
    }

    /**
     * Create a collection with the results of the specified callback.
     */
    public function map(callable $callback): static
    {
        $result = [];
        foreach ($this->values as $key => $value) {
            $result[] = call_user_func($callback, $value, $key);
        }
        return new static($result);
    }

    /**
     * Create a collection merging contents from multiple collections.
     */
    public function merge(Collection ...$collections): static
    {
        $result = $this->clone();
        foreach ($collections as $collection) {
            $collection->walk(function ($value, $key) use (&$result) {
                if (is_array($result)) {
                    if (is_int($key)) {
                        $result[] = $value;
                    } else {
                        $result[$key] = $value;
                    }
                } else {
                    $result->$key = $value;
                }
            });
        }
        return new static($result);
    }

    /**
     * Access the value at the specified position.
     */
    public function nth(int $position): mixed
    {
        $index = $position < 0 ? $this->count() + $position : $position;
        $key = $this->keys()->get($index);
        return $this->get($key);
    }

    /**
     * Create a collection from a part of the current values.
     */
    public function slice(int $offset = 0, null|int $length = null): static
    {
        $result = new static($this->clone());
        $keys = $this->keys()->toArray();
        $count = $this->count();
        $start = $offset < 0 ? $count + $offset : $offset;
        $end = match (true) {
            $length === null => $count - 1,
            $length < 0 => $count + $length - 1,
            default => $start + $length - 1,
        };
        for ($i = 0; $i < min($start, $count); $i++) {
            $result->unset($keys[$i]);
        }
        for ($i = max(0, $end + 1); $i < $count; $i++) {
            $result->unset($keys[$i]);
        }
        return $result;
    }

    /**
     * Cast stored values as an array.
     */
    public function toArray(): array
    {
        return (array) $this->values;
    }

    /**
     * Cast stored values as an object.
     */
    public function toObject(): object
    {
        return (object) $this->values;
    }

    /**
     * Remove the value at the specified key.
     */
    public function unset(int|string $key): static
    {
        if (is_array($this->values)) {
            unset($this->values[$key]);
        } else {
            unset($this->values->$key);
        }
        return $this;
    }

    /**
     * Create a collection containing all stored keys - discard keys.
     */
    public function values(): static
    {
        return $this->map(fn ($v) => $v);
    }

    /**
     * Run the specified callback over each stored value.
     */
    public function walk(callable $callback): static
    {
        foreach ($this->values as $key => $value) {
            call_user_func($callback, $value, $key);
        }
        return $this;
    }

    /**
     * Copy current data.
     */
    protected function clone(): array|object
    {
        return is_array($this->values) ? $this->values : clone $this->values;
    }
}
