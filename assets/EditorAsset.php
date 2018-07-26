<?php

namespace quoma\pageeditor\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class EditorAsset extends AssetBundle
{
    public $sourcePath = '@vendor/quoma/yii2-page-editor/assets/editor';
    public $css = [
        'css/editor.css',
    ];
    public $js = [
        'js/page-editor.js',
        'js/bootbox.min.js'
    ];
    public $depends = [
        'common\components\paginator\PaginatorAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\jui\JuiAsset',
        //Si un Box necesita Selec2 en el form, debe estar precargado
        'kartik\select2\Select2Asset',
        'kartik\select2\ThemeKrajeeAsset'
    ];
}