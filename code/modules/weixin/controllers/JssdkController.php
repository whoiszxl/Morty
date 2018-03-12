<?php
namespace app\modules\weixin\controllers;

use app\common\services\weixin\RequestService;
use Yii;

/**
 * jssdk控制器
 */
class JssdkController extends \yii\web\Controller {



    public function actionIndex(){
        $this->setWeixinConfig();
        //通过json返回需要给前台使用的签名加密包
        return $this->renderJSON($this->getSignPackage());
    }

    
    private function getSignPackage() {
        //获取到ticket
        $jsapiTicket = $this->getJsApiTicket();
        //状态不对,强制获取
        if( $jsapiTicket == 40001){
            $jsapiTicket = $this->getJsApiTicket(true);
        }
        //获取到请求的url
        $url = trim(Yii::$app->request->get("url"));
        //不存在,取上一个url
        if( !$url ){
            $url = isset( $_SERVER['HTTP_REFERER'] )?$_SERVER['HTTP_REFERER']:'';
        }

        //创建时间戳和随机字符串并且拼接一下
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        //加密
        $signature = sha1($string);
        //拼装出这个需要展示出来的签名包
        $signPackage = array(
            "appId"     => RequestService::getAppId(),
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            //"string" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }


    /**
     * 获取jsApiTicket
     */
    private function getJsApiTicket( $force = false) {
        //使用app_id拼接一个缓存key
        $cache_key = 'wx_js_tkt_'.substr( RequestService::getAppId() ,2);
        $cache = Yii::$app->cache;
        //先从缓存中获取
		$ticket = $cache->get($cache_key);

        //如果缓存不存在,或者设置了强制更新
        if ( !$ticket || $force ) {
            //获取到accesstoken
            $accessToken = RequestService::getAccessToken();
            //拼接url
            $url = "ticket/getticket?type=jsapi&access_token=$accessToken";
            //发送请求获取
            $res = RequestService::send($url);
            $ticket_info = $res;
            //如果出错了
            if( isset($ticket_info['errcode'])  && $ticket_info['errcode'] != 0 ){
                return $ticket_info['errcode'];
            }

            //获取到ticket
            $ticket = isset( $ticket_info['ticket'] )? $ticket_info['ticket']:'';

            //将ticket设置到缓存
            if ($ticket) {
				$cache->set($cache_key,$ticket,$ticket_info['expires_in'] - 200 );
            }

        }

        //返回
        return $ticket;
    }

    /**
     * 将weixin配置设置到request中
     */
    private function setWeixinConfig(){
		$config = \Yii::$app->params['weixin'];
		RequestService::setConfig( $config['appid'],$config['token'],$config['sk'] );
    }

    protected function renderJSON($data=[], $msg ="ok", $code = 200){
        header('Content-type: application/json');
        echo json_encode([
            "code" => $code,
            "msg"   =>  $msg,
            "data"  =>  $data,
            "req_id" =>  uniqid(),
        ]);
        return Yii::$app->end();
    }

}
