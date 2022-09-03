<?php
include_once('common.php');

$applier_idx = $_POST['applier_idx'];
$audio = $_POST['audio'];

//applier_idx 확인
$mem_idx = applier_check($conn_iv_20, $applier_idx);
if($mem_idx){
if($audio == true){
	//iv_applier app_iv_stat update
	$iv_applier_sql = "UPDATE iv_applier SET app_iv_stat = '2', app_mod_date = NOW() WHERE idx = '".$applier_idx."'";
	if(!$iv_applier_rst = mysqli_query($conn_iv_20, $iv_applier_sql)){ 
		$iv_applier_error = mysqli_error($conn_iv_20); 
	
		return_error('interview_audio', 'DB update', $iv_applier_error, $iv_applier_sql);
		return;
	}else{
		$response_data = array(
			"status" => 200, 
			"msg" => "db update 성공",
		);
		return_response($response_data);
	}
}else{
	return_error('interview_audio', 'audio check');
	return;
}
}else{
	return_error('interview_profile', 'DB select iv_applier');
}