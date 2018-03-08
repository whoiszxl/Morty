<?php

namespace app\modules\web\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;

class AccountController extends BaseController
{


    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //账户列表
    public function actionIndex(){
        return $this->render('index');
    }

    //账户编辑或者添加
    public function actionSet(){
        return $this->render('set');
    }


    //账户详情
    public function actionInfo(){
        return $this->render('info');
    }

}
