<?php

namespace Covaleski\Collection;

use ArrayAccess;
use Countable;

class Collection implements ArrayAccess, Countable
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
     * Assign the specified value to the specified key in all stored values.
     */
    public function assign(int|string $key, mixed $value): static
    {
        $this->walk(function (mixed &$item) use ($key, $value) {
            if (is_array($item)) {
                $item[$key] = $value;
            } elseif (is_object($item)) {
                $item->$key = $value;
            }
        });
        return $this;
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
     * Create a collection with a copy of the stored values.
     */
    public function copy(): static
    {
        return new static($this->clone());
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
            foreach ($this->values as $unused) {
                $count++;
            }
            return $count;
        }
    }

    /**
     * Remove the specified key from all stored values.
     */
    public function drop(int|string $key): static
    {
        $this->walk(function (mixed &$item) use ($key) {
            if (is_array($item)) {
                unset($item[$key]);
            } elseif (is_object($item)) {
                unset($item->$key);
            }
        });
        return $this;
    }

    /**
     * Create a collection with the values that pass the specified callback.
     */
    public function filter(callable $callback): static
    {
        $result = $this->copy();
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
    public function first(mixed $default = null): mixed
    {
        return $this->nth(0);
    }

    /**
     * Access the value at the specified key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->isset($key)) {
            return is_array($this->values)
                ? $this->values[$key]
                : $this->values->$key;
        } else {
            return $default;
        }
    }

    /**
     * Check whether a value exists at the specified key.
     */
    public function isset(int|string $key): bool
    {
        return is_array($this->values)
            ? array_key_exists($key, $this->values)
            : property_exists($this->values, $key);
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
    public function last(mixed $default = null): mixed
    {
        return $this->nth(-1, $default);
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
    public function nth(int $position, mixed $default = null): mixed
    {
        $key = $this->getKey($position);
        return $key === null ? $default : $this->get($key);
    }

    /**
     * Check whether an offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->isset($offset);
    }

    /**
     * Access the value at the specified offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the specified offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Remove the value at the specified offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->unset($offset);
    }

    /**
     * Remove and return the last stored value.
     */
    public function pop(): mixed
    {
        if (is_array($this->values())) {
            return array_pop($this->values);
        } else {
            $key = $this->getKey(-1);
            if ($key === null) {
                return null;
            } else {
                $value = $this->get($key);
                $this->unset($key);
                return $value;
            }
        }
    }

    /**
     * Store a value at the end of the collection.
     */
    public function push(mixed ...$values): static
    {
        if (is_array($this->values)) {
            array_push($this->values, ...$values);
        } else {
            $start = (int) $this->keys()
                ->filter(fn ($k) => ctype_digit($k))
                ->last(-1) + 1;
            foreach ($values as $i => $value) {
                $this->values->{$start + $i} = $value;
            }
        }
        return $this;
    }

    /**
     * Set the value at the specified key.
     */
    public function set(int|string $key, mixed $value): static
    {
        if (is_array($this->values)) {
            $this->values[$key] = $value;
        } else {
            $this->values->$key = $value;
        }
        return $this;
    }

    /**
     * Remove the first value of the collection.
     */
    public function shift(): mixed
    {
        if (is_array($this->values)) {
            return array_shift($this->values);
        } else {
            $first_key = $this->getKey(0);
            $first_value = $this->get($first_key);
            $this->unset($first_key);
            $unset_keys = [];
            $set_values = [];
            $this->walk(function ($value, $key) use (&$unset_keys, &$set_values) {
                $unset_keys[] = $key;
                if (ctype_digit($key)) {
                    $set_values[] = $value;
                } else {
                    $set_values[$key] = $value;
                }
            });
            foreach ($unset_keys as $key) {
                $this->unset($key);
            }
            foreach ($set_values as $key => $value) {
                $this->set($key, $value);
            }
            return $first_value;
        }
    }

    /**
     * Create a collection from a part of the current values.
     */
    public function slice(int $offset = 0, null|int $length = null): static
    {
        $result = $this->copy();
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
     * Add a value to the beginning of the collection.
     */
    public function unshift(mixed ...$values): static
    {
        if (is_array($this->values)) {
            array_unshift($this->values, ...$values);
        } else {
            $unset_keys = [];
            $set_values = $values;
            $this->walk(function ($value, $key) use (&$unset_keys, &$set_values) {
                $unset_keys[] = $key;
                if (ctype_digit($key)) {
                    $set_values[] = $value;
                } else {
                    $set_values[$key] = $value;
                }
            });
            foreach ($unset_keys as $key) {
                $this->unset($key);
            }
            foreach ($set_values as $key => $value) {
                $this->set($key, $value);
            }
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
        foreach ($this->values as $key => &$value) {
            $callback($value, $key);
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
     * Get the key at the specified position.
     */
    protected function getKey(int $position): null|int|string
    {
        $index = $position < 0 ? $this->count() + $position : $position;
        $keys = $this->keys();
        return $keys->isset($index) ? $keys->get($index) : null;
    }
}
