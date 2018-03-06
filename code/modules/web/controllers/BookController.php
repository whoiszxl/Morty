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
}
