<?php

namespace Freimaurerei\ServiceModel\Converter;

use Freimaurerei\ServiceModel\Exception\ConverterException;
use Freimaurerei\ServiceModel\Exception\ValidationException;
use Freimaurerei\ServiceModel\Model;
use yii\web\HttpException;

abstract class Converter
{
    /**
     * @var Converter[]
     */
    private static $converters = [];

    /**
     * @static
     *
     * @param $name
     *
     * @return Converter
     * @throws ConverterException
     */
    public static function factory($name)
    {
        if (!empty($name)) {
            if (array_key_exists($name, static::$converters)) {
                return static::$converters[$name];
            }
            $className = 'Freimaurerei\\ServiceModel\\Converter\\' . strtoupper($name) . 'Converter';
            if (class_exists($className)) {
                return static::$converters[$name] = new $className;
            }
        }
        throw new ConverterException(\Yii::t(
            'converter',
            'Converter "{converter}" not found.',
            ['{converter}' => $name]
        ));
    }

    /**
     * Конвертирует модель в данные выходного формата.
     *
     * @param Model $data
     * @param bool $runValidation Необходимость валидации модели перед экспортом.
     *
     * @throws ValidationException
     * @return mixed
     */
    public function export(Model $data, $runValidation = true)
    {
        if ($runValidation && !$data->validate()) {
            throw new ValidationException(\Yii::t(
                'converter',
                'Error validating "{model}" model data.',
                ['{model}' => get_class($data)]
            ));
        }

        return $this->exportInternal($data);
    }

    /**
     * Содержит логику конвертации модели в данные выходного формата.
     *
     * @param Model $data
     *
     * @return mixed
     */
    abstract protected function exportInternal(Model $data);

    /**
     * @param array $data
     *
     * @return mixed
     */
    abstract public function exportArray(array $data);

    /**
     * @param $string
     * @return array
     */
    abstract public function toArray($string);

    /**
     * Конвертация входных данных в объект класса {$schema}.
     * Дополнительно происходит валидация входных данных.
     *
     * @abstract
     *
     * @param string $data
     * @param Model $schema
     *
     * @return Model
     * @throws HttpException
     */
    public function import($data, Model $schema)
    {
        if (is_string($data)) {
            $data = $this->toArray($data);
        }
        $schema->setAttributes($data);
        return $schema;
    }
}