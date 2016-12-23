<?php

namespace Freimaurerei\ServiceModel;

use Freimaurerei\ServiceModel\Mock\Models\ValidatorTestModel;
use Freimaurerei\ServiceModel\Mock\Models\ValidatorTestRelatedModel;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorTestModel
     */
    private $model;

    protected function setUp()
    {
        $this->model = new ValidatorTestModel();
        $this->assertTrue($this->model->validate());
    }

    public function testArrayElementsValidator()
    {
        $this->model->arrayElementsValidatorTestField = [0];
        $this->assertFalse($this->model->validate());
        $this->assertNotEmpty($this->model->getFirstError('arrayElementsValidatorTestField'));
    }

    public function testArrayElementsValidatorWithNotModifier()
    {
        $this->model->arrayElementsValidatorTestFieldWithNotModifier = [0];
        $this->assertFalse($this->model->validate());
        $this->assertNotEmpty($this->model->getFirstError('arrayElementsValidatorTestFieldWithNotModifier'));
    }

    public function testArrayElementsValidatorTestFieldWithStrictModifier()
    {
        $this->model->arrayElementsValidatorTestFieldWithStrictModifier = [true];
        $this->assertTrue($this->model->validate());

        $this->model->arrayElementsValidatorTestFieldWithStrictModifier = [1];
        $this->assertFalse($this->model->validate());
        $this->assertNotEmpty($this->model->getFirstError('arrayElementsValidatorTestFieldWithStrictModifier'));
    }

    public function testArrayElementsValidatorTestFieldWithAllowEmptyModifier()
    {
        $this->model->arrayElementsValidatorTestFieldWithAllowEmptyModifier = [];
        $this->assertFalse($this->model->validate());
        $this->assertNotEmpty($this->model->getFirstError('arrayElementsValidatorTestFieldWithAllowEmptyModifier'));
    }

    public function testArrayValidatorTestIsUnique()
    {
        $this->model->arrayValidatorTestIsUnique = ['a', 'b'];
        $this->assertTrue($this->model->validate());

        $this->model->arrayValidatorTestIsUnique = ['a', 'A'];
        $this->assertFalse($this->model->validate());
        $this->assertNotEmpty($this->model->getFirstError('arrayValidatorTestIsUnique'));
    }

    public function testArrayValidatorTestIsUniqueCaseSensitive()
    {
        $this->model->arrayValidatorTestIsUniqueCaseSensitive = ['a', 'A'];
        $this->assertTrue($this->model->validate());

        $this->model->arrayValidatorTestIsUniqueCaseSensitive = ['a', 'a'];
        $this->assertFalse($this->model->validate());
        $this->assertNotEmpty($this->model->getFirstError('arrayValidatorTestIsUniqueCaseSensitive'));
    }

    public function testArrayValidatorTestIsUniqueObjects()
    {
        $this->model->arrayValidatorTestIsUniqueObjects = [
            new ValidatorTestRelatedModel(),
            new ValidatorTestRelatedModel()
        ];

        $this->model->arrayValidatorTestIsUniqueObjects[0]->field = 'a';
        $this->model->arrayValidatorTestIsUniqueObjects[1]->field = 'b';
        $this->assertTrue($this->model->validate());

        $this->model->arrayValidatorTestIsUniqueObjects[0]->field = 'a';
        $this->model->arrayValidatorTestIsUniqueObjects[1]->field = 'A';
        $this->assertFalse($this->model->validate());
    }

    public function testArrayValidatorTestIsUniqueObjectsCaseSensitive()
    {
        $this->model->arrayValidatorTestIsUniqueObjectsCaseSensitive = [
            new ValidatorTestRelatedModel(),
            new ValidatorTestRelatedModel()
        ];

        $this->model->arrayValidatorTestIsUniqueObjectsCaseSensitive[0]->field = 'a';
        $this->model->arrayValidatorTestIsUniqueObjectsCaseSensitive[1]->field = 'A';
        $this->assertTrue($this->model->validate());

        $this->model->arrayValidatorTestIsUniqueObjectsCaseSensitive[0]->field = 'a';
        $this->model->arrayValidatorTestIsUniqueObjectsCaseSensitive[1]->field = 'a';
        $this->assertFalse($this->model->validate());
    }

    public function testDateTimeValidatorTestField()
    {
        $date = '2112.12.12';

        $this->model->dateTimeValidatorTestField = $date;

        $this->assertTrue($this->model->validate());

        $this->assertEquals(
            date($this->model->dateTimeFormat, strtotime($date)),
            $this->model->dateTimeValidatorTestField
        );
    }

    public function testReplaceValidatorTestField()
    {
        $value = 'test test';

        $this->model->replaceValidatorTestField = $value;

        $this->assertTrue($this->model->validate());

        $this->assertSame(strtr($value, $this->model->replaceMap), $this->model->replaceValidatorTestField);
    }
}