<?php
print_r($_POST);

$code = $_POST['code'];
$app_code = $_GET['app_code'];
$access_token = $_GET['access_token'];

if(!$code){
    echo "<script>alert('정상적인 접근이 아닙니다.'); window.close(); opener.location.href='/login.php'; </script>";
    return;
}

$teamId = 'DQALHSS7F2';
//$clientId = 'interview.bluevisor.com';

if ($app_code == 'apple') {
    $clientId = 'com.bluevisor.interview'; //app
} else {
    $clientId = 'interview.bluevisor.com'; //web
}

$keyFileId = '738B4R89BJ';
$keyFileName = 'AuthKey_Alphagong.pem';
$redirectUri = 'https://api.highbuff.com/interview/20/call_back.php';
$authorizationCode = $code;

if ($app_code == 'apple') {
    $authorizationCode = $access_token;
} else {
    $authorizationCode = $code;
}


$jwt = generateJWT($keyFileId, $teamId, $clientId);

$data = [
    'client_id' => $clientId,
    'client_secret' => $jwt,
    'code' => $authorizationCode,
    'grant_type' => 'authorization_code',
    'redirect_uri' => $redirectUri
];

function encode($data) {	
    $encoded = strtr(base64_encode($data), '+/', '-_');
    return rtrim($encoded, '=');
}

/********* JWT을 사용하여 Client_secret 암호화 **********/

function generateJWT($keyFileId, $teamId, $clientId) {
	$header = [
		'alg' => 'ES256',
		'kid' => $keyFileId
	];
	$body = [
		'iss' => $teamId,
		'iat' => time(),
		'exp' => time() + 3600,
		'aud' => 'https://appleid.apple.com',
		'sub' => $clientId
	];

	$privKey = openssl_pkey_get_private(file_get_contents($_SERVER['DOCUMENT_ROOT']."/interview/20/apple/AuthKey_Alphagong.pem"));
		
	if (!$privKey){
	   return false;
	}

	$payload = encode(json_encode($header)).'.'.encode(json_encode($body));

	$signature = '';
	$success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
	if (!$success) return false;

	$raw_signature = fromDER($signature, 64);

	return $payload.'.'.encode($raw_signature);
}


/********* 데이터 암호화 함수 **********/

 /**
 * @param string $der
 * @param int    $partLength
 *
 * @return string
 */
function fromDER(string $der, int $partLength)
{
	$hex = unpack('H*', $der)[1];
	if ('30' !== mb_substr($hex, 0, 2, '8bit')) { // SEQUENCE
		throw new \RuntimeException();
	}
	if ('81' === mb_substr($hex, 2, 2, '8bit')) { // LENGTH > 128
		$hex = mb_substr($hex, 6, null, '8bit');
	} else {
		$hex = mb_substr($hex, 4, null, '8bit');
	}
	if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
		throw new \RuntimeException();
	}
	$Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
	$R = retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
	$R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
	$hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');
	if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
		throw new \RuntimeException();
	}
	$Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
	$S = retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
	$S = str_pad($S, $partLength, '0', STR_PAD_LEFT);
	return pack('H*', $R.$S);
}

/**
 * @param string $data
 *
 * @return string
 */
function retrievePositiveInteger(string $data)
{
	while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
		$data = mb_substr($data, 2, null, '8bit');
	}
	return $data;
}

/********* 데이터 담기 **********/

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://appleid.apple.com/auth/token');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch); //실행
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$response = json_decode($result);
$claims = explode('.', $response->id_token)[1];
$claims = json_decode(base64_decode($claims));

print_r($result);

if($status_code == 200) {
    $user_key = $claims->sub;
    $email = $claims->email;
    $token= $response->access_token;

    //header('Location: https://localinterviewr.highbuff.com/sns/apple/web/call?code='.$code.'&user_key='.$user_key.'&email='.$email.'&token='.$token);
    //header('Location: https://webtestinterviewr.highbuff.com/sns/apple/web/call?code='.$code.'&user_key='.$user_key.'&email='.$email.'&token='.$token);
    header('Location: https://interview.highbuff.com/sns/apple/web/call?code='.$code.'&user_key='.$user_key.'&email='.$email.'&token='.$token);
} else {
    echo "<script>alert('정상적인 접근이 아닙니다.'); window.close(); opener.location.href='/login.php'; </script>";
    return;
}

?>


