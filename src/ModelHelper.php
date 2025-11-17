<?php
namespace jiuly256\modelhelper;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * ModelHelper
 *
 * Helper robusto y reutilizable para manejar múltiples modelos dinámicos
 * en formularios tabulares o repetitivos.
 *
 * Evita errores con IDs vacíos, combina modelos existentes,
 * y crea nuevas instancias basadas en POST.
 */
class ModelHelper
{
    /**
     * Crea múltiples instancias de un modelo basándose en los modelos existentes
     * y los datos recibidos vía POST.
     *
     * @param string $modelClass  Clase del modelo.
     * @param array  $multipleModels Modelos ya existentes (opcional).
     * @return array Lista de modelos creados.
     */
    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model      = new $modelClass;
        $formName   = $model->formName();
        $post       = Yii::$app->request->post($formName);

        $models = [];

        // Indexar modelos existentes usando sus IDs válidos
        $indexed = [];

        if (!empty($multipleModels)) {
            foreach ($multipleModels as $m) {
                if (!empty($m->id)) {
                    $indexed[$m->id] = $m;
                }
            }
        }

        // Crear modelos según POST
        if ($post && is_array($post)) {
            foreach ($post as $item) {

                // Si el POST tiene ID y existe en el arreglo indexado → es un modelo existente
                if (isset($item['id']) &&
                    !empty($item['id']) &&
                    isset($indexed[$item['id']])
                ) {
                    $models[] = $indexed[$item['id']];
                } else {
                    // Modelo nuevo
                    $models[] = new $modelClass;
                }
            }
        }

        return $models;
    }
}
