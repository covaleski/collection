<?php

namespace Covaleski\Collection;

use ArrayAccess;
use Countable;
use Iterator;

class Collection implements ArrayAccess, Countable, Iterator
{
    /**
     * Current iterator key.
     */
    protected int $key;

    /**
     * Cached iterator keys.
     */
    protected array $keys;

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
    public function column(
        null|int|string $value_key = null,
        null|int|string $index_key = null,
    ): static {
        $result = [];
        foreach ($this->values as $i => $item) {
            $index = $index_key === null ? count($result) : match (true) {
                is_array($item) => $item[$index_key],
                is_object($item) => $item->$index_key,
            };
            $value = $value_key === null ? $item : match (true) {
                is_array($item) => $item[$value_key],
                is_object($item) => $item->$value_key,
            };
            $result[$index] = $value;
        }
        return new static($result);
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
     * Return the current element.
     */
    public function current(): mixed
    {
        return is_array($this->values)
            ? $this->values[$this->keys[$this->key]]
            : $this->values->{$this->keys[$this->key]};
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
        foreach ($this as $key => $value) {
            if (!call_user_func($callback, $value, $key)) {
                $result->unset($key);
            }
        }
        return $result;
    }

    /**
     * Return the first value that passes the specified callback.
     */
    public function find(callable $callback, mixed $default = null): mixed
    {
        foreach ($this->values as $value) {
            if ($callback($value)) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Get the first stored value.
     */
    public function first(mixed $default = null): mixed
    {
        return $this->nth(0, $default);
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
     * Return the key of the current element.
     */
    public function key(): mixed
    {
        return $this->keys[$this->key] ?? null;
    }

    /**
     * Create a collection containing all stored keys.
     */
    public function keys(): static
    {
        return new static($this->getKeys());
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
            foreach ($collection as $key => $value) {
                if (is_array($result)) {
                    if (is_int($key)) {
                        $result[] = $value;
                    } else {
                        $result[$key] = $value;
                    }
                } else {
                    $result->$key = $value;
                }
            }
        }
        return new static($result);
    }

    /**
     * Advance the internal pointer of an array.
     */
    public function next(): void
    {
        $this->key++;
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
        if (is_array($this->values)) {
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
     * Rewind the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->keys = $this->getKeys();
        $this->key = 0;
    }

    /**
     * Return the first that matches the specified value.
     */
    public function search(mixed $value, bool $strict = false): null|int|string
    {
        foreach ($this->values as $key => $candidate) {
            if ($strict ? $candidate === $value : $candidate == $value) {
                return $key;
            }
        }
        return null;
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
            $index = 0;
            foreach ($this->getKeys() as $i => $key) {
                $value = $this->get($key);
                $this->unset($key);
                if ($i > 0) {
                    $new_key = ctype_digit($key) ? $index++ : $key;
                    $this->set($new_key, $value);
                } else {
                    $first = $value;
                }
            }
            return $first ?? null;
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
     * Sort stored values.
     */
    public function sort(null|callable $callback = null): static
    {
        if (is_array($this->values)) {
            if ($callback === null) {
                asort($this->values);
            } else {
                uasort($this->values, $callback);
            }
        } else {
            $values = $this->toArray();
            if ($callback === null) {
                asort($values);
            } else {
                uasort($values, $callback);
            }
            foreach ($values as $key => $value) {
                $this->unset($key)->set($key, $value);
            }
        }
        return $this;
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
     * Return only values that 
     */
    public function unique(bool $strict = false): static
    {
        return $this->filter(function ($value, $key) use ($strict) {
            return $this->search($value, $strict) === $key;
        });
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
            foreach ($this->getKeys() as $key) {}
            $unset_keys = [];
            $set_values = $values;
            foreach ($this as $key => $value) {
                $unset_keys[] = $key;
                if (ctype_digit($key)) {
                    $set_values[] = $value;
                } else {
                    $set_values[$key] = $value;
                }
            }
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
     * Checks if current position is valid.
     */
    public function valid(): bool
    {
        return array_key_exists($this->key, $this->keys);
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
     * Create a collection with values that pass the specified column filter.
     */
    public function where(string $key, string $operator, mixed $value): static
    {
        $callback = match ($operator) {
            '=' => fn ($v) => $v === $value,
            '!=' => fn ($v) => $v !== $value,
            '>' => fn ($v) => $v > $value,
            '>=' => fn ($v) => $v >= $value,
            '<' => fn ($v) => $v < $value,
            '<=' => fn ($v) => $v <= $value,
            '^=' => fn ($v) => str_starts_with(strval($v), strval($value)),
            '$=' => fn ($v) => str_ends_with(strval($v), strval($value)),
            '*=' => fn ($v) => str_contains(strval($v), strval($value)),
        };
        return $this->filter(function ($item) use ($key, $callback) {
            $subject = match (true) {
                is_array($item) => $item[$key],
                is_object($item) => $item->$key,
            };
            return call_user_func($callback, $subject);
        });
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
        $keys = $this->getKeys();
        return array_key_exists($index, $keys) ? $keys[$index] : null;
    }

    /**
     * Get all keys.
     */
    protected function getKeys(): array
    {
        return is_array($this->values)
            ? array_keys($this->values)
            : array_map('strval', array_keys(get_object_vars($this->values)));
    }
}
