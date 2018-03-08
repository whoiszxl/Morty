<?php

namespace app\modules\web\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;

class BrandController extends BaseController
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    public function actionInfo()
    {
        
        return $this->render('info');
    }

    public function actionSet()
    {
        
        return $this->render('set');
    }

    public function actionImages()
    {
        
        return $this->render('images');
    }
}
