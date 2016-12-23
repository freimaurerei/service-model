<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\ArrayCollection;

class CollectionOfCollectionsModel extends ArrayCollection
{
    protected function getObjectClassName()
    {
        return CollectionModel::class;
    }
}