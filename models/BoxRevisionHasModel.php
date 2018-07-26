<?php

namespace quoma\pageeditor\models;

use Yii;

/**
 * This is the model class for table "box_revision_has_model".
 *
 * @property integer $box_revision_has_model_id
 * @property integer $revision_id
 * @property string $model_class
 * @property integer $model_id
 */
class BoxRevisionHasModel extends \yii\db\ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'box_revision_has_model';
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