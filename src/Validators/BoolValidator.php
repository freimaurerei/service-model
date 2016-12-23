<?php

namespace Freimaurerei\ServiceModel\Validators;

class BoolValidator extends CastValidator
{
    protected function getType()
    {
        return 'bool';
    }
}