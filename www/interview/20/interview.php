<?php
include_once('common.php');

$applier_idx = $_POST['applier_idx'];
$method = $_POST['method'];

//applier_idx 확인
$mem_idx = applier_check($conn_iv_20, $applier_idx);
if($mem_idx){
	if ($method == 'question') {
		//iv_report_result iv_question select
		$question_sql = "SELECT B.idx  q_idx, B.que_question question, A.repo_answer_time answer_time, B.que_lang_type tts_type  FROM iv_report_result A LEFT OUTER JOIN iv_question B ON A.que_idx = B.idx WHERE A.applier_idx = ".$applier_idx." AND A.que_idx IS NOT NULL AND A.delyn = 'N' ORDER BY A.idx ASC";

		if(!$question_rst = mysqli_query($conn_iv_20, $question_sql)){ 
			$question_error = mysqli_error($conn_iv_20); 
			return_error('interview', 'DB select iv_report_result iv_question', $question_error, $question_sql);
			return;
		}else{
			$question_cnt = mysqli_num_rows($question_rst);
		}

		//if ($question_cnt <= 0) { //
		//    return_error('interview', 'DB select iv_report_result iv_question', $question_error, $question_sql);
		//	return;
		//}else{
			$q = array();
			$iv_applier_profile_cnt = 0;
			while ($question_row = mysqli_fetch_array($question_rst)) {
				$q[$iv_applier_profile_cnt] = array(
					"q_idx" => $question_row['q_idx'],
					"question" => $question_row['question'],
					"answer_time" => $question_row['answer_time'],
					"wait_time" => 30,
				);

				ttsNaver($question_row["question"], $question_row["q_idx"],$question_row["tts_type"]);

				$iv_applier_profile_cnt++;
			}

			//iv_applier rec_idx select - 재촬영 타입 
			$applier_sql = "SELECT idx, rec_idx, rec_nos_idx, i_idx, info_idx, app_type FROM iv_applier WHERE idx = ".$applier_idx;
			$type = false;
			if(!$applier_rst = mysqli_query($conn_iv_20, $applier_sql)){ 
				$applier_error = mysqli_error($conn_iv_20); 
				return_error('interview', 'DB select iv_applier', $applier_error, $applier_sql);
				return;
			}else{
				$applier_row = mysqli_fetch_assoc($applier_rst);
				if($applier_row['rec_idx'] > 0){
					//rec_idx > 0 기업에서 올린 인터뷰
					if($applier_row['app_type'] == 'M' || $applier_row['rec_idx'] == 0){
						//i_idx null 내 인터뷰
						$type = true;
					}else{
						$type = false;
					}
				}else{
					//공고인터뷰가 아닌경우 재촬영
					$type = true;
                    if($applier_row['app_type'] == 'B'){
                        $type = false;
                    }
				}
			}

			$response_data = array(
				"status" => 200, 
				"msg" => "db select 성공",
				"q" => $q,
				"count_q" => $question_cnt,
				"event" => 0,
				"type" => $type
			);
			return_response($response_data);
		//}


	}else if($method == 'upload'){
		$count = $_POST['count'];
		$video_blob = $_FILES['video_blob'];
		$q_idx = $_POST['q_idx'];

		if($video_blob['tmp_name'] == ''){
			return_error('interview', 'request file', $applier_idx);
			return;
		}
		
		$temp_name = $video_blob['tmp_name'];
		$file_name = $video_blob['name']; //파일명
		$file_size = $video_blob['size']; //파일사이즈
		
		$newfilename = video_name_make($applier_idx, 'record', $file_name, $count, $q_idx); //저장 파일명
		$file_path = "/data/uploads/".$newfilename;
		
		//file upload
		if(curl_file_send($video_blob, $newfilename, $file_path)){
			//iv_report_result select
			//iv_report_result iv_question select
			$report_result_sql = "SELECT idx FROM iv_report_result WHERE applier_idx = ".$applier_idx." AND que_idx = ".$q_idx;

			if(!$report_result_rst = mysqli_query($conn_iv_20, $report_result_sql)){ 
				$report_result_error = mysqli_error($conn_iv_20); 
				return_error('interview', 'DB select iv_report_result iv_question', $report_result_error, $report_result_sql);
				return;
			}else{
				//$report_result_cnt = mysqli_num_rows($question_rst);
				$report_result_row = mysqli_fetch_assoc($report_result_rst);
				$repo_res_idx = $report_result_row['idx'];
			}
		

			//iv_video insert
			$iv_video_sql = "INSERT INTO iv_video (app_idx, que_idx, repo_res_idx, video_record, video_reg_date, video_mod_date, delyn) VALUES 
					({$applier_idx}, {$q_idx}, {$repo_res_idx}, '{$newfilename}', NOW(), NOW() ,'N')";
			if(!$iv_video_rst = mysqli_query($conn_iv_20, $iv_video_sql)){ 
				$iv_video_error = mysqli_error($conn_iv_20); 
			
				return_error('interview', 'DB insert iv_video', $iv_video_error, $iv_video_sql);
				return;
			}else{
				$iv_video_idx = mysqli_insert_id($conn_iv_20);
			}

			//iv_report_result update
			$iv_report_result_sql = "UPDATE iv_report_result SET repo_process = '1' WHERE applier_idx = ".$applier_idx." AND que_idx = ".$q_idx;
			if(!$iv_report_result_rst = mysqli_query($conn_iv_20, $iv_report_result_sql)){ 
				$iv_report_result_error = mysqli_error($conn_iv_20); 
				return_error('interview', 'DB update iv_report_result', $iv_report_result_error, $iv_report_result_sql);
				return;
			}else{
				//iv_report_result iv_question select
				$question_sql = "SELECT B.idx  q_idx, B.que_question question FROM iv_report_result A LEFT OUTER JOIN iv_question B ON A.que_idx = B.idx WHERE A.applier_idx = ".$applier_idx." AND A.que_idx IS NOT NULL AND A.delyn = 'N' ORDER BY A.idx ASC";

				if(!$question_rst = mysqli_query($conn_iv_20, $question_sql)){ 
					$question_error = mysqli_error($conn_iv_20); 
					return_error('interview', 'DB select iv_report_result iv_question', $question_error, $question_sql);
					return;
				}else{
					$question_cnt = mysqli_num_rows($question_rst);
				}

				//if ($question_cnt <= 0) { //
				//	return_error('interview', 'DB select iv_report_result iv_question', $question_error, $question_sql);
				//	return;
				//}else{
					if($count == $question_cnt){
						//iv_applier update
						$iv_applier_sql = "UPDATE iv_applier SET app_iv_stat = '3' WHERE idx = ".$applier_idx;
						if(!$iv_applier_rst = mysqli_query($conn_iv_20, $iv_applier_sql)){ 
							$iv_applier_error = mysqli_error($conn_iv_20); 
						}

						if(mysqli_affected_rows($conn_iv_20) <= 0){ //fail
							return_error('interview', 'DB update iv_applier', $iv_applier_error, $iv_applier_sql);
							return;
						}else{
							$response_data = array(
								"status" => 201, 
								"msg" => "인터뷰 완료",
							);
							return_response($response_data);
						}
					}else{
						$response_data = array(
							"status" => 200, 
							"msg" => "db update 성공",
						);
						return_response($response_data);
					}
				//}
			}

			//iv_report_result update
		}
	}else if($method == 'end'){
		//iv_applier rec_idx select
		$applier_sql = "SELECT idx, rec_idx, rec_nos_idx, i_idx, info_idx, app_type FROM iv_applier WHERE idx = ".$applier_idx;

		if(!$applier_rst = mysqli_query($conn_iv_20, $applier_sql)){ 
			$applier_error = mysqli_error($conn_iv_20); 
			return_error('interview', 'DB select iv_applier', $applier_error, $applier_sql);
			return;
		}else{
			$applier_row = mysqli_fetch_assoc($applier_rst);
			if($applier_row['rec_idx'] > 0){
				//rec_idx > 0 기업에서 올린 인터뷰
				if($applier_row['app_type'] == 'M' || $applier_row['rec_idx'] == 0){
					//i_idx null 내 인터뷰
					$type = 2;
					$link_type = "C";
				}else{
					//i_idx > 0 , iv_interview inter_type = B 모의인터뷰
					//i_idx > 0 , iv_interview inter_type = C 기업인터뷰

					//iv_interview, inter_type select
					$interview_sql = "SELECT inter_type FROM iv_interview WHERE idx = ".$applier_row['i_idx'];

					if(!$interview_rst = mysqli_query($conn_iv_20, $interview_sql)){ 
						$interview_error = mysqli_error($conn_iv_20); 
						return_error('interview', 'DB select iv_interview', $interview_error, $interview_sql);
						return;
					}else{
						//$report_result_cnt = mysqli_num_rows($question_rst);
						$interview_row = mysqli_fetch_assoc($interview_rst);

						if($interview_row['inter_type'] == 'C'){
							//i_idx > 0 , iv_interview inter_type = C 기업인터뷰
							$type = 1;
							$link_type = "C";
						}else if($interview_row['inter_type'] == 'B'){
							//i_idx > 0 , iv_interview inter_type = B 모의인터뷰
							$type = 4;
							$link_type = "B";
						}else{
							$response_data = array(
								"status" => 400, 
								"msg" => "인터뷰 TYPE ERROR",
							);
							return_response($response_data);
						}
					}
				}
			}else if($applier_row['rec_idx'] == NULL || $applier_row['rec_idx'] == 0){
				if($applier_row['rec_nos_idx'] > 0){
					//rec_nos_idx > 0 모의인터뷰
					$type = 5;
					$link_type = "B";
				}else if($applier_row['rec_nos_idx'] == NULL || $applier_row['rec_nos_idx'] == 0){
					//rec_idx null , rec_nos_idx null 연습인터뷰
					$type = 3;
					$link_type = "B";
				}else{
					$response_data = array(
						"status" => 400, 
						"msg" => "인터뷰 TYPE ERROR",
					);
					return_response($response_data);
				}
				
			}
			
			if($link_type == "C"){
				$aData = [
					'idx' => array($applier_row['rec_idx']),
					'state' => $applier_row['app_type'],
				];
				
				$link[0] = array(
					"p" => $server_url."/jobs/apply?app={$applier_idx}&data=".base64url_encode(opensslEncrypt(json_encode($aData)))
				);
				$link[1] = array(
					"p" => $server_url."/jobs/list"
				);
				$link[2] = array(
					"p" => $server_url."/report"
				);
			}else if($link_type == "B"){
				$link[0] = array(
					"p" => $server_url."/report"
				);
				$link[1] = array(
					"p" => $server_url."/"
				);
			}

			$response_data = array(
				"status" => 200, 
				"msg" => "db select 성공",
				"type" => $type,
				"link" => $link,
			);
			return_response($response_data);
		}

	}else{
		return_error('interview', 'method');
	}

}else{
	return_error('interview', 'DB select iv_applier');
}