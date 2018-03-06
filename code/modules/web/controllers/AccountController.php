<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class AccountController extends Controller
{

    //账户列表
    public function actionIndex(){
        $this->layout = true;
        return $this->render('index');
    }

    //账户编辑或者添加
    public function actionSet(){
        $this->layout = true;
        return $this->render('set');
    }


    //账户详情
    public function actionInfo(){
        $this->layout = true;
        return $this->render('info');
    }

}
