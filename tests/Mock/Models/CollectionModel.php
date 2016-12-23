<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\ArrayCollection;

class CollectionModel extends ArrayCollection
{
    protected function getObjectClassName()
    {
        return RelatedModel::class;
    }
}