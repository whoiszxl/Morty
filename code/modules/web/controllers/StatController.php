<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class StatController extends Controller
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //财务统计
    public function actionIndex(){
        
        return $this->render('index');
    }


    //商品售卖统计
    public function actionProduct(){
        
        return $this->render('product');
    }


    //会员消费统计
    public function actionMember(){
        
        return $this->render('member');
    }


    //分享统计
    public function actionShare(){
        
        return $this->render('share');
    }

}
