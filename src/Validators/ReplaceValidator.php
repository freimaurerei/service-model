<?php

namespace Freimaurerei\ServiceModel\Validators;

class ReplaceValidator extends Validator
{
    public $map = [];

    public function validateValue($value)
    {
    }

    public function validateAttribute($model, $attribute)
    {
        if (isset($model->$attribute)) {
            $model->$attribute = strtr($model->$attribute, $this->map);
        }
    }
}