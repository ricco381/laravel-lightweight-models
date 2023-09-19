<?php

namespace Ricco381\LaravelLightweightModel\Entities;

use Illuminate\Support\Arr;

/**
 * Class ArrayAccessTrait
 *
 * @todo sarv Дополнить описание класса
 *
 * @author ricco381
 * @package App\Ship\Abstracts\Entities
 * @time 07.09.2023 19:18
 */
trait ArrayAccessTrait
{

    /**
     * @param  mixed  $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param  mixed  $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    /**
     * @param  mixed  $offset
     * @param  mixed  $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * @param  mixed  $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @return bool
     */
    public function notEmpty(): bool
    {
        return !empty($this->data);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param  array  $except
     *
     * @return array
     */
    public function toArray(array $except = []): array
    {
        return Arr::except($this->data, $except);
    }

}