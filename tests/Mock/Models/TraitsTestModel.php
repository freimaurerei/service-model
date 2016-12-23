<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\Model as BaseModel;
use Freimaurerei\ServiceModel\Traits\GetModelPropertyElement;

class TraitsTestModel extends BaseModel
{
    use GetModelPropertyElement;

    public $field;

    public function getFieldElement($element)
    {
        return $this->getPropertyElement('field', $element);
    }
}