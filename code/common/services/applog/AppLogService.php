<?php

namespace app\common\services\applog;

use app\common\services\UtilService;
use app\models\log\AppAccessLog;
use app\models\log\AppLog;
use Yii;

class AppLogService {


    public static function addErrorLog($appname,$content){

        //通过yii获取到报错类
        $error = Yii::$app->errorHandler->exception;

        //创建一个error数据库模型
        $model_app_logs = new AppLog();
        //保存app名称和内容
        $model_app_logs->app_name = $appname;
        $model_app_logs->content = $content;

        //获取ip信息
        $model_app_logs->ip = UtilService::getIP();

        /**
         * HTTP_USER_AGENT是用来检查浏览页面的访问者
         * 在用什么操作系统（包括版本号）浏览器（包括
         * 版本号）和用户个人偏好的代码
         */
        if( !empty($_SERVER['HTTP_USER_AGENT']) ) {
            $model_app_logs ->ua = "[UA:{$_SERVER['HTTP_USER_AGENT']}]";
        }


        if ($error) {
            //设置errorname
            if(method_exists($error,'getName' )) {
                $model_app_logs->err_name = $error->getName();
            }

            //设置httpcode
            if (isset($error->statusCode)) {
                $model_app_logs->http_code = $error->statusCode;
            }
            //设置错误码
            $model_app_logs->err_code = $error->getCode();
        }

        //设置时间和保存
        $model_app_logs->created_time = date("Y-m-d H:i:s");
        $model_app_logs->save(0);
    }

	public static function addAppLog( $uid = 0 ){

		$get_params = \Yii::$app->request->get();
		$post_params = \Yii::$app->request->post();
		if( isset( $post_params['summary'] ) ){
			unset( $post_params['summary'] );
		}


		$target_url = isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'';

		$referer = Yii::$app->request->getReferrer();
		$ua = Yii::$app->request->getUserAgent();

		$access_log = new AppAccessLog();
		$access_log->uid = $uid;
		$access_log->referer_url = $referer?$referer:'';
		$access_log->target_url = $target_url;
		$access_log->query_params = json_encode(array_merge($get_params,$post_params));
		$access_log->ua = $ua?$ua:'';
		$access_log->ip = UtilService::getIP();
		$access_log->created_time = date("Y-m-d H:i:s");
		return $access_log->save(0);
	}

}