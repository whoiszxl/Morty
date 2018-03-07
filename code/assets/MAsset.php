<?php

namespace app\assets;

use yii\web\AssetBundle;

class MAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
    ];
    public $js = [
    ];

    public function registerAssetFiles( $view ){
		//加一个版本号,目的 ： 是浏览器获取最新的css 和 js 文件
		$release_version = defined("RELEASE_VERSION")?RELEASE_VERSION:time();
		$this->css = [
                  'font-awesome/css/font-awesome.css',
                  'css/m/css_style.css',
                  'css/m/app.css?ver='.$release_version,
		];
		$this->js = [
                  'plugins/jquery-2.1.1.js',
                  'js/m/TouchSlide.1.1.js',
                  'js/m/common.js?ver='.$release_version
		];
		parent::registerAssetFiles( $view );
	}
}
