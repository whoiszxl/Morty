<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\common\components\BaseWebController;

class DefaultController extends BaseWebController
{
    
    public function actionIndex() {
        //$this->layout = false;
        return $this->render("index");
    }
}
