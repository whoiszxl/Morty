<?php

namespace app\modules\m\controllers;

use yii\web\Controller;

class UserController extends Controller
{

    //绑定用户
    public function actionBind(){
        $this->layout = false;
        return $this->render('bind');
    }

    //购物车
    public function actionCart(){
        $this->layout = false;
        return $this->render('cart');
    }


    //用户订单
    public function actionOrder(){
        $this->layout = false;
        return $this->render('order');
    }
}
