<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/db_config.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/function.php");

//displayError();

$method = $_POST['method'];


// 비밀번호 확인
if ($method == 'checkPass') {
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($access_token == '' || $password == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) {
        $sql = "SELECT * FROM g5_member WHERE mb_id = '" . $result['user_id'] . "' AND mb_password = password('" . $password . "')";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        if ($row) {
            echo json_encode(array('status' => 200, 'message' => '성공'));
        } else {
            echo json_encode(array('status' => 401, 'message' => '비밀번호가 일치하지 않습니다.'));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
}

// 회원탈퇴 유의사항
else if ($method == 'notice') {
    $sql = 'SELECT cf_1 FROM g5_config';
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if ($row) {
        $agreement = '회원탈퇴 유의사항<br>' . str_replace("\r\n", "<br>", $row['cf_1']);
        echo json_encode(array('status' => 200, 'agreement' => $agreement));
    } else {
        echo json_encode(array('status' => 400, 'agreement' => '유의사항을 불러올 수 없습니다.'));
    }
}

// 탈퇴처리 DB 업데이트
else if ($method == 'leave') {
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);

    if (!empty($result['user_id'])) {
        mysqli_autocommit($conn, false);
        $result11 = mysqli_query($conn, "SELECT @@autocommit");
        $row = mysqli_fetch_row($result11);

        $sql = "UPDATE g5_member SET mb_level = '-1', mb_leave_date = NOW() WHERE `mb_id` = '" . $result['user_id'] . "'";
        mysqli_query($conn, $sql);
        $rst = mysqli_affected_rows($conn);

        if ($rst > 0) {
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

            if (!empty($json['error'])) { //fail
                mysqli_rollback($conn);
                echo json_encode(array('status' => 400, 'message' => '탈퇴가 정상적으로 이루어지지 않았습니다!'));
            } else { //success
                mysqli_commit($conn);
                mysqli_autocommit($conn, true);
                echo json_encode(array('status' => 200, 'message' => '성공'));
            }
        } else {
            mysqli_rollback($conn);
            echo json_encode(array('status' => 400, 'message' => '탈퇴 중 오류가 발생했습니다.', 'access_token' => $access_token, 'id' => $result['user_id']));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
}
