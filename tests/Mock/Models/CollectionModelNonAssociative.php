<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\ArrayCollection;

class CollectionModelNonAssociative extends ArrayCollection
{
    public $isAssociative = false;

    protected function getObjectClassName()
    {
        return RelatedModel::class;
    }
}