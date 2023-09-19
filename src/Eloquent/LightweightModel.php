<?php

namespace Ricco381\LaravelLightweightModel\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ricco381\LaravelLightweightModel\Traits\LightweightModelTrait;

/**
 * Class LightweightModel
 *
 *
 * @method \Ricco381\LaravelLightweightModel\Builders\Builder toLightweightModel()
 *
 * @author ricco381
 * @package Ricco381\LaravelLightweightModel\Models
 * @time 19.09.2023 15:48
 */
abstract class LightweightModel extends Model
{
    use LightweightModelTrait;

    protected function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasOne($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Begin querying the model.
     *
     * @return \Ricco381\LaravelLightweightModel\Builders\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }
}