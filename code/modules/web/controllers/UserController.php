<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class UserController extends Controller
{

    //登录页面
    public function actionLogin(){
        
        $this->layout = "user";
        return $this->render('login');
    }

    //编辑用户信息
    public function actionEdit(){
        
        $this->layout = "main";
        return $this->render('edit');
    }


    //重置密码
    public function actionResetPwd(){
        
        $this->layout = "main";
        return $this->render('reset_pwd');
    }
}
