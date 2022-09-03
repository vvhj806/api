<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 
include_once($_SERVER["DOCUMENT_ROOT"].'/alphagong/sns/config.php');

$client_id = $_POST['client_id']; //app uid
$user_key = $_POST['user_key']; //user_key
$email = $_POST['email']; //email

if ($user_key == '' || $client_id == '') {
    echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    $str = "[API_ERROR]\n경로 : /alphagong/sns/google/call_back.php\n에러 : google login not found user_key";
    telegram_send($str, "HB_TEST");
    return;
}

$object_sha = sha1($user_key);
$sql1 = "SELECT * FROM iv_sns_member WHERE `provider` = 'google' AND user_key = '".$user_key."' AND update_dates != ''"; 
$rst1 = mysqli_query($conn, $sql1);
$row1 = mysqli_fetch_array($rst1);
if ($email == ''){ //GET으로 들어온 이메일이 없다면
    $sql2 = "SELECT email FROM iv_sns_member WHERE `provider` = 'google' AND user_key = '".$user_key."'AND email != ''"; 
    $rst2 = mysqli_query($conn, $sql2);
    $row2 = mysqli_fetch_array($rst2);
    $email = $row2["email"];
}

$sql3 = "SELECT * FROM g5_member WHERE mb_id = 'google_".$object_sha."'";
$rst3 = mysqli_query($conn, $sql3);
$row3 = mysqli_fetch_array($rst3);
if ($row3['mb_leave_date'] != '') { //탈퇴회원
    echo json_encode(array('status' => -303, 'message' => '이미 탈퇴한 계정입니다.'));
    return;
} else if (!$row3) { //계정이 없는경우
    echo json_encode(array('status' => -303, 'message' => '존재하지 않는 계정입니다.'));
    return;
}

if (isset($row1)) { //기존 SNS 회원이라면 access_token 전달
    //유저 정보를 가지고 API 조회. 등록된 유저가 아닐 경우에는 유저를 등록, 등록된 유저는 access_token 전달
    $client_secret = setEncrypt($client_id, 'bluevisorencrypt'); //AES256CDC 암호화하여 secret 값 생성
    $url = 'https://api.highbuff.com/oauth2/token.php';
    $post_data = array(
        'grant_type' => 'client_credentials',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'client_scope' => 'alphagong',
        'user_id' => 'google_'.$object_sha
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $use_profile = 'https://alphagong.highbuff.com/img/profile/noprofile.png';
    $sql4 = "SELECT thumbnail FROM `iv_applier` WHERE user_id = '".$row3['mb_id']."' AND thumbnail != '' ORDER BY idx LIMIT 1";
    $rst4 = mysqli_query($conn, $sql4);
    $row4 = mysqli_fetch_assoc($rst4);
    if (isset($row4)) {
        $use_profile = 'https://alphagong.highbuff.com/data/uploads_thumbnail/'.$row4['thumbnail'];
    }

    $json = json_decode($result, true);
    if (isset($json['error'])) {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    } else {
        echo json_encode(array('status' => 200, 'access_token' => $json['access_token'], 'user_name' => $row3['mb_name'], 'user_profile' => $use_profile));
    }
} else { //기존 소셜로그인 회원이 아니라면
    //iv_sns_member 데이터 삽입
    $sql4 = "INSERT INTO iv_sns_member SET provider = 'google', user_key = '".$user_key."', object_sha = '".$object_sha."', email = '".$email."', dates = '".date("Y-m-d H:i:s")."'";
    mysqli_query($conn, $sql4);
    
    //회원가입 페이지로 이동 필요
    echo json_encode(array('status' => 201, 'message' => 'SNS 회원가입이 필요합니다.'));
}