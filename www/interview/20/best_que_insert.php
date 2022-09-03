<?php
date_default_timezone_set("Asia/Seoul");
$connect = mysqli_connect("172.27.0.215", "masterInterview", "Buffbuff7878!@", "new_interview");
mysqli_query($connect, "set names utf8;");

echo '[모범답안 넣기]<br><br>';

$dates = date("Y-m-d H:i:s");
$que_sentence = '자기소개를부탁드립니다.';  //질문문장
$best_sentence = '';    //모범답변
$category = ''; //iv_job_category idx

$sql = "SELECT idx FROM iv_question_copy_best WHERE REPLACE(que_question,' ','') = '" . $que_sentence . "'";
$rst = mysqli_query($connect, $sql);
$row = mysqli_fetch_array($rst);

print_r($row['idx']);

if ($row) {
    //테이블에 질문이 있으면 update
    $sql_u = "UPDATE iv_question_copy_best SET que_best_answer = '" . $best_sentence . "' WHERE  idx = '" . $row['idx'] . "'";
    // mysqli_query($connect, $sql_u);
} else if (!$row) {
    //테이블에 질문이 없으면 insert
    $sql_i = "INSERT INTO iv_question_copy_best (`job_idx`,`que_type`,`que_question`,`que_lang_type`,`que_reg_date`,`delyn`, `que_best_answer`) 
                  VALUES ('" . $category . "', 'j', '" . $que_sentence . "','0', '" . $dates . "', 'N', '" . $best_sentence . "')";
    // mysqli_query($connect, $sql_i);
} else {
}
