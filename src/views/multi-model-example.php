<?php
/**
 * Vista DEMO para uso del helper de multi-modelos.
 *
 * Esta vista sirve como ejemplo educativo para que un desarrollador
 * pueda copiarla y adaptarla a sus propios modelos.
 *
 * Renderiza dinámicamente todas las instancias recibidas en $modelos.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\base\Model[] $modelos */

$this->title = "Ejemplo de Multi-Model";
?>
<div class="multi-model-demo">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">
        Este es un formulario DEMO generado con <b>ModelHelper</b>.  
        Modifícalo sin miedo para adaptarlo a tus entidades reales.
    </p>

    <?php $form = ActiveForm::begin(); ?>

    <table class="table table-bordered" id="multi-model-table">
        <thead>
            <tr>
                <th style="width: 40%">Atributos del modelo</th>
                <th style="width: 10%">Eliminar</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($modelos as $i => $model): ?>
            <tr>
                <td>
                    <?php
                    // Render dinámico de todos los atributos "seguros"
                    foreach ($model->safeAttributes() as $attribute) {
                        echo $form->field($model, "[$i]{$attribute}")->textInput();
                    }

                    // Campo oculto para el ID
                    if ($model->hasAttribute('id')) {
                        echo $form->field($model, "[$i]id")->hiddenInput()->label(false);
                    }
                    ?>
                </td>

                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

    <button type="button" id="add-row" class="btn btn-success btn-sm">
        <i class="fa fa-plus"></i> Agregar fila
    </button>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
/**
 * JS genérico:
 * - Clona dinámicamente la última fila
 * - Limpia los inputs
 * - Ajusta los índices correctamente
 */
$this->registerJs(<<<JS

function renumerarFilas() {
    $('#multi-model-table tbody tr').each(function(index) {
        $(this).find('[name]').each(function() {
            let nuevoName = $(this).attr('name').replace(/\\[\\d+\\]/, '[' + index + ']');
            $(this).attr('name', nuevoName);
        });
    });
}

$('#add-row').on('click', function() {
    let lastRow = $('#multi-model-table tbody tr:last');
    let newRow = lastRow.clone();

    // Limpiar inputs
    newRow.find('input').each(function() {
        if ($(this).attr('type') === 'hidden' && $(this).attr('name').includes('[id]')) {
            $(this).val(''); // ID vacío = nuevo registro
        } else {
            $(this).val('');
        }
    });

    $('#multi-model-table tbody').append(newRow);
    renumerarFilas();
});

$(document).on('click', '.remove-row', function() {
    $(this).closest('tr').remove();
    renumerarFilas();
});

JS
);
?>
