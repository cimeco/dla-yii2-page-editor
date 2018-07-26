<?php

namespace quoma\pageeditor\models;

use Yii;

/**
 * This is the model class for table "page".
 *
 * @property integer $page_id
 * @property string $slug
 * @property string $name
 * @property integer $status
 * @property integer $theme_id
 *
 * @property Theme $theme
 * @property Snapshot[] $snapshots
 */
class Page extends \quoma\core\db\ActiveRecord
{
    
    const STATUS_DELETED = 0;
    const STATUS_DISABLED = 5;
    const STATUS_ENABLED = 10;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'theme_id', 'name'], 'required'],
            [['status', 'theme_id'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['theme_id'], 'exist', 'skipOnError' => true, 'targetClass' => Theme::className(), 'targetAttribute' => ['theme_id' => 'theme_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'page_id' => Yii::t('app', 'ID'),
            'slug' => Yii::t('app', 'Slug'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'statusName' => Yii::t('app', 'Status'),
            'theme_id' => Yii::t('app', 'Theme'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTheme()
    {
        return $this->hasOne(Theme::className(), ['theme_id' => 'theme_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSnapshots()
    {
        return $this->hasMany(Snapshot::className(), ['page_id' => 'page_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSnapshot()
    {
        return $this->getSnapshots()->where(['status' => Snapshot::STATUS_ENABLED])->orderBy(['timestamp' => SORT_DESC])->one();
    }
    
    /**
     * @return array
     */
    public function behaviors() {
        
        return [
            'unix_timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    \common\components\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \common\components\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            'slug' => [
                'class' => '\yii\behaviors\SluggableBehavior',
                'attribute' => 'name',
                'ensureUnique' => true,
                'immutable' => true
            ],
        ];
    }
    
    public static function getStatusList()
    {
        return [
            self::STATUS_ENABLED => Yii::t('app', 'Enabled'),
            self::STATUS_DISABLED => Yii::t('app', 'Disabled'),
        ];
    }
    
    public function getStatusName()
    {
        if(!$this->status){
            return null;
        }
        
        return static::getStatusList()[$this->status];
    }
    
    public static function findSlug($slug)
    {
        return Page::find()->where(['status' => Page::STATUS_ENABLED, 'slug' => $slug])->one();
    }
}