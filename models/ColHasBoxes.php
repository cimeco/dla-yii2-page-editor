<?php

namespace quoma\pageeditor\models;

use Yii;

/**
 * This is the model class for table "col_has_boxes".
 *
 * @property integer $col_has_boxes_id
 * @property integer $col_id
 * @property integer $box_id
 *
 * @property Box $box
 * @property Col $col
 */
class ColHasBoxes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'col_has_boxes';
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBox()
    {
        return $this->hasOne(Box::className(), ['box_id' => 'box_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCol()
    {
        return $this->hasOne(Col::className(), ['col_id' => 'col_id']);
    }
}