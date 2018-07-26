<?php

namespace quoma\pageeditor\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "col".
 *
 * @property integer $col_id
 * @property string $slug
 * @property string $uid
 * @property integer $order
 * @property string $devices
 * @property string $class
 * @property string $style
 * @property string $tags
 * @property integer $timestamp
 */
class Col extends \quoma\core\db\ActiveRecord
{
    private $_maxWidth;
    private $_lazy = false;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'col';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order'], 'integer'],
            [['slug', 'class'], 'string', 'max' => 45],
            [['uid', 'style'], 'string', 'max' => 255],
            [['devices'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'col_id' => Yii::t('app', 'Col ID'),
            'slug' => Yii::t('app', 'Slug'),
            'uid' => Yii::t('app', 'Uid'),
            'order' => Yii::t('app', 'Order'),
            'devices' => Yii::t('app', 'Devices'),
            'class' => Yii::t('app', 'Class'),
            'style' => Yii::t('app', 'Style'),
        ];
    }
    
    /**
     * @return type
     */
    public function behaviors() {
        
        return [
            'unix_timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    \common\components\db\ActiveRecord::EVENT_BEFORE_INSERT => ['timestamp'],
                    \common\components\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['timestamp'],
                ],
            ],
        ];
    }
    
    /**
     * Devuelve una Col en función de los parámetros slug y variations. Si la 
     * col no existe, genera una nueva.
     * @param string $slug
     * @param array $variations
     * @param array $options
     * @return \quoma\pageeditor\models\Col
     * @throws \yii\web\HttpException
     */
    public static function get($slug, $variations = [], $options = [])
    {
        $uid = '';
        if(!empty($variations) && is_array($variations)){
            $uid = [];
            if (is_array($variations)) {
                foreach ($variations as $value) {
                    $uid[] = $value;
                }
            }
            $uid = serialize($uid);

            if(strlen($uid) > 255){
                $uid = hash('sha512', $uid);
            }
        }
        
        $col = self::find()->where([
            'slug' => $slug,
            'uid' => $uid
        ])->orderBy(['timestamp' => SORT_DESC])->one();
        
        if(!$col){
            $col = new Col();
            $col->slug = $slug;
            $col->uid = $uid;
            
            //Col tags
            if(isset($variations['tags'])){
                $col->setTags($variations['tags']);
            }
            
            if(!$col->save()){
                throw new \yii\web\HttpException(500, 'Col could not be saved.');
            }
        }
        
        //Option maxWidth 
        if(isset($options['maxWidth'])){
            $col->_maxWidth = $options['maxWidth'];
        }
        
        //Option maxWidth 
        if(isset($options['lazy'])){
            $col->_lazy = (boolean)$options['lazy'];
        }
        
        return $col;
    }
    
    public function getMaxWidth()
    {
        return $this->_maxWidth ? $this->_maxWidth : false;
    }
    
    public function setTags($tags = [])
    {
        $this->_tags = serialize($tags);
    }
    public function getTags()
    {
        $tags = unserialize($this->_tags);
        if(!is_array($tags)){
            $tags = [];
        }
        return $tags;
    }
    
    public function __toString() 
    {
        return $this->render();
    }
    
    public function getBoxes()
    {
        //Realizamos un join, dado que necesitamos ordenar por un campo de la tabla usada para junction
        $query = Box::find();
        
        $query->multiple = true;
        $query->innerJoin('col_has_boxes', 'col_has_boxes.box_id = box.box_id');
        $query->andWhere(['col_has_boxes.col_id' => $this->col_id]);
        $query->orderBy(['col_has_boxes.col_has_boxes_id' => SORT_ASC]);
        
        return $query;
        
        //return $this->hasMany(Box::className(), ['box_id' => 'box_id'])->viaTable('col_has_boxes', ['col_id' => 'col_id']);
    }
    
    public function render($editable = true)
    {

        if($editable){
            //JS para inicializar el editor
            Yii::$app->view->registerJs('var _EL = new function(){
                this.init = function(json){
                    for(s in json.js){
                        document.write(\'<scr\'+\'ipt src="\'+json.js[s]+\'"></sc\'+\'ript>\');
                    }
                    for(s in json.css){
                        document.write(\'<link href="\'+json.css[s]+\'" rel="stylesheet">\');
                    }
                    this.baseUrl = json.baseUrl;
                    this.userPermissions = json.userPermissions;
                };
            }', \yii\web\View::POS_BEGIN, '_page_editor_loader_');

            //JS del editor (solo se cargaran si el usuario esta logeado en backend
            Yii::$app->view->registerJsFile( Yii::$app->backendUrlManager->createAbsoluteUrl( ['/page-editor/col/assets'], '' ), 
                [
                    'position' => \yii\web\View::POS_END,
                    'depends' => [
                        \yii\web\YiiAsset::className()
                    ]
                ], 
                '_page_editor_assets_loader_');
        }
        
        $view = '';
        
        //Si se debe cargar de forma perezosa
        if($this->_lazy){
            
            \quoma\pageeditor\assets\LazyAsset::register( Yii::$app->view );
            Yii::$app->view->registerJs('$("[data-lz]").lz(200);', \yii\web\View::POS_READY, '_page_editor_lz_');
            
        }else{
            foreach($this->boxes as $box){
                
                //Si un box falla, no debe generar error. Solo no debe mostrarse
                try{
                    if($editable){
                        $boxView = $box->editable();
                    }else{
                        $boxView = $box->run();
                    }
                } catch (\Throwable $e) {
                    $boxView = '';
                } catch (\Exception $e) {
                    $boxView = '';
                }
                    
                if($boxView){
                    $view .= $boxView;
                }
            }
        }
        
        return Html::tag('div', $view, [
            'data-col' => $this->col_id, 
            'data-mw' => $this->maxWidth, 
            'data-lz' => $this->_lazy ? \yii\helpers\Url::to(['pe/col/lz', 'c' => $this->col_id]) : false
        ]);
        
    }
    
    public function lazy()
    {
        $view = '';
        
        foreach($this->boxes as $box){
            $boxView = @$box->editable();
            if($boxView){
                $view .= $boxView;
            }
        }
        
        return $view;
    }
    
    public function cloneCol()
    {
        $newCol = new Col;
        $newCol->slug = $this->slug;
        $newCol->uid = $this->uid;
        $newCol->devices = $this->devices;
        $newCol->class = $this->class;
        $newCol->style = $this->style;
        $newCol->_tags = $this->_tags;
        
        return $newCol;
    }
    
    /**
     * La relación getBoxes se declaró utilizando join, por lo que este método
     * no funciona para esta relación
     * @param string $name the case sensitive name of the relationship, e.g. `orders` for a relation defined via `getOrders()` method.
     * @param ActiveRecordInterface $model the model to be linked with the current one.
     * @param array $extraColumns additional column values to be saved into the junction table.
     * This parameter is only meaningful for a relationship involving a junction table
     * (i.e., a relation set with [[ActiveRelationTrait::via()]] or [[ActiveQuery::viaTable()]].)
     * @throws InvalidCallException if the method is unable to link two models. 
    */
    public function link($name, $model, $extraColumns = array()) {
        
        if($name == 'boxes'){
            $chb = new ColHasBoxes();
            $chb->box_id = $model->box_id;
            $chb->col_id = $this->col_id;
            $chb->save();
        }else{
            parent::link($name, $model, $extraColumns);
        }
    }
    
    public function addBox($session, $box)
    {
        //Si hay una revisión, clonamos el Box con los datos de la revisión
        if($box->hasSession($session)){
            $newBox = $box->cloneSession($session);
            $this->link('boxes', $newBox);
        }else{
            $this->link('boxes', $box);
        }
    }
    
    /**
     * Devuelve el id de la ultima version publicada de la columna
     * @return int
     */
    public function getLastVersion()
    {
        $last = static::find()->where(['uid' => $this->uid, 'slug' => $this->slug])->orderBy(['timestamp' => SORT_DESC])->one();
        return $last->col_id;
    }
}