use jiuly256\modelhelper\ModelHelper;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Acción genérica para manejar múltiples instancias de un modelo.
 *
 * @param string $modelClass   Nombre de la clase del modelo, ej: RestriccionesInstrumental::class
 * @param array  $findCondition Condición para cargar los modelos existentes desde DB
 * @param callable|null $beforeSave Callback opcional que se ejecuta antes de cada save($model)
 * @param callable|null $afterSave  Callback opcional que se ejecuta después de cada save($model)
 *
 * Ejemplo de uso:
 * return $this->actionMultiModel(
 *      RestriccionesInstrumental::class,
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
    // 1. Cargar modelos existentes
    $modelos = $modelClass::findAll($findCondition);

    // Si no hay ninguno, al menos uno vacío
    if (empty($modelos)) {
        $modelos = [new $modelClass($findCondition)];
    }

    // 2. Procesar POST
    if (Yii::$app->request->isPost) {

        // IDs anteriores en DB
        $oldIDs = ArrayHelper::map($modelos, 'id', 'id');

        // Crear objetos según lo enviado por POST (nuevo + existentes)
        $modelos = ModelHelper::createMultiple($modelClass, $modelos);

        // Cargar POST en los modelos
        Model::loadMultiple($modelos, Yii::$app->request->post());

        // Detectar IDs nuevos
        $newIDs = ArrayHelper::map($modelos, 'id', 'id');

        // Determinar cuáles eliminar
        $deletedIDs = array_diff($oldIDs, $newIDs);

        if (!empty($deletedIDs)) {
            $modelClass::deleteAll(['id' => $deletedIDs]);
        }

        // Validar
        if (Model::validateMultiple($modelos)) {

            foreach ($modelos as $m) {

                // Callback opcional (para completar foreign keys, etc)
                if ($beforeSave !== null) {
                    call_user_func($beforeSave, $m);
                }

                $m->save(false);

                if ($afterSave !== null) {
                    call_user_func($afterSave, $m);
                }
            }

            Yii::$app->session->setFlash('success', 'Registros guardados correctamente.');
            return $this->refresh();
        }

        // Si no valida, aquí podrías hacer debug
    }

    // 3. Renderizar vista
    return $this->render('multiple', [
        'modelos' => $modelos
    ]);
}
