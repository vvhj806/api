<?php

include_once('common.php');

$test = $_REQUEST['test'];
$sendtype = $_REQUEST['sendtype'];
$token = $_REQUEST['token'];
$mem_id = $_REQUEST['mem_id'];
$send_data = $_REQUEST['send_data'];
$type = $_REQUEST['type'];
$link = $_REQUEST['link'] ?? '';
$title = $_REQUEST['title'] ?? '';
$message = $_REQUEST['message'] ?? '';
$img_url = $_REQUEST['imgurl'] ?? '';
$send_data_type = $_REQUEST['send_data_type'];

$pushData = [];

if ($sendtype == 'm') {
    if (!empty($mem_id)) {
        $mem_id_arr = preg_split('/\r\n|[\r\n]/', $mem_id);
        $i = 1;
        $tokens = "";
        foreach ($mem_id_arr as $val) {
            $bar = '';
            if ($i < count($mem_id_arr)) {
                $bar = ',';
            }
            $tokens .= "'{$val}'" . $bar;

            $i++;
        }
    } else if (!empty($send_data)) {
        $tokens = $send_data;
    }

    if($send_data_type == 'A'){
        $addwhere = "mem_type IN ('M','C')";
    }else if($send_data_type == 'G'){
        $addwhere = "mem_type IN ('M')";
    }else if($send_data_type == 'B'){
        $addwhere = "mem_type IN ('C')";
    }else if($send_data_type == 'I'){
        $addwhere = "idx IN({$tokens})";
    }else if($send_data_type == 'M'){
        $addwhere = "mem_id IN({$tokens})";
    }


    $sql = "SELECT * FROM iv_member WHERE {$addwhere} AND mem_token IS NOT NULL AND delyn = 'N'";

    if (!$rst = mysqli_query($conn_iv_20, $sql)) {
        $error = mysqli_error($conn_iv_20);
        return_error('iv_member select error', 'iv_member select ' . $sql.$tokens, $error, $sql);
        return;
    } else {
        // $row = mysqli_fetch_assoc($rst);
        $token = [];
        while ($row = mysqli_fetch_array($rst)) {
            array_push($token, $row['mem_token']);
        }
    }
}


if ($sendtype == 's') { //단일전송
    $pushData['to'] = $token;
} else if ($sendtype == 'm') { //여러토큰 전송
    $pushData['registration_ids'] = $token;
} else if ($sendtype == 'a') { //전체전송
    $pushData['registration_ids'] = $token;
}

$msg = array(
    "type" => $type,
    "link" => $link
);

$notification = array(
    "title"   => $title,
    "body" => $message,
    "image" => $img_url
);

$pushData['data'] = $msg;
$pushData['notification'] = $notification;
$pushData['mutable_content'] = true;

$data = json_encode($pushData);

$result = sendPush($data);
//print_r('<br>');
//var_dump($pushData);
//print_r('<br>');
//var_dump($sql);
//print_r('<br>');
if ($test == 'test') {
    echo  "<br><br><a href='https://api.highbuff.com/interview/20/push_test.php' >뒤로가기 <a>";
} else {
    //return $result;
}


/*********************************************
//손원호 테스트
//$token = "cIXMcw9MSQeYxIun0No7Wy:APA91bGSyJYB7Fmbmf-_tu8Kg-Z9HnAgnpDMDU9a9vUoVGMFryI0wWwqCfh1_k9V_lPJuWSq5RG5uWXdvLmIitrMU9wp4fK2LXdDIhJTINcE0PkZySt6dQ5iX5Nhy3PymFKaTTEmMhow";
//김태완 테스트
//$token = "ck5Kt5PlR3S12rUrjGA5PJ:APA91bEHwqj7m6RZZooh2TAU8Sh_oY2h4zdl70Y8cHbaiqaO1McIraxSTMMJAH4Qa6hsk26ib59z9Bo3zWiR5jD2JUFkj7yfFSpAlJs6OSz_Ou2sRjMfZzgSWeNP-BYRlJkVjzhjUlZ4";
//멀티 테스트
// $token = [
//     "cIXMcw9MSQeYxIun0No7Wy:APA91bGSyJYB7Fmbmf-_tu8Kg-Z9HnAgnpDMDU9a9vUoVGMFryI0wWwqCfh1_k9V_lPJuWSq5RG5uWXdvLmIitrMU9wp4fK2LXdDIhJTINcE0PkZySt6dQ5iX5Nhy3PymFKaTTEmMhow",
//     "ck5Kt5PlR3S12rUrjGA5PJ:APA91bEHwqj7m6RZZooh2TAU8Sh_oY2h4zdl70Y8cHbaiqaO1McIraxSTMMJAH4Qa6hsk26ib59z9Bo3zWiR5jD2JUFkj7yfFSpAlJs6OSz_Ou2sRjMfZzgSWeNP-BYRlJkVjzhjUlZ4"
// ];
// $title = 'test제목';
// $message = 'test내용';
//$img_url = "https://interview.highbuff.com/static/www/img/inc/logo_txt.png";

$msg = array(
    // 'title'     => '테스트1',
    // 'message'   => '내용입니다1',
    // 'subtitle'  => 'Support!',
    // 'vibrate'   => 1,
    // 'sound'     => 0,
    // 'largeIcon' => 'large_icon',
    // 'smallIcon' => 'small_icon',
    // 'imgurllink' => $img_url,
    "type" => $type,
    "link" => $link
);

$data = json_encode(array(
    "to" => $token, //주제로 보내기 /topics/name  -  토큰 취득시 주제를 정해줘서 해당 주제에 포함된 전체 토큰에 메시지를 보낼수있음
    //"registration_ids" => $token,  //여러 토큰
    "data" => $msg, // onMessageReceived() 메소드를 타며 커스텀마이징 한대로 작동한다.
    "notification" => array(
        //"icon" => "", 
        // "click_action" => "",
        "title"   => $title,
        "body" => $message,
        "image" => $img_url
    ),
    "mutable_content" => true
));
 **********************************************************************************/
