🌟 ModelHelper para Yii2 — Gestión avanzada de múltiples modelos dinámicos

Un componente ligero, robusto y totalmente reutilizable para manejar creación, carga, validación y borrado automático de múltiples modelos en un solo formulario dinámico en Yii2.

🚀 ¿Qué resuelve este paquete?

Yii2 ofrece loadMultiple y validateMultiple, pero no resuelve el problema real:

¿Cómo reconstruyo N modelos dinámicos enviados por POST?

¿Cómo detecto cuáles se eliminaron en el frontend?

¿Cómo creo automáticamente los nuevos?

¿Cómo los renumero de manera segura?

¿Cómo evito IDs vacíos o índices rotos?

👉 ModelHelper lo hace por ti.

Con una sola línea:

$modelos = ModelHelper::createMultiple(MyModel::class, $modelosIniciales);


… obtienes una colección perfectamente reconstruida, segura y lista para loadMultiple.

📦 Instalación (Composer)
composer require jiuly256/yii2-modelhelper


Luego agrégalo a tu config si usas alias personalizados:

Yii::setAlias('@jiuly256', '@vendor/jiuly256');

🔧 Uso básico
Controller (100% genérico)
use jiuly256\modelhelper\ModelHelper;
use yii\base\Model;
use yii\helpers\ArrayHelper;

public function actionMultiple($id)
{
    // Cargamos modelos existentes
    $modelos = MyModel::findAll(['parent_id' => $id]);

    if (empty($modelos)) {
        $modelos = [new MyModel(['parent_id' => $id])];
    }

    if (Yii::$app->request->isPost) {

        // IDs originales
        $oldIDs = ArrayHelper::map($modelos, 'id', 'id');

        // Reconstrucción automática
        $modelos = ModelHelper::createMultiple(MyModel::class, $modelos);

        // Cargar datos POST
        Model::loadMultiple($modelos, Yii::$app->request->post());

        // Nuevos IDs después del POST
        $newIDs = ArrayHelper::map($modelos, 'id', 'id');

        // Detectar eliminados
        $deletedIDs = array_diff($oldIDs, $newIDs);
        if ($deletedIDs) {
            MyModel::deleteAll(['id' => $deletedIDs]);
        }

        // Guardar
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

Vista ejemplo (lista para copiar)

Archivo recomendado:

📂 src/views/multi-model-example.php

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

🧠 ¿Cómo funciona internamente?

ModelHelper::createMultiple():

✔ Analiza el POST
✔ Busca coincidencias por id
✔ Reconstruye modelos existentes
✔ Crea nuevos modelos para índices nuevos
✔ Ignora IDs vacíos
✔ Evita colisiones en índices
✔ Devuelve un arreglo ordenado y completamente listo para loadMultiple

Tu controller queda limpio.
Tu vista maneja filas dinámicas sin romper nada.
Tu backend controla automáticamente qué se borra y qué se crea.

Productividad +100.

🧩 Estructura del proyecto
yii2-modelhelper/
│
├── src/
│   ├── ModelHelper.php
│   └── views/multi-model-example.php
│
├── README.md
├── LICENSE
├── composer.json

🛠 Requisitos

PHP 5.6+ / 7.x / 8.x

Yii2 Framework

Composer

Probado en proyectos legacy + proyectos modernos.

🤝 Contribuciones

¡Pull Requests bienvenidos!
Reporta issues, mejoras, ejemplos, integraciones o tests.

📄 Licencia

MIT.
Puedes usarlo en proyectos personales, comerciales, privados o open source.
