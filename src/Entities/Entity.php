<?php

namespace Ricco381\LaravelLightweightModel\Entities;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;

/**
 * Class Entity
 *
 * @method static $relation
 *
 * @author ricco381
 * @package App\Ship\Abstracts\Entities
 * @time 20.07.2023 13:19
 */
class Entity implements ArrayAccess, Arrayable, JsonSerializable
{
    use ArrayAccessTrait;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var array
     */
    private array $relations = [];

    /**
     * @var array
     */
    private array $types = [];

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->parseMethodTypes();
        $this->setData($data);
    }

    /**
     * @param  array  $models
     *
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * @param $attributes
     *
     * @return $this
     */
    public function newFromBuilder($attributes): static
    {
        return new static($attributes);
    }

    /**
     * @param $name
     * @param $models
     *
     * @return void
     */
    public function setRelation($name, $models)
    {
        $this->relations[$name] = $models;
    }

    /**
     * @param $data
     *
     * @return void
     */
    protected function setData($data): void
    {
        if ($data instanceof Model) {
            $this->fillFromModel($data);
        } else {
            $this->fillFromArray((array)$data);
        }
    }

    /**
     * @param  Model  $model
     *
     * @return void
     */
    protected function fillFromModel(Model $model): void
    {
        foreach ($model->getAttributes() as $name => $value) {
            $this->__set($name, $value);
        }

        $setRelation = function ($model, $relations) use (&$setRelation) {
            foreach ($model->getRelations() as $name => $relation) {
                if (is_null($relation)) {
                    continue;
                }

                $relations[$name] = $this->convertToType($name, $relation->getAttributes());

                if ($relation->getRelations()) {
                    $setRelation($relation, $relations[$name]);
                }
            }

            return $relations;
        };

        Arr::map($setRelation($model, []), function ($relation, $name) {
            $this->__set($name, $relation);
        });
    }

    /**
     * @param  array  $data
     *
     * @return void
     */
    protected function fillFromArray(array $data): void
    {
        $new = array_fill_keys(array_keys($this->types), null);

        foreach (array_merge($new, $data) as $name => $value) {
            $this->__set($name, $value);
        }


    }

    /**
     * @param  string  $name
     * @param $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $normalizeName = $this->normalizationName($name);

        $data = $this->convertToType($normalizeName, $value);

        if (method_exists($this, $name)) {
            $this->relations[$name] = $data;
        } else {
            $this->data[$normalizeName] = $data;
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        return $this->__get($key);
    }

    /**
     * @param  string  $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @param  string  $name
     * @param  array  $arguments
     *
     * @return mixed|void|null
     */
    public function __call(string $name, array $arguments)
    {
        $item = $this->normalizationName($name);

        if (isset($this->$item)) {
            $data = $this->$item;

            if (!empty($arguments)) {
                $key = array_shift($arguments);
                $default = array_shift($arguments);

                return Arr::get($data, $key, $default);
            }

            return $data;
        }

        return null;
    }

    /**
     * @param  string  $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]) || (!empty($this->data[$name]) && is_null($this->data[$name]));
    }

    /**
     * @return void
     */
    private function parseMethodTypes(): void
    {
        $class = new \ReflectionClass($this);
        $doc = $class->getDocComment();

        preg_match_all('/@method\s+(\S+)\s+(\w+)/', $doc, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $types = explode('|', $match[1]);
            $this->types[$this->normalizationName($match[2])] = Arr::first($types);
        }
    }

    /**
     * @param $name
     *
     * @return string
     */
    private function normalizationName($name): string
    {
        return Str::snake($name, '_');
    }

    /**
     * @param $name
     * @param $value
     *
     * @return mixed
     */
    private function convertToType($name, $value): mixed
    {
        $type = $this->getType($name);

        return match ($type) {
            'bool', 'boolean' => (boolean)$value,
            'integer', 'int'  => (int)$value,
            'array'           => $this->convertJsonToArray($value),
            default           => $value,
        };
    }

    /**
     * @param $data
     *
     * @return array|mixed
     */
    private function convertJsonToArray($data)
    {
        if (!is_null($data) && !is_array($data)) {
            return json_decode($data, true);
        }

        return is_array($data) ? $data : Arr::wrap($data);
    }

    /**
     * @param $name
     *
     * @return string
     */
    private function getType($name): string
    {
        return $this->types[$this->normalizationName($name)] ?? 'none';
    }

    /**
     * @param  mixed  $data
     *
     * @return static
     */
    public static function make(mixed $data)
    {
        if ($data instanceof self) {
            return $data;
        }

        return new static($data);
    }

    /**
     * @param $name
     * @param $entity
     * @param  \Closure  $closure
     *
     * @return static
     */
    protected function getRelations($name, $entity, \Closure $closure): mixed
    {
        $name = Str::afterLast($name, '::');

        if (!isset($this->relations[$name])) {
            $data = $this->relations[$name] = call_user_func($closure);
        } else {
            $data = $this->relations[$name];
        }

        if ($data instanceof Collection) {
            return collect($data)->transform(fn($item) => $entity::make($item));
        }

        return $entity::make($data);
    }
}