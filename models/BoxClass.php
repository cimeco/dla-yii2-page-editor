<?php

namespace quoma\pageeditor\models;

use Yii;
use webvimark\modules\UserManagement\models\rbacDB\Role;

/**
 * This is the model class for table "box_class".
 *
 * @property integer $box_class_id
 * @property string $class
 * @property integer $status
 *
 * @property BoxClassHasRole[] $boxClassHasRoles
 */
class BoxClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'box_class';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['class'], 'string', 'max' => 255],
            [['roles'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'box_class_id' => Yii::t('app', 'Box Class ID'),
            'class' => Yii::t('app', 'Class'),
            'status' => Yii::t('app', 'Status'),
            'statusName' => Yii::t('app', 'Status'),
        ];
    }
    
    public function behaviors() {
        
        $behaviors = array_merge(parent::behaviors(), [
            'status' => [
                'class' => \common\behaviors\StatusBehavior::className(),
                'config' => 'basic',
            ],
        ]);
        
        return $behaviors;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBoxClassHasRoles()
    {
        return $this->hasMany(BoxClassHasRole::className(), ['box_class_id' => 'box_class_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Role::className(), ['name' => 'auth_item_name'])->viaTable('box_class_has_role', ['box_class_id' => 'box_class_id']);
    }
    
    private $_roles;
    /**
     * Agrega categorias al articulo. Al hacer esto, se genera un evento para
     * que la relacion sea guardada luego de guardar el objeto.
     * @param type $roles
     */
    public function setRoles($roles){
        if(empty($roles)){
            $roles = [];
        }
        
        $this->_roles = $roles;
        
        $saveRoles = function($event){
            //Quitamos las relaciones actuales
            $this->unlinkAll('roles', true);
            //Guardamos las nuevas relaciones
            foreach ($this->_roles as $name){
                $role = Role::findOne($name);
                $this->link('roles', $role);
            }
        };

        $this->on(self::EVENT_AFTER_INSERT, $saveRoles);
        $this->on(self::EVENT_AFTER_UPDATE, $saveRoles);
    }
}