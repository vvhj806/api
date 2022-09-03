<?php

$upfile_dir = '/home/api/www/interview/20/best_answer';
$addr = $_SERVER["REMOTE_ADDR"];
// $dates = date("Y-m-d H:i:s");
error_reporting(E_ALL);
	ini_set("display_errors", 1);

date_default_timezone_set("Asia/Seoul");
$connect = mysqli_connect("172.27.0.215", "masterInterview", "Buffbuff7878!@", "new_interview");
mysqli_query($connect, "set names utf8;");

setlocale(LC_CTYPE, 'ko_KR.eucKR');

//장시간 데이터 처리될경우
set_time_limit(0);

echo ('<meta http-equiv="content-type" content="text/html; charset=utf-8">');

$file_name=$_FILES['upload_file']['name'];
$file_type=$_FILES['upload_file']['type'];
$file_size=$_FILES['upload_file']['size'];
$file_tmp  = $_FILES['upload_file']['tmp_name'];

// echo ('<script>alert("'.$file_name.'")</script>');
// echo ('<script>alert("'.$file_type.'")</script>');
// echo ('<script>alert("'.$file_size.'")</script>');
// echo ('<script>alert("'.$file_tmp.'")</script>');


if ($file_name){
	if (file_exists("{$upfile_dir}/{$file_name}") ) { unlink("{$upfile_dir}/{$file_name}"); }
	if (move_uploaded_file($file_tmp,"{$upfile_dir}/{$file_name}")) {
	} else {
		echo ("<script>window.alert('디렉토리에 복사실패'); history.go(-1) </script>");
		exit;
	}
}
// $file_name = '질문 db_인터뷰.csv';
// 저장된 파일을 읽어 들인다
$csvLoad  = file("{$upfile_dir}/{$file_name}");

// 행으로 나누어서 배열에 저장
$csvArray = explode("\r\n",implode($csvLoad));

// print_r($csvArray);

for($i=1;$i<count($csvArray)-1;$i++){
    // echo ('<script>alert("'.$file_name.'")</script>');
    // 각 행을 콤마를 기준으로 각 필드에 나누고 DB입력시 에러가 없게 하기위해서 addslashes함수를 이용해 \를 붙입니다
    // print_r($csvArray[$i]);

    
	if(strpos($csvArray[$i],'"') !== false) { 
		//echo "포함";
		// print_r($csvArray[$i]);
		$aa = explode('"',$csvArray[$i]);
		// echo '<br>';echo '<br>';echo '<br>';
        // print_r($aa);
        // exit;
		// print_r( $aa[2]);
		$bb = str_replace(',','',$aa[1]);
		// print_r '<br>';
		// print_r( $bb);

        

		// echo '<br>';

		$cc = str_replace($aa[1],$bb,$csvArray[$i]);
        // print_r( $cc);
		// echo '<br>';
		// echo '4856416894'.$cc;
		//echo '<br>';
		$dd=str_replace('"','',$cc);
		$ee=str_replace("'",'',$dd);
        // print_r($ee);
        // exit;
		//$csvArray[$i]= str_replace(',','!',$cc);
		$csvArray[$i]=$ee;
        // exit;
	}
	else{
		//echo "노";
	}
    $field= explode(",",addslashes($csvArray[$i]));
    // print_r($field);

    // 나누어진 각 필드에 앞뒤에 공백을 뺸뒤 ''따옴표를 붙이고 ,콤마로 나눠서 한줄로 만듭니다.
    $value     = "'" . trim(implode("','",$field)) . "'";
	//echo $value."<br>";
    // print_r($value);
	$value_arr=explode(',',$value);
	$job_idx=$value_arr[0];
	// print_r($job_idx);
	$que_type=$value_arr[1];
	// print_r($que_type);
    $que_question =  str_replace("'","",$value_arr[2]);
    // print_r($que_question);
    $que_sentence=str_replace(' ','',$que_question);

    $que_best_answer = $value_arr[4];
    // print_r($que_best_answer);
    $dates = date("Y-m-d H:i:s");
    // $que_sentence = '자기소개를부탁드립니다.';  //질문문장
    $best_sentence =  str_replace("'","",$que_best_answer);    //모범답변
    // print_r($que_sentence);
    $category = str_replace("'","",$job_idx); //iv_job_category idx
    // print_r($category);
    
    $sql = "SELECT idx FROM iv_question_copy_best WHERE replace(REPLACE(que_question,' ',''), '\r\n', '') = '" . $que_sentence . "'";
    $rst = mysqli_query($connect, $sql);
    $row = mysqli_fetch_array($rst);
    print_r($sql);
    print_r($row);
    if ($row) {
        // print_r($best_sentence);
        //테이블에 질문이 있으면 update
        // echo ('<script>alert("'.$file_name.'121111")</script>');
        $sql_u = "UPDATE iv_question_copy_best SET que_best_answer = '" . $best_sentence . "' WHERE  idx = '" . $row['idx'] . "'";
        mysqli_query($connect, $sql_u);
    } else if (!$row) {
        // print_r($row);
        //테이블에 질문이 없으면 insert
        $sql_i = "INSERT INTO iv_question_copy_best (`job_idx`,`que_type`,`que_question`,`que_lang_type`,`que_reg_date`,`delyn`, `que_best_answer`) 
                      VALUES ('" . $category . "', 'j', '" . $que_question . "',0, '" . $dates . "', 'N', '" . $best_sentence . "')";
        mysqli_query($connect, $sql_i);
    } else {
    }
}

unlink("{$upfile_dir}/{$file_name}");
?>