<?php

namespace app\modules\m\controllers;

use yii\web\Controller;
use app\modules\m\controllers\common\BaseController;

class PayController extends BaseController
{


    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }
    //支付
    public function actionBuy()
    {
        return $this->render('buy');
    }
}
