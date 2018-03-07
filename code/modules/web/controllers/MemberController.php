<?php

namespace app\modules\web\controllers;

use yii\web\Controller;

class MemberController extends Controller
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    public function actionIndex(){
        
        return $this->render('index');
    }

    public function actionInfo(){
        
        return $this->render('info');
    }

    public function actionSet(){
        
        return $this->render('set');
    }


    public function actionComment(){
        
        return $this->render('comment');
    }
}
