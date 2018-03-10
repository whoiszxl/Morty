<?php

namespace app\modules\m\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;
use app\common\services\UrlService;

class OauthController extends BaseController{


    /**
     * 登录
     */
    public function actionLogin() {
        $scope = $this->get("scope", "snsapi_base");
        $appid = \Yii::$app->params['weixin']['appid'];
        $redirect_uri = UrlService::buildMUrl("/oauth/callback");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state=STATE#wechat_redirect";
    
        echo $url;
    }


    /**
     * 登录回调
     */
    public function actionCallback(){
        echo "back";
    }

}
