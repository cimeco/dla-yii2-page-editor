<?php

namespace quoma\pageeditor\controllers;

use Yii;
use quoma\pageeditor\models\BoxClass;
use quoma\pageeditor\models\search\BoxClassSearch;
use quoma\core\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use quoma\pageeditor\components\editor\BoxFactory;
use common\behaviors\StatusBehavior;
use yii\helpers\ArrayHelper;

/**
 * BoxClassController implements the CRUD actions for BoxClass model.
 */
class BoxClassController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            
        ]);
    }

    /**
     * Lists all BoxClass models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BoxClassSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BoxClass model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $box = new $model->class;
        
        return $this->render('view', [
            'model' => $model,
            'box' => $box
        ]);
    }

    /**
     * Creates a new BoxClass model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdateBoxes()
    {
        BoxFactory::getInstance()->updateBoxes();
        $this->redirect(['index']);

    }

    /**
     * Updates an existing BoxClass model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->box_class_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'box' => new $model->class
            ]);
        }
    }

    /**
     * Finds the BoxClass model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BoxClass the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BoxClass::findOne($id)) !== null) {
            
            if(!class_exists($model->class)){
                throw new \yii\web\HttpException(500, 'Class '.$model->class.' not found.');
            }
            
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}