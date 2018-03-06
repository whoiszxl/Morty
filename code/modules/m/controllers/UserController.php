<?php

namespace app\modules\m\controllers;

use yii\web\Controller;

class UserController extends Controller
{

    public function actionBind(){
        $this->layout = false;
        return $this->render('bind');
    }
}
