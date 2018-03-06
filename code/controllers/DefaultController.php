<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    
    public function actionIndex() {
        //$this->layout = false;
        return $this->render("index");
    }
}
