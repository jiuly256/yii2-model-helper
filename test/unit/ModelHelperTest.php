<?php

namespace jiuly256\modelhelper\tests\unit;

use PHPUnit\Framework\TestCase;
use jiuly256\modelhelper\ModelHelper;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * DummyModel simula un modelo Yii2 simple para pruebas de ModelHelper
 */
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

    public function formName()
    {
        return 'DummyModel';
    }
}

class ModelHelperTest extends TestCase
{
    /**
     * Test: Sin modelos previos y sin POST
     */
    public function testCreateMultipleWithEmptyInput()
    {
        $_POST = []; // No hay datos enviados

        $models = ModelHelper::createMultiple(DummyModel::class, []);

        $this->assertIsArray($models, 'Debe retornar un array');
        $this->assertCount(0, $models, 'Array vacío cuando no hay POST ni modelos previos');
    }

    /**
     * Test: Con modelos existentes pero sin POST
     */
    public function testCreateMultipleWithExistingModelsNoPost()
    {
        $existing = [new DummyModel(['id' => 1, 'name' => 'Test'])];

        $_POST = []; // Sin POST
        $models = ModelHelper::createMultiple(DummyModel::class, $existing);

        $this->assertIsArray($models, 'Debe retornar un array');
        $this->assertCount(0, $models, 'Array vacío sin POST');
    }

    /**
     * Test: Con modelos existentes y POST parcial
     */
    public function testCreateMultipleWithExistingModelsAndPost()
    {
        $existing = [
            new DummyModel(['id' => 1, 'name' => 'Existente'])
        ];

        $_POST['DummyModel'] = [
            ['id' => 1, 'name' => 'Existente'], // Mismo ID → reutiliza modelo
            ['id' => '', 'name' => 'Nuevo']    // Sin ID → crea modelo nuevo
        ];

        $models = ModelHelper::createMultiple(DummyModel::class, $existing);

        $this->assertCount(2, $models, 'Debe crear un nuevo modelo para POST adicional');
        $this->assertEquals('Existente', $models[0]->name, 'Primer modelo conserva datos existentes');
        $this->assertEquals('Nuevo', $models[1]->name, 'Segundo modelo toma datos del POST');

        // Limpiar POST
        unset($_POST['DummyModel']);
    }

    /**
     * Test: Validación de modelos generados
     */
    public function testValidationOfCreatedModels()
    {
        $_POST['DummyModel'] = [
            ['id' => '', 'name' => 'Valido'],
            ['id' => '', 'name' => ''] // No válido
        ];

        $models = ModelHelper::createMultiple(DummyModel::class, []);
        Model::loadMultiple($models, $_POST);

        $valid = Model::validateMultiple($models);

        $this->assertFalse($valid, 'Debe fallar la validación por modelo sin name');
        $this->assertCount(2, $models, 'Se generaron dos modelos');

        unset($_POST['DummyModel']);
    }

    /**
     * Test: IDs existentes combinados correctamente
     */
    public function testCreateMultipleWithIds()
    {
        $existing = [
            new DummyModel(['id' => 10, 'name' => 'A']),
            new DummyModel(['id' => 20, 'name' => 'B'])
        ];

        $_POST['DummyModel'] = [
            ['id' => 10, 'name' => 'A'],
            ['id' => '', 'name' => 'C']
        ];

        $models = ModelHelper::createMultiple(DummyModel::class, $existing);

        $this->assertCount(2, $models, 'Se deben generar dos modelos');
        $this->assertEquals('A', $models[0]->name, 'Primer modelo coincide con ID existente');
        $this->assertEquals('C', $models[1]->name, 'Segundo modelo es nuevo');

        unset($_POST['DummyModel']);
    }
}
