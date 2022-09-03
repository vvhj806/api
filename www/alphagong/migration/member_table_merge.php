<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

//exit;
displayError();
//
//$str = 1;
//
//$key = substr(hash('sha256', 'bluevisorencrypt', true), 0, 32);
//echo $key ."<br>";
//$iv = substr(hash('sha256', 'bluevisorencrypt', true), 0, 16);
//echo $iv."<br>";
//
//echo openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv)."<br>";
//
//echo base64_encode(openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv));
//
//exit;


$sql = 'SELECT * FROM iv_member';
$rst = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($rst)) {
    $level = $row['level'];
    $leave_date = '';
    if ($level == -1) {
        $leave_date = $row['leave_date'];
    }
    $sql1 = "INSERT INTO g5_member SET
        mb_id = '".trim($row['user_id'])."',
        mb_password = '".trim($row['user_pw'])."',
        mb_name = '".trim($row['user_name'])."',
        mb_nick = '".trim($row['user_name'])."',
        mb_email = '".trim($row['user_id'])."',
        mb_level = '".$level."',
        mb_hp = '".trim($row['mobile'])."',
        mb_datetime = '".$row['dates']."',
        mb_leave_date = '".$leave_date."',
        mb_mailling = '".$row['s_agree']."',
        mb_sms = '".$row['m_agree']."',
        mb_1 = '".$row['p_agree']."',
        mb_2 = '".$row['platform']."',
        mb_3 = '".$row['token']."'
    ";
    echo $sql1."<br>";
    $rst1 = mysqli_query($conn, $sql1);
}