<?php

namespace app\modules\weixin\controllers;

use app\common\services\UrlService;
use app\common\services\weixin\RequestService;
use app\common\components\BaseWebController;

/**
 * 微信公众号菜单控制器
 */
class MenuController extends BaseWebController{

	/**
	 * 设置菜单显示
	 */
    public function actionSet() {
		//按照微信官方的要求来拼装数据
        $menu  = [
			"button" => [
				[
					"name" => "商城",
					"type" => "view",
					"url"  => UrlService::buildMUrl("/default/index")
				],
				[
					"name" => "我",
					"type" => "view",
					"url" => UrlService::buildMUrl("/user/index")
				]
			]
        ];
		
		//获取到配置中的微信参数
		$config = \Yii::$app->params['weixin'];
		//将配置设置到weixin的request服务中
		RequestService::setConfig($config['appid'], $config['token'], $config['sk']);
		//就可以获取到临时access_token了
        $access_token = RequestService::getAccessToken();

		//如果token存在就发送一个post请求过去微信接口
        if($access_token){
            $url = "menu/create?access_token={$access_token}";
			$ret = RequestService::send( $url,json_encode($menu,JSON_UNESCAPED_UNICODE), 'POST' );
			var_dump( $ret );
        }
    }
}
 