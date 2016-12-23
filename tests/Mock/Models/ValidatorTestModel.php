<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\Model as BaseModel;
use Freimaurerei\ServiceModel\Validators\ArrayElementsValidator;
use Freimaurerei\ServiceModel\Validators\CastValidator;
use Freimaurerei\ServiceModel\Validators\DateTimeValidator;
use Freimaurerei\ServiceModel\Validators\ReplaceValidator;

class ValidatorTestModel extends BaseModel
{
    public $dateTimeFormat = 'Y-m-d';
    public $replaceMap = [' ' => '-'];

    public $arrayElementsValidatorTestField;
    public $arrayElementsValidatorTestFieldWithNotModifier;
    public $arrayElementsValidatorTestFieldWithStrictModifier;
    public $arrayElementsValidatorTestFieldWithAllowEmptyModifier = [true];
    public $arrayValidatorTestIsUnique;
    public $arrayValidatorTestIsUniqueCaseSensitive;
    /** @var ValidatorTestRelatedModel[] */
    public $arrayValidatorTestIsUniqueObjects;
    /** @var ValidatorTestRelatedModel[] */
    public $arrayValidatorTestIsUniqueObjectsCaseSensitive;
    public $dateTimeValidatorTestField;
    public $replaceValidatorTestField;

    public function relations()
    {
        return [
            'arrayValidatorTestIsUniqueObjects' => [
                self::HAS_MANY,
                ValidatorTestRelatedModel::class
            ],
            'arrayValidatorTestIsUniqueObjectsCaseSensitive' => [
                self::HAS_MANY,
                ValidatorTestRelatedModel::class
            ],
        ];
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    'arrayElementsValidatorTestField',
                    ArrayElementsValidator::class,
                    'range' => [true],
                ],
                [
                    'arrayElementsValidatorTestFieldWithNotModifier',
                    ArrayElementsValidator::class,
                    'range' => [false],
                    'not' => true,
                ],
                [
                    'arrayElementsValidatorTestFieldWithStrictModifier',
                    ArrayElementsValidator::class,
                    'range' => [true],
                    'strict' => true,
                ],
                [
                    'arrayElementsValidatorTestFieldWithAllowEmptyModifier',
                    ArrayElementsValidator::class,
                    'range' => [true],
                    'allowEmpty' => false,
                    'skipOnEmpty' => false,
                ],
                ['arrayValidatorTestIsUnique', CastValidator::CAST_ARRAY, 'isUnique' => true],
                [
                    'arrayValidatorTestIsUniqueCaseSensitive',
                    CastValidator::CAST_ARRAY,
                    'isUnique' => true,
                    'caseSensitive' => true,
                ],
                ['arrayValidatorTestIsUniqueObjects', CastValidator::CAST_ARRAY, 'isUnique' => true],
                [
                    'arrayValidatorTestIsUniqueObjectsCaseSensitive',
                    CastValidator::CAST_ARRAY,
                    'isUnique' => true,
                    'caseSensitive' => true
                ],
                ['dateTimeValidatorTestField', DateTimeValidator::className(), 'format' => $this->dateTimeFormat],
                ['replaceValidatorTestField', ReplaceValidator::className(), 'map' => $this->replaceMap],
            ]
        );
    }
}