<?php

namespace quoma\pageeditor\models;

use Yii;

/**
 * This is the model class for table "box_revision".
 *
 * @property integer $box_revision_id
 * @property integer $box_id
 * @property integer $timestamp
 * @property boolean $active
 * @property string $session
 * @property string $css_class
 * @property string $style
 * @property string $_data
 */
class BoxRevision extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'box_revision';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['box_id','session'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }
    
    /**
     * IMPORTANTE: slug behavior en save()
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
     * Renders a view file.
     * @param string $file the view file to be rendered. This can be either a file path or a path alias.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file does not exist.
     */
    public function renderFile($file, $params = [])
    {
        return $this->getView()->renderFile($file, $params, $this);
    }
    
    /**
     * Relación para modelos relacionados al box. Se debe pasar como parámetro
     * el nombre de la clase asociada que se busca.
     * @param string $class
     * @return \yii\db\ActiveQuery
     */
    public function getModels($class)
    {
        $refClass = new \ReflectionClass($class);
        $primaryKey = $class::primaryKey();
        $primaryKey = array_shift( $primaryKey );
        
        //Nombre de la tabla del modelo asociado
        $table = $class::tableName();
        
        $query = $class::find();
        $query->multiple = true;
        $query->innerJoin('box_revision_has_model', "box_revision_has_model.model_id = $table.$primaryKey");
        $query->andWhere(['box_revision_has_model.box_revision_id' => $this->box_revision_id, 'model_class' => $class]);
        $query->orderBy(['box_revision_has_model.box_revision_has_model_id' => SORT_ASC]);
        
        return $query;
        
    }
    
    private $_models;
    /**
     * Setea modelos al box
     * @param array $models
     */
    public function setModels($models)
    {
        $this->_models = $models;
        
        $deleteOldModels = function(){
            BoxRevisionHasModel::deleteAll([
                'box_revision_id' => $this->box_revision_id
            ]);
        };
        
        $this->on(self::EVENT_BEFORE_UPDATE, $deleteOldModels);
        
        $save = function(){
            foreach($this->_models as $model){
                $reflectionClass = new \ReflectionClass($model);
                $className = $reflectionClass->name;
                
                $boxHasModel = new BoxRevisionHasModel();
                $boxHasModel->box_revision_id = $this->box_revision_id;
                $boxHasModel->model_class = $className;
                $boxHasModel->model_id = $model->getPrimaryKey();
                
                $boxHasModel->save();
            }
        };
        
        $this->on(self::EVENT_AFTER_INSERT, $save);
        $this->on(self::EVENT_AFTER_UPDATE, $save);
    }
    
    /**
     * Box attrs relation
     * @return ActiveQuery
     */
    public function getBoxAttrs()
    {
        return $this->hasMany(BoxRevisionAttr::className(), ['box_revision_id' => 'box_revision_id']);
    }
    
    public function setBoxAttr($attr, $value)
    {
        $boxAttr = BoxRevisionAttr::find()->where(['box_revision_id' => $this->box_revision_id, 'attr' => $attr])->one();
        
        if($boxAttr === null){
            $boxAttr = new BoxRevisionAttr();
            $boxAttr->box_revision_id = $this->box_revision_id;
            $boxAttr->attr = $attr;
        }
        
        $boxAttr->value = $value;
        $boxAttr->type = gettype($value);
        $boxAttr->save();
    }
    
    public function getBox()
    {
        return $this->hasOne(Box::className(), ['box_id' => 'box_id']);
    }
    
    public function getAllRelModels()
    {
        return $this->hasMany(BoxRevisionHasModel::className(), ['box_revision_id' => 'box_revision_id']);
    }
}