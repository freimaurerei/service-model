<?php

namespace Freimaurerei\ServiceModel;

use Freimaurerei\ServiceModel\Converter\Converter;
use Freimaurerei\ServiceModel\Mock\Models\Model as MockModel;
use Freimaurerei\ServiceModel\Mock\Models\RelatedModel;
use yii\base\ErrorException;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $dataFile;

    /**
     * @var MockModel
     */
    protected $model;

    protected function setUp()
    {
        $this->dataFile = __DIR__ . '/Mock/Data/model';
        $this->model = new MockModel();
        $data = $this->getExpectedDataAsArray();
        // Чтобы проверить каст, переводим сначала все, кроме булевых значений в string.
        // Булевые значения переводим в числа.
        array_walk_recursive(
            $data,
            function (&$attribute) {
                if (isset($attribute)) {
                    $attribute = is_bool($attribute) ? (int)$attribute : (string)$attribute;
                }
            }
        );
        $this->model->setAttributes($data);
    }

    private function getExpectedData($type)
    {
        return file_get_contents("$this->dataFile.$type");
    }

    protected function getExpectedDataAsArray()
    {
        return json_decode($this->getExpectedData('json'), true);
    }

    protected function getModelAttributesAsArray()
    {
        return json_decode(Converter::factory('json')->export($this->model), true);
    }

    public function testCast()
    {
        $this->assertSame($this->getExpectedDataAsArray(), $this->getModelAttributesAsArray());
        $this->assertTrue($this->model->validate());
    }

    public function testRelatedValidation()
    {
        $this->model->testObject->field = null;
        $this->assertFalse($this->model->validate());
        $this->assertArrayHasKey('testObject', $this->model->getErrors());
    }

    public function testManyRelatedValidation()
    {
        array_walk(
            $this->model->testObjectsArray,
            function ($object) {
                /** @var \Freimaurerei\ServiceModel\Mock\Models\RelatedModel $object */
                $object->field = null;
            }
        );
        $this->assertFalse($this->model->validate());
        $this->assertArrayHasKey('testObjectsArray', $this->model->getErrors());
    }

    public function testCastNullsToNotEmptyFields()
    {
        $attributes = [
            'testString',
            'testInt',
            'testBool',
            'testFloat',
            'testArray',
        ];

        $this->model->setAttributes(array_fill_keys($attributes, null));
        $expectedData = $this->getExpectedDataAsArray();

        foreach ($attributes as $attribute) {
            $this->assertNotSame($expectedData[$attribute], $this->model->$attribute);
        }
        $this->assertFalse($this->model->validate());
        $this->assertSameSize($attributes, $this->model->getErrors());
    }

    public function testXML()
    {
        $this->assertXmlStringEqualsXmlString(
            $this->getExpectedData('xml'),
            Converter::factory('xml')->export($this->model)
        );
    }

    public function testMagicCall()
    {
        $value = 'test';

        $this->assertNotEquals($value, $this->model->getTestString());
        $this->model->setTestString($value);
        $this->assertSame($value, $this->model->getTestString());
    }

    public function testGetNotExistingField()
    {
        $method = 'getNotExistingField';

        $this->setExpectedException('Exception');
        $this->model->$method();
    }

    public function testGetPrivateField()
    {
        $method = 'getPrivateField';

        $this->setExpectedException('Exception');
        $this->model->$method();
    }

    public function testGetProtectedField()
    {
        $method = 'getProtectedField';

        $this->setExpectedException('Exception');
        $this->model->$method();
    }

    public function testSetWithoutArgument()
    {
        $this->setExpectedException(
            ErrorException::class,
            'Missing argument 1 for ' . MockModel::class . '::setTestString'
        );
        /** @noinspection PhpParamsInspection */
        $this->model->setTestString();
    }

    public function testArrayCollectionAccess()
    {
        foreach ($this->model->testCollectionModel as $collection) {
            foreach ($collection as $model) {
                $this->assertInstanceOf(RelatedModel::class, $model);
            }
        }

        $count = count($this->model->testCollectionModel) + 1;

        $this->model->testCollectionModel["collection$count"] = [new RelatedModel()];
        $this->assertCount($count, $this->model->testCollectionModel);
    }

    public function testSetNullAttributes()
    {
        $this->model->setAttributes(
            array_fill_keys(
                $this->model->attributes(),
                null
            )
        );

        foreach ($this->model->getAttributes() as $attribute) {
            $this->assertNull($attribute);
        }
    }

    public function testSerialization()
    {
        /** @var MockModel $unserialized */
        $unserialized = unserialize(serialize($this->model));
        $this->assertEquals($this->model->getAttributes(), $unserialized->getAttributes());
    }
}