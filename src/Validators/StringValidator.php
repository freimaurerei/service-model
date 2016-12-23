<?php

namespace Freimaurerei\ServiceModel\Validators;

class StringValidator extends CastValidator
{
    protected function getType()
    {
        return 'string';
    }
}