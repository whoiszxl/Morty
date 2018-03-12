<?php

namespace app\modules\m\controllers;

use app\common\services\ConstantMapService;
use yii\web\Controller;
use app\modules\m\controllers\common\BaseController;
use app\models\oauth\OauthMemberBind;
use app\models\sms\SmsCaptcha;
use app\models\member\Member;
use app\common\services\member\MemberService;
use app\common\services\UrlService;
use app\common\services\UtilService;
use app\common\services\QueueListService;
use app\common\services\DataHelper;
use app\models\City;
use app\models\member\MemberAddress;
use app\common\services\AreaService;

class UserController extends BaseController
{

    public function actionIndex(){
        return $this->render('index',[
        	'current_user' => $this->current_user
		]);
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

		$this->record_log("database have this member_info:".var_export($member_info,true) );

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

		$this->record_log("openid is :".$openid );		
        // 判断openid是否存在
		if ($openid) {
			//检查该手机号是否绑定过其他微信（一个手机号只能绑定一个微信,也只能绑定一个支付宝）
            $client_type = ConstantMapService::$client_type_wechat;
            //查询数据库 看看是否已经绑定过了
			$bind_info = OauthMemberBind::findOne([ 'member_id' => $member_info['id'], "openid" => $openid ,'type' => $client_type ]);
			
			$this->record_log("have bind?? :".json_encode($bind_info) );	
			
			//未绑定,需要绑定
            if ( ! $bind_info) {
				$this->record_log("begin bind wechat and member");	
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

				$this->record_log("end bind wechat and member");	

				//绑定之后要做的事情
				QueueListService::addQueue( "bind",[
					'member_id' => $member_info['id'],
					'type' => 1,
					'openid' => $model_bind->openid
				] );
			}
		}

		//如果用户头像或者unionid没有，就获取//这个时候做登录特殊处理，例如更新用户名和头像等等新
		$url = ( $referer && $referer != "/m/user/bind" )?$referer:UrlService::buildMUrl("/");
		if( UtilService::isWechat() && ( $member_info->avatar == ConstantMapService::$default_avatar || $member_info->nickname == $member_info->mobile ) ){
			$url = $this->getAuthLoginUrl('snsapi_userinfo',$referer);
		}

		//设置登录态
		$this->record_log("登录成功后跳转的url是:".$url);	
		$this->setLoginStatus( $member_info );
		return $this->renderJSON([ 'url' => UrlService::buildMUrl("/default/index")  ],"绑定成功~~");
    }

    //购物车
    public function actionCart(){
        
        return $this->render('cart');
    }


    //用户订单
    public function actionOrder(){
        
        return $this->render('order');
    }


    public function actionAddress(){
		//查询当前用户的地址,按照id降序
        $list = MemberAddress::find()->where([ 'member_id' => $this->current_user['id'],'status' => 1 ])
			->orderBy([ 'is_default' => SORT_DESC,'id' => SORT_DESC ])->asArray()->all();
		$data = [];
		if( $list ){
			//查询地区
			$area_mapping = DataHelper::getDicByRelateID( $list,City::className(),"area_id","id",[ 'province','city','area' ] );
			foreach( $list as $_item){
				$tmp_area_info = $area_mapping[ $_item['area_id'] ];
				$tmp_area = $tmp_area_info['province'].$tmp_area_info['city'];
				if( $_item['province_id'] != $_item['city_id'] ){
					$tmp_area .= $tmp_area_info['area'];
				}

				$data[] = [
					'id' => $_item['id'],
					'is_default' => $_item['is_default'],
					'nickname' => UtilService::encode( $_item['nickname'] ),
					'mobile' => UtilService::encode( $_item['mobile'] ),
					'address' => $tmp_area.UtilService::encode( $_item['address'] ),
				];
			}
		}
		return $this->render('address',[
			'list' => $data
		]);
    }

    public function actionAddress_set(){
        if( \Yii::$app->request->isGet ){
			$id = intval( $this->get("id",0) );
			$info = [];
			if( $id ){
				$info = MemberAddress::find()->where([ 'id' => $id,'member_id' => $this->current_user['id'] ])->one();
			}
			return $this->render('address_set',[
				"province_mapping" => AreaService::getProvinceMapping(),
				'info' => $info
			]);
		}

		$id = intval( $this->post("id",0) );
		$nickname = trim( $this->post("nickname","") );
		$mobile = trim( $this->post("mobile","") );
		$province_id = intval( $this->post("province_id",0) );
		$city_id = intval( $this->post("city_id",0) );
		$area_id = intval( $this->post("area_id",0) );
		$address = trim( $this->post("address","" ) );
		$date_now = date("Y-m-d H:i:s");

		if( mb_strlen( $nickname,"utf-8" ) < 1 ){
			return $this->renderJSON([],"请输入符合规范的收货人姓名~~",-1);
		}

		if( !preg_match("/^[1-9]\d{10}$/",$mobile) ){
			return $this->renderJSON([],"请输入符合规范的收货人手机号码~~",-1);
		}

		if( $province_id < 1 ){
			return $this->renderJSON([],"请选择省~~",-1);
		}

		if( $city_id < 1 ){
			return $this->renderJSON([],"请选择市~~",-1);
		}

		if( $area_id < 1 ){
			return $this->renderJSON([],"请选择区~~",-1);
		}

		if( mb_strlen( $address,"utf-8" ) < 3 ){
			return $this->renderJSON([],"请输入符合规范的收货人详细地址~~",-1);
		}

		$info = [];
		if( $id ){
			$info = MemberAddress::find()->where([ 'id' => $id,'member_id' => $this->current_user['id'] ])->one();
		}

		if( $info ){
			$model_address = $info;
		}else{
			$model_address = new MemberAddress();
			$model_address->member_id = $this->current_user['id'];
			$model_address->status = 1;
			$model_address->created_time = $date_now;
		}

		$model_address->nickname = $nickname;
		$model_address->mobile = $mobile;
		$model_address->province_id = $province_id;
		$model_address->city_id = $city_id;
		$model_address->area_id = $area_id;
		$model_address->address = $address;
		$model_address->updated_time = $date_now;
		$model_address->save( 0 );

		return $this->renderJSON([],"操作成功");
    }

    public function actionFav(){
        $list = MemberFav::find()->where([ 'member_id' => $this->current_user['id'] ])->orderBy([ 'id' => SORT_DESC ])->all();
		$data = [];
		if( $list ){
			$book_mapping = DataHelper::getDicByRelateID( $list ,Book::className(),"book_id","id",[ 'name','price','main_image','stock' ] );
			foreach( $list as $_item ){
				$tmp_book_info = $book_mapping[ $_item['book_id'] ];
				$data[] = [
					'id' => $_item['id'],
					'book_id' => $_item['book_id'],
					'book_price' => $tmp_book_info['price'],
					'book_name' => UtilService::encode( $tmp_book_info['name'] ),
					'book_main_image' => UrlService::buildPicUrl( "book",$tmp_book_info['main_image'] )
				];
			}
		}
		return $this->render("fav",[
			'list' => $data
		]);
    }


    public function actionComment(){
		$list = MemberComments::find()->where([ 'member_id' => $this->current_user['id'] ])
			->orderBy([ 'id' => SORT_DESC ])->asArray()->all();

		return $this->render('comment',[
			'list' => $list
		]);
	}

	public function actionComment_set(){
		if( \Yii::$app->request->isGet ){
			$pay_order_id = intval( $this->get("pay_order_id",0) );
			$book_id = intval( $this->get("book_id",0) );
			$pay_order_info = PayOrder::findOne([ 'id' => $pay_order_id,'status' => 1,'express_status' => 1 ]);
			$reback_url = UrlService::buildMUrl("/user/index");
			if( !$pay_order_info ){
				return $this->redirect( $reback_url );
			}

			$pay_order_item_info  = PayOrderItem::findOne([ 'pay_order_id' => $pay_order_id,'target_id' => $book_id ]);
			if( !$pay_order_item_info ){
				return $this->renderJSON( [],ConstantMapService::$default_syserror,-1 );
			}

			if(  $pay_order_item_info['comment_status'] ){
				return $this->renderJS( "您已经评论过啦，不能重复评论~~",$reback_url );
			}


			return $this->render('comment_set',[
				'pay_order_info' => $pay_order_info,
				'book_id' => $book_id
			]);
		}

		$pay_order_id = intval( $this->post("pay_order_id",0) );
		$book_id = intval( $this->post("book_id",0) );
		$score = intval( $this->post("score",0) );
		$content = trim( $this->post('content','') );
		$date_now  = date("Y-m-d H:i:s");

		if( $score <= 0 ){
			return $this->renderJSON([],"请打分~~",-1);
		}

		if( mb_strlen( $content,"utf-8" ) < 3 ){
			return $this->renderJSON([],"请输入符合要求的评论内容~~",-1);
		}

		$pay_order_info = PayOrder::findOne([ 'id' => $pay_order_id,'status' => 1,'express_status' => 1 ]);
		if( !$pay_order_info ){
			return $this->renderJSON( [],ConstantMapService::$default_syserror,-1 );
		}

		$pay_order_item_info  = PayOrderItem::findOne([ 'pay_order_id' => $pay_order_id,'target_id' => $book_id ]);
		if( !$pay_order_item_info ){
			return $this->renderJSON( [],ConstantMapService::$default_syserror,-1 );
		}

		if(  $pay_order_item_info['comment_status'] ){
			return $this->renderJSON( [],"您已经评论过啦，不能重复评论~~",-1 );
		}

		$book_info = Book::findOne([ 'id' => $book_id ]);
		if( !$book_info ){
			return $this->renderJSON( [],ConstantMapService::$default_syserror,-1 );
		}

		$model_comment = new MemberComments();
		$model_comment->member_id = $this->current_user['id'];
		$model_comment->book_id = $book_id;
		$model_comment->pay_order_id = $pay_order_id;
		$model_comment->score = $score * 2;
		$model_comment->content = $content;
		$model_comment->created_time = $date_now;
		$model_comment->save( 0 );

		$pay_order_item_info->comment_status = 1;
		$pay_order_item_info->update( 0 );

		$book_info->comment_count += 1;
		$book_info->update( 0 );


		return $this->renderJSON([],"评论成功~~");
	}
	

	public function actionAddress_ops(){
		$act = trim( $this->post("act","") );
		$id = intval( $this->post("id",0) );

		if( !in_array( $act,[ "del","set_default" ] ) ){
			return $this->renderJSON( [],ConstantMapService::$default_syserror,-1 );
		}

		if( !$id ){
			return $this->renderJSON( [],ConstantMapService::$default_syserror,-1 );
		}

		$info = MemberAddress::find()->where([ 'member_id' => $this->current_user['id'],'id' => $id ])->one();
		switch ( $act ){
			case "del":
				$info->is_default = 0;
				$info->status = 0;
				break;
			case "set_default":
				$info->is_default = 1;
				break;
		}

		$info->updated_time = date("Y-m-d H:i:s");
		$info->update( 0 );

		if( $act == "set_default" ){
			MemberAddress::updateAll(
				[ 'is_default' => 0  ],
				[ 'AND',[ 'member_id' => $this->current_user['id'],'status' => 1 ] ,[ '!=','id',$id ] ]
			);
		}
		return $this->renderJSON( [],"操作成功~~" );
	}
	
}
