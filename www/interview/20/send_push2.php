<?php

include_once('common.php');

//$token = $_REQUEST['token'];
//$title = $_REQUEST['title'];
//$message = $_REQUEST['message'];

//손원호 테스트
$token = "cxpSvXPsTGOyaGC2SjzwJ0:APA91bFTMPXJiACv48gyughkmWSIZ1IOmoYRG1pkOti6dRGsEg3ygy4wG6sp7npCsex1LgGQggdJIpEwqYxObvcW6O5qt-5xW0IS6iuMiHIvYrldcs702qs9wJsll0tEjhNPlfRWzLt9";
//김태완 테스트
//$token = "ck5Kt5PlR3S12rUrjGA5PJ:APA91bEHwqj7m6RZZooh2TAU8Sh_oY2h4zdl70Y8cHbaiqaO1McIraxSTMMJAH4Qa6hsk26ib59z9Bo3zWiR5jD2JUFkj7yfFSpAlJs6OSz_Ou2sRjMfZzgSWeNP-BYRlJkVjzhjUlZ4";
//멀티 테스트
// $token = [
//     "cIXMcw9MSQeYxIun0No7Wy:APA91bGSyJYB7Fmbmf-_tu8Kg-Z9HnAgnpDMDU9a9vUoVGMFryI0wWwqCfh1_k9V_lPJuWSq5RG5uWXdvLmIitrMU9wp4fK2LXdDIhJTINcE0PkZySt6dQ5iX5Nhy3PymFKaTTEmMhow",
//     "ck5Kt5PlR3S12rUrjGA5PJ:APA91bEHwqj7m6RZZooh2TAU8Sh_oY2h4zdl70Y8cHbaiqaO1McIraxSTMMJAH4Qa6hsk26ib59z9Bo3zWiR5jD2JUFkj7yfFSpAlJs6OSz_Ou2sRjMfZzgSWeNP-BYRlJkVjzhjUlZ4"
// ];
$title = 'test제목';
$message = 'test내용';

$img_url = "https://interview.highbuff.com/static/www/img/inc/logo_txt.png";

$msg = array(
    // 'title'     => '테스트1',
    // 'message'   => '내용입니다1',
    // 'subtitle'  => 'Support!',
    // 'vibrate'   => 1,
    // 'sound'     => 0,
    // 'largeIcon' => 'large_icon',
    // 'smallIcon' => 'small_icon',
    // 'imgurllink' => $img_url,
    "type" => "link",
    "link" => "help/guide/interview"
);

$data = json_encode(array(
    "to" => $token, //주제로 보내기 /topics/name  -  토큰 취득시 주제를 정해줘서 해당 주제에 포함된 전체 토큰에 메시지를 보낼수있음
    //"registration_ids" => $token,
    "data" => $msg, // onMessageReceived() 메소드를 타며 커스텀마이징 한대로 작동한다.
    "notification" => array(
        //"icon" => "", 

        // "click_action" => "",
        "title"   => $title,
        "body" => $message,
        "image" => $img_url
    )
));


sendPush($data);

//echo  "<br><br><a href='https://api.highbuff.com/interview/20/push_test.php' >뒤로가기 <a>";