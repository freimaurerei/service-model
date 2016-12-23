<?php

namespace Freimaurerei\ServiceModel\Validators;

class IntValidator extends CastValidator
{
    protected function getType()
    {
        return 'int';
    }
}