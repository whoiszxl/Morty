<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class QrcodeController extends Controller
{
    public function actionIndex(){
        $this->layout = false;
        return $this->render('index');
    }

    public function actionSet(){
        $this->layout = false;
        return $this->render('set');
    }
}
