<?php

namespace Freimaurerei\ServiceModel\Validators;

use yii\base\Exception;

class ArrayElementsValidator extends Validator
{
    public $range;

    public $strict = false;

    public $allowEmpty = true;

    public $not = false;

    /**
     * @param mixed $value
     * @return null|array
     */
    public function validateValue($value)
    {
        if ($this->isEmpty($value)) {

            return $this->allowEmpty ? null : [
                \Yii::t('validator', 'cannot be empty.'),
                []
            ];
        }

        if (!is_array($value)) {
            throw new Exception(var_export($value, true) /*\Yii::t(
               'validator',
                'must be an array.'
            )*/);
        }
        if (!is_array($this->range)) {
            throw new Exception(\Yii::t('yii', 'The "range" property must be specified with a list of values.'));
        }

        $isValid = true;
        foreach ($value as $valueElement) {
            $inRange = in_array($valueElement, $this->range, $this->strict);
            $isValid = $this->not ? !$inRange : $inRange;
            if (!$isValid) {
                break;
            }
        }

        return $isValid ? null : [
            \Yii::t('validator', 'contains {not} listed values.',
                [
                    'not' => $this->not ? ' ' : ' not ',
                ]),
            []
        ];
    }
}
