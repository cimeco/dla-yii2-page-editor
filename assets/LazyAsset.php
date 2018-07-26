<?php

namespace quoma\pageeditor\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class LazyAsset extends AssetBundle
{
    public $sourcePath = '@vendor/quoma/yii2-page-editor/assets/lazy';
    public $css = [
    ];
    public $js = [
        'lz.js',
    ];
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}