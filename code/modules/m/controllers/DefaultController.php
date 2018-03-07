<?php

namespace app\modules\m\controllers;

use yii\web\Controller;


class DefaultController extends Controller
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //品牌首页
    public function actionIndex()
    {
        return $this->render('index');
    }
}
