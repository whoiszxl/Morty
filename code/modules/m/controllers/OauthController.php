<?php
namespace app\modules\m\controllers;
use app\common\services\ConstantMapService;
use app\common\services\oauth\ClientService;
use app\common\services\oauth\WeixinService;
use app\common\services\QueueListService;
use app\models\member\Member;
use app\models\oauth\OauthMemberBind;
use app\modules\m\controllers\common\BaseController;
use Yii;
use yii\log\FileTarget;

class OauthController extends BaseController {

    public function actionLogin(){
        /*来这里了就要把openid清除掉*/
        $this->removeWxCookie();

        $scope = $this->get('type', 'snsapi_base');
        $referer = $this->get('referer', '');

        //创建微信服务,调用静默登录,跳转到返回的url中,就是callback了
        $state = urlencode($referer);
        $target = new WeixinService();
		$url = $target->Login( $scope,$state );
        return $this->redirect($url);
    }


    /**
     * 回调
     */
    public function actionCallback(){
        $code = $this->get('code','');
        $state = $this->get('state','');

        //不存在,移除微信cookie
        if(!$code){
            $this->removeWxCookie();
            $this->record_log("403 weixin auth code param error,code:{$code},state:{$state}");
            return $this->goHome();
        }

        //通过code获取到access_token
        $ret_token = ClientService::getAccessToken( 'weixin',[ 'code' => $code ] );
        //不存在,也清除之
        if( !$ret_token ){
            $this->removeWxCookie();
            $this->record_log("weixin get userinfo fail:".ClientService::getLastErrorMsg() );
            return $this->goHome();
        }

        //获取到openid和unionid,
        $openid  = isset($ret_token['openid'])?$ret_token['openid']:'';
        echo "获取openid:".$openid."<br>";
        $unionid  = isset($ret_token['unionid'])?$ret_token['unionid']:'';
        echo "获取到unionid:".$unionid."<br>";

        //不存在清除cookie
        if( !$openid  ){
            $this->removeWxCookie();
            $this->record_log("params uid openid  missed,data:".var_export($ret_token,true) );
            return $this->goHome();
        }

        //记录日志
        $this->record_log("auth info:".var_export($ret_token,true) );

        //将两个id存入缓存
		$this->setCookie($this->auth_cookie_current_openid,$openid);
		$this->setCookie($this->auth_cookie_current_unionid,$unionid);

        //查询数据库中openid,并且类型为wechat的数据
        $reg_bind = OauthMemberBind::findOne([ "openid" => $openid,'type' => ConstantMapService::$client_type_wechat ]);
        echo "查询绑定关系:".json_encode($reg_bind)."<br>";

        if( $reg_bind ){//如果已经绑定了
            //通过id和status查询到这条记录
            $member_info = Member::findOne([ 'id' => $reg_bind['member_id'],'status' => 1]);
            //不存在的话,就删除吧
			if( !$member_info ){
				$reg_bind->delete();
				return $this->goHome();
			}

            //如果scope为查用户信息的话
			if ( $ret_token['scope'] == "snsapi_userinfo" ){
                //通过access_token和openid获取到微信用户信息
				$wechat_userinfo = ClientService::getUserInfo( "weixin",$ret_token['access_token'],[ 'uid' => $openid ] );
                //信息存在,如果存在unionid就存一下
                if ( $wechat_userinfo ) {
					if( isset( $wechat_userinfo['unionid']) ){
						$reg_bind->unionid = $wechat_userinfo['unionid'];
						$reg_bind->save(0);
					}

					//这个时候做登录特殊处理，例如更新用户名和头像等等新
					if( $member_info->avatar == ConstantMapService::$default_avatar ){
						//需要做一个队列数据库了
						//$wechat_userinfo['headimgurl']
						QueueListService::addQueue( "member_avatar",[
							'member_id' => $member_info['id'],
							'avatar_url' => $wechat_userinfo['headimgurl'],
						] );
					}

					if( $member_info->nickname == $member_info->mobile ){
						$member_info->nickname = $wechat_userinfo['nickname'];
						$member_info->update(0);
					}
				}
			}

			$this->setLoginStatus( $member_info );
		}else{
        	$this->removeAuthToken();
		}

        $reback_url = urldecode($state);
        return $this->redirect($reback_url);
    }


    /**
     * 清除登录token和openid,unionid就是注销
     */
    public function actionLogout(){
        $this->removeAuthToken();
        $this->removeWxCookie();
        $this->goHome();
        return;
    }

    /**
     * 清除openid和unionid
     */
    private function removeWxCookie(){
        $this->removeCookie($this->auth_cookie_current_openid);
        $this->removeCookie($this->auth_cookie_current_unionid);
    }

    /**
     * 记录日志
     */
	public static function record_log($msg){
		$log = new FileTarget();
		$log->logFile = Yii::$app->getRuntimePath() . "/logs/wx_info_".date("Ymd").".log";
		$log->messages[] = [
			"[url:{$_SERVER['REQUEST_URI']}][post:".http_build_query($_POST)."] [msg:{$msg}]",
			1,
			'application',
			microtime(true)
		];
		$log->export();
	}

}
