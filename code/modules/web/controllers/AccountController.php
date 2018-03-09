<?php

namespace app\modules\web\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;
use app\models\User;
use app\common\services\UrlService;
use app\common\services\ConstantMapService;
use app\models\log\AppAccessLog;

class AccountController extends BaseController
{


    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //账户列表
    public function actionIndex(){

        $status = intval($this->get("status", ConstantMapService::$status_default));
        $mix_kw = trim($this->get("mix_kw",""));
        $p = intval($this->get("p", 1));
        
        $query = User::find();
        if($status > ConstantMapService::$status_default){
            $query->andWhere(['status'=>$status]);
        }

        if($mix_kw){
            $where_nickname = ['LIKE','nickname','%'.$mix_kw.'%',false];
            $where_mobile = ['LIKE','mobile','%'.$mix_kw.'%',false];
            $query->andWhere(['OR', $where_nickname, $where_mobile]);
        }

        //分页
        $page_size = 20;
        $total_res_count = $query->count();
        $total_page = ceil($total_res_count / $page_size);

        $list = $query->orderBy(['uid' => SORT_DESC])
            ->offset(($p-1) * $page_size)
            ->limit($page_size)
            ->all();
        return $this->render('index', [
            'list' => $list,
            'status_mapping' => ConstantMapService::$status_mapping,
            'search_conditions' => [
                'mix_kw' => $mix_kw,
                'status' => $status,
                'p' => $p
            ],
            'pages' => [
                'total_count' => $total_res_count,
                'page_size' => $page_size,
                'total_page' => $total_page,
                'p' => $p
            ]
        ]);
    }

    //账户编辑或者添加
    public function actionSet(){

        //判断是不是get提交
        if( \Yii::$app->request->isGet ){
            //获取传递过来的id值
			$id = intval( $this->get("id",0) );
            $info = [];
            //如果存在id,就获取数据库中的记录
			if( $id ){
				$info = User::find()->where([ 'uid' => $id ])->one(  );
            }
            //并且渲染到页面
			return $this->render("set",[
				'info' => $info
			]);
		}

        //post提交,获取post数据
		$id = intval( $this->post("id",0) );
		$nickname = trim( $this->post("nickname","") );
		$mobile = trim( $this->post("mobile","") );
		$email = trim( $this->post("email","") );
		$login_name = trim( $this->post("login_name","") );
		$login_pwd = trim( $this->post("login_pwd","") );
		$date_now  = date("Y-m-d H:i:s");

        //有效性验证
		if( mb_strlen( $nickname,"utf-8" ) < 1 ){
			return $this->renderJSON( [] , "请输入符合规范的姓名~~" ,-1);
		}

		if( mb_strlen( $mobile,"utf-8" ) < 1 ){
			return $this->renderJSON( [] , "请输入符合规范的手机号码~~" ,-1);
		}

		if( mb_strlen( $email,"utf-8" ) < 1 ){
			return $this->renderJSON( [] , "请输入符合规范的邮箱地址~~" ,-1);
		}

		if( mb_strlen( $login_name,"utf-8" ) < 1 ){
			return $this->renderJSON( [] , "请输入符合规范的登录名~~" ,-1);
		}

		if( mb_strlen( $login_pwd,"utf-8" ) < 1 ){
			return $this->renderJSON( [] , "请输入符合规范的登录密码~~" ,-1);
		}

		if( in_array( $login_pwd,ConstantMapService::$low_password ) ){
			return $this->renderJSON( [] , "登录密码太简单，请换一个~~" ,-1);
		}

        //通过登录名查找但是要排除当前的uid去查是否存在这个用户
        $has_in = User::find()->where([ 'login_name' => $login_name ])->andWhere([ '!=','uid',$id ])->count();
		if( $has_in ){
			return $this->renderJSON( [] , "该登录名已存在，请换一个试试~~" ,-1);
		}

        //通过传过来的id查询数据库是否存在
        $info = User::find()->where([ 'uid' => $id ])->one(  );
        //存在就直接更新了
		if( $info ){
			$model_user = $info;
		}else{
            //不存在就新建,然后设置一个新的盐,设置创建时间
			$model_user = new User();
			$model_user->setSalt();
			$model_user->created_time = $date_now;
        }
        //设置post中的值
		$model_user->nickname = $nickname;
		$model_user->mobile = $mobile;
		$model_user->email = $email;
		$model_user->avatar = ConstantMapService::$default_avatar;
		$model_user->login_name = $login_name;
		if( $login_pwd !=  ConstantMapService::$default_password ){
			$model_user->setPassword($login_pwd);
		}
        $model_user->updated_time = $date_now;
        
        //保存一发
		$model_user->save( 0 );

		return $this->renderJSON( [],"操作很ok了" );
    }


    //账户详情
    public function actionInfo(){
        //获取传递过来的id
        $id = intval( $this->get("id",0) );
        //构建一个返回的url
        $reback_url = UrlService::buildWebUrl("/account/index");
        //如果不存在这个id就直接跳回去呀
		if( !$id ){
			return $this->redirect( $reback_url );
		}

        //通过id查询用户信息
        $info = User::find()->where([ 'uid' => $id ])->one();
        //不存在也跳回去
		if( !$info ){
			return $this->redirect( $reback_url );
		}

        //访问日志中查询这个人的记录,查最近10条
		$access_list = AppAccessLog::find()->where([ 'uid' => $id ])->orderBy([ 'id' => SORT_DESC ])->limit( 10 )->all();

        //返回视图并渲染
		return $this->render("info",[
			'info' => $info,
			'access_list' => $access_list
		]);
    }

    //操作方法
    public function actionOps(){
        if(!\Yii::$app->request->isPost){
            return $this->renderJson([],"系统繁忙,请稍后再操作哦",-1);
        }

        $uid = intval($this->post("uid", 0));
        $act = trim($this->post("act", ""));

        if(!$uid){
            return $this->renderJson([], "请选择需要操作的账号!!",-1);
        }

        if(!in_array($act, ["remove","recover"])){
            return $this->renderJson([], "操作有毛病了,please reclick~~~",-1);
        }

        $user_info = User::find()->where(['uid'=>$uid])->one();
        if(!$user_info){
            return $this->renderJsoon([], "你要操作的账号不在了",-1);
        }

        switch($act){
            case "remove":
                $user_info->status = 0;
                break;
            case "recover":
                $user_info->status = 1;
                break;
        }

        $user_info->updated_time = date("Y-m-d H:i:s");
        $user_info->update(0);

        return $this->renderJson([], "操作成功了");
    }

}
