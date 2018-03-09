<?php

namespace app\modules\m\controllers\common;


use app\common\components\BaseWebController;

class BaseController extends BaseWebController{


    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }


    public function beforeAction($action){
        return true;
    }

}