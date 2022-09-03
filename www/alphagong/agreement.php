<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

$sql = 'SELECT cf_stipulation, cf_privacy FROM g5_config';
$rst = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($rst);
$agreement = '이용약관<br>'.str_replace("\r\n", "<br>", $row['cf_stipulation']).'<br><br>개인정보처리방침<br>'.str_replace("\r\n", "<br>", $row['cf_privacy']);
echo json_encode(array('status' => 200, 'agreement' => $agreement));