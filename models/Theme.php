<?php

namespace quoma\pageeditor\models;

use quoma\core\behaviors\StatusBehavior;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "theme".
 *
 * @property integer $theme_id
 * @property string $name
 * @property string $slug
 * @property integer $status
 * @property string $basePath
 * @property string $baseUrl
 *
 * @property Website[] $websites
 */
class Theme extends \quoma\core\db\ActiveRecord
{
    //Para subir el theme
    public $zip;
    
    public $folder;
    
    public $repo;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'theme';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['folder'],'required', 'when' => function($model){ return ($this->zip || $this->repo || !$model->isNewRecord) ? false : true; }],
            [['status'], 'integer'],
            [['zip'], 'file', 'skipOnEmpty' => false, 'extensions' => 'zip', 'when' => function($model){ return $model->folder || $this->repo || !$model->isNewRecord ? false : true; }],
            [['zip'], 'file', 'skipOnEmpty' => true, 'extensions' => 'zip', 'when' => function($model){ return $model->folder || $this->repo || !$model->isNewRecord ? true : false; }],
            [['name'], 'string', 'max' => 250],
            [['folder'],'string', 'max' => 100],
            [['repo'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'theme_id' => Yii::t('app', 'Theme ID'),
            'name' => Yii::t('app', 'Name'),
            'slug' => Yii::t('app', 'Slug'),
            'status' => Yii::t('app', 'Status'),
            'basePath' => Yii::t('app', 'Base Path'),
            'baseUrl' => Yii::t('app', 'Base Url'),
            'statusName' => Yii::t('app', 'Status'),
            'folder' => Yii::t('app', 'Folder'),
        ];
    }
    
    public function behaviors()
    {
        return [
            'slug' => [
                'class' => '\yii\behaviors\SluggableBehavior',
                'attribute' => 'name',
                'ensureUnique' => true,
                'immutable' => true
            ],
            'status' => [
                'class' => StatusBehavior::className(),
                'config' => 'basic',
            ],
            'timestamps' => [
                'class' => \yii\behaviors\TimestampBehavior::className(),
            ]
        ];
    }
    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsites()
    {
        return $this->hasMany(Website::className(), ['theme_id' => 'theme_id']);
    }
    
    public function getThemesPath()
    {
        $module = \quoma\pageeditor\PageEditorModule::getInstance();
        if($module){
            return $module->themesPath;
        }
        
        throw new \yii\web\HttpException(500, 'Page Editor Module has not been initialized.');
    }
    
    public function getUploadPath()
    {
        if($this->slug){
            return $this->getThemesPath().DIRECTORY_SEPARATOR.$this->slug;
        }
        
        throw new \yii\web\HttpException(500, 'Slug is required to execute this function.');
    }

    /**
     * Sube y descomprime el zip. Devuelve la ruta o false en caso de fallo.
     * Necesitamos el slug para q funcione, por lo que se debe guardar antes
     * de llamar a esta funcion.
     * @return boolean
     */
    public function upload()
    {
        if ($this->validate() && $this->zip) {
            
            $zip = new \ZipArchive();
            $res = $zip->open( $this->zip->tempName );
            
            if ($res === TRUE) {
                
                if (file_exists($this->getUploadPath()) || is_dir($this->getUploadPath())) {
                    \yii\helpers\FileHelper::removeDirectory($this->getUploadPath());
                }
                
                $zip->extractTo( $this->getUploadPath() );
                $zip->close();
                
            } else {
                throw new \yii\web\HttpException(500, 'Error uploading theme zip.');
            }

            return true;
        } else {
            return false;
        }
    }
    
    public function beforeSave($insert) 
    {
        if(parent::beforeSave($insert)){
            $this->fillPaths();
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Llena el campo basePath. Si se subio un zip, utiliza el baseName del zip.
     * Si se cargo el campo folder, utiliza este campo
     */
    private function fillPaths()
    {

        if($this->zip && empty($this->folder)){
            $this->basePath = $this->getUploadPath().DIRECTORY_SEPARATOR.$this->zip->baseName;
            //TODO: baseUrl
        }elseif(!empty($this->folder)){
            $this->folder = str_replace(['\\', '/'], '', $this->folder);
            $this->basePath = $this->getThemesPath().DIRECTORY_SEPARATOR.$this->folder;
        }
        
    }
    
    /**
     * Devuelve la lista de iconos del tema, si existe el archivo icons.php.
     * Si se para el objeto view, y el archivo icons.php tiene definido el
     * css para los iconos, lo registra.
     * @return array
     */
    public function getIcons($view = null)
    {

        $icons = [];
        
        //Path del archivo con info de iconos del tema
        $file = $this->basePath.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'icons.php';
        
        if(file_exists($file)){
            $data = require($file);
            
            //Lista de iconos disponibles para elegir
            if(isset($data['icons'])){
                $icons = $data['icons'];
            }
            
            //Assets para poder mostrar los iconos en el panel de admin
            if(isset($data['assets']) 
                    && isset($data['assets']['class']) 
                    && isset($data['assets']['css']) 
                    && $view){
                
                $am = $view->getAssetManager();
                $bundle = $am->getBundle($data['assets']['class'], true);
                
                //Los css solo deberian contener info relacionada a los iconos
                foreach($data['assets']['css'] as $css){
                    $view->registerCssFile($bundle->baseUrl.DIRECTORY_SEPARATOR.$css);
                }
            }
            
        }
        
        return $icons;
        
    }
    
    /**
     * Devuelve la lista de vistas del tema para el grupo dado, si existe el 
     * archivo views.php.
     * @return array
     */
    public function getViews($group)
    {
        
        $views = [];

        //Path del archivo con info de iconos del tema
        $file = $this->basePath.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'views.php';
        
        if(file_exists($file)){
            $data = require($file);
            
            //Lista de views disponibles para elegir
            if(isset($data[$group->slug])){
                $views = $data[$group->slug];
            }
            
        }
        
        return $views;
        
    }
    
    /**
     * Devuelve el label asociado a una vista
     * @param Group $group
     * @param string $view
     * @return string
     */
    public function getViewLabel($group, $view)
    {
        if(empty($view)){
            return '';
        }
        
        $views = $this->getViews($group);
        $map = \yii\helpers\ArrayHelper::map($views, 'name', 'label');
        
        if(isset($map[$view])){
            return $map[$view];
        }
        
        return '';
    }
    
    /**
     * Devuelve el archivo de vista a renderizar asociado a un articulo
     * @param Group $group
     * @param string $view
     * @return string
     */
    public function getArticleViewFile($article)
    {
        if($article->view && $article->group->articleHas('view')){  
            $relative = $article->group->slug.DIRECTORY_SEPARATOR.$article->view;
            $file = $this->basePath.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'article'.DIRECTORY_SEPARATOR.$relative.'.php';
            
            if(file_exists($file)){
                return $relative;
            }
        }
        
        return 'view';
    }
    
    public static function getList()
    {
        return self::find()->where(['status' => \common\behaviors\StatusBehavior::STATUS_ENABLED])->all();
    }
    
    public function getGitUrl()
    {
        
        if(file_exists($this->basePath.'/.git/config')){
                
            $file = parse_ini_file($this->basePath.'/.git/config');
            $url = $file['url'];
            $url = preg_replace('/https:.*@/i','https://',$url);
            return trim($url);

        }
        
        return false;
        
    }
    
    public function getGitUsername()
    {
        
        if(file_exists($this->basePath.'/.git/config')){
                
            $file = parse_ini_file($this->basePath.'/.git/config');
            $url = $file['url'];
            
            $username = null;
            
            preg_match('/https:.*:/i', $url, $match);
            
            if(empty($match)){
                preg_match('/https:.*@/i', $url, $match);
            }
            
            if(isset($match[0])){
                $username = str_replace(['https://',':','@'],'',$match[0]);
            }
            
            return $username;

        }
        
        return false;
        
    }
    
    /**
     * Devuelve una lista para utilizar en select
     */
    public static function findForSelect()
    {
        
        $themes = self::find()->where(['status' => \common\behaviors\StatusBehavior::STATUS_ENABLED])->all();
        
        return \yii\helpers\ArrayHelper::map($themes, 'theme_id', 'name');
        
    }
}
