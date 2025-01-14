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
     * Create a collection containing values that pass the specified callback.
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
     * Run the specified callback over each element and return the results.
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
     * Get the current values as an array.
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
