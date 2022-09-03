<?php
$access_token = $_GET['at'];
$isApp = $_GET['isApp'];
$leaveCheck = $_GET['leaveCheck'];

if (!$access_token) {
    echo "<script>alert('정상적인 접근이 아닙니다.'); window.close(); opener.location.href='/login.php'; </script>";
    return;
}

// 연동해제 하기
$teamId = 'DQALHSS7F2';
if($isApp == 1) {
    $clientId = 'com.bluevisor.interview'; //app
} else {
    $clientId = 'interview.bluevisor.com'; //web
}
$keyFileId = '738B4R89BJ';
$keyFileName = 'AuthKey_Alphagong.pem';
$redirectUri = 'https://api.highbuff.com/interview/20/leave.php';

$jwt = generateJWT($keyFileId, $teamId, $clientId);

if($leaveCheck == 'login' && $isApp==1){
$data = [
    'client_id' => $clientId,
    'client_secret' => $jwt,
    'code' => $access_token,
    'grant_type' => 'authorization_code',
    'redirect_uri' => $redirectUri
];
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
  
  if ($status_code == 200) {
      $access_token = $response->access_token; // 액세스 토큰
  }
}

$data = [
    'client_id' => $clientId,
    'client_secret' => $jwt,
    'token' => $access_token
];

function encode($data)
{
    $encoded = strtr(base64_encode($data), '+/', '-_');
    return rtrim($encoded, '=');
}

/********* JWT을 사용하여 Client_secret 암호화 **********/

function generateJWT($keyFileId, $teamId, $clientId)
{
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

    $privKey = openssl_pkey_get_private(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/interview/20/apple/AuthKey_Alphagong.pem"));

    if (!$privKey) {
        return false;
    }

    $payload = encode(json_encode($header)) . '.' . encode(json_encode($body));

    $signature = '';
    $success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
    if (!$success) return false;

    $raw_signature = fromDER($signature, 64);

    return $payload . '.' . encode($raw_signature);
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
    return pack('H*', $R . $S);
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



$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://appleid.apple.com/auth/revoke');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch); //실행
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$response = json_decode($result);
curl_close($ch);

if ($status_code == 200) {
    //애플쪽에서 응답값을 던져주고 있지않음
    
}

if($leaveCheck=='login'){
header('Location: https://interview.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke&leaveCheck=login&isApp='.$isApp);
//header('Location: https://localinterviewr.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke&leaveCheck=login&isApp='.$isApp);
//header('Location: https://webtestinterviewr.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke&leaveCheck=login&isApp='.$isApp);
} else {
header('Location: https://interview.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke&isApp='.$isApp);
//header('Location: https://localinterviewr.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke');
//header('Location: https://webtestinterviewr.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke&isApp='.$isApp);
}
// 주소 interview로 바꾸기
// header('Location: https://localinterviewr.highbuff.com/sns/apple/web/leave/callback?leaveState=revoke');

