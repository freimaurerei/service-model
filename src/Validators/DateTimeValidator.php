<?php

namespace Freimaurerei\ServiceModel\Validators;

class DateTimeValidator extends Validator
{
    public $format = 'Y-m-d';

    public function validateValue($value)
    {
    }

    public function validateAttribute($model, $attribute)
    {
        if (isset($model->$attribute)) {
            $model->$attribute = date($this->format, strtotime($model->$attribute));
        }
    }
}