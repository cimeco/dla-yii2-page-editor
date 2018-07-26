<?php

namespace quoma\pageeditor\models;

use Yii;

/**
 * This is the model class for table "snapshot".
 *
 * @property integer $snapshot_id
 * @property integer $page_id
 * @property integer $timestamp
 * @property integer $status
 *
 * @property Page $page
 */
class Snapshot extends \yii\db\ActiveRecord
{
    
    const STATUS_DELETED = 0;
    const STATUS_DISABLED = 5;
    const STATUS_ENABLED = 10;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'snapshot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id'], 'required'],
            [['page_id', 'timestamp', 'status'], 'integer'],
            [['page_id'], 'exist', 'skipOnError' => true, 'targetClass' => Page::className(), 'targetAttribute' => ['page_id' => 'page_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'snapshot_id' => Yii::t('app', 'Snapshot ID'),
            'page_id' => Yii::t('app', 'Page ID'),
            'timestamp' => Yii::t('app', 'Timestamp'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Page::className(), ['page_id' => 'page_id']);
    }
}