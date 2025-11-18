<?php

namespace jiuly256\modelhelper\tests\unit;

use PHPUnit\Framework\TestCase;
use jiuly256\modelhelper\ModelHelper;
use yii\base\Model;

class DummyModel extends Model
{
    public $id;
    public $name;

    public function rules()
    {
        return [
            [['name'], 'required'],
        ];
    }
}

class ModelHelperTest extends TestCase
{
    public function testCreateMultipleWithEmptyInput()
    {
        $models = ModelHelper::createMultiple(DummyModel::class, []);

        $this->assertIsArray($models, 'Debe retornar un array');
        $this->assertCount(0, $models, 'Sin POST, el array debe estar vacío');
    }

    public function testCreateMultipleWithExistingModels()
    {
        $existing = [new DummyModel(['id' => 1, 'name' => 'Test'])];
        $_POST['DummyModel'] = [
            ['id' => 1, 'name' => 'Test'],
            ['id' => '', 'name' => 'Nuevo']
        ];

        $models = ModelHelper::createMultiple(DummyModel::class, $existing);

        $this->assertCount(2, $models, 'Debe crear un nuevo modelo para POST adicional');
        $this->assertInstanceOf(DummyModel::class, $models[0]);
        $this->assertInstanceOf(DummyModel::class, $models[1]);

        // Limpiar POST
        unset($_POST['DummyModel']);
    }
}
