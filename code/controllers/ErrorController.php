<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\log\FileTarget;
use app\common\components\BaseWebController;


class ErrorController extends BaseWebController
{
   
    public function actionError() {

        //$this->layout = false;
        //1.记录错误信息到文件和数据库中
        $error = \Yii::$app->errorHandler->exception;
        $err_msg = '';
        if($error) {
            //2.获取到文件名,报错行,报错信息和报错码
            $file = $error->getFile();
            $line = $error->getLine();
            $message = $error->getMessage();
            $code = $error->getCode();

            //3.记录到文件系统
            $log = new FileTarget();
            $log->logFile = \Yii::$app->getRuntimePath().'/logs/error.log';
            $err_msg = $message." [file:{$file}][line:{$line}][code:{$code}][url:{$_SERVER['REQUEST_URI']}][POST_DATA:".http_build_query($_POST)."]";

            $log->messages[] = [
                $err_msg,
                1,
                'application',
                microtime(true)
            ];

            $log->export();
            
            //TODO 还需写入数据库
        }
        return $this->render("error", ["err_msg" => $err_msg]);
    }
}
