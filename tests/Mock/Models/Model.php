<?php

namespace Freimaurerei\ServiceModel\Mock\Models;

use Freimaurerei\ServiceModel\Model as BaseModel;
use Freimaurerei\ServiceModel\Validators\ArrayValidator;
use Freimaurerei\ServiceModel\Validators\CastValidator;

/**
 * Class Model
 * @package Freimaurerei\ServiceModel\Mock\Models
 *
 * @method string getTestString()
 * @method setTestString(\string $testString)
 * @method getNotExistingField()
 * @method getPrivateField()
 * @method getProtectedField()
 */
class Model extends BaseModel
{
    /**
     * @var string
     */
    public $testString;

    /**
     * @var int
     */
    public $testInt;

    /**
     * @var bool
     */
    public $testBool;

    /**
     * @var float
     */
    public $testFloat;

    /**
     * @var RelatedModel
     */
    public $testObject;

    /**
     * @var int[]
     */
    public $testArray;

    /**
     * @var RelatedModel[]
     */
    public $testObjectsArray;
    public $testNullString;
    public $testNullInt;
    public $testNullBool;
    public $testNullFloat;
    public $testNullObject;
    public $testNullArray;

    private /** @noinspection PhpUnusedPrivateFieldInspection */
        $privateField;
    protected $protectedField;

    /**
     * @var CollectionModel
     */
    public $testCollectionModel;

    /**
     * @var CollectionModel[]
     */
    public $testCollectionModelArray;

    /**
     * @var CollectionModelNonAssociative
     */
    public $testCollectionModelNonAssociative;

    /**
     * @var CollectionModelNonAssociative[]
     */
    public $testCollectionModelNonAssociativeArray;

    /**
     * @var CollectionOfCollectionsModel
     */
    public $testCollectionOfCollectionsModel;

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['testString', CastValidator::CAST_STRING, 'allowEmpty' => false, 'skipOnEmpty' => false,],
                ['testNullString', CastValidator::CAST_STRING, 'skipOnEmpty' => false,],
                ['testInt', CastValidator::CAST_INT, 'allowEmpty' => false, 'skipOnEmpty' => false,],
                ['testNullInt', CastValidator::CAST_INT, 'skipOnEmpty' => false,],
                ['testBool', CastValidator::CAST_BOOL, 'allowEmpty' => false, 'skipOnEmpty' => false,],
                ['testNullBool', CastValidator::CAST_BOOL, 'skipOnEmpty' => false,],
                ['testFloat', CastValidator::CAST_FLOAT, 'allowEmpty' => false, 'skipOnEmpty' => false,],
                ['testNullFloat', CastValidator::CAST_FLOAT, 'skipOnEmpty' => false,],
                [
                    'testArray',
                    CastValidator::CAST_ARRAY,
                    'allowEmpty' => false,
                    'type' => ArrayValidator::TYPE_INT,
                    'skipOnEmpty' => false,
                ],
                ['testNullArray', CastValidator::CAST_ARRAY, 'skipOnEmpty' => false,],
            ]
        );
    }

    public function relations()
    {
        return [
            'testObject' => [self::HAS_ONE, RelatedModel::className()],
            'testObjectsArray' => [self::HAS_MANY, RelatedModel::className()],
            'testCollectionModel' => [self::HAS_ONE, CollectionModel::className()],
            'testCollectionModelArray' => [self::HAS_MANY, CollectionModel::className()],
            'testCollectionModelNonAssociative' => [self::HAS_ONE, CollectionModelNonAssociative::className()],
            'testCollectionModelNonAssociativeArray' => [self::HAS_MANY, CollectionModelNonAssociative::className()],
            'testCollectionOfCollectionsModel' => [self::HAS_ONE, CollectionOfCollectionsModel::className()],
        ];
    }
}