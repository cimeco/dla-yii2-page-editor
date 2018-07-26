<?php

namespace quoma\pageeditor\models;

use Yii;

/**
 * This is the model class for table "box_revision_attr".
 *
 * @property integer $box_attr_id
 * @property integer $box_id
 * @property string $attr
 * @property integer $type
 * @property integer $value
 */
class BoxRevisionAttr extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'box_revision_attr';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
}