<?php
include_once('common.php');


$method = $_POST['method'];
$applier_idx = $_POST['applier_idx'];

//applier_idx 확인
$mem_idx = applier_check($conn_iv_20, $applier_idx);

if($mem_idx){

	if ($method == 'profile') { //프로필 - 기기 촬영 및 앨범 선택 완료 시  
		$thumbnail = $_FILES['thumbnail'];
		if($thumbnail['tmp_name'] == ''){
			return_error('interview_profile', 'request file', $applier_idx);
			return;
		}
		
		$temp_name = $thumbnail['tmp_name'];
		$file_name = $thumbnail['name']; //파일명
		$file_size = $thumbnail['size']; //파일사이즈
		
		$newfilename = file_name_make($applier_idx, 'thumbnail', $file_name); //저장 파일명
		$file_path = "/data/uploads_thumbnail/".$newfilename;
		
		//file upload
		if(curl_file_send($thumbnail, $newfilename, $file_path)){
			
			//iv_file file_type = A insert
			$iv_file_sql = "INSERT INTO iv_file (file_type, file_org_name, file_save_name, file_size, file_reg_date, file_mod_date, delyn) VALUES 
					('A', '{$file_name}', '{$file_path}', '{$file_size}', NOW(), NOW() ,'N')";
			if(!$iv_file_rst = mysqli_query($conn_iv_20, $iv_file_sql)){ 
				$iv_file_error = mysqli_error($conn_iv_20); 
			
				return_error('interview_profile', 'DB insert iv_file', $iv_file_error, $iv_file_sql);
				return;
			}else{
				$iv_file_idx = mysqli_insert_id($conn_iv_20);
			}

			//iv_applier file_idx_thumb update
			$iv_applier_sql = "UPDATE iv_applier SET file_idx_thumb = '".$iv_file_idx."', app_iv_stat = '1', app_mod_date = NOW() WHERE idx = '".$applier_idx."'";
			if(!$iv_applier_rst = mysqli_query($conn_iv_20, $iv_applier_sql)){ 
				$iv_applier_error = mysqli_error($conn_iv_20); 
			
				return_error('interview_profile', 'DB update', $iv_applier_error, $iv_applier_sql);
				return;
			}else{
				//$response_data = array(
				//	"status" => 200, 
				//	"msg" => "db update 성공",
				//);
				//return_response($response_data);
			}

			//iv_applier_profile iv_applier_profile insert
			$iv_applier_profile_sql = "INSERT INTO iv_applier_profile (app_idx, mem_idx, file_idx) VALUES
					({$applier_idx}, {$mem_idx}, {$iv_file_idx})";
			if(!$iv_applier_profile_rst = mysqli_query($conn_iv_20, $iv_applier_profile_sql)){ 
				$iv_applier_profile_error = mysqli_error($conn_iv_20); 
			
				return_error('interview_profile', 'DB insert iv_applier_profile', $iv_applier_profile_error, $iv_applier_profile_sql);
				return;
			}else{
				$response_data = array(
					"status" => 200, 
					"msg" => "db insert 성공",
					"file_idx" => $iv_file_idx
				);
				return_response($response_data);
			}
		}
		
	}else if($method == 'exist'){ //프로필 - 서버에 저장된 프로필 불러오기
		$iv_applier_profile_sql = "SELECT iff.idx file_idx, iff.file_save_name file_save_name FROM iv_applier_profile iap LEFT OUTER JOIN iv_file iff ON iap.file_idx = iff.idx WHERE iap.mem_idx = ".$mem_idx." LIMIT 12";

		if(!$iv_applier_profile_rst = mysqli_query($conn_iv_20, $iv_applier_profile_sql)){ 
			$iv_applier_profile_error = mysqli_error($conn_iv_20);
			
			return_error('interview_profile', 'DB select iv_applier_profile', $iv_applier_profile_error, $iv_applier_profile_sql);
			return;
		}

	   // if (mysqli_num_rows($iv_applier_profile_rst) <= 0) { //
			//return_error('interview_profile', 'DB select iv_applier_profile', $iv_applier_profile_error, $iv_applier_profile_sql);
			//return;
	  //  }else{
			$profile_list = array();
			$iv_applier_profile_cnt = 0;
			while ($iv_applier_profile_row = mysqli_fetch_array($iv_applier_profile_rst)) {
				$profile_list[$iv_applier_profile_cnt] = array(
					"file_idx" => $iv_applier_profile_row['file_idx'],
					"file_save_name" => $file_server_url.$iv_applier_profile_row['file_save_name']
				);
				$iv_applier_profile_cnt++;
			}
			$response_data = array(
				"status" => 200, 
				"msg" => "db select 성공",
				"profile_list" => $profile_list
			);
			return_response($response_data);
		//}

	}else if($method == 'complete'){ //프로필 - 인터뷰에 프로필 선택 값 저장
		$file_idx = $_POST['file_idx'];
		//iv_applier file_idx_thumb update
		$iv_applier_sql = "UPDATE iv_applier SET file_idx_thumb = '".$file_idx."', app_iv_stat = '1', app_mod_date = NOW() WHERE idx = '".$applier_idx."'";
		if(!$iv_applier_rst = mysqli_query($conn_iv_20, $iv_applier_sql)){ 
			$iv_applier_error = mysqli_error($conn_iv_20); 
		
			return_error('interview_profile', 'DB update', $iv_applier_error, $iv_applier_sql);
			return;
		}else{
			$response_data = array(
				"status" => 200, 
				"msg" => "db update 성공",
			);
			return_response($response_data);
		}
	}else if($method == 'start'){
		//iv_applier file_idx_thumb select
		$applier_sql = "SELECT idx, file_idx_thumb FROM iv_applier WHERE idx = ".$applier_idx;

		if(!$applier_rst = mysqli_query($conn_iv_20, $applier_sql)){ 
			$applier_error = mysqli_error($conn_iv_20); 
			return_error('interview', 'DB select iv_applier', $applier_error, $applier_sql);
			return;
		}else{
			$applier_row = mysqli_fetch_assoc($applier_rst);
			if($applier_row['file_idx_thumb'] > 0){
				//iv_applier, iv_file file_idx_thumb select
				$applier_file_sql = "SELECT A.idx, A.file_idx_thumb file_idx, B.file_save_name file_save_name FROM iv_applier A LEFT JOIN iv_file B ON A.file_idx_thumb = B.idx WHERE A.idx= ".$applier_idx." AND B.delyn = 'N'";

				if(!$applier_file_rst = mysqli_query($conn_iv_20, $applier_file_sql)){ 
					$applier_file_error = mysqli_error($conn_iv_20); 
					return_error('interview', 'DB select iv_applier iv_file', $applier_file_error, $applier_file_sql);
					return;
				}else{
					//$report_result_cnt = mysqli_num_rows($question_rst);
					$applier_file_row = mysqli_fetch_assoc($applier_file_rst);

					$profile_list[0] = array(
						"file_idx" => $applier_file_row['file_idx'],
						"file_save_name" => $file_server_url.$applier_file_row['file_save_name']
					);
				}
			}else if($applier_row['file_idx_thumb'] == NULL || $applier_row['file_idx_thumb'] == 0){
				$profile_list[0] = array(
					"file_idx" => 0,
					"file_save_name" => $server_url."/static/www/img/sub/prf_no_img.jpg"
				);
			}
		}
		$response_data = array(
			"status" => 200, 
			"msg" => "db select 성공".$applier_idx,
			"profile_list" => $profile_list
		);
		return_response($response_data);
	}else{
		return_error('interview_profile', 'method');
	}
}else{
	return_error('interview_profile', 'DB select iv_applier2');
}