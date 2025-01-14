<?php

namespace Covaleski\Collection;

class Collection
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
     * Create a collection with values that pass the specified callback.
     */
    public function filter(callable $callback): static
    {
        $result = new static(
            is_array($this->values) ? $this->values : clone $this->values,
        );
        $this->walk(function ($value, $key) use ($callback, $result) {
            if (!call_user_func($callback, $value, $key)) {
                $result->unset($key);
            }
        });
        return $result;
    }

    /**
     * Create a collection containing this collection's keys.
     */
    public function keys(): static
    {
        $keys = [];
        foreach ($this->values as $key => $unused) $keys[] = $key;
        return new Collection($keys);
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
        $result = $this->values;
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
     * Get stored values as an array.
     */
    public function toArray(): array
    {
        return (array) $this->values;
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
     * Run the specified callback over each element.
     */
    public function walk(callable $callback): static
    {
        foreach ($this->values as $key => $value) {
            call_user_func($callback, $value, $key);
        }
        return $this;
    }
}
