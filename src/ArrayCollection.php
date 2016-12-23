<?php

namespace Freimaurerei\ServiceModel;

use Freimaurerei\ServiceModel\Exception\ModelException;

abstract class ArrayCollection extends Model implements \Countable
{
    /**
     * @var array
     */
    private $collection = [];

    /**
     * Позволяет задать ассоциативность коллекции.
     *
     * @var bool
     */
    public $isAssociative = true;

    /**
     * Возвращает имя класса объектов в массивах коллекции. Класс должен наследовать @see self или @see Model.
     *
     * @return string
     */
    abstract protected function getObjectClassName();

    public function setAttributes($values, $safeOnly = true)
    {
        if ($values instanceof \ArrayObject) {
            $values = $values->getArrayCopy();
        }

        if (!is_array($values) || empty($values)) {
            return;
        }

        $objectClassName = $this->getObjectClassName();

        if (!is_subclass_of($objectClassName, Model::class)) {
            throw new ModelException(
                \Yii::t(
                    'arrayCollection',
                    '{class}::getObjectClassName() must return name of a class extending {self} or {model}',
                    [
                        '{class}' => get_class($this),
                        '{self}' => __CLASS__,
                        '{model}' => Model::class,
                    ]
                )
            );
        }

        $collection = [];

        $gcEnabled = gc_enabled();
        if ($gcEnabled) {
            gc_disable();
        }

        foreach ($values as $key => $value) {
            /** @var self[]|Model[] $objects */
            $objects = [];

            foreach ($value as $objectData) {
                /** @var self|Model $currentObject */
                if ($objectData instanceof $objectClassName) {
                    $currentObject = $objectData;
                } else {
                    $currentObject = new $objectClassName();
                    $currentObject->setAttributes($objectData);
                }

                $objects[] = $currentObject;
            }

            $collection[$key] = $objects;
        }

        $this->collection = $collection;

        if ($gcEnabled) {
            gc_enable();
        }
    }

    /**
     * @param null $names
     * @return array|\ArrayObject
     */
    public function getAttributes($names = null, $except = [])
    {
        $data = array_map(
            function ($item) {
                /**
                 * @param self[]|Model[] $item
                 * @return array
                 */
                return array_map(
                    function ($object) {
                        /**
                         * @param self|Model $object
                         * @return array
                         */
                        if ($object instanceof self && is_subclass_of($this->getObjectClassName(), __CLASS__)) {
                            $object->isAssociative = false;
                        }
                        return $object->getAttributes();
                    },
                    $item
                );
            },
            $this->collection
        );

        return $this->isAssociative ? new \ArrayObject($data) : array_values($data);
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if ($this->beforeValidate()) {
            foreach ($this->collection as $key => $item) {
                foreach ($item as $object) {
                    $this->validateObject($object, $key);
                }
            }

            $this->afterValidate();
            return !$this->hasErrors();
        } else {
            return false;
        }
    }

    /**
     * @param self|Model $object
     * @param int $index
     */
    private function validateObject(Model $object, $index)
    {
        if (!$object->validate()) {
            foreach ($object->getErrors() as $itemErrors) {
                foreach ($itemErrors as $itemError) {
                    $this->addError(
                        'collection',
                        \Yii::t(
                            'model',
                            'Error in {attribute}[{index}]. {itemError}',
                            [
                                '{attribute}' => 'Collection',
                                '{index}' => $index,
                                '{itemError}' => $itemError,
                            ]
                        )
                    );
                }
            }
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->collection);
    }

    public function offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

    public function count()
    {
        return count($this->collection);
    }
}