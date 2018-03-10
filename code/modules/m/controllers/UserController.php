<?php

namespace app\modules\m\controllers;

use yii\web\Controller;
use app\modules\m\controllers\common\BaseController;
use app\models\oauth\OauthMemberBind;
use app\models\sms\SmsCaptcha;
use app\models\member\Member;
use app\common\services\member\MemberService;
use app\common\services\UrlService;
use app\common\services\UtilService;

class UserController extends BaseController
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //绑定用户
    public function actionBind(){
        //如果是get提交,展示页面并且将上一个页面存到当前页面
        if( \Yii::$app->request->isGet ){
			return $this->render("bind",[
				'referer' => trim( $this->get("referer") )
			]);
        }
        //获取到手机号,图片验证码,手机验证码,上一个提交,openid,unionid和当前时间
		$mobile = trim( $this->post("mobile") );
		$img_captcha = trim( $this->post("img_captcha") );
		$captcha_code = trim( $this->post("captcha_code") );
		$referer = trim( $this->post("referer","") );
		$openid   = $this->getCookie($this->auth_cookie_current_openid);
		$unionid   = $this->getCookie($this->auth_cookie_current_unionid,'');
		$date_now = date("Y-m-d H:i:s");

        //有效性验证
		if( mb_strlen($mobile,"utf-8") < 1 || !preg_match("/^[1-9]\d{10}$/",$mobile) ){
			return $this->renderJSON([],"请输入符合要求的手机号码~~",-1);
		}

		if (mb_strlen( $img_captcha, "utf-8") < 1) {
			return $this->renderJSON([], "请输入符合要求的图像校验码~~", -1);
		}

		if (mb_strlen( $captcha_code, "utf-8") < 1) {
			return $this->renderJSON([], "请输入符合要求的手机验证码~~", -1);
		}


		if ( !SmsCaptcha::checkCaptcha($mobile, $captcha_code ) ) {
			return $this->renderJSON([], "请输入正确的手机验证码~~", -1);
		}

        //通过手机号查询数据库是否存在这条用户记录
		$member_info = Member::find()->where([ 'mobile' => $mobile,'status' => 1 ])->one();

        //不存在
		if( !$member_info ){
            //将用户保存到数据库
            $ret = MemberService::set( [ 'mobile' => $mobile,'passwd' => '' ] );
            //不成功,输出错误
			if( !$ret ){
				return $this->renderJSON([],MemberService::getLastErrorMsg(),-1);
            }
            //通过这个id和状态去查询到这个用户的数据库信息
			$member_info = Member::find()->where([ 'id' => $ret,'status' => 1 ])->one();
		}

        //如果不存在 或者status不行
		if ( !$member_info || !$member_info['status']) {
			return $this->renderJSON([], "您的账号已被禁止，请联系客服解决~~", -1);
		}

        // 判断openid是否存在
		if ($openid) {
			//检查该手机号是否绑定过其他微信（一个手机号只能绑定一个微信,也只能绑定一个支付宝）
            $client_type = ConstantMapService::$client_type_wechat;
            //查询数据库 看看是否已经绑定过了
			$bind_info = OauthMemberBind::findOne([ 'member_id' => $member_info['id'], "openid" => $openid ,'type' => $client_type ]);
            //未绑定,需要绑定
            if ( ! $bind_info) {
                //将每个数据填充到数据库
				$model_bind  = new OauthMemberBind();
				$model_bind->member_id = $member_info['id'];
				$model_bind->type = $client_type;
				$model_bind->client_type = ConstantMapService::$client_type_mapping[ $client_type ];
				$model_bind->openid = $openid ?: '';
				$model_bind->unionid = $unionid ?: '';
				$model_bind->extra = '';
				$model_bind->updated_time = $date_now;
				$model_bind->created_time = $date_now;
				$model_bind->save(0);

				//绑定之后要做的事情
				// QueueListService::addQueue( "bind",[
				// 	'member_id' => $member_info['id'],
				// 	'type' => 1,
				// 	'openid' => $model_bind->openid
				// ] );
			}
		}

		//如果用户头像或者unionid没有，就获取//这个时候做登录特殊处理，例如更新用户名和头像等等新
		$url = ( $referer && $referer != "/m/user/bind" )?$referer:UrlService::buildMUrl("/");
		if( UtilService::isWechat() && ( $member_info->avatar == ConstantMapService::$default_avatar || $member_info->nickname == $member_info->mobile ) ){
			$url = $this->getAuthLoginUrl('snsapi_userinfo',$referer);
		}

		$this->setLoginStatus( $member_info );
		return $this->renderJSON([ 'url' => $url  ],"绑定成功~~");
    }

    //购物车
    public function actionCart(){
        
        return $this->render('cart');
    }


    //用户订单
    public function actionOrder(){
        
        return $this->render('order');
    }


    public function actionIndex(){
        
        return $this->render('index');
    }

    public function actionAddress(){
        
        return $this->render('address');
    }

    public function actionAddress_set(){
        
        return $this->render('address_set');
    }

    public function actionFav(){
        
        return $this->render('fav');
    }


    public function actionComment(){
        
        return $this->render('comment');
    }


    public function actionComment_set(){
        
        return $this->render('comment_set');
    }
}
