<?php

namespace Freimaurerei\ServiceModel\Traits;

use Freimaurerei\ServiceModel\Exception\ModelException;

trait GetModelPropertyElement
{
    protected function getPropertyElement($propertyName, $key)
    {
        $property = $this->$propertyName;

        if (!is_array($property)) {
            throw new ModelException(\Yii::t(
                'trait',
                'Property "{class}.{property}" must be an array.',
                ['{class}' => get_class($this), '{property}' => 'goodsOnSell']
            ));
        }

        if (!array_key_exists($key, $property)) {
            throw new ModelException(\Yii::t(
                'trait',
                'Property "{class}.{property}" does not contain key "{key}".',
                ['{class}' => get_class($this), '{property}' => $propertyName, '{key}' => $key]
            ));
        }

        return $property[$key];
    }
}