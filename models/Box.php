<?php

namespace quoma\pageeditor\models;

use Yii;
use yii\helpers\Html;
use quoma\pageeditor\components\editor\BoxFactory;

/**
 * This is the model class for table "box".
 * 
 * Temporal Object Pattern
 *
 * @property integer $box_id
 * @property string $box_class
 */
class Box extends \yii\db\ActiveRecord implements \yii\base\ViewContextInterface
{

    public $boxAttributes = [];
    
    private $_view;
    
    public $buttonsTemplate = '{refresh} {configure} {delete}';
    
    private $_session;
    
    private $_revision = false;
    
    //Los usuarios sin permisos especiales no puede realizar las siguientes acciones desde el editor:
    public $boxSortable = true;
    public $boxEditable = true;
    public $boxDeletable = true;
    
    /**
     * Esta función asigna una sesión para editar el contenido y la configuración
     * del box. Si ya existe una revisión activa, la nueva revisión clona su
     * configuración. Si no existe una revisión activa, se crea una nueva con la
     * configuración por defecto.
     * @param type $session
     * @return type
     */
    public function setSession($session)
    {
        $this->_session = $session;
        
        $revision = BoxRevision::find()->where(['box_id' => $this->box_id])->andWhere(['or', ['session' => $session], ['active' => true]])->orderBy(['timestamp' => SORT_DESC])->one();
        $this->_revision = $revision;

        if(!$revision){
            $newRevision = new BoxRevision;
            $newRevision->box_id = $this->box_id;
            $newRevision->active = false;
            $newRevision->session = $session;
            $newRevision->save();
            
            $this->_revision = $newRevision;
        }elseif($revision->active){
            $newRevision = $this->cloneRevision($session, $revision);
            $newRevision->active = 0;
            
            $this->_revision = $newRevision;
        }
        
        return $this->_revision;
    }
    
    public function getSession()
    {
        return $this->_session;
    }
    
    public function getRevision()
    {
        
        if($this->_revision === false){
            //Si no ha sido seteada por el método setSession
            $this->_revision = $this->getActiveRevision();
        }
        return $this->_revision;
    }
    
    public function getActiveRevision()
    {
        $revision = BoxRevision::find()->where(['box_id' => $this->box_id, 'active' => true])->orderBy(['timestamp' => SORT_DESC])->one();
        return $revision;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'box';
    }
    
    public static function instantiate($row)
    {
        if($row['box_class'] && class_exists($row['box_class'])){
            return new $row['box_class'];
        }
        
        return new self;
    }
    
    public function init()
    {
        parent::init();
        $this->box_class = $this->className();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['box_class'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'box_id' => Yii::t('app', 'Box ID'),
            'box_class' => Yii::t('app', 'Box Class'),
        ];
    }
    
    public function run()
    {
        if(class_exists($this->box_class)){
            $class = new $this->box_class;
            return $class->run();
        }
    }
    
    /**
     * Returns the view object that can be used to render views or view files.
     * The [[render()]] and [[renderFile()]] methods will use
     * this view object to implement the actual view rendering.
     * If not set, it will default to the "view" application component.
     * @return \yii\web\View the view object that can be used to render views or view files.
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = Yii::$app->getView();
        }
        return $this->_view;
    }
    
    /**
     * Sets the view object to be used by this widget.
     * @param View $view the view object that can be used to render views or view files.
     */
    public function setView($view)
    {
        $this->_view = $view;
    }

    /**
     * 
     * @return string
     */
    public function getViewPath() 
    {
        $refClass = new \ReflectionClass($this->box_class);
        return dirname($refClass->getFileName()).DIRECTORY_SEPARATOR.'views';
    }
    
    /**
     * Renders a view.
     * The view to be rendered can be specified in one of the following formats:
     *
     * - path alias (e.g. "@app/views/site/index");
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
     *   active module.
     * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
     *
     * If the view name does not contain a file extension, it will use the default one `.php`.
     *
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file does not exist.
     */
    public function render($view, $params = [])
    {
        //En prod, no debe lanzar error
        try{
            return $this->getView()->render($view, $params, $this);
        } catch(\Exception $e) {
            if(YII_ENV == 'dev'){
                throw $e;
            }
            return '';
        } catch(\Throwable $e) {
            if(YII_ENV == 'dev'){
                throw $e;
            }
            return '';
        }
    }
    
    /**
     * Necesitamos filtrar los scripts y css que ya se encuentran cargados en la 
     * vista.
     * @param string $view
     * @param array $params
     * @return string
     */
    public function renderAjax($view, $params = [])
    {
        
        Yii::$app->assetManager->baseUrl = Yii::$app->backendUrlManager->baseUrl.'/assets';
        
        //Fix error
        if (class_exists('yii\debug\Module')) {
            $this->getView()->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
        }
        
        $this->getView()->on(\yii\web\View::EVENT_END_PAGE, function($event){ 
            
            $positions = $event->sender->jsFiles;
            foreach($positions as $position => $js){
                $new = array_filter($js, 
                function($item){
                    return !preg_match('/jquery(\.min)?\.js|'
                            . 'jquery.ui(\.min)?\.js|'
                            . 'select2(.*)?\.js|' //Select2 debe ser precargado; si se carga mediante ajax, falla
                            . 'yii(\.min)?\.js|'
                            . 'yii\.validation(\.min)?\.js/i', $item);
                });
                $event->sender->jsFiles[$position] = $new;
            }
            
            $event->sender->cssFiles = array_filter($event->sender->cssFiles, 
                function($item){
                    return !preg_match('/bootstrap(\.min)?\.css|'
                            . 'jquery.ui(\.min)?\.css|'
                            . 'select2(.*)?\.css/i', $item);
                });
            
        });
        
        return $this->getView()->renderAjax($view, $params, $this);
    }
    
    public function renderForm()
    {
        return $this->renderAjax('form', ['model' => $this]);
    }
    
    /**
     * Relación para modelos relacionados al box. Se debe pasar como parámetro
     * el nombre de la clase asociada que se busca.
     * @param string $class
     * @return \yii\db\ActiveQuery
     */
    public function getModels($class)
    {
        return $this->revision->getModels($class);
    }
    
    public function setModels($models)
    {
        $this->revision->setModels($models);
    }
    
    /**
     * Box attrs relation
     * @return ActiveQuery
     */
    public function getBoxAttrs()
    {
        return $this->revision->getBoxAttrs();
    }
    
    public function setBoxAttr($attr, $value)
    {
        if(empty($this->session)){
            throw new \yii\web\HttpException(500, 'Not session was defined.');
        }
        
        $this->revision->setBoxAttr($attr, $value);
    }
    
    private $_attrs = [];
    public function __get($name) 
    {
        //Condición necesaria para que funcione:
        if(empty($this->_attrs) && $name !== 'box_id' && $name !== 'revision'){
            $this->initAttrs();
        }
        
        if(isset($this->boxAttributes[$name]) && array_key_exists($name, $this->_attrs)){
            return $this->_attrs[$name];
        }elseif(isset($this->boxAttributes[$name])){
            return $this->boxAttributes[$name];
        }
        return parent::__get($name);
    }
    
    public function __set($name, $value) 
    {
        if(isset($this->boxAttributes[$name])){
            $this->_attrs[$name] = $value;
        }else{
            parent::__set($name, $value);
        }
    }
    
    /**
     * Inicializa los atributos de la caja en función de la revisión actual
     */
    public function initAttrs()
    {
        if($this->revision){
            $attrs = $this->revision->boxAttrs;
            foreach($attrs as $attr){
                $this->_attrs[$attr->attr] = $attr->value;
            }
        }
    }
    
    public function beforeSave($insert) 
    {
        
        if(!$insert && !$this->revision->save()){
            return false;
        }
        
        foreach($this->_attrs as $attr => $value){
            $this->revision->setBoxAttr($attr, $value);
        }
        
        return parent::beforeSave($insert);
    }
    
    public function afterFind() 
    {
        parent::afterFind();
    }
    
    
    public function getMode()
    {
        $options = array_map(function($option){ return $option ? '1' : '0'; },[
            $this->boxSortable,
            $this->boxEditable,
            $this->boxDeletable
        ]);

        $bin = implode('', $options);
        
        $mode = bindec($bin);
        return $mode;
    }            
    
    /**
     * 
     */
    public function editable()
    {
        
        $view = $this->run();
        
        $refClass = new \ReflectionClass(self::className());

        $decorated = Html::tag('div', $view, [
            'data-id' => $this->box_id, 
            'data-box' => $refClass->getShortName(), 
            'data-mode' => $this->getMode()
        ]);

        return $decorated;

    }
    
    /**
     * Botones por defecto al mostrar el Box
     * @return type
     */
    private function defaultButtons()
    {
        $configAction = Yii::$app->backendUrlManager->createUrl(['page-editor/box/configure', 'id' => $this->box_id]);
        $reloadAction = Yii::$app->backendUrlManager->createUrl(['page-editor/box/delete', 'id' => $this->box_id]);
        
        return [
            //'refresh' => Html::a('<span class="glyphicon glyphicon-refresh"></span>', '#', ['data-refresh' => true, 'data-action' => $reloadAction]),
            'configure' => Html::a('<span class="glyphicon glyphicon-pencil"></span>', '#', ['data-configure' => true, 'data-action' => $configAction]),
            'delete' => Html::a('<span class="glyphicon glyphicon-trash"></span>', '#', ['data-delete' => true]),
        ];
    }
    
    public function getBoxName()
    {
        $class = get_called_class();
        throw new \yii\web\HttpException(500, 'Box name not declared (getBoxName()). '.$class);
    }
    
    public function getBoxDescription()
    {
        return '';
    }
    
    public function getBoxTags()
    {
        return [];
    }
    
    /**
     * Devuelve true si un Box ha sido editado con la sesión correspondiente
     * al parámetro $session
     * @param type $session
     * @return type
     */
    public function hasSession($session)
    {
        return BoxRevision::find()->where(['box_id' => $this->box_id, 'session' => $session])->exists();
    }
    
    /**
     * Clona la sesión recibida como param $session
     * @param type $session
     */
    public function cloneSession($session)
    {
        $this->setSession($session);
        
        $box = BoxFactory::getInstance()->createBox($this->box_class);
        $box->cloneRevision($session, $this->revision);
        
        return $box;
        
    }
    
    /**
     * Clona la revisión recibida por el parámetro $revision correspondiente a 
     * $session y la activa.
     * @param string $session
     * @param BoxRevision $revision
     * @throws \yii\web\HttpException
     */
    private function cloneRevision($session, $revision)
    {
        $newRevision = new BoxRevision;
        $newRevision->box_id = $this->box_id;
        $newRevision->active = true;
        $newRevision->session = $session;
        
        if(!$newRevision->save()){
            throw new \yii\web\HttpException(500, 'Revision not saved.');
        }
        
        //Attrs
        $attrs = $revision->boxAttrs;
        foreach($attrs as $attr){
            $newRevision->setBoxAttr($attr->attr, $attr->value);
        }

        //Models
        $boxRModels = $revision->allRelModels;
        
        foreach($boxRModels as $boxRModel){
            $brhm = new BoxRevisionHasModel;
            $brhm->box_revision_id = $newRevision->box_revision_id;
            $brhm->model_class = $boxRModel->model_class;
            $brhm->model_id = $boxRModel->model_id;
            $brhm->save();
        }
        
        return $newRevision;

    }
    
    public function fields()
    {
        $fields = [
            'box_id',
            'box_class',
            'boxEditable',
            'boxDeletable',
            'boxSortable'
        ];
        
        foreach($this->boxAttributes as $attr => $v){
            $fields[] = $attr;
        }
        
        return $fields;
    }
    
}