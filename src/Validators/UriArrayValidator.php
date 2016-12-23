<?php

namespace Freimaurerei\ServiceModel\Validators;

class UriArrayValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (isset($model->$attribute)) {
            if (!is_array($model->$attribute)) {
                $model->$attribute = explode(',', $model->$attribute);
            }
        }
    }
}