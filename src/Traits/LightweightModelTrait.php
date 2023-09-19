<?php

namespace Ricco381\LaravelLightweightModel\Traits;

use Ricco381\LaravelLightweightModel\Builders\Builder;

/**
 * Class LightweightModelTrait
 *
 *
 * @author ricco381
 * @package Ricco381\LaravelLightweightModel\Traits
 * @time 19.09.2023 12:42
 */
trait LightweightModelTrait
{

    /**
     * @return Builder
     */
    public static function lightweight(): Builder
    {
        return (new static())->newQuery()->toLightweightModel();
    }

    /**
     * @param $query
     *
     * @return Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * @return string
     */
    abstract public function getLightweightModelClass(): string;
}