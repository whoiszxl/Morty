<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class FinanceController extends Controller
{
    public function actionIndex()
    {
        $this->layout = false;
        return $this->render('index');
    }


    /**
     * 财务流水
     */
    public function actionAccount()
    {
        $this->layout = false;
        return $this->render('account');
    }

    /**
     * 订单详情
     */
    public function actionPay_info()
    {
        $this->layout = false;
        return $this->render('pay_info');
    }

}
