<?php

namespace jiuly256\modelhelper\controllers;

use Yii;
use yii\web\Controller;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use jiuly256\modelhelper\ModelHelper;

/**
 * Ejemplo de controlador para manejar múltiples modelos dinámicos
 * utilizando la clase ModelHelper.
 *
 * Este archivo NO debe usarse directamente en producción.
 * Es un ejemplo educativo que muestra buenas prácticas y patrones
 * para trabajar con formularios dinámicos (multi-registros).
 *
 * Cómo funciona:
 * - Carga modelos existentes
 * - Crea nuevos modelos según los registros enviados por POST
 * - Identifica elementos eliminados y los borra
 * - Valida y guarda todos en una sola operación elegante
 *
 * El objetivo es que cualquier desarrollador pueda copiar y adaptar
 * este método a un controlador real en su propio proyecto.
 */
class MultiModelControllerExample extends Controller
{
    /**
     * Acción DEMO completamente genérica para manejar múltiples instancias
     * de un modelo en un formulario dinámico.
     *
     * Parámetros:
     * @param string $modelClass   Clase del modelo (ejemplo: \common\models\Item::class)
     * @param array  $findCondition Condición para buscar registros existentes
     * @param callable|null $beforeSave Callback antes de guardar cada modelo
     * @param callable|null $afterSave  Callback después de guardar cada modelo
     *
     * Uso sugerido en proyectos reales:
     *
     * return $this->actionMultiModel(
     *      \common\models\RestriccionesInstrumental::class,
     *      ['cirugia_id' => $cirugia_id],
     *      function($m) use ($cirugia_id) {
     *          $m->cirugia_id = $cirugia_id;
     *      }
     * );
     */
    public function actionMultiModel(
        string $modelClass,
        array $findCondition = [],
        callable $beforeSave = null,
        callable $afterSave = null
    ) {

        // 1. Cargar modelos existentes de la base de datos
        $modelos = $modelClass::findAll($findCondition);

        // Si no existen, crear al menos un registro vacío
        if (empty($modelos)) {
            $modelos = [new $modelClass($findCondition)];
        }

        // 2. Procesar la solicitud POST
        if (Yii::$app->request->isPost) {

            // IDs existentes antes del POST
            $oldIDs = ArrayHelper::map($modelos, 'id', 'id');

            // Crear instancias según los datos enviados
            $modelos = ModelHelper::createMultiple($modelClass, $modelos);

            // Cargar datos en los modelos
            Model::loadMultiple($modelos, Yii::$app->request->post());

            // Determinar IDs recibidos en POST
            $newIDs = ArrayHelper::map($modelos, 'id', 'id');

            // Identificar eliminados
            $deletedIDs = array_diff($oldIDs, $newIDs);

            if (!empty($deletedIDs)) {
                $modelClass::deleteAll(['id' => $deletedIDs]);
            }

            // Validación masiva elegante
            if (Model::validateMultiple($modelos)) {

                // Guardar uno por uno
                foreach ($modelos as $m) {

                    // Callback de modificación previa (asignar foreign keys, defaults, etc.)
                    if ($beforeSave !== null) {
                        call_user_func($beforeSave, $m);
                    }

                    $m->save(false);

                    // Callback post-guardado opcional
                    if ($afterSave !== null) {
                        call_user_func($afterSave, $m);
                    }
                }

                Yii::$app->session->setFlash('success', 'Registros guardados correctamente.');
                return $this->refresh();
            }
        }

        // 3. Render genérico
        return $this->render('@vendor/jiuly256/yii2-modelhelper/views/multi-model-example', [
            'modelos' => $modelos
        ]);
    }
}
