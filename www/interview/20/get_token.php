<?php include_once($_SERVER["DOCUMENT_ROOT"].'/interview/20/sns_config.php') ?>
<?php include_once($_SERVER["DOCUMENT_ROOT"].'/db_config.php') ?>
<?php
$dates = date("Y-m-d H:i:s");
$addr = $_SERVER["REMOTE_ADDR"];
$user_token = $_POST["user_token"];
$user_id = $_POST["user_id"];

if($user_token == "" || $user_id == ""){
	$str = "[IV_ERROR]\n경로 : /app/get_token.php\n에러 : 비정상접근 토큰 혹은 id 없음\n#user_token=".$user_token."#user_id=".$user_id;
	telegram_send($str, "DEV");

	header('Content-Type: application/json; charset=utf8');
	$json = json_encode(array("status"=>"400", "error"=>"비정상접근 토큰 혹은 id 없음"), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
	echo $json;
	return;
}

$sql1 = "SELECT * FROM iv_member WHERE user_id = '".$user_id."'";
$rst1 = mysqli_query($conn, $sql1);
$row1 = mysqli_fetch_array($rst1);

if (!$row1){
	$str = "[IV_ERROR]\n경로 : /app/get_token.php\n에러 : 유저 정보 없음\nuser_id=".$user_id;
	telegram_send($str, "DEV");

	header('Content-Type: application/json; charset=utf8');
	$json = json_encode(array("status"=>"400", "error"=>"유저 정보 없음", "sql"=>$sql1), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
	echo $json;
	return;
}

$qry2 = "UPDATE iv_member SET token = '".$user_token."' WHERE user_id = '".$user_id."'";
mysqli_query($conn, $qry2);

header('Content-Type: application/json; charset=utf8');
$json = json_encode(array("status"=>"200", "name"=>$row1["user_name"]), JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
echo $json;

?>