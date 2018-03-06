<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class BrandController extends Controller
{

    public function actionInfo()
    {
        $this->layout = false;
        return $this->render('info');
    }

    public function actionSet()
    {
        $this->layout = false;
        return $this->render('set');
    }

    public function actionImages()
    {
        $this->layout = false;
        return $this->render('images');
    }
}
