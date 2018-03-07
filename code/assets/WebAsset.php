<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WebAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    // public $css = [
    //     'css/web/bootstrap.min.css',
    //     'font-awesome/css/font-awesome.css',
    //     'css/web/style.css?ver='.RELEASE_VERSION,
    // ];
    // public $js = [
    //     'plugins/jquery-2.1.1.js',
    //     'js/web/bootstrap.min.js',
    //     'js/web/common.js?ver='.RELEASE_VERSION,
    // ];

    public $css = [
    ];
    public $js = [
    ];
    public function registerAssetFiles( $view ){
		//加一个版本号,目的 ： 是浏览器获取最新的css 和 js 文件
		$release_version = defined("RELEASE_VERSION")?RELEASE_VERSION:time();
		$this->css = [
            'css/web/bootstrap.min.css',
            'font-awesome/css/font-awesome.css',
            'css/web/style.css?ver='.$release_version,
		];
		$this->js = [
            'plugins/jquery-2.1.1.js',
            'js/web/bootstrap.min.js',
            'js/web/common.js?ver='.$release_version,
		];
		parent::registerAssetFiles( $view );
	}
}
