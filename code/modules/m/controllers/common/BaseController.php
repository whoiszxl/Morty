<?php

namespace app\modules\m\controllers\common;
use app\common\components\BaseWebController;
use app\common\services\UrlService;
use app\common\services\UtilService;
use app\models\member\Member;

class BaseController extends BaseWebController {

	protected  $auth_cookie_name = "whoiszxl_member";
	protected  $auth_cookie_current_openid = "sass_idc_m_openid";
	protected  $auth_cookie_current_unionid = "sass_idc_m_unionid";
	protected  $salt = "dm3HsNYz3Uyddd46Rjg";
	protected $current_user = null;

	/*这部分永远不用登录*/
	protected $allowAllAction = [
		'm/oauth/login',
		'm/oauth/logout',
		'm/oauth/callback',
		'm/user/bind',
		'm/pay/callback',
		'm/product/ops'
	];

	/**
	 * 以下特殊url
	 * 如果在微信中,可以不用登录(但是必须要有openid)
	 * 如果在H5浏览器,可以不用登录
	 */
	public $special_AllowAction = [
		'm/default/index',
		'm/product/index',
		'm/product/info'
	];

    /**
     * 构造函数
     */
	public function __construct($id, $module, $config = []){
		parent::__construct($id, $module, $config = []);
		$this->layout = "main";

        //初始化标题详情和图片
		\Yii::$app->view->params['share_info'] = json_encode( [
			'title' => \Yii::$app->params['title'],
			'desc' => \Yii::$app->params['title'],
			'img_url' => UrlService::buildWwwUrl("/images/common/qrcode.jpg"),
		] );
	}

    /**
     * 每个action之前的操作
     */
	public function beforeAction( $action ){
		$login_status = $this->checkLoginStatus();
		$this->setMenu();

		if ( in_array($action->getUniqueId(), $this->allowAllAction ) ) {
			return true;
		}

		if( !$login_status ){
			if( \Yii::$app->request->isAjax ){
				$this->renderJSON([],"未登录,系统将引导您重新登录~~",-302);
			}else{
				//获取user/bind的地址
				$redirect_url = $this->getBindUrl();
				//如果当前是微信浏览器
				if( UtilService::isWechat() ){
					//获取到cookie中的openid
					$openid = $this->getCookie($this->auth_cookie_current_openid,"");
					//存在的话就直接通过请求
					if( $openid ){
						if (in_array( $action->getUniqueId(), $this->special_AllowAction ) ){
							return true;
						}
					}else{
						//不存在就直接获取登录的地址
						$redirect_url = $this->getAuthLoginUrl();
					}
				}else{
					//如果是浏览器的话,直接过滤特殊url就是了
					if ( in_array( $action->getUniqueId(), $this->special_AllowAction ) ) {
						return true;
					}
				}
				//重定向到登录地址
				$this->redirect( $redirect_url );
			}
			return false;
		}
		return true;
	}

    /**
     * 检查登录状态
     */
	protected function checkLoginStatus(){

        //从cookie中获取登录的cookie
		$auth_cookie = $this->getCookie( $this->auth_cookie_name );
		if( !$auth_cookie ){
			return false;
        }
        
        //将cookie拆分
		list($auth_token,$member_id) = explode("#",$auth_cookie);
		if( !$auth_token || !$member_id ){
			return false;
        }
        
        //如果会员id有效
		if( $member_id && preg_match("/^\d+$/",$member_id) ){
            //就从数据库中获取出来
            $member_info = Member::findOne([ 'id' => $member_id,'status' => 1 ]);
            //不存在记录,cookie中移除
			if( !$member_info ){
				$this->removeAuthToken();
				return false;
            }
            //校验用户的token
			if( $auth_token != $this->geneAuthToken( $member_info ) ){
				$this->removeAuthToken();
				return false;
            }
            //将当前用户更新为这个member_info
			$this->current_user = $member_info;
			\Yii::$app->view->params['current_user'] = $member_info;
			return true;
		}
		return false;
	}

    /**
     * 设置登录态,生成会员token后设置到cookie中
     */
	public function setLoginStatus( $user_info ){
		$auth_token = $this->geneAuthToken( $user_info );
		$this->setCookie($this->auth_cookie_name,$auth_token."#".$user_info['id']);
	}

    /**
     * 从cookie中移除这个会员的cookie
     */
	protected  function removeAuthToken(){
		$this->removeCookie($this->auth_cookie_name);
	}

    //通过mdt的这个规则生成这个会员的token
	public function geneAuthToken( $member_info ){
		return md5( $this->salt."-{$member_info['id']}-{$member_info['mobile']}-{$member_info['salt']}");
	}

    /**
     * 获取这个绑定的url地址
     */
	protected function getBindUrl(){
		$referer = $_SERVER['REQUEST_URI'] ;
		return UrlService::buildMUrl("/user/bind",[ 'referer' => $referer ]);
	}

    /**
     * 获取snsapi_base验证登录的url
     */
	protected function getAuthLoginUrl( $type='snsapi_base',$referer = ''){
		$referer = $referer?$referer:$_SERVER['REQUEST_URI'];
		$url = UrlService::buildMUrl("/oauth/login", [ "type" => $type,"referer" => $referer ]);
		return $url;
	}

    /**
     * 设置菜单
     */
	protected function setMenu(){

		$menu_hide = false;
		$url = \Yii::$app->request->getPathInfo();
		if( stripos($url,"product/info") !== false ){
			$menu_hide = true;
		}

		\Yii::$app->view->params['menu_hide'] = $menu_hide;
	}

    /**
     * 返回到首页
     */
	public function goHome(){
		return $this->redirect( UrlService::buildMUrl("/") );
	}


}