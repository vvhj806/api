<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

$method = $_POST['method'];
if ($method == 'getType') { // ============================ 인터뷰-종류/직렬 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        //인터뷰 종류에 대한 배열
        $type_list = array();
        $sql = "SELECT code, step FROM iv_type WHERE process = 1 AND depth = 1";
        $rst = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($rst)) {
            array_push($type_list, array('idx' => $row['code'], 'title' => $row['step']));
        }
        
        //인터뷰 직렬에 대한 배열
        $step_list = array();
        $sql1 = 'SELECT code, SUBSTRING_INDEX(SUBSTRING_INDEX(step,"##",-2), "##", 1) as step, SUBSTR(code, 1, 3) as type_idx FROM iv_type WHERE process = 1 AND depth = 2';
        $rst1 = mysqli_query($conn, $sql1);
        while ($row1 = mysqli_fetch_assoc($rst1)) {
            $type_idx = $row1['type_idx'];
            $code = $row1['code'];
            $title = $row1['step'];

            array_push($step_list, array('idx' => $type_idx, 'code' => $code, 'title' => $title));
        }
        echo json_encode(array('status' => 200, 'type' => $type_list, 'step' => $step_list));
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'setType') { // ============================ 인터뷰-종류/직렬 선택 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    if ($access_token == '' || $code == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        //들어오는 code값이 존재하는지 여부 확인
        $sql = "SELECT * FROM iv_type WHERE code = '".$code."' LIMIT 1";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (!isset($row)) {
            echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
            return;
        }
        //iv_applier insert
        $sql1 = "INSERT INTO iv_applier SET
                code = '".$code."',
                step = '".trim($row['step'])."',
                user_id = '".$result['user_id']."',
                dates = '".date("Y-m-d H:i:s")."',
                process = 0";
        $rst1 = mysqli_query($conn, $sql1);
        if ($rst1) $last_idx = mysqli_insert_id($conn);
        
        //질문 5개 랜덤으로 가져와서 insert
        $sql2 = "SELECT * FROM `iv_question` WHERE code = '".$code."' ORDER BY RAND() LIMIT 5";
        $rst2 = mysqli_query($conn, $sql2);
        while ($row2 = mysqli_fetch_array($rst2)) {
            $sql3 = "INSERT INTO iv_result SET
                    user_id = '".$result['user_id']."',
                    code = '".$code."',
                    question_idx = '".$row2['idx']."',
                    applier_idx = '".$last_idx."'";
            mysqli_query($conn, $sql3);
        }

        //result에 total 값 insert
        $sql4 = "INSERT INTO iv_result SET
                user_id = '".$result['user_id']."',
                code = '".$code."',
                question_idx = 'total',
                applier_idx = '".$last_idx."'";
        mysqli_query($conn, $sql4);

        $sql5 = "SELECT mb_4 FROM g5_member WHERE mb_id = '".$result['user_id']."'";
        $rst5 = mysqli_query($conn, $sql5);
        $row5 = mysqli_fetch_assoc($rst5);
        $tutorial = isset($row5['mb_4']) ? $row5['mb_4'] : 0;

        echo json_encode(array('status' => 200, 'applier_idx' => $last_idx, 'tutorial' => $tutorial));
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'setProfile') { // ============================ 인터뷰-프로필 촬영 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $applier_idx = isset($_POST['applier_idx']) ? trim($_POST['applier_idx']) : '';

    if ($access_token == '' || $applier_idx == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    if (!isset($_FILES['thumbnail']['tmp_name']) || ($_FILES['thumbnail']['error'] != UPLOAD_ERR_OK)) {
        echo json_encode(array('status' => -203, 'message' => '파일 업로드 중 오류가 발생했습니다.'));
        return;
    }

    $extension = end(explode('.', $_FILES['thumbnail']['name']));
    if (!in_array($extension, array('jpg', 'jpeg', 'png', 'webm', 'mp4'))) { //파일 확장자 체크
        echo json_encode(array('status' => -203, 'message' => '파일 업로드 중 오류가 발생했습니다!'));
        return;
    }

    $sql = "SELECT idx FROM iv_applier WHERE idx = '".$applier_idx."'";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (!isset($row)) {
        echo json_encode(array('status' => -202, 'message' => '진행중인 인터뷰를 찾을 수 없습니다.'));
        return;
    }

    $filename = $_FILES['thumbnail']['tmp_name']; 
    $handle = fopen($filename, "r"); 
    $data = base64_encode(fread($handle, filesize($filename))); 
    $post = array('thumbnail' => $data, 'extension' => $extension, 'applier_idx' => $applier_idx); 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, 'https://alphagong.highbuff.com/app/app_profile.php'); 
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    $result = curl_exec($ch); 
    curl_close($ch); 
    $json = json_decode($result, true);

    if ($json['status'] == 200) { //정상적으로 파일업로드가 될경우에는 DB update
        $sql1 = "UPDATE `iv_applier` SET thumbnail = '".$json['file_name']."', dates = '".date("Y-m-d H:i:s")."', process = '2' WHERE idx = '".$applier_idx."'";
        mysqli_query($conn, $sql1);
        echo json_encode(array('status' => 200));
    } else {
        echo json_encode(array('status' => $json['status'], 'message' => $json['message']));
    }
} else if ($method == 'getQuestion') { // ============================ 인터뷰-시작(질문정보 받기) ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $applier_idx = isset($_POST['applier_idx']) ? trim($_POST['applier_idx']) : '';
    if ($access_token == '' || $applier_idx == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $sql = "SELECT A.idx, B.idx as q_idx, B.question, A.process FROM iv_result as A INNER JOIN iv_question as B ON A.question_idx = B.idx  WHERE A.applier_idx = '".$applier_idx."' AND A.question_idx != 'total' ORDER BY A.idx ASC";
    $rst = mysqli_query($conn, $sql);
    $question_list = array();
    $incomplete_idx = -1;
    for($i=0; $row = mysqli_fetch_assoc($rst); $i++) {
        if ($incomplete_idx == -1 && $row['process'] == 0) { //미완성 인터뷰가 있다면 인덱스값을 저장함
            $incomplete_idx = $row['q_idx'];
        }

        //naver TTS 관련 동작, 질문index를 전달해서 mp3 파일 url을 전달해줘야함
        $post = array('question_idx' => $row['q_idx'], 'question_title' => $row['question']); 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, 'https://alphagong.highbuff.com/app/app_tts.php'); 
        curl_setopt($ch, CURLOPT_POST, 1); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
        $result = curl_exec($ch); 
        curl_close($ch);
        $json = json_decode($result, true);
        array_push($question_list, array('idx' => $row['q_idx'], 'title' => $row['question'], 'tts' => $json['url']));
    }

    if (count($question_list) == 0) { //질문정보가 없을경우에는
        echo json_encode(array('status' => -202, 'message' => '진행중인 인터뷰를 찾을 수 없습니다.'));
        return;
    }

    echo json_encode(array('status' => 200, 'question_count' => count($question_list), 'question' => $question_list, 'incomplete_idx' => $incomplete_idx));
} else if ($method == 'uploadVideo') { // ============================ 인터뷰-시작(영상 업로드) ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $applier_idx = isset($_POST['applier_idx']) ? trim($_POST['applier_idx']) : '';
    $count = isset($_POST['count']) ? trim($_POST['count']) : '';
    $question_idx = isset($_POST['question_idx']) ? trim($_POST['question_idx']) : '';

    if ($access_token == '' || $applier_idx == '' || $count == '' || $question_idx == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }


    if (!isset($_FILES['video']['tmp_name']) || ($_FILES['video']['error'] != UPLOAD_ERR_OK)) {
        echo json_encode(array('status' => -203, 'message' => '파일 업로드 중 오류가 발생했습니다.'));
        return;
    }

    $extension = end(explode('.', $_FILES['video']['name']));
    if (!in_array($extension, array('webm', 'mp4'))) { //파일 확장자 체크
        echo json_encode(array('status' => -203, 'message' => '파일 업로드 중 오류가 발생했습니다!'));
        return;
    }

    $sql = "SELECT idx FROM iv_applier WHERE idx = '".$applier_idx."'";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (!isset($row)) {
        echo json_encode(array('status' => -202, 'message' => '진행중인 인터뷰를 찾을 수 없습니다.'));
        return;
    }

    $count = str_pad($count, 2, 0, STR_PAD_LEFT); //2자리로 표현
    $filename = $_FILES['video']['tmp_name']; 
    $handle = fopen($filename, "r"); 
    $data = base64_encode(fread($handle, filesize($filename))); 
    $post = array('video' => $data, 'extension' => $extension, 'applier_idx' => $applier_idx, 'count' => $count, 'question_idx' => $question_idx); 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, 'https://alphagong.highbuff.com/app/app_video.php'); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    $result = curl_exec($ch); 
    curl_close($ch); 
    $json = json_decode($result, true);

    if ($json['status'] == 200) { //정상적으로 파일업로드가 될경우에는 DB update
        $sql1 = "UPDATE `iv_applier` SET record_".$count." = '".$json['file_name']."', dates = '".date("Y-m-d H:i:s")."' WHERE idx = '".$applier_idx."'";
        mysqli_query($conn, $sql1);

        $sql2 = "UPDATE iv_result SET process = 1, dates = '".date("Y-m-d H:i:s")."' WHERE applier_idx = '".$applier_idx."' AND process = 0 ORDER BY idx ASC LIMIT 1 ";
        mysqli_query($conn, $sql2);

        $sql3 = "SELECT idx FROM iv_result WHERE applier_idx = '".$applier_idx."' AND process = 0 AND question_idx != 'total' ORDER BY idx ASC LIMIT 1";
        $rst3 = mysqli_query($conn, $sql3);
        $row3 = mysqli_fetch_array($rst3);
        $incomplete_idx = $row3["idx"]; //미완성된 idx

        if ($incomplete_idx) { //인터뷰 미완료
            echo json_encode(array('status' => 200, 'incomplete_idx' => $incomplete_idx));
        } else { //인터뷰 모두 완료
            $sql4 = "UPDATE iv_applier SET process = 3 WHERE idx = '".$applier_idx."'";
            mysqli_query($conn, $sql4);

            $sql5 = "SELECT mb_id, mb_name, mb_hp FROM g5_member WHERE mb_id = (SELECT user_id FROM iv_applier WHERE idx = '".$applier_idx."')";
            $rst5 = mysqli_query($conn, $sql5);
            $row5 = mysqli_fetch_assoc($rst5);

            $user_name = $row5["mb_name"];
            $user_id = $row5["mb_id"];
            $mobile = $row5["mb_hp"];

            $msg = "[알파공_완료_알림]\n유저명 : ".$user_name."(".$user_id.")\n연락처 : ".$mobile."\n<a href='https://alphagong.highbuff.com/admin/login.php'>[평가하러가기]</a>";
            telegram_send($msg, "company");
            echo json_encode(array('status' => 200, 'incomplete_idx' => -1));
        }
    } else {
        echo json_encode(array('status' => $json['status'], 'message' => $json['message']));
    }
} else if ($method == 'getEvent') { // ============================ 인터뷰-이벤트 진행 여부 확인 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }
    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $start_date = '2022-02-14 09:00:00';
        $end_date = '2022-03-14 18:00:00';
        $event_link = 'https://interview.highbuff.com/job_landing.php';
        $event_image = 'https://interview.highbuff.com/share/img/main/jop_bn.jpg';
        $today = date('Y-m-d H:i:s');
        
        if ($today >= $start_date && $today <= $end_date) { //이벤트 기간이 포함되면
            $sql = "SELECT * FROM g5_member WHERE mb_id = '".$result['user_id']."'";
            $rst = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($rst);
            echo json_encode(array('status' => 200, 'user_name' => $row['mb_name'], 'user_phone' => $row['mb_hp'], 'event_link' => $event_link, 'event_image' => $event_image));
        } else {
            echo json_encode(array('status' => -301, 'message' => '진행중인 이벤트가 없습니다.'));
            return;
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else {
    echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
}