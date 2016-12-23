<?php

namespace Freimaurerei\ServiceModel\Validators;

abstract class CastValidator extends Validator
{
    const CAST_BOOL = BoolValidator::class;
    const CAST_INT = IntValidator::class;
    const CAST_FLOAT = FloatValidator::class;
    const CAST_STRING = StringValidator::class;
    const CAST_ARRAY = ArrayValidator::class;

    public $allowEmpty = true;

    /**
     * @return string
     */
    abstract protected function getType();

    public function validateValue($value)
    {
        return null;
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!$this->allowEmpty && $this->isEmpty($value)) {
            $this->addError(
                $model,
                $attribute,
                $this->message !== null ? $this->message : \Yii::t('yii', '{attribute} cannot be blank.')
            );
        } elseif (isset($value)) {
            settype($model->$attribute, $this->getType());
        }
    }
}