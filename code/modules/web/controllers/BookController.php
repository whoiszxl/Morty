<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class BookController extends Controller
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

    public function actionSet()
    {
        
        return $this->render('set');
    }

    public function actionInfo()
    {
        
        return $this->render('info');
    }

    public function actionImages()
    {
        
        return $this->render('images');
    }


    //图书列表
    public function actionCat()
    {
        
        return $this->render('cat');
    }


    //图书编辑或添加
    public function actionCat_set()
    {
        
        return $this->render('cat_set');
    }


}
