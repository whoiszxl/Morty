<?php

namespace app\modules\m\controllers;

use yii\web\Controller;
use app\modules\m\controllers\common\BaseController;
use app\models\brand\BrandImages;
use app\models\brand\BrandSetting;

class DefaultController extends BaseController{


    //品牌首页
    public function actionIndex(){

        $info = BrandSetting::find()->one();
    	$image_list = BrandImages::find()->all();

        return $this->render('index',[
        	'info' => $info,
			'image_list' => $image_list
		]);
    }
}
