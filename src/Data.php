<?php

namespace MenyongMenying\MLibrary\Kucil\Utilities\Data;

use ArrayAccess;
use AllowDynamicProperties;

/**
 * @author MenyongMenying <menyongmenying.main@email.com>
 * @version 0.0.1
 * @date 2025-07-30
 */
#[AllowDynamicProperties]
final class Data implements ArrayAccess
{
    public function __construct(array|object $data = [], bool $recursive = false, bool $isFirstLayer = true)
    {
        $reserveIndex = 1;
        foreach ((array) $data as $key => $value) {
            $isValidKey = $this->isValidKey($key);
            if (!$isValidKey && !$isFirstLayer) {
                continue;
            }
            if (!$isValidKey) {
                $key = 'index' . $reserveIndex++;
            }
            if ($recursive && (is_array($value) || is_object($value))) {
                $value = $this->wrapRecursive($value);
            }
            $this->{$key} = $value;
        }
        return;
    }

    private function isAssocWithValidKey(array $arr) :bool
    {
        foreach ($arr as $k => $_) {
            if (!$this->isValidKey($k)) {
                return false;
            }
        }
        return true;
    }

    public function __get(string $key) :mixed
    {
        return $this->{$key} ?? null;
    }

    public function __set(string $key, mixed $value) :void
    {
        $this->{$key} = $value;
        return;
    }

    public function __isset(string $key) :bool
    {
        return isset($this->{$key});
    }

    public function __unset(string $key) :void
    {
        unset($this->{$key});
        return;
    }

    public function offsetExists(mixed $offset) :bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet(mixed $offset) :mixed
    {
        return $this->{$offset} ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value) :void
    {
        $this->{$offset} = $value;
        return;
    }

    public function offsetUnset(mixed $offset) :void
    {
        unset($this->{$offset});
        return;
    }

    public function toArray() :array
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof self) {
                $result[$key] = $value->toArray();
                continue;
            }
            if (is_array($value)) {
                $result[$key] = $this->deepArrayConvert($value);
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }

    private function wrapRecursive(array|object $value) :mixed
    {
        if (is_array($value)) {
            if ($this->isAssocWithValidKey($value)) {
                return new self($value, true, false);
            }
            foreach ($value as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $value[$k] = $this->wrapRecursive($v);
                }
            }
            return $value;
        }
        if (is_object($value)) {
            $array = (array) $value;
            if ($this->isAssocWithValidKey($array)) {
                return new self($array, true, false);
            }
            foreach ($array as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $array[$k] = $this->wrapRecursive($v);
                }
            }
            return $array;
        }
        return $value;
    }

    private function isValidKey(mixed $key) :bool
    {
        return is_string($key) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key);
    }

    private function deepArrayConvert(array $arr) :array
    {
        foreach ($arr as $k => $v) {
            if ($v instanceof self) {
                $arr[$k] = $v->toArray();
                continue;
            }
            if (is_array($v)) {
                $arr[$k] = $this->deepArrayConvert($v);
            }
        }
        return $arr;
    }
}