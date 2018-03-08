<?php

namespace app\modules\web\controllers;

use yii\web\Controller;
use app\models\User;
use app\common\services\UrlService;
use app\modules\web\controllers\common\BaseController;

class UserController extends BaseController
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //登录页面
    public function actionLogin(){
        if(\Yii::$app->request->isGet){
            $this->layout = "user";
            return $this->render('login');
        }

        //1.登录逻辑处理
        $login_name = trim($this->post('login_name',''));
        $login_pwd = trim($this->post('login_pwd',''));

        if(!$login_name || !$login_pwd){
            return $this->renderJS('请输入正确的用户名和密码', UrlService::buildWebUrl('/user/login'));
        }

        //2.从数据库通过用户名获取记录
        $user_info = User::find()->where(['login_name' => $login_name])->one();
        if(!$user_info){
            return $this->renderJS('请输入正确的用户名', UrlService::buildWebUrl('/user/login'));
        }

        //3.验证密码
        //密码加密算法: md5(login_pwd + md5(login_salt))
        $auth_pwd = md5($login_pwd.md5($user_info['login_salt']));
        if($auth_pwd != $user_info['login_pwd']){
            return $this->renderJS('请输入正确的密码', UrlService::buildWebUrl('/user/login'));
        }

        //4.保存用户的登录状态到cookie
        $this->setLoginStatus($user_info);


        return $this->redirect(UrlService::buildWebUrl("/dashboard/index"));

    }

    //编辑用户信息
    public function actionEdit(){

        if(\Yii::$app->request->isGet){

            //获取当前登录人的信息并且渲染到前端
            return $this->render("edit", ['user_info'=>$this->current_user]);
        }

        
    }


    //重置密码
    public function actionResetPwd(){
        return $this->render('reset_pwd');
    }

    public function actionLogout(){
        $this->removeLoginStatus();
        return $this->redirect(UrlService::buildWebUrl("/user/login"));
    }
}
