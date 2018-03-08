<?php

namespace app\modules\web\controllers\common;

use app\common\components\BaseWebController;
use app\models\User;
use app\common\services\UrlService;

class BaseController extends BaseWebController {

    protected $auth_cookie_name = "whoiszxl_cookie";

    public $allowAllAction = [
        "web/user/login"
    ];

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }


    //登录统一验证
    public function beforeAction($action){
        $is_login = $this->checkLoginStatus();

        if(in_array($action->getUniqueId(),$this->allowAllAction)){
            return true;
        }

        if(!$is_login){
            if(\Yii::$app->request->isAjax){
                $this->renderJson([], '未登录，请先登录', -302);
            }else{
                $this->redirect(UrlService::buildWebUrl("/user/login"));
            }

            return false;
        }

        return true;
    }

    private function checkLoginStatus(){
        $auth_cookie = $this->getCookie($this->auth_cookie_name,"");
        if(!$auth_cookie){
            return false;
        }

        list($auth_token,$uid) = explode("#", $auth_cookie);

        if(!$auth_token || !$uid){
            return false;
        }

        if(!preg_match("/^\d+$/",$uid)){
            return false;
        }

        $user_info = User::find()->where(['uid'=>$uid])->one();
        if(!$user_info){
            return false;
        }

        if($auth_token != $this->geneAuthToken($user_info)){
            return false;
        }

        return true;
    }

    /**
     * 统一生成加密字段，加密字符串 = md5(login_name+login_pwd+login_salt)
     */
    public function geneAuthToken($user_info) {
        return md5($user_info['login_name'].$user_info['login_pwd'].$user_info['login_salt']);
    }


    public function removeLoginStatus() {
        $this->removeCookie($this->auth_cookie_name);
    }


    /**
     * 设置登录态
     * 加密字符串+"#"+uid,  加密字符串 = md5(login_name+login_pwd+login_salt)
     */
    public function setLoginStatus($user_info) {
        $auth_token = $this->geneAuthToken($user_info);
        $this->setCookie($this->auth_cookie_name,$auth_token."#".$user_info['uid']);
    }
}