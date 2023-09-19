# Легкие модели для laravel

## Установка

```php
composer require ricco381/laravel-lightweight-model
```

## Использование

### Создаем PostEntity для модели Post
```php
namespace App;

use Ricco381\LaravelLightweightModel\Entities\Entity;

/**
* Class PostEntity
 *
 * @method integer id();
 * @method integer column1();
 * @method string column2();
 * @method array column3();
 * .... другие поля которые в базе данных
 * 
 * @package App
 * @time 19.09.2023 16:38
 */
class PostEntity extends Entity
{

}
```

### Модель laravel теперь наследуем от LightweightModel и указываем нашу PostEntity

```php
namespace App;

use Ricco381\LaravelLightweightModel\Eloquent\LightweightModel;

class Post extends LightweightModel
{

     ...
     
     /**
      * @return string
      */
    public function getLightweightModelClass(): string
    {
        return PostEntity::class;
    }
}
```

### Получить коллекцию легких моделей
```php
$models = Post::query()
            ->toLightweightModel()
            ->get();
#или так

$models = Post::lightweight()->get();
```

## Использование связей

### Добавляем в класс PostEntity метод comment
```php

namespace App;

use Ricco381\LaravelLightweightModel\Entities\Entity;

...
class PostEntity extends Entity
{
    /**
     * @return CommentEntity
     */
    public function comment()
    {
        return $this->getRelations(__METHOD__, CommentEntity::class, function () {
            return Comment::lightweight()->where('post_id', $this->id())->first();
        });
    }
}
```
Если в запросе был использован **with(['comment'])** метот comment() класса PostEntity не будет выполнять запрос


### Создаем новый entity для модели Comment

```php
/**
* Class PostEntity
 *
 * @method integer id();
 * @method integer column1();
 * @method string column2();
 * @method array column3();
 * .... другие поля которые в базе данных
 * 
 * @package App
 * @time 19.09.2023 16:38
 */
class CommentEntity extends Entity 
{

}

```
### Теперь можно получить связь
```php
$post = Post::lightweight()->first();
$comment = $post->comment();
```