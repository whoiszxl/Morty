<?php

namespace app\modules\weixin\controllers;

use app\common\services\SearchService;
use app\common\services\UrlService;
use app\common\services\weixin\MessageService;
use app\common\services\weixin\MsgCryptService;
use app\models\book\Book;

/**
 * 
 */
class MsgController extends BaseController{
    
    public function actionIndex(){
        
        if(!$this->checkSignature()){
            $this->record_log( "[微信签名验证是否通过]:未通过" );
            return "error signature";
        }
        $this->record_log( "[微信签名验证是否通过]:通过了" );

        //微信第一次认证
        if(array_key_exists("echostr",$_GET) && $_GET["echostr"]){
            return $_GET["echostr"];
        }

        $this->search( "你好" );
        exit;
        /**
         * 因为很多都设置了register_globals禁止,不能用$GLOBALS["HTTP_RAW_POST_DATA"];
         * php://input 是个可以访问请求的原始数据的只读流。 POST 请求的情况下，
         * 最好使用 php://input 来代替 $HTTP_RAW_POST_DATA，因为它不依赖于特定的 php.ini 指令。
         * 而且，这样的情况下 $HTTP_RAW_POST_DATA 默认没有填充， 
         * 比激活 always_populate_raw_post_data 潜在需要更少的内存。 
         * enctype="multipart/form-data" 的时候 php://input 是无效的。 
         */
        $xml_data = file_get_contents("php://input");
        //打一个日志
		$this->record_log( "[xml_data]:". $xml_data );
		if( !$xml_data ){
            $this->record_log( "[怕是xml消息没通过]" );
			return 'error xml ~~';
		}

		$msg_signature = trim( $this->get("msg_signature","") );
		$timestamp = trim( $this->get("timestamp","") );
		$nonce = trim( $this->get("nonce","") );

		$config = \Yii::$app->params['weixin'];
		$target = new MsgCryptService( $config['token'], $config['aeskey'], $config['appid']);
		$err_code = $target->decryptMsg($msg_signature, $timestamp, $nonce, $xml_data, $decode_xml);
		if ( $err_code != 0) {
            $this->record_log( "[那个加密消息报错了]:". $xml_data );
			return 'error decode ~~';
		}

		$this->record_log( '[decode_xml]:'.$decode_xml );

		MessageService::add( $decode_xml );

		$xml_obj = simplexml_load_string($decode_xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$from_username = $xml_obj->FromUserName;
		$to_username = $xml_obj->ToUserName;
		$msg_type = $xml_obj->MsgType;//信息类型
		$reply_time = time();

		$res = [ 'type'=>'text','data'=>$this->defaultTip() ];
		switch ( $msg_type ){
			case "text":
				if( $xml_obj->Content == "商城账号"){
					$res = [ 'type'=>'text','data'=> '系统正在开发，请等系统正式上线再来获取' ];
				}else{
					$kw = trim( $xml_obj->Content );
					$res = $this->search( $kw );
				}
				break;
			case "event":
				$res = $this->parseEvent( $xml_obj );
				break;
			default:
				break;
		}

		switch($res['type']){
			case "rich":
				$plain_data = $this->richTpl($from_username,$to_username,$res['data']);
				break;
			default:
				$plain_data = $this->textTpl($from_username,$to_username,$res['data']);
		}

		$encrypt_msg = '';
		$err_code = $target->encryptMsg($plain_data, $reply_time, $nonce,$encrypt_msg);
		if ( $err_code != 0) {
			return 'error encode ~~';
		}
		return $encrypt_msg;
    }


    public function checkSignature() {
        $signature = trim($this->get("signature",""));
        $timestamp = trim($this->get("timestamp",""));
        $nonce = trim($this->get("nonce",""));
        $tmpArr = array(\Yii::$app->params['weixin']['token'],$timestamp,$nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }

    }


    private function search( $kw ){
		$res = SearchService::search( [ 'kw' => $kw,'limit' => 3 ] );
		$data = $res['total']?$this->getRichXml( $res['data'] ):$this->defaultTip();
		$type = $res['total']?"rich":"text";
		return ['type' => $type ,"data" => $data];
	}

	private function parseEvent( $dataObj ){
		$resType = "text";
		$resData = $this->defaultTip();
		$event = $dataObj->Event;
		switch($event){
			case "subscribe":

				$resData = $this->subscribeTips();
				break;
			case "CLICK"://自定义菜单点击类型是CLICK的，可以回复指定内容
				$eventKey = trim($dataObj->EventKey);
				switch($eventKey){
				}
				break;
		}
		return [ 'type'=>$resType,'data'=>$resData ];
	}

	//文本内容模板
    private function textTpl( $from_username,$to_username,$content )
	{
		$textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>";
		return sprintf($textTpl, $from_username, $to_username, time(), "text", $content);
	}

	//富文本
	private function richTpl( $from_username ,$to_username,$data){
		$tpl = <<<EOT
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
%s
</xml>
EOT;
		return sprintf($tpl, $from_username, $to_username, time(), $data);
	}

	private function getRichXml( $list ){
		$article_count = count( $list );
		$article_content = "";
		foreach($list as $_item){
			$tmp_description = mb_substr( strip_tags( $_item['summary'] ),0,20,"utf-8" );
			$tmp_pic_url = UrlService::buildPicUrl( "book",$_item['main_image'] );
			$tmp_url = UrlService::buildMUrl( "/product/info",[ 'id' => $_item['id'] ] );
			$article_content .= "
<item>
<Title><![CDATA[{$_item['name']}]]></Title>
<Description><![CDATA[{$tmp_description}]]></Description>
<PicUrl><![CDATA[{$tmp_pic_url}]]></PicUrl>
<Url><![CDATA[{$tmp_url}]]></Url>
</item>";
		}

		$article_body = "<ArticleCount>%s</ArticleCount>
<Articles>
%s
</Articles>";
		return sprintf($article_body,$article_count,$article_content);
	}

	/**
	 * 默认回复语
	 */
	private function defaultTip(){
		$resData = <<<EOT
没找到你想要的东西（：\n
EOT;
		return $resData;
	}

	/**
	 * 关注默认提示
	 */
	private function subscribeTips(){
		$resData = <<<EOT
        多谢你挂住我
EOT;
			return $resData;
		}
}
