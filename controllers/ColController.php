<?php

namespace quoma\pageeditor\controllers;

use Yii;
use quoma\pageeditor\models\Col;
use quoma\core\web\Controller;
use yii\filters\VerbFilter;
use quoma\pageeditor\components\editor\BoxFactory;

/**
 * ColController implements actions for Col model.
 */
class ColController extends Controller
{
    public $enableCsrfValidation = false;
    
    public $freeAccessActions = ['lz','assets'];
    
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
        
    //Publica los cambios. TODO: transacción
    public function actionSave()
    {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $session = Yii::$app->request->post('session');
        $cols = Yii::$app->request->post('cols');
        
        if(!$session || empty($cols)){
            return [
                'status' => 'error',
                'errors' => 'Empty data.'
            ];
        }
        
        $transaction = Col::getDb()->beginTransaction();
        
        try {
            
            foreach($cols as $col){

                $oldCol = $this->findModel($col['id']);
                $newCol = $oldCol->cloneCol();

                if($newCol->save() && isset($col['boxes'])){
                    foreach($col['boxes'] as $order => $box){
                        $box = \quoma\pageeditor\models\Box::findOne($box);
                        $newCol->addBox($session, $box);
                    }
                }
            }
            $transaction->commit();
            
        } catch(\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch(\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        
        //La aplicación puede proporcionar un método para llamar luego de publicar una página
        $pageEditor = Yii::$app->getModule('page-editor');
        if(is_callable($pageEditor->afterPublish)){
            call_user_func($pageEditor->afterPublish, Yii::$app->request);
        }
        
        return [
            'status' => 'success',
            'col' => $newCol
        ];
        
    }
    
    /**
     * Utilizamos JSONP para cargar los assets necesarios. Esta acción arma el
     * json necesario. El callback utilizado es _EL.init
     * 
     * Esta accion valida permisos dentro de la acción para enviar una respuesta
     * vacia a los usuarios que no tienen permiso, en lugar de un 403.
     */
    public function actionAssets()
    {
        //Solo https
        if(!Yii::$app->request->isSecureConnection){
            return;
        }
        
        if(Yii::$app->user->isGuest 
                || Yii::$app->devicedetect->isMobile()){
            return;
        }
        
        //Verificamos permisos
        $route = Yii::$app->controller->getRoute();
        if(!\webvimark\modules\UserManagement\models\User::canRoute( $route )){
            return;
        }
        
        $bundle = \quoma\pageeditor\assets\EditorAsset::register(Yii::$app->view);
        $bundle->registerAssetFiles(Yii::$app->view);
        
        $this->registerCustomAssets();
        
        $bundles = [];

        //Ordenamos los js de acuerdo al orden de dependencia
        foreach($this->getView()->assetBundles as $key => $b){
            foreach ($b->depends as $depends){
                if(!isset($bundles[$depends]) && isset($this->getView()->assetBundles[$depends])){
                    $bundles[$depends] = $this->getView()->assetBundles[$depends];
                }
            }
            if(!isset($bundles[$key]) && isset($this->getView()->assetBundles[$key])){
                $bundles[$key] = $this->getView()->assetBundles[$key];
            }
        }
        
        $assets = [
            'css' => [],
            'js' => [],
        ];
        foreach($bundles as $key => $b){
            //JS
            foreach($b->js as $js){
                $assets['js'][] = str_replace('http:','', Yii::$app->urlManager->hostInfo).'/'.$b->baseUrl.'/'.$js;
            }
            //CSS
            foreach($b->css as $css){
                $assets['css'][] = str_replace('http:','', Yii::$app->urlManager->hostInfo).'/'.$b->baseUrl.'/'.$css;
            }
        }
        $data = $this->filter($assets);
        $data['baseUrl'] = \yii\helpers\Url::to(['/page-editor'], '');
        $data['userPermissions'] = $this->getUserPermissions();
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSONP;
        
        return [
            'data' => $data,
            'callback' => '_EL.init'
        ];
    }
    
    //TODO: independizar de modulo webvimark
    /**
     * Devuelve los permisos del usuario respecto a los boxes.
     * @return int
     */
    private function getUserPermissions()
    {
        
        $options = array_map(function($option){ return $option ? '1' : '0'; },[
            \webvimark\modules\UserManagement\models\User::hasPermission('canSortAllBoxes'),
            \webvimark\modules\UserManagement\models\User::hasPermission('canEditAllBoxes'),
            \webvimark\modules\UserManagement\models\User::hasPermission('canDeleteAllBoxes'),
        ]);

        $bin = implode('', $options);
        
        $mode = bindec($bin);
        return $mode;
    }
    
    //TODO: CACHE!!
    public function actionLz($c)
    {
        $col = Col::findOne($c);
        return $col->lazy();
    }
    
    /**
     * Registra assets enumerados en la propiedad editorAsset. El desarrollador
     * puede añadir aquí los assets que necesite cargar cuando se carga el editor.
     */
    private function registerCustomAssets()
    {
        $module = \quoma\pageeditor\PageEditorModule::getInstance();
        
        foreach($module->editorAssets as $assetsClass){
            $assets = Yii::createObject($assetsClass);
            $assets->register(Yii::$app->view);
        }
    }
    
    /**
     * Filtra los js y css ya cargados en la vista. TODO: parametrizar
     * @param type $data
     * @return type
     */
    private function filter($data)
    {
        $filtered = [
            'js' => [],
            'css' => []
        ];
        
        $filtered['js'] = array_filter(
            $data['js'], 
            function($item){
                return !preg_match('/jquery(\.min)?\.js|yii(\.min)?\.js|yii\.validation(\.min)?\.js/i', $item);
            }
        );

        $filtered['css'] = array_filter(
            $data['css'], 
            function($item){
                return !preg_match('/bootstrap(\.min)?\.css/i', $item);
            }
        );
            
        return $filtered;
    }
    
    /**
     * Devuelve el timestamp de una col
     * @param type $col
     * @return type
     */
    public function actionCheckVersion()
    {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $col = Col::findOne( Yii::$app->request->post('id') );
        
        if($col){
            return [
                'status' => 'success',
                'last' => $col->lastVersion
            ];
        }
        
        return [
            'status' => 'error',
            'error' => 'Col not found.'
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
        if (($model = Col::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
