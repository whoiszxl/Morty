<?php

namespace app\modules\web\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;

class DashboardController extends BaseController
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

}
