<?php

namespace Freimaurerei\ServiceModel\Converter;

use Freimaurerei\ServiceModel\Model;
use yii\web\HttpException;

class UriParamsConverter extends Converter
{
    /**
     * Конвертация объекта в JSON.
     *
     * @param Model $data
     *
     * @return string
     * @throws HttpException
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
        return http_build_query($data);
    }

    public function toArray($string)
    {
        $string = parse_url($string, PHP_URL_QUERY); // TODO переписать
        $string = html_entity_decode($string);
        $string = explode('&', $string);
        $result = [];

        foreach ($string as $val) {
            $x = explode('=', $val);
            $result[$x[0]] = $x[1];
        }
        unset($val, $x, $string);
        return $result;
    }
}