<?php

namespace quoma\pageeditor\controllers;

use quoma\core\helpers\StringHelper;
use Yii;
use quoma\pageeditor\models\Theme;
use quoma\pageeditor\models\search\ThemeSearch;
use quoma\core\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ThemeController implements the CRUD actions for Theme model.
 */
class ThemeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return parent::behaviors();
    }

    /**
     * Lists all Theme models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ThemeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Theme model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Theme model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Theme();
        $pull = new \quoma\pageeditor\models\forms\PullForm();
        $output = [];

        if ($model->load(Yii::$app->request->post())) {
            
            if($model->repo && $pull->load(Yii::$app->request->post()) && $pull->validate()){
                //Clone
                $folder = StringHelper::systemName($model->name);
                $result = $pull->cloneRepo($model->repo, $model->getThemesPath(), $folder);
                $output = $result['output'];
                
                //En caso de exito
                if($result['status'] == 'success'){
                    
                    $model->basePath = $model->getThemesPath().DIRECTORY_SEPARATOR.$folder;
                    
                    if($model->save()){
                        $this->upload($model);
                        return $this->redirect(['view', 'id' => $model->theme_id]);
                    }
                }
            }else{
            
                $model->zip = UploadedFile::getInstance($model, 'zip');

                if($model->save()){
                    $this->upload($model);
                    return $this->redirect(['view', 'id' => $model->theme_id]);
                }
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'pull' => $pull,
            'output' => $output
        ]);
        
    }
    
    private function upload($model)
    {
        $attrs = [
            'basePath',
            'baseUrl',
        ];
        
        if ($model->upload() && $model->save(false, $attrs)) {
            return true;
        } 
            
        if(empty($model->repo) && empty($model->folder) && empty($model->basePath)){
            throw new \yii\web\HttpException(500, 'The zip could not be uploaded.');
        }
        
        return false;
    }

    /**
     * Updates an existing Theme model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $pull = new \quoma\pageeditor\models\forms\PullForm();
        $output = [];

        if ($model->load(Yii::$app->request->post())) {
            
            if($model->repo && $pull->load(Yii::$app->request->post()) && $pull->validate()){
                //Clone
                $folder = StringHelper::systemName($model->name);
                $result = $pull->cloneRepo($model->repo, $model->getThemesPath(), $folder);
                $output = $result['output'];
                
                //En caso de exito
                if($result['status'] == 'success'){
                    
                    $model->basePath = $model->getThemesPath().DIRECTORY_SEPARATOR.$folder;
                    
                    if($model->save()){
                        $this->upload($model);
                        return $this->redirect(['view', 'id' => $model->theme_id]);
                    }
                }
            }else{
            
                $model->zip = UploadedFile::getInstance($model, 'zip');

                if($model->save()){
                    $this->upload($model);
                    return $this->redirect(['view', 'id' => $model->theme_id]);
                }
            }
        }
        
        return $this->render('update', [
            'model' => $model,
            'pull' => $pull,
            'output' => $output
        ]);
    }
    
    /**
     * Deletes an existing Theme model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionPull($id)
    {
        
        $model = $this->findModel($id);
        
        $pull = new \quoma\pageeditor\models\forms\PullForm();
        
        $output = [];
        
        if($pull->load(Yii::$app->request->post())){
            
            if($pull->validate()){
                $url = $model->gitUrl;

                $output[] = 'Pull from: '.$url;

                $fullUrl = $pull->getFullUrl($url);

                exec("cd $model->basePath && git pull $fullUrl 2>&1", $output);
            }
        }else{
            $pull->username = $model->gitUsername ? $model->gitUsername : null;
        }
        
        return $this->render('pull', ['model' => $model, 'pull' => $pull, 'output' => $output]);
        
    }

    /**
     * Finds the Theme model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Theme the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Theme::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
