<?php

namespace Freimaurerei\ServiceModel\Validators;

use Freimaurerei\ServiceModel\Model;

class ArrayValidator extends Validator
{
    const TYPE_BOOL = 'bool';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_MIXED = 'mixed';

    public $hasNotUniqueValuesMessage;

    public $type = self::TYPE_MIXED;

    public $allowEmpty = true;

    public $allowEmptyElements = true;

    public $isAssociative = false;

    public $isUnique = false;

    public $caseSensitive = false;

    /**
     * @return string[]
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_BOOL,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_STRING,
            self::TYPE_ARRAY,
        ];
    }

    /**
     * @return bool
     */
    protected function isTypeAvailable()
    {
        return in_array($this->type, static::getAvailableTypes());
    }

    /**
     * @param mixed $var
     */
    private function castToArray(&$var)
    {
        settype($var, 'array');
    }

    /**
     * @param mixed $var
     */
    private function castToType(&$var)
    {
        if (isset($var) || !$this->allowEmptyElements) {
            settype($var, $this->type);
        }
    }

    public function validateValue($value)
    {
    }

    public function validateAttribute($model, $attribute)
    {
        if (isset($model->$attribute)) {
            $this->castToArray($model->$attribute);
            if ($this->isTypeAvailable()) {
                $arrayElementCallback = $this->type === self::TYPE_ARRAY
                    ? [$this, 'castToArray']
                    : [$this, 'castToType'];

                array_walk($model->$attribute, $arrayElementCallback);
            }

            if ($this->isUnique) {
                $array = $model->$attribute;

                array_walk(
                    $array,
                    function (&$item) {
                        if ($item instanceof Model) {
                            /** @var Model $item */
                            $item = $item->getAttributes();
                        }
                    }
                );

                if (!$this->caseSensitive) {
                    array_walk_recursive(
                        $array,
                        function (&$string) {
                            if (is_string($string)) {
                                $string = mb_strtolower($string, 'UTF-8');
                            }
                        }
                    );
                }

                if (count($array) != count(array_unique($array, SORT_REGULAR))) {
                    $this->addError(
                        $model,
                        $attribute,
                        $this->hasNotUniqueValuesMessage !== null
                            ? $this->hasNotUniqueValuesMessage
                            : \Yii::t(
                            'validator',
                            '{attribute} contains not unique values.',
                            ['{attribute}' => $attribute]
                        )
                    );
                }
            }

            $model->$attribute = $this->isAssociative
                ? (object)$model->$attribute
                : array_values($model->$attribute);
        } elseif (!$this->allowEmpty) {
            $this->addError(
                $model,
                $attribute,
                $this->message !== null ? $this->message : \Yii::t('yii', '{attribute} cannot be blank.')
            );
        }
    }
}