<?php

namespace Freimaurerei\ServiceModel\Converter;

use Freimaurerei\ServiceModel\Exception\XMLConverterException;
use Freimaurerei\ServiceModel\Model;

class XMLConverter extends Converter
{
    /**
     * Конвертация объекта в XML.
     * @param Model $data
     *
     * @return mixed
     */
    protected function exportInternal(Model $data)
    {
        $node = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><{$data->name()} />");
        $this->array2node($data->getAttributes(), $node);
        return $node->asXML();
    }

    /**
     * @param array $data
     * @param string $rootName
     *
     * @return mixed
     */
    public function exportArray(array $data, $rootName = 'response')
    {
        $node = new \SimpleXMLElement("<{$rootName} />");
        $this->array2node($data, $node);
        return $node->asXML();
    }

    private function array2node($data, \SimpleXMLElement $node)
    {
        $data = (array)$data;
        foreach ($data as $key => $element) {
            if (is_array($element) || is_object($element)) {
                if (!is_numeric($key)) {
                    $this->array2node($element, $node->addChild($key));
                } else {
                    $this->array2node($element, $node->addChild('item'));
                }
            } else {
                if (is_numeric($key)) {
                    if (is_numeric($element)) {
                        $node->addChild('item', $element);
                    } else {
                        $this->addChildCDATA($node, 'item', $element);
                    }
                } else {
                    if (is_numeric($element)) {
                        $node->$key = $element;
                    } else {
                        $this->addChildCDATA($node, $key, $element);
                    }
                }
            }
        }
    }

    /**
     * Добавляет элемент, обернутый в CDATA
     * @param \SimpleXMLElement $node
     * @param                   $key
     * @param                   $value
     * @return array
     */
    private function addChildCDATA(\SimpleXMLElement $node, $key, $value)
    {
        $childNode = $node->addChild($key); //Added a nodename to create inside the function
        $childNode = dom_import_simplexml($childNode);
        $childNode->appendChild($childNode->ownerDocument->createCDATASection($value));
    }

    /**
     * @param \SimpleXMLElement|array $node
     * @return array
     */
    private function node2array($node)
    {
        // todo придумать что-нибудь получше
        if (is_array($node) && count($node) == 1) {
            $array = reset($node);
            if (is_array($array)) {
                return $this->node2array($array);
            }
        }

        if ($node instanceof \SimpleXMLElement) {
            $array = (array)$node;

            foreach (array_slice($array, 0) as $key => $value) {
                if ($value instanceof \SimpleXMLElement) {
                    $value = empty($value) ? null : $this->node2array($value);
                    if ($key === 'item') {
                        $array[] = $value;
                    } else {
                        $array[$key] = $value;
                    }
                } elseif (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $v = empty($v) ? null : $this->node2array($v);

                        if ($key === 'item') {
                            $array[$k] = $v;
                        } else {
                            $array[$key][$k] = $v;
                        }
                    }
                } elseif (is_scalar($value) && $key == 'item') {
                    $array[] = $value;
                }
                if ($key === 'item') {
                    unset($array['item']);
                }
            }
        } else {
            $array = $node;
        }

        return $array;
    }

    /**
     * @param $string
     * @throws \Freimaurerei\ServiceModel\Exception\XMLConverterException
     * @return array
     */
    public function toArray($string)
    {
        $string = preg_replace("/>\s+</", '><', $string);
        set_error_handler(
            function () {
            }
        );
        $node = simplexml_load_string($string, null, LIBXML_NOCDATA);
        restore_error_handler();

        if (!$node instanceof \SimpleXMLElement) {
            throw new XMLConverterException(\Yii::t(
                'converter',
                'Wrong XML.'
            ));
        }

        return $this->node2array($node);
    }
}
