<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

//加入版本号 RELEASE_VERSION
if(file_exists(__DIR__."/version_book.txt")) {
    define("RELEASE_VERSION",trim(file_get_contents(__DIR__."/version_book.txt")));
}else{
    define("RELEASE_VERSION",time());
}

echo RELEASE_VERSION;exit;

(new yii\web\Application($config))->run();
