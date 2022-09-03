<?php

function displayError(){
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}

function getRandomString($length = 6) {
    return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

function telegram_send($msg, $bot){
	$dates = date("Y-m-d H:i:s");
	$addr = $_SERVER["REMOTE_ADDR"];

	if($bot == "HB"){
		$bots = "https://api.telegram.org/bot717979983:AAGhwOUPdtx-BxbD05F3iofJJW9K5Ktlxqk/sendmessage?chat_id=-1001426729381&text="; //HB_PB
	}else if($bot == "HB_TEST"){
		$bots = "https://api.telegram.org/bot709119014:AAH62cjLzTUTAY7-cn6dxobrTlsLkuMlDzE/sendmessage?chat_id=-312690750&text="; //HB_PB_TEST
	}else if($bot == "inputmoneycs"){
		$bots = "https://api.telegram.org/bot1064502462:AAEfITT34pWRc1SG2gTns4H_S6OK6Pe3X0EU/sendmessage?chat_id=-366137723&text="; //bluevisor(입출금관련내용)
	}else if($bot == "DEV"){
		$bots = "https://api.telegram.org/bot5078097512:AAFlbpDsfxa0VertR3_SDkzP3golEVmDieQ/sendMessage?chat_id=-1001765236622&parse_mode=HTML&text=";
	}else if($bot == "company"){
		$bots = "https://api.telegram.org/bot5090857619:AAEPEvPD9MxC0-_GTAwa7AAK7j-ZSSieIvc/sendmessage?chat_id=-693228471&parse_mode=HTML&text=";
	} else if($bot == "LABELING"){
		$bots = "https://api.telegram.org/bot2019878841:AAHeJCIKWs0UTS98zB7ab4XVa8ujnaBx1eg/sendmessage?chat_id=-1001689923646&parse_mode=HTML&text=";
	}
	$str = urlencode($msg."\n\n접속정보 : ".$addr."\n시간 : ".$dates);
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $bots.$str
	));
	$resp = curl_exec($curl);
}

function sendWideShotMessage($msg, $phone, $conn, $userid=null){
	$apikey = "VTN6dXVKUlVWT2U4K1REZEJtZzhpYlN0WlFuOWxIM3dWVE8veE40Tk81T0czdGxTNTErVkhGb09BZGZseC9LdVJCS2F2cWVxMTVNR0tEa01zSThCWVE9PQ==";//bluevisorapi1
	$receiver = str_replace("-","",$phone);
	$sender = "18554549";
	$strlen = mb_strlen($msg, "euc-kr");
	$userkey = time().rand(100,999);

	if($strlen > 85){ // lms
		$url = "https://api.wideshot.co.kr/api/v1/message/lms";
	}else{ //sms or isms
    if(substr($receiver, 0, 3) === "010"){ //010으로 시작하면 sms
			$url = "https://api.wideshot.co.kr/api/v1/message/sms";
		}else{ //isms
			$url = "https://api.wideshot.co.kr/api/v1/message/isms";
		}
	}

	$header_data = array(
		'sejongApiKey:'.$apikey
	);
	$post_data = array(
		'callback' => $sender,
		'contents' => $msg,
		'receiverTelNo' => $receiver,
		'title' => "하이버프",
		'userKey' => $userkey
	);
	$ch = curl_init($url);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $header_data);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);

	$json = json_decode($result, true);
	$sendResult = $json["code"];
	if($sendResult != "200"){
		$sendResult = $sendResult."/".$json["message"];
	}
	
	$qry = "INSERT INTO sms_result(`userkey`, `user_id`, `content`, `mobile`, `status`, `dates`) VALUES('".$userkey."', '".$userid."', '".$msg."', '".$receiver."', '".$sendResult."', '".date("Y-m-d H:i:s")."')";
	mysqli_query($conn, $qry);

	if($sendResult != "200"){
		return false;
	}else{
		return true;
  }
}

function setEncrypt($str, $secret_key='secret key') {
	$key = substr(hash('sha256', $secret_key, true), 0, 32);
	$iv = substr(hash('sha256', $secret_key, true), 0, 16);
	return base64_encode(openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv));
}

function setDecrypt($str, $secret_key='secret key') {
	$key = substr(hash('sha256', $secret_key, true), 0, 32);
	$iv = substr(hash('sha256', $secret_key, true), 0, 16);
	return openssl_decrypt(base64_decode($str), "AES-256-CBC", $key, 0, $iv);
}

function getUserSnsProvider($user_id) {
    if (strpos($user_id, "kakao_") !== false) {
        return "KAKAO";
    } else if (strpos($user_id, "naver_") !== false) {
        return "NAVER";
    }  else if (strpos($user_id, "google_") !== false) {
        return "GOOGLE";
    } else if (strpos($user_id, "apple_") !== false) {
        return "APPLE";
    } else {
        return false;
    }
}

//access_token이 유효한지 확인하는 함수
function isValidAccessToken($access_token) {
    $url = 'https://api.highbuff.com/oauth2/resource.php';
    $post_data = 'access_token='.$access_token; //x-www-form-urlencoded => post data
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($result, true);

    if (isset($json['error'])) { //error
        if ($json['error_description']  == 'The access token provided is invalid') { //invalid token
            return -10;
        } else if ($json['error_description']  == 'The access token provided has expired') { //expired token
            return -11;
        } else { //other error
            return -1; 
        }
    } else { //success
        return true;
    }
}

//access_token을 전달하여 accee token 검증하고, 맞으면 user_id를 구함
function getUserId($access_token) {
    $result = isValidAccessToken($access_token);
    if ($result === true) {
        $url = 'https://api.highbuff.com/oauth2/token.php';
        $post_data = array(
            'grant_type' => 'token_credentials',
            'access_token' => $access_token
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($result, true);

        if (isset($json['error'])) { //error
            return -10;
        } else { //success
            return array('user_id' => $json['user_id']);
        }
    } else { //error
        return $result;
    }
}

//----------------------------------------------
//==============================================

function goto_url($url){
    $url = str_replace("&amp;", "&", $url);
    //echo "<script> location.replace('$url'); </script>";

    if (!headers_sent())
        header('Location: '.$url);
    else {
        echo '<script>';
        echo 'location.replace("'.$url.'");';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }
    exit;
}

// 세션변수 생성
function setSession($session_name, $value){
    if (PHP_VERSION < '5.3.0')
        session_register($session_name);
    // PHP 버전별 차이를 없애기 위한 방법
    $$session_name = $_SESSION[$session_name] = $value;
}


// 세션변수값 얻음
function getSession($session_name){
    return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : '';
}


// 쿠키변수 생성
function setCookies($cookie_name, $value, $expire){
    setcookie(md5($cookie_name), base64_encode($value), time() + $expire, '/', COOKIE_DOMAIN);
}

// 쿠키변수값 얻음
function getCookies($cookie_name){
    $cookie = md5($cookie_name);
    if (array_key_exists($cookie, $_COOKIE))
        return base64_decode($_COOKIE[$cookie]);
    else
        return "";
}


// 경고메세지를 경고창으로
function alert($msg='', $back=''){
    $msg = $msg ? strip_tags($msg, '<br>') : '올바른 방법으로 이용해 주십시오.';
	echo "<script>alert('".$msg."')</script>";
	if($back == 'back'){
		echo "<script>history.back(-1)</script>";
	}else{
		echo "<script>location.href='".$back."'</script>";
	}
}

function MobileCheck() {
  global $HTTP_USER_AGENT;
  $MobileArray  = array("iphone","lgtelecom","skt","mobile","samsung","nokia","blackberry","android","android","sony","phone");
  $checkCount = 0;
      for($i=0; $i<sizeof($MobileArray); $i++){
          if(preg_match("/$MobileArray[$i]/", strtolower($HTTP_USER_AGENT))){ $checkCount++; break; }
      }
  return ($checkCount >= 1) ? "Mobile" : "Computer";
}


function rtn_mobile_chk() {
    // 모바일 기종(배열 순서 중요, 대소문자 구분 안함)
    $ary_m = array("iPhone","iPod","IPad","Android","Blackberry","SymbianOS|SCH-M\d+","Opera Mini","Windows CE","Nokia","Sony","Samsung","LGTelecom","SKT","Mobile","Phone");

    for($i=0; $i<count($ary_m); $i++){
        if(preg_match("/$ary_m[$i]/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return $ary_m[$i];
            break;
        }
    }

    return "PC";

}

/*
 * mecab php바인딩후 태그와 택스트로 분리하기
 *
 * @param string $str 문자열
 * @param array $code 걸러내고자 하는 코드값 NNG등
 * @return array $mecab_array 배열로 리턴
 */
function incodingMecab($str, $code = array()){
    $mecab_array = array();
    $mecab = new \MeCab\Tagger(['-d', '/usr/local/lib/mecab/dic/mecab-ko-dic']);

    //형태소분석하여 결과값 도출
    $result = $mecab->parse($str);
     
    //결과값에서 줄단위로 분리
    preg_match_all('/[^EOS](.*)\n/', $result, $find_code);
 
    //각줄별로 루프를 돌며 텍스트와 태그(코드)값분리
    for($i=0; $i < count($find_code[0]); $i++)
    {
        preg_match('/(.*)(?=\t)/', $find_code[0][$i], $find_text); // text
        preg_match('/(?<=\t)([^\,]+)/', $find_code[0][$i], $find_tag); // tag
        //걸러내고자하는 코드가 있을시
        if(count($code) > 0)
        {
            //걸러내려는 코드안에 태그가 포함되는지
            if(in_array($find_tag[0],$code)
                //중복되는 텍스트가 있는지
                && in_array($find_text[0],$mecab_array) === false)
            {
                $mecab_array[] = $find_text[0];
                //$mecab_array[$i]["code"] = $find_tag[0]; //태그값은 필요 없어 주석
            }
        } else {
            //중복되는 텍스트가 있는지
            if(in_array($find_text[0],$mecab_array) === false)
            {
                $mecab_array[] = $find_text[0];
                //$mecab_array[$i]["code"] = $find_tag[0];//태그값은 필요 없어 주석
            }
        }
    }
    //객체를 비움
    //mecab_destroy($mecab);
    return $mecab_array;
}

?>