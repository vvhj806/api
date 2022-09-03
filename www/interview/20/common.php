<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/function.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/db_config.php");

// displayError();

//url file 경로 사용 
//ini_set('allow_url_fopen','ON');
//$server_url = "https://webtestinterviewr.highbuff.com";
//$server_url = "https://realinterviewr.highbuff.com/";
$server_url = "https://interview.highbuff.com/";

//미디어 서버 https://media.highbuff.com
$file_server_url = "https://media.highbuff.com";
$file_server_path = "https://media.highbuff.com";

function file_name_make($index, $field, $file_name)
{
    //file name 형식
    //DB index - field name - timestamp - random(string+ing 10자) . 확장자
    $fileinfo = pathinfo($file_name);
    $file_extension = $fileinfo['extension']; //파일확장자

    //timestamp 13자리
    list($microtime, $timestamp) = explode(' ', microtime());
    $time = $timestamp . substr($microtime, 2, 3);

    $newfilename = $index . '-' . $field . '-' . $time . '-' . random_str_int(10) . '.' . $file_extension;

    return $newfilename;
}

function video_name_make($index, $field, $file_name, $count, $q_idx)
{
    //video file name 형식
    //DB index - field name _ count(ex:01) - question idx - timestamp - random(string+ing 10자) . 확장자
    $fileinfo = pathinfo($file_name);
    $file_extension = $fileinfo['extension']; //파일확장자

    $num = sprintf('%02d', $count); //count

    //timestamp 13자리
    list($microtime, $timestamp) = explode(' ', microtime());
    $time = $timestamp . substr($microtime, 2, 3);

    $newfilename = $index . '-' . $field . '_' . $num . '-' . $q_idx . '-' . $time . '-' . random_str_int(10) . '.' . $file_extension;

    return $newfilename;
}

function random_str_int($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//사용안함 - 동일서버 업로드시 사용(업로드 함수 - move_uploaded_file())
function file_error($file_error, $error_path, $temp_name)
{
    if (!empty($file_error)) {
        $listOfErrors = array(
            '1' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            '2' => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            '3' => 'The uploaded file was only partially uploaded.',
            '4' => 'No file was uploaded.',
            '6' => 'Missing a temporary folder. Introduced in PHP 5.0.3.',
            '7' => 'Failed to write file to disk. Introduced in PHP 5.1.0.',
            '8' => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.'
        );
        $error = $file_error;

        if (!empty($listOfErrors[$error])) {

            $str = "[IV_ERROR]\n경로 : " . $error_path . " \n에러 : 파일 업로드 에러/" . $listOfErrors[$error];
            telegram_send($str, "DEV");
            header('Content-Type: application/json; charset=utf8');
            $json = json_encode(array("status" => 400, "msg" => "파일 업로드 에러"), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
            echo $json;
            return;
        } else {
            $str = "[IV_ERROR]\n경로 : " . $error_path . " \n에러 : 파일 업로드 에러/" . $listOfErrors[$error];
            telegram_send($str, "DEV");
            header('Content-Type: application/json; charset=utf8');
            $json = json_encode(array("status" => 400, "msg" => "파일 업로드 에러"), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
            echo $json;
            return;
        }
    } else {
        echo '[error] Problem saving file: ' . $temp_name;
        $str = "[IV_ERROR]\n경로 : " . $error_path . " \n에러 : 파일 저장 에러/" . $temp_name;
        telegram_send($str, "DEV");
        header('Content-Type: application/json; charset=utf8');
        $json = json_encode(array("status" => 400, "msg" => "파일 저장 에러"), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
        echo $json;
        return;
    }
}

//사용안함 - 예제로 오류 있음
function socket_file_upload($files, $file_path, $file_server_path, $newfilename)
{

    $info   = parse_url($file_server_path);

    // Setting Protocol
    switch ($info['scheme'] = strtoupper($info['scheme'])) {
        case 'HTTP':
            $info['port']   = 80;
            break;

        case 'HTTPS':
            $info['ssl']    = 'ssl://';
            $info['port']   = 443;
            break;

        default:
            return false;
    }

    $host = $file_server_path; // 원격 서버명
    $port = 80; // 원격 서버 포트
    $path = '/app/interview/file_upload.php';; // 화일을 받아서 처리해주는 화일명

    $file_type = $files['type'];
    $file_tmp_name = $files['tmp_name'];
    $file_name = $files['name'];

    srand((float)microtime() * 1000000);

    $boundary = "---------------------------" . substr(md5(rand(0, 32000)), 0, 10);
    $data = "--$boundary";

    $content_file = implode("", file($file_tmp_name));

    $data .= "Content-Disposition: form-data; name=file1; filename=\"$newfilename\"\r\n";
    $data .= "Content-Type: $file_type\r\n";
    $data .= $content_file . "\r\n";
    $data .= "--$boundary";

    $data .= "--\r\n\r\n";
    $data .= "--\r\n\r\n";

    $msg =
        "POST $path HTTP/1.0
	Content-Type: multipart/form-data; boundary=$boundary
	Content-Length: " . strlen($data) . "\r\n\r\n";

    $result = "";

    // open the connection
    $f = fsockopen($info['ssl'] . $info['host'], $info['port']);

    fwrite($f, $msg . $data);

    // get the response
    while (!feof($f)) $result .= fread($f, 32000);
    echo $result; // -- Debug
    fclose($f);

    //파일저장 확인
    //return socket_file_open($info, $path);
}

//사용안함 - 예제로 오류 있음
//file open
function socket_file_open($info, $file_path)
{
    // Setting Protocol
    switch ($info['scheme'] = strtoupper($info['scheme'])) {
        case 'HTTP':
            $info['port']   = 80;
            break;

        case 'HTTPS':
            $info['ssl']    = 'ssl://';
            $info['port']   = 443;
            break;

        default:
            return false;
    }

    $socket = fsockopen($info['ssl'] . $info['host'], $info['port']);
    if ($socket) {
        $header = "GET " . $file_path . " HTTP/1.0\n\n";
        fwrite($socket, $header); //소켓에 사용시 헤더나 데이터를 보내는데 사용

        $data = '';
        while (!feof($socket)) {
            $data .= fgets($socket);
        }
        fclose($socket);

        $data = explode("\r\n\r\n", $data, 2); //$data안에서 header와 본문(html)분리

        return $data[1];
    } else {
        return false;
    }
}
//
function curl_file_send($files, $newfilename, $filepath)
{

    $postfields = array(
        'upload_file' => curl_file_create($files['tmp_name'], $files['type'], $newfilename),
        'filepath' => $filepath
    );

    $header = array();
    $header[] = 'Content-Type: multipart/form-data';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://media.highbuff.com/app/interview/file_upload.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60초
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $return = curl_exec($ch);

    curl_close($ch);
    return $return;
}

//사용안함 - ssh 라이브러리 필요
function sftp_file_upload($files, $file_path)
{

    // SSH Host
    $ssh_host = 'media.highbuff.com'; //IP주소 또는 도메인주소
    // SSH Port
    $ssh_port = '22';
    // SSH User
    $ssh_auth_user = 'root';
    // SSH Password
    $ssh_auth_pass = 'buff7878!@';
    // SSH Connect 체크
    if (!function_exists("ssh2_connect")) die('Function ssh2_connect does not exist.');

    // SSH Connect
    if (!$conn_id = ssh2_connect($ssh_host, $ssh_port)) die('Failed to connect.');

    // SSH Auth
    if (!ssh2_auth_password($conn_id, $ssh_auth_user, $ssh_auth_pass)) die('Failed to authenticate.');

    // SSH SFTP 접속
    if (!$sftp_conn = ssh2_sftp($conn_id)) die('Failed to create a sftp connection.');


    // SSH 업로드
    $localFile =  @file_get_contents($files['tmp_name']);
    $stream = @fopen("ssh2.sftp://{$sftp_conn}{$file_path}", 'w');
    @fwrite($stream, $localFile);
    @fclose($stream);


    /*	
	// SSH 서버 명령어 실행
	// 폴더 생성
    ssh2_sftp_mkdir($sftp_conn, './www/ddd');
	// 이름 바꾸기
    ssh2_sftp_rename($sftp_conn, '기존 파일 경로', '바꿀 파일 경로');
    // 삭제
    ssh2_exec($conn_id, 'rm -rf 삭제(파일)경로');
    // 복사
    ssh2_exec($conn_id, 'cp  원본(파일)경로 복사(파일)경로');
    // 이동
    ssh2_exec($conn_id, 'mv 원본(파일)경로 이동(파일)경로');
*/
}

function return_error($path, $msg, $appIdx = '' ,$error = '', $sql = '')
{
    $str = "[API_ERROR]\n경로 : " . $path . " \n에러 : " . $msg . " 에러/" . $error . "\napplierIdx" . $appIdx . "\n쿼리 : " . $sql;
    telegram_send($str, "DEV");
    header('Content-Type: application/json; charset=utf8');
    $json = json_encode(array("status" => 400, "msg" => $msg . " 에러"), JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}

function return_response($data)
{
    header('Content-Type: application/json; charset=utf8');

    $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    echo $json;
}

function applier_check($conn_iv_20, $applier_idx)
{

    //applier_idx 확인
    $iv_applier_sql = "SELECT idx, mem_idx FROM iv_applier WHERE idx = '" . $applier_idx . "'";

    if (!$iv_applier_rst = mysqli_query($conn_iv_20, $iv_applier_sql)) {
        $iv_applier_error = mysqli_error($conn_iv_20);
        return_error('interview_profile', 'DB select iv_applier', $iv_applier_error, $iv_applier_sql);
        return;
    }

    if (mysqli_num_rows($iv_applier_rst) <= 0) { //
        return false;
    } else {
        $iv_applier_row = mysqli_fetch_assoc($iv_applier_rst);
        $mem_idx = $iv_applier_row['mem_idx'];
    }
    return $mem_idx;
}

function ttsNaver($tts_text, $tts_index, $tts_type, $server_type = 'real')
{
    $postfields = array(
        'tts_text' => $tts_text,
        'tts_index' => $tts_index,
        'tts_type' => $tts_type,
        'server_type' => $server_type
    );

    $header = array();
    $header[] = 'Content-Type: multipart/form-data';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://media.highbuff.com/app/interview/naver_tts.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60초
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $return = curl_exec($ch);

    curl_close($ch);
    echo $return;

    return $return;
}

if (!function_exists('base64url_encode')) {
    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

$key = '';
$blockSize = 16;
function parseParams($params = null)
{
    if ($params === null) {
        return;
    }

    if (is_array($params)) {
        if (isset($params['key'])) {
            $key = $params['key'];
        }

        if (isset($params['blockSize'])) {
            $blockSize = $params['blockSize'];
        }

        return;
    }

    $key = (string) $params;
}
function opensslEncrypt($data)
{
    $key = 'bluevisor';
    $digest = 'SHA256';
    $cipher = 'AES-256-CTR';
    //$hmacKey = bin2hex(\hash_hkdf('SHA256', $key));

    $secret = hash_hkdf($digest, $key);
    $iv = ($ivSize = openssl_cipher_iv_length($cipher)) ? openssl_random_pseudo_bytes($ivSize) : null;
    $data = openssl_encrypt($data, $cipher, $secret, OPENSSL_RAW_DATA, $iv);
    $result = $iv . $data;
    $hmacKey = hash_hmac($digest, $result, $secret, true);
    $hmacKey = $hmacKey . $result;

    return $hmacKey;
}


function sodium_encrypt($data, $params = null)
{

    $key = sodium_crypto_secretbox_keygen();
    parseParams($params);

    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES); // 24 bytes
    $data = sodium_pad($data, $blockSize);
    $ciphertext = $nonce . sodium_crypto_secretbox($data, $nonce, $key);

    sodium_memzero($data);
    sodium_memzero($key);

    return $ciphertext;
}

function setEncrypt222($str, $secret_key = 'secret key')
{
    $key = substr(hash('sha256', $secret_key, true), 0, 32);
    $iv = substr(hash('sha256', $secret_key, true), 0, 16);
    return base64_encode(openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv));
}

function setDecrypt222($str, $secret_key = 'secret key')
{
    $key = substr(hash('sha256', $secret_key, true), 0, 32);
    $iv = substr(hash('sha256', $secret_key, true), 0, 16);
    return openssl_decrypt(base64_decode($str), "AES-256-CBC", $key, 0, $iv);
}


function sendPush($data){
    $ch = curl_init("https://fcm.googleapis.com/fcm/send");
    $key = "AAAAcP8dvp8:APA91bEmz-VTmMgjpNtH0EGChzmTpu7zn7TOOKtadcixtDhGl1-OKd-zNaN9e3EtFZ6rvC-9UBr9W7KX6o_MY49v2laVhzQNXaAs1Oe6CKnktscIHyywdIYoZoMJqACxQby50X_6Dicr";
    $header = array("Content-Type:application/json", "Authorization:key={$key}");
    // $data = json_encode(array(
    //     "to" => "핸드폰 or 에뮬로 실행하고 로그에 나온 장문의 토큰 문자열",
    //     "notification" => array(
    //         "title"   => $_REQUEST['title'],
    //         "message" => $_REQUEST['message'])
    //         ));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_exec($ch);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}