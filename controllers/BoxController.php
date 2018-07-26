<?php

namespace quoma\pageeditor\controllers;

use Yii;
use quoma\pageeditor\models\Box;
use quoma\core\web\Controller;
use yii\filters\VerbFilter;
use quoma\pageeditor\components\editor\BoxFactory;
use yii\web\NotFoundHttpException;
use yii\web\View;

/**
 * BoxController implements actions for Box model.
 */
class BoxController extends Controller
{
    public $enableCsrfValidation = false;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'configure' => ['post'],
                ],
            ],
        ]);
    }
    
    /**
     * Permite configurar un Box.
     * @throws \yii\web\HttpException
     */
    public function actionConfigure()
    {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $box = $this->findModel( Yii::$app->request->post('box_id') );
        
        $session = Yii::$app->request->post('session');
        if(!$session){
            throw new \yii\web\HttpException(500, 'Not session.');
        }
        
        return $this->configure($box, $session);
        
    }
    
    private function configure($box, $session)
    {
        $status = 'success';
        
        $box->setSession( $session );

        if($box->load(Yii::$app->request->post())){
            
            if(!$box->save()){
                $status = 'error';
            }

        }
        
        return [
            'status' => $status,
            'box' => $box,
            'errors' => $box->getErrors(),
            'form' => $box->renderForm(),
            'view' => $box->editable(),
        ];
    }
    
    /**
     * Permite crear un box.
     * @return type
     */
    public function actionCreate()
    {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $class = Yii::$app->request->post('class');
        
        if(!Yii::$app->request->post('session')){
            throw new \yii\web\HttpException(500, 'Not session.');
        }
        $session = Yii::$app->request->post('session');
        
        if(is_subclass_of($class, Box::className())){
            
            $box = BoxFactory::getInstance()->createBox($class);
            
            if($box){
                return $this->configure($box, $session);
            }
            
            return [
                'status' => 'error',
                'errors' => 'Box has not been saved.',
            ];
        }
        
        return [
            'status' => 'error',
            'errors' => [
                'Invalid box class.'
            ],
        ];
        
    }
    
    public function actionDelete($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->findModel($id)->delete();
        
        return [
            'status' => 'success'
        ];
    }
    
    public function actionSearch()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $col_id = Yii::$app->request->post('col');
        $col = \quoma\pageeditor\models\Col::findOne($col_id);
        
        $boxes = BoxFactory::getInstance()->getBoxes($col->getTags());
        
        return [
            'status' => 'success',
            'list' => $this->renderPartial('index', ['boxes' => $boxes]),
        ];
        
    }
    
    /**
     * Finds the Box model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Box the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Box::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
