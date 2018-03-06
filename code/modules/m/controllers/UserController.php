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


    public function actionIndex(){
        $this->layout = false;
        return $this->render('index');
    }

    public function actionAddress(){
        $this->layout = false;
        return $this->render('address');
    }

    public function actionAddress_set(){
        $this->layout = false;
        return $this->render('address_set');
    }

    public function actionFav(){
        $this->layout = false;
        return $this->render('fav');
    }


    public function actionComment(){
        $this->layout = false;
        return $this->render('comment');
    }


    public function actionComment_set(){
        $this->layout = false;
        return $this->render('comment_set');
    }
}
