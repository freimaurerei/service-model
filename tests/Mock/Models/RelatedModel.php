<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\Model as BaseModel;
use Freimaurerei\ServiceModel\Validators\CastValidator;

class RelatedModel extends BaseModel
{
    public $field;

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['field', CastValidator::CAST_BOOL, 'allowEmpty' => false, 'skipOnEmpty' => false],
            ]
        );
    }
}