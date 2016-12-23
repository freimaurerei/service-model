<?php

namespace Freimaurerei\ServiceModel;

use Freimaurerei\ServiceModel\Exception\ModelException;
use Freimaurerei\ServiceModel\Exception\ValidationException;

abstract class Model extends \yii\base\Model implements \JsonSerializable, \Serializable
{
    protected $areAttributesSafe = true;

    /**
     * Связь один к одному
     */
    const HAS_ONE = 'has_one';

    /**
     * Связь один ко многому
     */
    const HAS_MANY = 'has_many';

    /**
     * Список зарегистрированных callback`ов для разных типов.
     *
     * @var \Closure[]
     */
    private $formatters = [];

    public function __call($name, $parameters)
    {
        if (strlen($name) > 3) {
            $prefix = substr($name, 0, 3);

            $isCalledMethodSetter = strcasecmp($prefix, 'set') === 0;

            if ($isCalledMethodSetter && count($parameters) < 1) {
                trigger_error('Missing argument 1 for ' . get_class($this) . '::' . $name, E_USER_WARNING);
                return null;
            }

            if ($isCalledMethodSetter || strcasecmp($prefix, 'get') === 0) {
                $property = lcfirst(substr($name, 3));

                if (in_array($property, $this->attributes())) {
                    if ($isCalledMethodSetter) {
                        $this->$property = array_pop($parameters);
                        return null;
                    }
                    return $this->$property;
                }
            }
        }

        return parent::__call($name, $parameters);
    }

    public function init()
    {
        parent::init();

        $this->registerDefaultFormatters();
    }

    /**
     * Массив встроенных форматтеров
     *
     * @return \Closure[]
     */
    protected function defaultFormatters()
    {
        return [
            'DateTime' => function (\DateTime $time) {
                return $time->format('c');
            }
        ];
    }

    private function registerDefaultFormatters()
    {
        $this->formatters = $this->defaultFormatters();
    }

    /**
     * @param string $type
     * @param callable $callback
     */
    public function registerFormatter($type, \Closure $callback)
    {
        $this->formatters[$type] = $callback;
    }

    /**
     * Настройки связей модели.
     * Пример:
     * [
     * 'comments' => [self::HAS_MANY, 'CommentSchema'],
     * 'author'   => [self::HAS_ONE, 'UserSchema'],
     * ]
     *
     * @return array
     */
    public function relations()
    {
        return [];
    }

    /**
     * Название экспортируемого объекта
     *
     * @return string
     */
    public function name()
    {
        return 'response';
    }

    public function getSafeAttributeNames()
    {
        return $this->areAttributesSafe ? $this->attributes() : parent::getSafeAttributeNames();
    }

    /**
     * Проставляет значение аттрибутов модели. Если передается многомерный массив, то аттрибуты проставляются рекурсивно.
     * @param array $values
     * @param bool $safeOnly
     * @throws ModelException
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if (!is_array($values) || empty($values)) {
            return;
        }

        $relations = array_intersect_key($this->relations(), $values);

        $attributes = array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributes());
        $scalarValues = array_diff_key($values, $relations);
        foreach ($scalarValues as $name => $value) {
            if (isset($attributes[$name])) {
                $this->$name = $value;
            } elseif ($safeOnly) {
                $this->onUnsafeAttribute($name, $value);
            }
        }

        $gcEnabled = gc_enabled();
        if ($gcEnabled) {
            gc_disable();
        }

        foreach ($relations as $propertyName => $relation) {
            $this->populateRelated($relation, $propertyName, $values[$propertyName], $safeOnly);
        }

        if ($gcEnabled) {
            gc_enable();
        }
    }

    /**
     * Заполняет поле связанной моделью. Выполняет инстанцирование модели, в случае если передается массив атрибутов.
     * При передаче аргумента (@link $returnValue) значения передаются в него.
     *
     * @param array $relation
     * @param string $propertyName
     * @param mixed $value
     * @param bool $safeOnly
     * @throws ModelException
     */
    private function populateRelated($relation, $propertyName, $value, $safeOnly = true)
    {
        if (!isset($value)) {
            $this->$propertyName = $value;
        } else {
            switch ($relation[0]) {
                case self::HAS_ONE:
                    if ($value instanceof $relation[1]) {
                        $this->$propertyName = $value;
                    } else {
                        /** @var $model self|ArrayCollection */
                        $model = new $relation[1];
                        $model->setAttributes($value, $safeOnly);
                        $this->$propertyName = $model;
                    }
                    break;
                case self::HAS_MANY:
                    if (is_array($value)) {
                        $models = [];
                        foreach ($value as $key => $valueSet) {
                            if ($valueSet instanceof $relation[1]) {
                                $models[$key] = $valueSet;
                            } else {
                                /** @var $model self|ArrayCollection */
                                $model = new $relation[1];
                                $model->setAttributes($valueSet, $safeOnly);
                                $models[$key] = $model;
                            }
                        }
                        $this->$propertyName = $models;
                    }
                    break;
                default:
                    throw new ModelException(\Yii::t(
                        'model',
                        'Wrong relation configuration: "{name}".',
                        ['{name}' => $propertyName]
                    ));
            }
        }
    }

    /**
     * @param mixed $attribute
     */
    private function normalizeAttribute(&$attribute)
    {
        if ($attribute instanceof Model) {
            $attribute = $attribute->getAttributes();
        }
    }

    /**
     * Возвращает атрибуты модели. В случае если у модели есть связи, то в результирующем массиве будет массив
     * с аттрибутами связанной сущности.
     *
     * @param string[] $names
     * @param array $except
     *
     * @throws Exception\ValidationException
     * @throws Exception\ModelException
     * @return array
     */
    public function getAttributes($names = null, $except = [])
    {
        if (!isset($names)) {
            $names = array_merge($this->attributes(), array_keys($this->relations()));
        }

        $values = array_fill_keys($names, null);

        $relations = array_intersect_key($this->relations(), array_flip($names));
        $names = array_diff($names, array_keys($relations));

        $names = array_filter($names, function ($key) use ($except) {
            return !array_key_exists($key, $except);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($names as $name) {
            $value = $this->$name;
            if (is_array($value)) {
                foreach ($value as &$item) {
                    $this->normalizeAttribute($item);
                }
                unset($item);
            } else {
                $this->normalizeAttribute($value);
            }

            $values[$name] = $this->formatValue($value);
        }

        foreach ($relations as $propertyName => $relation) {
            $property = $this->$propertyName;
            if (is_null($property)) {
                $values[$propertyName] = null;
                continue;
            }
            switch ($relation[0]) {
                case self::HAS_ONE:
                    if ($property instanceof $relation[1]) {
                        /** @var self|ArrayCollection $property */
                        $values[$propertyName] = $property->getAttributes();
                    } elseif (is_object($property)) {
                        /** @var self|ArrayCollection $className */
                        $className = $relation[1];
                        throw new ValidationException(\Yii::t(
                            'model',
                            'Trying to get properties of related object "{name}" of wrong type.'
                            . 'Got {class}. {relation} expected.',
                            [
                                '{name}' => $propertyName,
                                '{class}' => get_class($property),
                                '{relation}' => $className::className(),
                            ]
                        ));
                    } else {
                        $values[$propertyName] = $property;
                    }
                    break;

                case self::HAS_MANY:
                    $values[$propertyName] = array_map(
                        function ($relatedObject) use ($relation, $propertyName) {
                            if ($relatedObject instanceof $relation[1]) {
                                /** @var self|ArrayCollection $relatedObject */
                                return $relatedObject->getAttributes();
                            } elseif (is_object($relatedObject)) {
                                /** @var self|ArrayCollection $className */
                                $className = $relation[1];
                                throw new ValidationException(\Yii::t(
                                    'model',
                                    'Trying to get properties of related array "{name}" element of wrong type.'
                                    . ' Got {class}. {relation} expected.',
                                    [
                                        '{name}' => $propertyName,
                                        '{class}' => get_class($relatedObject),
                                        '{baseClass}' => $className::className(),
                                    ]
                                ));
                            }

                            return $relatedObject;
                        },
                        (array)$property
                    );
                    break;
                default:
                    throw new ModelException(\Yii::t(
                        'model',
                        'Wrong relation configuration: "{name}".',
                        ['{name}' => $propertyName]
                    ));
            }
        }

        return $values;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if ($this->beforeValidate()) {
            $relations = $this->relations();
            $relationAttributes = isset($attributeNames)
                ? array_intersect_key($relations, array_flip($attributeNames))
                : $relations;

            foreach ($relationAttributes as $propertyName => $relation) {
                switch ($relation[0]) {
                    case self::HAS_ONE:
                        if (isset($this->$propertyName)) {
                            $this->validateRelated($this->$propertyName, $propertyName);
                        }
                        break;
                    case self::HAS_MANY:
                        if (!empty($this->$propertyName)) {
                            foreach ($this->$propertyName as $index => $relatedObject) {
                                $this->validateRelated(
                                    $relatedObject,
                                    $propertyName,
                                    "Error in element with index $index"
                                );
                            }
                        }
                        break;
                }
            }

            foreach ($this->getValidators() as $validator) {
                /** @var \yii\validators\Validator $validator */
                $validator->validateAttributes($this, $attributeNames);
            }
            $this->afterValidate();
            return !$this->hasErrors();
        } else {
            return false;
        }
    }

    /**
     * @param self $relatedObject
     * @param string $propertyName
     * @param string $prependMessage
     */
    private function validateRelated($relatedObject, $propertyName, $prependMessage = null)
    {
        if (isset($relatedObject) && !$relatedObject->validate()) {
            foreach ($relatedObject->getErrors() as $relatedObjectFieldErrors) {
                foreach ($relatedObjectFieldErrors as $relatedObjectFieldError) {
                    $this->addError(
                        $propertyName,
                        \Yii::t(
                            'model',
                            'Error in {attribute}. {prependMessage}{relatedObjectFieldError}',
                            [
                                '{attribute}' => $this->getAttributeLabel($propertyName),
                                '{prependMessage}' => $prependMessage ? "$prependMessage. " : '',
                                '{relatedObjectFieldError}' => $relatedObjectFieldError,
                            ]
                        )
                    );
                }
            }
        }
    }

    /**
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function formatValue($value)
    {
        if (is_object($value)) {
            $class = get_class($value);
            if (array_key_exists($class, $this->formatters)) {
                $callback = $this->formatters[$class];
                return $callback($value);
            }
        }

        return $value;
    }

    public function jsonSerialize()
    {
        return $this->getAttributes();
    }

    public function serialize()
    {
        return serialize($this->getAttributes());
    }

    public function unserialize($serialized)
    {
        $this->setAttributes(unserialize($serialized));

        $this->init();
        $this->attachBehaviors($this->behaviors());
    }
}
