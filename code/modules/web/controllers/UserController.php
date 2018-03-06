<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class UserController extends Controller
{

    //登录页面
    public function actionLogin(){
        
        $this->layout = true;
        return $this->render('login');
    }

    //编辑用户信息
    public function actionEdit(){
        
        $this->layout = false;
        return $this->render('edit');
    }


    //重置密码
    public function actionResetPwd(){
        
        $this->layout = false;
        return $this->render('reset_pwd');
    }
}
