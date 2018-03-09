<?php

namespace app\modules\web\controllers;

use yii\web\Controller;
use app\modules\web\controllers\common\BaseController;
use app\models\User;
use app\common\services\ConstantMapService;

class AccountController extends BaseController
{


    public function __construct($id, $module, array $config = []) {
        parent::__construct($id, $module, $config);
        //指定需要加载的layout的名称,不然会默认加载外部的layout
        $this->layout = "main";
    }

    //账户列表
    public function actionIndex(){

        $status = intval($this->get("status", ConstantMapService::$status_default));
        $mix_kw = trim($this->get("mix_kw",""));
        $p = intval($this->get("p", 1));
        
        $query = User::find();
        if($status > ConstantMapService::$status_default){
            $query->andWhere(['status'=>$status]);
        }

        if($mix_kw){
            $where_nickname = ['LIKE','nickname','%'.$mix_kw.'%',false];
            $where_mobile = ['LIKE','mobile','%'.$mix_kw.'%',false];
            $query->andWhere(['OR', $where_nickname, $where_mobile]);
        }

        //分页
        $page_size = 20;
        $total_res_count = $query->count();
        $total_page = ceil($total_res_count / $page_size);

        $list = $query->orderBy(['uid' => SORT_DESC])
            ->offset(($p-1) * $page_size)
            ->limit($page_size)
            ->all();
        return $this->render('index', [
            'list' => $list,
            'status_mapping' => ConstantMapService::$status_mapping,
            'search_conditions' => [
                'mix_kw' => $mix_kw,
                'status' => $status,
                'p' => $p
            ],
            'pages' => [
                'total_count' => $total_res_count,
                'page_size' => $page_size,
                'total_page' => $total_page,
                'p' => $p
            ]
        ]);
    }

    //账户编辑或者添加
    public function actionSet(){
        return $this->render('set');
    }


    //账户详情
    public function actionInfo(){
        return $this->render('info');
    }

}
