<?php

namespace quoma\pageeditor;

Use Yii;

class PageEditorModule extends \yii\base\Module
{
    public $controllerNamespace = 'quoma\pageeditor\controllers';
    
    public $defaultControllerBehaviors= [];
    
    //Path del directorio donde se suben los themes
    public $themesPath = '@frontend/themes';
    
    //Paths del directorio donde se suben los boxes
    public $boxesPaths = ['@frontend/boxes'];
    
    //Clases de assets a publicar al cargar el editor
    public $editorAssets = [];
    
    //Necesario para que webvimark/user-management tome las rutas
    public $controllerMap = [
        'col' => '\quoma\pageeditor\controllers\ColController',
        'box' => '\quoma\pageeditor\controllers\BoxController',
        'theme' => '\quoma\pageeditor\controllers\ThemeController',
    ];
    
    public $afterPublish;

    public function behaviors()
    {
        $frontendOrigins = [
            preg_replace('/(https?:\/\/)|\/\//i','http://',Yii::$app->frontendUrlManager->baseUrl),
            preg_replace('/(https?:\/\/)|\/\//i','https://',Yii::$app->frontendUrlManager->baseUrl),
        ];
        
        foreach(Yii::$app->params['corsDomains'] as $cors){
            $frontendOrigins[] = preg_replace('/(https?:\/\/)|\/\//i','http://',$cors);
            $frontendOrigins[] = preg_replace('/(https?:\/\/)|\/\//i','https://',$cors);
        }
        
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => $frontendOrigins,
                    'Access-Control-Allow-Credentials' => true,
                ],

            ],
        ];
    }
    
    public function init()
    {
        parent::init();
        Yii::setAlias('@pageeditor', __DIR__);
    }
}
