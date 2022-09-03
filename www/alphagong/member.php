<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

$method = $_POST['method'];
if ($method == 'verifyPhone') { // ============================ 휴대폰 인증 ============================
    $phone = isset($_POST['user_phone']) ?  str_replace("-", "", $_POST['user_phone']) : '';
    if ($phone == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    if ($phone == '01011111111') { //app test를 위한 인증 pass
        echo json_encode(array('status' => 200, 'vertification_code' => 123456));
        exit;
    }

    $sql = "SELECT mb_no FROM g5_member WHERE mb_hp = '".$phone."' LIMIT 1";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        echo json_encode(array('status' => -101, 'message' => '이미 사용중인 휴대폰 번호입니다.'));
    } else {
        $certNumber = rand(100000, 999999);
        $msg = "[인증번호:".$certNumber."] 하이버프 알파공\nSMS 인증번호입니다.";
        $result = sendWideShotMessage($msg, $phone, $conn);
        if ($result) {
            echo json_encode(array('status' => 200, 'vertification_code' => $certNumber));
        } else {
            echo json_encode(array('status' => -105, 'message' => 'SMS 인증 요청을 실패하였습니다. 고객센터로 문의해주세요.'));
        }
    }
} else if ($method == 'registerMember') { // ============================ 회원가입 ============================
    $sms_agree = isset($_POST['user_sms_agree']) ? trim($_POST['user_sms_agree']) : '';
    $email_agree = isset($_POST['user_email_agree']) ? trim($_POST['user_email_agree']) : '';
    $phone = isset($_POST['user_phone']) ? str_replace("-", "", trim($_POST['user_phone'])) : '';
    $email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $password = isset($_POST['user_password']) ? trim($_POST['user_password']) : '';

    if ($sms_agree == '' || $email_agree == '' || $phone == '' || $email == '' || $name == '' || $password == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $secret_key = "bluevisorencrypt";
    $key = substr(hash('sha256', $secret_key), 0, 32);
    $iv = substr(hash('sha256', $secret_key), 0, 16);
    $password = openssl_decrypt(base64_decode($password), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);

    $sql = "SELECT mb_no FROM g5_member WHERE mb_hp = '".$phone."' LIMIT 1";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        echo json_encode(array('status' => -101, 'message' => '이미 사용중인 휴대폰 번호입니다.'));
        return;
    }

    $sql = "SELECT mb_no FROM g5_member WHERE mb_id = '".$email."' LIMIT 1";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        echo json_encode(array('status' => -102, 'message' => '이미 사용중인 이메일입니다.'));
        return;
    }

    $sql = "INSERT INTO g5_member SET
            mb_id = '".$email."',
            mb_password = password('".$password."'),
            mb_name = '".$name."',
            mb_nick = '".$name."',
            mb_email = '".$email."',
            mb_level = 0,
            mb_hp = '".$phone."',
            mb_mailling = ".$email_agree.",
            mb_sms = ".$sms_agree.",
            mb_datetime = '".date("Y-m-d H:i:s")."',
            mb_1 = 0"; //마케팅 정보 수신 및 푸시알림동의 default 0(off)
    mysqli_query($conn, $sql);
    $rst = mysqli_affected_rows($conn);
    if ($rst > 0) { //success
        echo json_encode(array('status' => 200));
    } else { //fail
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }

} else if ($method == 'registerSNSMember') { // ============================ SNS회원가입 ============================
    $sms_agree = isset($_POST['user_sms_agree']) ? trim($_POST['user_sms_agree']) : '';
    $email_agree = isset($_POST['user_email_agree']) ? trim($_POST['user_email_agree']) : '';
    $provider = isset($_POST['provider']) ? trim($_POST['provider']) : '';
    $user_key = isset($_POST['user_key']) ? trim($_POST['user_key']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if ($sms_agree == '' || $email_agree == '' || $provider == '' || $user_key == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }
    $user_key = sha1($user_key);
    $user_id = $provider.'_'.$user_key;

    //중복 아이디 체크
    $sql1 = "SELECT mb_no FROM g5_member WHERE mb_id = '".$user_id."' LIMIT 1";
    $rst1 = mysqli_query($conn, $sql1);
    $row1 = mysqli_fetch_assoc($rst1);
    if (isset($row1)) {
        echo json_encode(array('status' => -107, 'message' => '이미 사용중인 계정입니다!'));
        exit;
    }
    
    //sns table update
    $sql2 = "UPDATE iv_sns_member SET update_dates = '".date("Y-m-d H:i:s")."' WHERE object_sha = '".$user_key."' ORDER BY idx DESC LIMIT 1";
    mysqli_query($conn, $sql2);

    //member insert
    $sql3 = "INSERT INTO g5_member SET
            mb_id = '".$user_id."',
            mb_email = '".$email."',
            mb_level = 0,
            mb_mailling = ".$email_agree.",
            mb_sms = ".$sms_agree.",
            mb_datetime = '".date("Y-m-d H:i:s")."',
            mb_1 = 0"; //마케팅 정보 수신 및 푸시알림동의 default 0(off)
    mysqli_query($conn, $sql3);
    $rst3 = mysqli_affected_rows($conn);
    if ($rst3 > 0) { //success
        echo json_encode(array('status' => 200));
    } else { //fail
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다!'));
    }
} else if ($method == 'findEmail') { // ============================ 아이디(이메일) 찾기 ============================
    $phone = isset($_POST['user_phone']) ?  str_replace("-", "", trim($_POST['user_phone'])) : '';
    if ($phone == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $sql = "SELECT * FROM g5_member WHERE mb_hp = '".$phone."' ORDER BY mb_no DESC LIMIT 1";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        $user_id = $row['mb_id'];
        if ($provider = getUserSnsProvider($user_id)) {
            echo json_encode(array('status' => -106, 'message' => 'SNS 로그인('.$provider.') 회원입니다. SNS 로그인 정보를 확인해주세요.'));
            return;
        }

        $certNumber = rand(100000, 999999);
        $msg = "[인증번호:".$certNumber."] 하이버프 알파공\nSMS 인증번호입니다.";
        $result = sendWideShotMessage($msg, $phone, $conn);
        if ($result) {
            if (strpos($user_id, '@') === false) { //계정이 이메일 형식이 아닐때는 평문 전달
                echo json_encode(array('status' => 200, 'vertification_code' => $certNumber, 'user_email' => $user_id));
            } else { //이메일은 마스킹 처리해서 전달
                list($first, $last) = explode('@', $user_id);
                $first = str_replace(substr($first, '3'), str_repeat('*', strlen($first)-3), $first);
                $last = explode('.', $last);
                $last_domain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0'])-1), $last['0']);
                $maskingEmail = $first.'@'.$last_domain.'.'.$last['1'];
                echo json_encode(array('status' => 200, 'vertification_code' => $certNumber, 'user_email' => $maskingEmail));
            }
        } else {
            echo json_encode(array('status' => -105, 'message' => 'SMS 인증 요청을 실패하였습니다. 고객센터로 문의해주세요.'));
        }
    } else {
        echo json_encode(array('status' => -103, 'message' => '등록된 회원을 찾을 수 없습니다.'));
    }
} else if ($method == 'findPassword') { // ============================ 비밀번호 찾기 ============================
    $email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $phone = isset($_POST['user_phone']) ?  str_replace("-", "", trim($_POST['user_phone'])) : '';
    if ($email == '' || $phone == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $sql = "SELECT * FROM g5_member WHERE mb_id = '".$email."' AND mb_hp = '".$phone."' ORDER BY mb_no DESC LIMIT 1";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) { //find user
        $randNumber = rand(100000, 999999);
        $sql1 = "UPDATE g5_member SET mb_password = password('".$randNumber."') WHERE mb_no = '".$row['mb_no']."'";
        mysqli_query($conn, $sql1);

        $msg = "[임시비밀번호:".$randNumber."] 하이버프 알파공 임시비밀번호 입니다.\n로그인 후 마이페이지에서 비밀번호를 변경해주세요.";
        $result = sendWideShotMessage($msg, $phone, $conn);
        echo json_encode(array('status' => 200));
    } else {
        echo json_encode(array('status' => -103, 'message' => '등록된 회원을 찾을 수 없습니다.'));
    }

} else if ($method == 'login') { // ============================ 로그인 ============================
    $email = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
    $password = isset($_POST['user_password']) ? trim($_POST['user_password']) : '';
    $client_id = isset($_POST['uid']) ? trim($_POST['uid']) : ''; //APP 고유값
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $platform = isset($_POST['platform']) ? trim($_POST['platform']) : '';

    $secret_key = "bluevisorencrypt";
    $key = substr(hash('sha256', $secret_key), 0, 32);
	$iv = substr(hash('sha256', $secret_key), 0, 16);
	$password = openssl_decrypt(base64_decode($password), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
    
    if ($email == '' || $password == '' || $client_id == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다!'),JSON_UNESCAPED_UNICODE);
        // echo json_encode(array('status' => 400, 'message' => 'email : '.$email.'password : '.$password.'client_id : '.$client_id));
        return;
    }

    $sql = "SELECT mb_no, mb_id, mb_today_login, mb_name, mb_hp, mb_leave_date FROM g5_member WHERE mb_id = '".$email."' AND mb_password = password('".$password."') ORDER BY mb_no DESC LIMIT 1";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) { //회원이 존재하면

        if ($row['mb_leave_date'] != '') { //22.06.13 igg add 탈퇴 회원 체크
            echo json_encode(array('status' => -303, 'message' => '이미 탈퇴한 계정입니다.'));
            return;
        }

        $use_profile = 'https://alphagong.highbuff.com/img/profile/noprofile.png';
        $sql1 = "SELECT thumbnail FROM `iv_applier` WHERE user_id = '".$row['mb_id']."' AND thumbnail != '' ORDER BY idx LIMIT 1";
        $rst1 = mysqli_query($conn, $sql1);
        $row1 = mysqli_fetch_assoc($rst1);
        if (isset($row1)) {
            $use_profile = 'https://alphagong.highbuff.com/data/uploads_thumbnail/'.$row1['thumbnail'];
        }

        //알파공 앱 로그인할때마다 앱에서 platform하고 token 정보 받아서 업데이트 진행
        $sql1 = "UPDATE g5_member SET mb_2 = '".$platform."', mb_3 = '".$token."' WHERE mb_no = '".$row['mb_no']."'";
        mysqli_query($conn, $sql1);

        $client_secret = setEncrypt($client_id, 'bluevisorencrypt'); //AES256CDC 암호화하여 secret 값 생성
        $url = 'https://api.highbuff.com/oauth2/token.php';
        $post_data = array(
            'grant_type' => 'client_credentials',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'client_scope' => 'alphagong',
            'user_id' => $email
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($result, true);
        if (isset($json['error'])) {
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'),JSON_UNESCAPED_UNICODE);
        } else {
            //22.03.11 오늘 처음 로그인 방문 로그 삽입
            if (substr($row['mb_today_login'], 0, 10) != date('Y-m-d')) {
                $sql2 = " UPDATE g5_member SET mb_today_login = '".date('Y-m-d')."' WHERE mb_no = '".$row['mb_no']."' ";
                mysqli_query($conn, $sql2);
            }
            
//            //22.03.11 방문자 통계 로그 삽입
//            $sql3 = 'SELECT max(vi_id) as max_vi_id FROM g5_visit';
//            $rst3 = mysqli_query($conn, $sql3);
//            $row3 = mysqli_fetch_assoc($rst3);
//
//            $vi_id = $row3['max_vi_id'] + 1;
//            $sql4 = " INSERT g5_visit ( vi_id, vi_ip, vi_date, vi_time, vi_referer, vi_agent, vi_browser, vi_os, vi_device ) values ( '{$vi_id}', '', '".date('Y-m-d')."', '".date('H:i:s')."', '', '', '', '".$platform."', '".$platform."' ) ";
//            $rst4 = mysqli_query($conn, $sql4);
//
//            if ($rst4) {
//                $sql5 = " INSERT g5_visit_sum (vs_count, vs_date) values ( 1, '".date('Y-m-d')."' ) ";
//                $rst5 = mysqli_query($conn, $sql5);
//
//                // DUPLICATE 오류가 발생한다면 이미 날짜별 행이 생성되었으므로 UPDATE 실행
//                if (!$rst5) {
//                    $sql5 = " UPDATE g5_visit_sum SET vs_count = vs_count + 1 WHERE vs_date = '".date('Y-m-d')."' ";
//                    $rst5 = mysqli_query($conn, $sql5);
//                }
//
//                // 오늘
//                $sql1 = " SELECT vs_count as cnt FROM g5_visit_sum WHERE vs_date = '".date('Y-m-d')."' ";
//                $rst1 = mysqli_query($conn, $sql1);
//                $row1 = mysqli_fetch_assoc($rst1);
//                $vi_today = isset($row1['cnt']) ? $row1['cnt'] : 0;
//
//                // 어제
//                $sql1 = " SELECT vs_count as cnt FROM g5_visit_sum WHERE vs_date = DATE_SUB('".date('Y-m-d')."', INTERVAL 1 DAY) ";
//                $rst1 = mysqli_query($conn, $sql1);
//                $row1 = mysqli_fetch_assoc($rst1);
//                $vi_yesterday = isset($row1['cnt']) ? $row1['cnt'] : 0;
//
//                // 최대
//                $sql1 = " SELECT max(vs_count) as cnt FROM g5_visit_sum ";
//                $rst1 = mysqli_query($conn, $sql1);
//                $row1 = mysqli_fetch_assoc($rst1);
//                $vi_max = isset($row1['cnt']) ? $row1['cnt'] : 0;
//
//                // 전체
//                $sql1 = " SELECT sum(vs_count) as total FROM g5_visit_sum ";
//                $rst1 = mysqli_query($conn, $sql1);
//                $row1 = mysqli_fetch_assoc($rst1);
//                $vi_sum = isset($row1['total']) ? $row1['total'] : 0;
//
//                $visit = '오늘:'.$vi_today.',어제:'.$vi_yesterday.',최대:'.$vi_max.',전체:'.$vi_sum;
//
//                $sql1 = " UPDATE g5_config SET cf_visit = '".$visit."'";
//                mysqli_query($conn, $sql1);
//            }

            echo json_encode(array('status' => 200, 'access_token' => $json['access_token'], 'user_name' => $row['mb_name'], 'user_profile' => $use_profile, 'user_phone' => $row['mb_hp'],'test' => $client_secret),JSON_UNESCAPED_UNICODE);

            // echo json_encode(array('status' => 200, 'json' => $json));
        }
    } else {
        echo json_encode(array('status' => -302, 'message' => '아이디 혹은 비밀번호가 올바르지 않습니다.'));
    }
} else if ($method == 'getBestScore') { // ============================ 메뉴 화면-베스트점수 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $user_id = $result['user_id'];

        $sql = "SELECT CAST(JSON_EXTRACT(analysis, '$.sum') as DECIMAL(4,2)) as sum, applier_idx as idx FROM `iv_result` WHERE user_id = '".$user_id."' AND question_idx = 'total' AND score != ''";
        $rst = mysqli_query($conn, $sql);
        $best_score_idx = 0;
        $best_score = 0;
        while ($row = mysqli_fetch_array($rst)) {
            if ($row['sum'] > $best_score) {
                $best_score = $row['sum'];
                $best_score_idx = $row['idx'];
            }
        }

        echo json_encode(array('status' => 200, 'best_score' => $best_score, 'best_score_idx' => $best_score_idx));
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'getAvgScore') { // ============================ 메뉴 화면-평균점수 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $user_id = $result['user_id'];

        $sql = "SELECT round(AVG(CAST(JSON_EXTRACT(analysis, '$.sum') as DECIMAL(4,2))),2) as avg FROM iv_result WHERE user_id = '".$user_id."' AND question_idx = 'total' AND score != ''";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($rst);
        $avg_score = 0;
        if (isset($row)) {
            $avg_score = $row['avg'];
        }

        echo json_encode(array('status' => 200, 'avg_score' => $avg_score));
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'getMypage') { // ============================ 마이페이지 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $user_id = $result['user_id'];

        $use_profile = 'https://alphagong.highbuff.com/img/profile/noprofile.png';
        $sql1 = "SELECT thumbnail FROM `iv_applier` WHERE user_id = '".$user_id."' AND thumbnail != '' ORDER BY idx LIMIT 1";
        $rst1 = mysqli_query($conn, $sql1);
        $row1 = mysqli_fetch_assoc($rst1);
        if (isset($row1)) {
            $use_profile = 'https://alphagong.highbuff.com/data/uploads_thumbnail/'.$row1['thumbnail'];
        }
        
        $sql = "SELECT * FROM g5_member WHERE mb_id = '".$user_id."'";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (isset($row)) {
            $user_name = trim($row['mb_name']);
            //$user_email = ($row['mb_email'] == '') ? '없음' : trim($row['mb_email']);
            $user_email = $user_id;
            $user_phone = trim($row['mb_hp']);
            $user_tutorial = ($row['mb_4'] == '') ? 1 : $row['mb_4']; //1다시보기 0다시보지않기
            $user_push_agree = ($row['mb_1'] == '') ? 1 : $row['mb_1']; //1마케팅정보수신동의 0미동의
            $user_sms_agree = trim($row['mb_sms']);
            $user_email_agree = trim($row['mb_mailling']);

            echo json_encode(array('status' => 200, 'user_profile' => $use_profile, 'user_name' => $user_name, 'user_email' => $user_email, 'user_phone' => $user_phone, 'user_tutorial' => $user_tutorial, 'user_push_agree' => $user_push_agree, 'user_sms_agree' => $user_sms_agree, 'user_email_agree' => $user_email_agree));
        } else { //아이디를 찾을수 없을때
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다!'));
        }
        
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'changePassword') { // ============================ 마이페이지-비밀번호 변경 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    if ($access_token == '' || $password == '' || $new_password == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $secret_key = "bluevisorencrypt";
    $key = substr(hash('sha256', $secret_key), 0, 32);
	$iv = substr(hash('sha256', $secret_key), 0, 16);
	$password = openssl_decrypt(base64_decode($password), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
    $new_password = openssl_decrypt(base64_decode($new_password), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $user_id = $result['user_id'];
        $sql = "SELECT * FROM g5_member WHERE mb_id = '".$user_id."' AND mb_password = password('".$password."')";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($rst);
        if (isset($row)) {
            $sql1 = "UPDATE g5_member SET mb_password = password('".$new_password."') WHERE mb_no = ".$row['mb_no'];
            mysqli_query($conn, $sql1);
            $rst = mysqli_affected_rows($conn);
            if ($rst > 0) { //success
                echo json_encode(array('status' => 200));
            } else { //fail
                echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
            }
        } else {
            echo json_encode(array('status' => -104, 'message' => '비밀번호가 올바르지 않습니다.'));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'changeOption') { // ============================ 마이페이지-옵션 변경(튜토리얼,푸시,SMS,이메일) ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $option = isset($_POST['option']) ? trim($_POST['option']) : '';
    $value = isset($_POST['value']) ? trim($_POST['value']) : 0;
    if ($access_token == '' || $option == '' || $value == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    //유효한 옵션이 맞는지 검사
    switch ($option) {
        case 'tutorial' :
            $option = 'mb_4';
            break;
        case 'push' :
            $option = 'mb_1';
            break;
        case 'sms' :
            $option = 'mb_sms';
            break;
        case 'email' :
            $option = 'mb_mailling';
            break;
        default :
            $option = '';
            break;
    }

    if ($option == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $sql = "UPDATE g5_member SET ".$option." = ".$value." WHERE mb_id = '".$result['user_id']."'";
        mysqli_query($conn, $sql);
        $rst = mysqli_affected_rows($conn);
        if ($rst > 0) { //success
            echo json_encode(array('status' => 200));
        } else { //fail
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'verifyMypagePhone') { // ============================ 마이페이지-휴대폰 인증 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $phone = isset($_POST['user_phone']) ?  str_replace("-", "", $_POST['user_phone']) : '';
    if ($access_token == '' || $phone == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }    

    $result = getUserId($access_token);
    if (isset($result['user_id'])) {
        //변경할 휴대폰 번호가 이미 사용중인지 확인
        $sql = "SELECT mb_no FROM g5_member WHERE mb_hp = '".$phone."' LIMIT 1";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (isset($row)) {
            echo json_encode(array('status' => -108, 'message' => '이미 인증된 휴대폰 번호입니다. 인증된 계정으로 사용 부탁드립니다.'));
        } else {
            $certNumber = rand(100000, 999999);
            $msg = "[인증번호:".$certNumber."] 하이버프 알파공\nSMS 인증번호입니다.";
            $result = sendWideShotMessage($msg, $phone, $conn);
            if ($result) {
                echo json_encode(array('status' => 200, 'vertification_code' => $certNumber));
            } else {
                echo json_encode(array('status' => -105, 'message' => 'SMS 인증 요청을 실패하였습니다. 고객센터로 문의해주세요.'));
            }
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'updateInformation') { // ============================ 마이페이지-이름,휴대폰번호 변경 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $phone = isset($_POST['user_phone']) ?  str_replace("-", "", $_POST['user_phone']) : '';
    $name = isset($_POST['user_name']) ? trim($_POST['user_name']) : '';
    if ($access_token == '' || $phone == '' || $name == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) {
        //변경할 휴대폰 번호가 이미 사용중인지 확인
        $sql = "SELECT mb_no FROM g5_member WHERE mb_hp = '".$phone."' LIMIT 1";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (isset($row)) {
            echo json_encode(array('status' => -108, 'message' => '이미 인증된 휴대폰 번호입니다. 인증된 계정으로 사용 부탁드립니다.'));
            return;
        }

        $sql = "UPDATE g5_member SET mb_name = '".$name."', mb_nick = '".$name."', mb_hp = '".$phone."' WHERE mb_id = '".$result['user_id']."'";
        mysqli_query($conn, $sql);
        $rst = mysqli_affected_rows($conn);
        if ($rst > 0) { //success
            echo json_encode(array('status' => 200));
        } else { //fail
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'logout') { // ============================ 마이페이지-로그아웃 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $url = 'https://api.highbuff.com/oauth2/token.php';
        $post_data = array(
            'grant_type' => 'unset_credentials',
            'access_token' => $access_token
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($result, true);

        if (isset($json['error'])) { //fail
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        } else { //success
            echo json_encode(array('status' => 200));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'refreshAccessToken') { // ============================ 토큰 갱신 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $url = 'https://api.highbuff.com/oauth2/token.php';
    $post_data = array(
        'grant_type' => 'token_refresh_credentials',
        'access_token' => $access_token
    );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($result, true);
    if (isset($json['error'])) { //fail
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else { //success

        $result = getUserId($json['access_token']);
        if (isset($result['user_id'])) { //success
            $user_id = $result['user_id'];
            $sql = "SELECT mb_name, mb_hp FROM g5_member WHERE mb_id = '".$user_id."'";
            $rst = mysqli_query($conn, $sql);
            $row = mysqli_fetch_array($rst);
            if (isset($row)) {
                echo json_encode(array('status' => 200, 'access_token' => $json['access_token'], 'user_name' => $row['mb_name'], 'user_phone' => $row['mb_hp']));
            } else {
                echo json_encode(array('status' => 200, 'access_token' => $json['access_token']));
            }
            
        } else if ($result == -10) {
            echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
        } else if ($result == -11) {
            echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
        } else {
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        }
        
    }
} else {
    echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
}