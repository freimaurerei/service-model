<?php

namespace Freimaurerei\ServiceModel\Converter;

use Freimaurerei\ServiceModel\Model;

class JSONConverter extends Converter
{
    /**
     * Конвертация объекта в JSON.
     *
     * @param Model $data
     *
     * @return string
     */
    protected function exportInternal(Model $data)
    {
        return $this->exportArray($data->getAttributes());
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function exportArray(array $data)
    {
        return json_encode($data);
    }

    public function toArray($string)
    {
        return json_decode($string, true);
    }
}