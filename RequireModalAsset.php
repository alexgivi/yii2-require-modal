<?php

namespace alexgivi\requireModal;

use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;

class RequireModalAsset extends AssetBundle
{
    public $sourcePath = '@vendor/alexgivi/yii2-require-modal/assets/';

    public $depends = [
        BootstrapPluginAsset::class
    ];

    public $js = [
        'require-modal.js'
    ];
}
