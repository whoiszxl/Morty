<?php

namespace app\controllers;

use app\common\components\BaseWebController;
use app\common\services\captcha\ValidateCode;
use app\common\services\UtilService;
use app\models\sms\SmsCaptcha;


class DefaultController extends BaseWebController{
    
    private $captcha_cookie_name = "validate_code";

    public function actionIndex() {
        //$this->layout = false;
        return $this->render("index");
    }

    /**
     * 验证码生成
     */
    public function actionImg_captcha(){
		$font_path = \Yii::$app->getBasePath().'/web/fonts/captcha.ttf';
		$captcha_handle = new ValidateCode( $font_path );
        $captcha_handle->doimg();
        $this->setCookie($this->captcha_cookie_name, $captcha_handle->getCode());
    }
    
    public function actionGet_captcha(){
        $mobile = $this->post("mobile", "");
        $img_captcha = $this->post("img_captcha", "");
        if(!$mobile || !preg_match('/^1[0-9]{10}$/',$mobile)){
            $this->removeCookie($this->captcha_cookie_name);
            return $this->renderJson([],"请输入正确的手机号码",-1);
        }

        $referer = isset( $_SERVER['HTTP_REFERER'] )?$_SERVER['HTTP_REFERER']:'';
        if( stripos($referer,$_SERVER['HTTP_HOST']) === false ){
			$this->removeCookie( $this->captcha_cookie_name );
			return $this->renderJSON();
        }
        
        $img_captcha = str_replace(' ', '', $img_captcha);
        $captcha_code = $this->getCookie($this->captcha_cookie_name,"");
        if($captcha_code && strtolower($img_captcha) != $captcha_code){
            $this->removeCookie($this->captcha_cookie_name);
            return $this->renderJson([],"请输入正确的验证码,你输入的验证码是{$img_captcha},正确的验证码是{$captcha_code}",-1);
        }

        $last = SmsCaptcha::getLastCaptcha($mobile);
        if($last && ( time() - strtotime($last->created_time) < 60 ) ) {
			return $this->renderJSON([],"发送得太快啦",-1);
        }
        
        $sms_template = "您此次操作的会员绑定验证码为：xxxx";

        $model = new SmsCaptcha();
		$model->geneCustomCaptcha($mobile, $sms_template,UtilService::getIP() );
		$this->removeCookie( $this->captcha_cookie_name );
		if( $model ){
			return $this->renderJSON([],'手机验证码是：'.$model->captcha );
		}
		return $this->renderJSON([],"unknown",-1);
    }
}
