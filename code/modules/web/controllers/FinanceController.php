<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class FinanceController extends Controller
{


    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    public function actionIndex()
    {
        
        return $this->render('index');
    }


    /**
     * 财务流水
     */
    public function actionAccount()
    {
        
        return $this->render('account');
    }

    /**
     * 订单详情
     */
    public function actionPay_info()
    {
        
        return $this->render('pay_info');
    }

}
