<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class BookController extends Controller
{
    public function actionIndex()
    {
        $this->layout = false;
        return $this->render('index');
    }

    public function actionSet()
    {
        $this->layout = false;
        return $this->render('set');
    }

    public function actionInfo()
    {
        $this->layout = false;
        return $this->render('info');
    }

    public function actionImages()
    {
        $this->layout = false;
        return $this->render('images');
    }


    //图书列表
    public function actionCat()
    {
        $this->layout = false;
        return $this->render('cat');
    }


    //图书编辑或添加
    public function actionCat_set()
    {
        $this->layout = false;
        return $this->render('cat_set');
    }


}
