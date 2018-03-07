<?php

namespace app\modules\m\controllers;

use yii\web\Controller;

class UserController extends Controller
{

    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //绑定用户
    public function actionBind(){
        
        return $this->render('bind');
    }

    //购物车
    public function actionCart(){
        
        return $this->render('cart');
    }


    //用户订单
    public function actionOrder(){
        
        return $this->render('order');
    }


    public function actionIndex(){
        
        return $this->render('index');
    }

    public function actionAddress(){
        
        return $this->render('address');
    }

    public function actionAddress_set(){
        
        return $this->render('address_set');
    }

    public function actionFav(){
        
        return $this->render('fav');
    }


    public function actionComment(){
        
        return $this->render('comment');
    }


    public function actionComment_set(){
        
        return $this->render('comment_set');
    }
}
