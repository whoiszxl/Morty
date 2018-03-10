<?php

namespace app\modules\m\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;
use app\common\services\UrlService;
use app\common\components\HttpClient;

class OauthController extends BaseController{


    /**
     * 登录
     */
    public function actionLogin() {
        $scope = $this->get("scope", "snsapi_base");
        $appid = \Yii::$app->params['weixin']['appid'];
        $redirect_uri = UrlService::buildMUrl("/oauth/callback");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state=STATE#wechat_redirect";
    
        return $this->redirect($url);
    }


    /**
     * 登录回调
     */
    public function actionCallback(){
        //访问login回调之后会带回来一个code参数
        $code = $this->get("code", "");
        if(!$code){
            return $this->goHome();
        }

        //通过code获取网页授权使用的access_token
        $appid = \Yii::$app->params['weixin']['appid'];
        $sk = \Yii::$app->params['weixin']['sk'];
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$sk}&code={$code}&grant_type=authorization_code";

        $ret = HttpClient::get($url);
        $ret = @json_decode($ret, true);
        $ret_token = isset($ret['access_token'])?$ret['access_token']:"";
        if(!$ret_token){
            return $this->goHome();
        }

        $openid = isset($ret['openid'])?$ret['openid']:'';
        $scope = isset($ret['scope'])?$ret['scope']:'';
        if($scope == "snsapi_userinfo"){
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$ret_token}&openid={$openid}&lang=zh_CN";
            $wechat_user_info = HttpClient::get($url);
            var_dump($wechat_user_info);
        }


        echo "back";
    }

}
