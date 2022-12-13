<?php

namespace BestSnipp\Eden\Traits;

use BestSnipp\Eden\Components\Fields\Field;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait WithModel
{

    private string $DRIVER_ELOQUENT = 'eloquent';
    private string $DRIVER_DATABASE = 'database';
    private string $DRIVER_MODEL = 'model';
    private string $DRIVER_ARRAY = 'array';
    private string $DRIVER_OBJECT = 'object';

    /**
     * @var string|array|Model
     */
    public static $model = null;

    /**
     * @var string|array|Model
     */
    protected $record = null;

    /**
     * @var 'model_instance'|'object'|'array'|'model_class'|'table'
     */
    private $modelType = 'model_instance';

    /**
     * @return string|array|Model|null
     */
    protected function model()
    {
        return get_class($this)::$model;
    }

    /**
     * @return string|array|Model|null
     */
    protected function record()
    {
        return $this->record;
    }

    /**
     * @return string|array|Model|null
     */
    protected function resolveRecord()
    {
        if ($this->resourceId) {
            $record = app($this->model())->find($this->resourceId);
            abort_if(is_null($record), 404);
            $this->record = $record;
        }
    }

    protected function resolveModel()
    {
        $model = $this->model();
        if (is_null($model)) {
            return;
        }

        if (is_array($model)) {
            $this->modelType = $this->DRIVER_ARRAY;
            self::$model = $model;

        } else if ($model instanceof Model) {
            $this->modelType = $this->DRIVER_MODEL;
            self::$model = $model;

        } else if (is_object($model)) {
            $this->modelType = $this->DRIVER_OBJECT;
            self::$model = $model;

        } else if (is_string($model)) {
            try {
                app($model);
                $this->modelType = $this->DRIVER_ELOQUENT;
                self::$model = $model;

            } catch (BindingResolutionException $exception) {
                $this->modelType = $this->DRIVER_DATABASE;
                self::$model = $model;
            }
        } else {
            throw new \Exception(sprintf('"Only Array, String and %s accepted as Model', Model::class));
        }
    }

    protected function getRecordValue($key, $value = null)
    {
        if (!is_null($this->record)) {
            if (is_subclass_of($this->record, Model::class) && Arr::exists($this->record, $key)) {
                $value = Arr::get($this->record, $key) ?? $value;

            } else if (in_array($this->record, ['array']) && Arr::exists($this->record, $key)) {
                $value = Arr::get($this->record, $key) ?? $value;

            } else if(in_array($this->record, [get_class($this->record)]) && Arr::exists($this->record, $key)){
                $value = $this->record->$key ?? $value;

            } else if (in_array($this->record, ['object']) && property_exists($this->record, $key)) {
                $value = $this->record->$key ?? $value;
            }
        }

        return $value;
    }

    public function getRecordIdentifier($record = null)
    {
        if ($record instanceof Model) {
            return base64_encode($record->{$record->getKeyName()});
        }

        return (is_null($record)) ? base64_encode(Str::ulid()) : '';
    }

}
