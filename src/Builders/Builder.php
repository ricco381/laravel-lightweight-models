<?php

namespace Ricco381\LaravelLightweightModel\Builders;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Model;
use Ricco381\LaravelLightweightModel\Entities\Entity;
use Ricco381\LaravelLightweightModel\Traits\LightweightModelTrait;

/**
 * Class Builder
 *
 * @todo sarv Дополнить описание класса
 *
 * @author ricco381
 * @package Ricco381\LaravelLightweightModel\Builders
 * @time 19.09.2023 11:57
 *
 * @property  LightweightModelTrait $model
 */
class Builder extends BaseBuilder
{
    /**
     * @var bool
     */
    private bool $isLightweightModel = false;

    /**
     * @return static
     */
    public function toLightweightModel(): static
    {
        $this->isLightweightModel = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLightweightModel(): bool
    {
        return $this->isLightweightModel;
    }

    /**
     * @param $columns
     *
     * @return Model[]|Builder[]
     */
    public function getModels($columns = ['*'])
    {
        return $this->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }

    /**
     * @param  array  $items
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(array_map(function ($item) use ($items, $instance) {
            $model = $instance->newFromBuilder($item);

            if (count($items) > 1) {
                $model->preventsLazyLoading = Model::preventsLazyLoading();
            }

            return $model;
        }, $items));
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = ['*'])
    {
        $builder = $this->applyScopes();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }

   public function getRelation($name)
   {
       $relation = parent::getRelation($name);

       if ($this->isLightweightModel()) {
           $relation->toLightweightModel();
       }

       return $relation;
   }

    /**
     * Create a new instance of the model being queried.
     *
     * @param  array  $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function newModelInstance($attributes = [])
    {
        if (!$this->isLightweightModel()) {
            return parent::newModelInstance($attributes);
        }

        if (!method_exists($this->model, 'getLightweightModelClass')) {
            $model = Entity::class;
        } else {
            $model = $this->model->getLightweightModelClass();
        }

        return new $model($attributes);
    }
}