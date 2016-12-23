<?php

namespace Freimaurerei\ServiceModel;

use Freimaurerei\ServiceModel\Mock\Models\TraitsTestModel;

class TraitsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TraitsTestModel
     */
    private $model;

    protected function setUp()
    {
        $this->model = new TraitsTestModel();
    }

    public function testGetModelPropertyElement()
    {
        $this->model->field = ['name' => 'some name'];
        $this->assertEquals('some name', $this->model->getFieldElement('name'));
    }
}