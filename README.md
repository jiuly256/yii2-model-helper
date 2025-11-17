# 🐬 ModelHelper para Yii2 — Gestión avanzada de múltiples modelos dinámicos

Un componente ligero, robusto y totalmente reutilizable para manejar **creación, carga, validación y borrado automático de múltiples modelos** en un solo formulario dinámico en Yii2.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
![Yii2](https://img.shields.io/badge/Yii2-Framework-blue)
![Status](https://img.shields.io/badge/Stable-Yes-brightgreen)
![PHP](https://img.shields.io/badge/PHP-5.6%2B%20%7C%207.x%20%7C%208.x-777BB4?logo=php)

---

## 🚀 ¿Qué resuelve este paquete?

Yii2 ofrece `loadMultiple` y `validateMultiple`, pero no resuelve el *problema real*:

- Reconstruir N modelos dinámicos enviados por POST
- Detectar elementos eliminados en el frontend
- Crear automáticamente nuevos registros
- Evitar índices rotos y conflictos de ID
- Validación y guardado masivo sencillo

Con una sola línea:

```php
$modelos = ModelHelper::createMultiple(MyModel::class, $modelosIniciales);
```

## 📦 Instalación (Composer)

```bash
composer require jiuly256/yii2-modelhelper
```

## 🔧 Uso básico en controlador
```php
use jiuly256\modelhelper\ModelHelper;
use yii\base\Model;
use yii\helpers\ArrayHelper;

public function actionMultiple($id)
{
    $modelos = MyModel::findAll(['parent_id' => $id]);

    if (empty($modelos)) {
        $modelos = [new MyModel(['parent_id' => $id])];
    }

    if (Yii::$app->request->isPost) {
        $oldIDs = ArrayHelper::map($modelos, 'id', 'id');
        $modelos = ModelHelper::createMultiple(MyModel::class, $modelos);
        Model::loadMultiple($modelos, Yii::$app->request->post());

        $newIDs = ArrayHelper::map($modelos, 'id', 'id');
        $deletedIDs = array_diff($oldIDs, $newIDs);

        if ($deletedIDs) {
            MyModel::deleteAll(['id' => $deletedIDs]);
        }

        if (Model::validateMultiple($modelos)) {
            foreach ($modelos as $m) {
                $m->parent_id = $id;
                $m->save(false);
            }
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    return $this->render('multiple', [
        'modelos' => $modelos
    ]);
}
```

## 📄 Vista ejemplo multi-model-example.php
```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\base\Model[] $modelos */
?>

<h1>Ejemplo de Multi-Model</h1>

<?php $form = ActiveForm::begin(); ?>

<table class="table table-bordered" id="multi-model-table">
    <thead>
        <tr>
            <th>Atributos</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>

    <?php foreach ($modelos as $i => $model): ?>
        <tr>
            <td>
                <?php
                foreach ($model->safeAttributes() as $attribute) {
                    echo $form->field($model, "[$i]{$attribute}")->textInput();
                }

                if ($model->hasAttribute('id')) {
                    echo $form->field($model, "[$i]id")->hiddenInput()->label(false);
                }
                ?>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

<button type="button" id="add-row" class="btn btn-success btn-sm">Agregar fila</button>

<div class="form-group mt-3">
    <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS

function renumerar() {
    $('#multi-model-table tbody tr').each(function(index) {
        $(this).find('[name]').each(function() {
            let nuevo = $(this).attr('name').replace(/\\[\\d+\\]/, '[' + index + ']');
            $(this).attr('name', nuevo);
        });
    });
}

$('#add-row').on('click', function() {
    let r = $('#multi-model-table tbody tr:last').clone();
    r.find('input').val('');
    $('#multi-model-table tbody').append(r);
    renumerar();
});

$(document).on('click', '.remove-row', function() {
    $(this).closest('tr').remove();
    renumerar();
});

JS
);
?>

```

## 🧱 Estructura del paquete
```php
yii2-modelhelper/
│
├── src/
│   ├── ModelHelper.php
│   └── views/multi-model-example.php
│
├── README.md
├── LICENSE
├── composer.json

```

## 🛠 Requisitos
- PHP 5.6+ / 7.x / 8.x
- Yii2 Framework
- Composer

## 🤝 Contribuciones
Pull Requests, issues y mejoras bienvenidas.
Se agradecen ejemplos, tests y demos adicionales.
