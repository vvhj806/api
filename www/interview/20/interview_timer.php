<?php
include_once('common.php');

$applier_idx = $_POST['applier_idx'];
$answer_time = $_POST['answer_time'];

//applier_idx 확인
$mem_idx = applier_check($conn_iv_20, $applier_idx);
if($mem_idx){
//iv_report_result repo_answer_time update
$iv_report_result_sql = "UPDATE iv_report_result SET repo_answer_time = ".$answer_time.", repo_mod_date = NOW() WHERE applier_idx = '".$applier_idx."'";

if(!$iv_report_result_rst = mysqli_query($conn_iv_20, $iv_report_result_sql)){ 
	$iv_report_result_error = mysqli_error($conn_iv_20); 

	return_error('interview_audio', 'DB update', $iv_report_result_error, $iv_report_result_sql);
	return;
}else{
	$response_data = array(
		"status" => 200, 
		"msg" => "db update 성공",
	);
	return_response($response_data);
}

}else{
	return_error('interview_profile', 'DB select iv_applier');
}



