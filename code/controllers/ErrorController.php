<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\log\FileTarget;
use app\common\components\BaseWebController;
use app\common\services\applog\AppLogService;


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
            
            //将错误写入数据库
            AppLogService::addErrorLog(\Yii::$app->id,$err_msg);
        }
        return $this->render("error", ["err_msg" => $err_msg]);
    }

    public function actionCapture(){
		$yii_cookies = [];
		$cookies = Yii::$app->request->cookies;
		foreach( $_COOKIE as $_c_key => $_c_val ){
			$yii_cookies[] = $_c_key.":".$cookies->get($_c_key);
		}

		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$ua = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		$url = $this->post("url","");
		$message = $this->post("message","");
		$error = $this->post("error","");
		$err_msg = "JS ERROR：[url:{$referer}],[ua:{$ua}],[js_file:{$url}],[error:{$message}],[error_info:{$error}]";

		if( !$url ){
			$err_msg .= ",[cookie:{".implode(";",$yii_cookies)."}]";
		}

		ApplogService::addErrorLog("app-js",$err_msg);
	}
}
