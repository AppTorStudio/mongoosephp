<?php

namespace Mongoose;

use Error;

class Models
{

    public static $models = [];

    public static function addModel($name, $fields, $options = [])
    {
        self::$models[$name] = [
            "name"    => $name,
            "fields"  => $fields,
            "options" => $options,
        ];
    }

    public static function model($name): Model
    {
        if (isset(self::$models[$name])) {
            return new Model($name, self::$models[$name]['fields'], self::$models[$name]['options']);
        }
    }

    public static function __callStatic($name, $arguments): Model
    {
        if (isset(self::$models[$name])) {

            $model = new Model($name, self::$models[$name]['fields'], self::$models[$name]['options']);

            if (isset($arguments[0]) && is_array($arguments[0])) {
                foreach ($arguments[0] as $key => $value) {
                    $model->$key = $value;
                }
            }

            return $model;
        } else {
            throw new Error("Model $name not found");
        }
    }

}