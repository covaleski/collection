<?php

namespace Covaleski\Collection;

use Countable;
use stdClass;

class Collection implements Countable
{
    /**
     * Create the collection instance.
     */
    public function __construct(protected array|object $values)
    {
    }

    /**
     * Create a collection with all the values from the specified column.
     */
    public function column(string $key): static
    {
        return $this->map(fn (mixed $item): mixed => match (true) {
            is_array($item) => $item[$key],
            is_object($item) => $item->$key,
        });
    }

    /**
     * Get the current length.
     */
    public function count(): int
    {
        if (is_countable($this->values)) {
            return count($this->values);
        } else {
            $count = 0;
            foreach ($this->values as $value) $count++;
            return $count;
        }
    }

    /**
     * Create a collection with values that pass the specified callback.
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
     * Access the first stored value or the one at the specified key.
     */
    public function get(null|string $key = null): mixed
    {
        if ($key === null) {
            $data = (array) $this->values;
            return reset($data);
        } elseif (is_array($this->values)) {
            return $this->values[$key];
        } else {
            return $this->values->$key;
        }
    }

    /**
     * Create a collection containing this collection's keys.
     */
    public function keys(): static
    {
        return $this->map(fn ($_, $k) => $k);
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
     * Create a collection merging contents from other collections.
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

    // /**
    //  * Create a collection from a section of the current values.
    //  */
    // public function slice(int $offset = 0, null|int $length = null): static
    // {
    //     $result = $this->clone();
    //     return new static($result);
    // }

    /**
     * Get stored values as an array.
     */
    public function toArray(): array
    {
        return (array) $this->values;
    }

    /**
     * Get stored values as an object.
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
     * Create a collection containing this collection's values.
     */
    public function values(): static
    {
        return $this->map(fn ($v) => $v);
    }

    /**
     * Run the specified callback over each element.
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

    /**
     * Create an empty set of data.
     */
    protected function create(): array|object
    {
        return is_array($this->values) ? [] : new stdClass;
    }
}
