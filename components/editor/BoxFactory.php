<?php

namespace quoma\pageeditor\components\editor;

use common\components\helpers\ClassFinderHelper;
use quoma\pageeditor\PageEditorModule;
use Yii;
use quoma\pageeditor\models\Box;
use quoma\pageeditor\models\BoxClass;
use yii\helpers\ArrayHelper;
use webvimark\modules\UserManagement\models\User;

/**
 * Description of BoxFactory
 *
 * @author martin
 */
class BoxFactory extends \yii\base\Component{
    
    private static $_instance;
    
    /**
     * @return BoxFactory
     */
    public static function getInstance()
    {
        if(empty(static::$_instance)){
            static::$_instance = new self;
        }
        return static::$_instance;
    }
    
    /**
     * Devuelve un array con los nombres de clases de todos los boxes
     * @return array
     */
    public function findAllBoxes()
    {
        $pageEditor = PageEditorModule::getInstance();
        
        $directories = [];
        foreach($pageEditor->boxesPaths as $path){
            $directories = array_merge( glob(Yii::getAlias($path) . '/*' , GLOB_ONLYDIR), $directories );
        }

        $classes = static::findClasses($directories);
        
        return $classes;
        
    }
    
    /**
     * Busca los nombres de las clases alojadas en los directorios
     * de la configuracion pasada como parametro.
     *
     * @param $paths   Listado de directorios
     * @return array
     */
    public static function findClasses($paths)
    {
        $classes = get_declared_classes();
        // Itero en los directorios configuados
        foreach ($paths as $key=>$dir) {
            // Obtengo todos los archivos
            $fullDir = Yii::getAlias($dir);
            $files = scandir($fullDir);
            // Itero los archivos buscando clases
            foreach ($files as $key2=>$file) {
                if ($file!="." && $file!=".." && !is_dir($fullDir .'/'. $file)) {
                    include_once $fullDir .'/'. $file;
                }
            }
        }
        $classes = array_diff( get_declared_classes(), $classes);

        $retClasses = [];
        foreach ($classes as $class) {
            if( is_subclass_of($class, \quoma\pageeditor\models\Box::className()) && static::inDirs($class, $paths) ){
                $retClasses[] = $class;
            }
        }
        return $retClasses;
    }
    
    public static function inDirs($class, $paths)
    {
        $reflectionClass = new \ReflectionClass($class);
        foreach($paths as $path){
            if(stripos($reflectionClass->getFileName(), $path) !== false){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Devuelve un objeto por cada Box
     * TODO: Habilitar/Deshabilitar boxes
     * @return array
     */
    public function getAllBoxes()
    {
        $classes = $this->findAllBoxes();
        $boxes = [];
        foreach ($classes as $class){
            $boxes[] = new $class;
        }

        return $boxes;
    }
    
    /**
     * Devuelve un objeto por cada Box habilitado y taggeado con alguno de los tags recibidos
     * como parámetro
     * @param array $tags
     * @return \quoma\pageeditor\components\editor\class
     */
    public function getBoxes(array $tags)
    {
        
        $this->updateBoxes();
        
        $classes = BoxClass::find()->where(['status' => \common\behaviors\StatusBehavior::STATUS_ENABLED])->all();
        $boxes = [];
        
        foreach ($classes as $boxClass){
            
            $roles = ArrayHelper::getColumn($boxClass->roles, 'name');
            
            //Verificamos que la clase exista
            if(class_exists($boxClass->class) && User::hasRole($roles)){
                $box = new $boxClass->class;

                if(empty($tags) || array_intersect($box->getBoxTags(), $tags)){
                    $boxes[] = $box;
                }
            }
        }

        return $boxes;
    }
    
    public function createBox($class)
    {
        $box = new Box;
        $box->box_class = $class;
        
        if($box->save()){
            //Para que sea generado el modelo correspondiente a la clase:
            return Box::findOne($box->box_id);
        }
        
        throw new \yii\web\HttpException(500, 'Box not saved.');
    }
    
    /**
     * Actualiza la lista de boxes en base de datos:
     *  - Agrega nuevos boxes.
     *  - Elimina boxes inexistentes.
     */
    public function updateBoxes()
    {
        $classes = $this->findAllBoxes();
        $dbClasses = ArrayHelper::getColumn(BoxClass::find()->select('class')->asArray()->all(),'class');
        
        $toCreate = array_diff($classes, $dbClasses);

        foreach($toCreate as $class){
            
            $model = new BoxClass();
            $model->class = $class;
            $model->status = \common\behaviors\StatusBehavior::STATUS_ENABLED;
            $model->save();
            
        }
        
        $toDelete = array_diff($dbClasses, $classes);
        BoxClass::deleteAll(['class' => $toDelete]);
    }
}
