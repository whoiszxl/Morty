<?php

namespace app\common\components;

use yii\web\Controller;
use Yii;
use yii\web\HttpException;
/**
 * 集成常用的公用方法,提供给所有controller使用
 */
class BaseWebController extends Controller {

    public $enableCsrfValidation = false;//关闭csrf

    
    //获取http的post参数
    public function post($key, $default = "") {
		return \Yii::$app->request->post($key, $default);
	}


    //获取http的get参数
	public function get($key, $default = "") {
		return \Yii::$app->request->get($key, $default);
    }

    
    public function setTitle($title = ""){
        $this->getView()->title = $title;
    }

	protected function geneReqId() {
		return uniqid();
	}

	protected function setCookie($name,$value,$expire = 0){
		$cookies = \Yii::$app->response->cookies;
		$cookies->add( new \yii\web\Cookie([
			'name' => $name,
			'value' => $value,
			'expire' => $expire
		]));
	}

	protected  function getCookie($name,$default_val=''){
		$cookies = \Yii::$app->request->cookies;
		return $cookies->getValue($name, $default_val);
	}


	protected function removeCookie($name){
		$cookies = \Yii::$app->response->cookies;
		$cookies->remove($name);
	}

	protected function renderJSON($data=[], $msg ="ok", $code = 200)
	{
		header('Content-type: application/json');
		echo json_encode([
			"code" => $code,
			"msg"   =>  $msg,
			"data"  =>  $data,
			"req_id" =>  $this->geneReqId(),
		]);

		return \Yii::$app->end();
	}

	protected  function renderJS($msg,$url = "/"){
		return $this->renderPartial("@app/views/common/js", ['msg' => $msg, 'location' => $url]);
	}

    protected function isAjax()
    {
        return Yii::$app->request->isAjax;
    }

    /**
     * 统一响应ajax
     * 请求规范
     * @param $status
     * @param $msg
     * @param array $arrOther
     * @return array
     */
    public function getResponseForAjax($status, $msg, array $arrOther=[])
    {
        $arrResponse = ['status'=>$status, 'info'=>$msg];

        return array_merge($arrResponse, $arrOther);
    }

}