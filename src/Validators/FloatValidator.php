<?php

namespace Freimaurerei\ServiceModel\Validators;

class FloatValidator extends CastValidator
{
    protected function getType()
    {
        return 'float';
    }
}