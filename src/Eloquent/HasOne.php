<?php

namespace Ricco381\LaravelLightweightModel\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class HasOne
 *
 * @todo sarv Дополнить описание класса
 *
 * @author ricco381
 * @package Ricco381\LaravelLightweightModel\Eloquent
 * @time 19.09.2023 15:50
 */
class HasOne extends \Illuminate\Database\Eloquent\Relations\HasOne
{
    /**
     * @param $parent
     *
     * @return Model|null
     */
    protected function getDefaultFor($parent)
    {
        return null;
    }
}