<?php
date_default_timezone_set("Asia/Seoul");
// $connect = mysqli_connect("14.63.226.99", "masterInterview", "Buffbuff7878!@", "test_interview");    //webtest
$conn_iv20 = mysqli_connect("172.27.0.215", "masterInterview", "Buffbuff7878!@", "new_interview");        //2.0
mysqli_query($conn_iv20, "set names utf8;");

$sql = "SELECT bb.idx job_idx, aa.* FROM iv_worknet_jobopening aa
LEFT JOIN
(SELECT a.id, b.idx FROM 
(SELECT id, mapping, 3_depth FROM iv_worknet_job_category_3dp WHERE mapping != '' GROUP BY id) a
LEFT JOIN 
(SELECT idx, job_depth_1, job_depth_2, job_depth_3 FROM iv_job_category WHERE idx >= 154 AND job_depth_3 IS NOT NULL) b
ON 
a.mapping = b.idx) bb
ON aa.jobsCd = bb.id
";

$rst = mysqli_query($conn_iv20, $sql);
if (!$rst = mysqli_query($conn_iv20, $sql)) {
    $error = mysqli_error($conn_iv20);
    echo '2 : ' . $error;
    exit;
} else {

    while ($row = mysqli_fetch_array($rst)) {
        $wanted = json_decode($row['wanted'], true);
        $wantedInfo = json_decode($row['wantedInfo'], true);

        //공고 근무형태
        $work_type = explode('/', $wantedInfo['empTpNm']);
        if ($work_type[0] == '기간의 정함이 없는 근로계약') {
            $rec_work_type = 0;
        } else if ($work_type[0] == '기간의 정함이 있는 근로계약') {
            $rec_work_type = 1;
        } else if ($work_type[0] == '시간선택제 일자리') {
            $rec_work_type = 1;
        } else if ($work_type[0] == '파견근로') {
            $rec_work_type = '0,1';
        } else if ($work_type[0] == '대체인력') {
            $rec_work_type = '0,1';
        } else {
            $rec_work_type = '0,1';
        }
        echo $row['wantedAuthNo'] . ' : ' . $rec_work_type . '<br>';

        if($wanted['career']=='경력'){
			$rec_career = 'C';
		}else if($wanted['career']=='신입'){
			$rec_career = 'N';
		}else if($wanted['career']=='관계없음'){
			$rec_career = 'A';
		}else{
			$rec_career = 'A';
		}

        $sql_update = "update iv_recruit set rec_career = '{$rec_career}' where rec_ex_1 = '{$row['wantedAuthNo']}'";

        if (!$rst_update = mysqli_query($conn_iv20, $sql_update)) {
            //          $error_update = mysqli_error($conn_iv20);
            //         echo '1 : ' . $error_update;
        } else {
            echo "공고 업데이트 완료 : {$sql_update}<br>";
        }
        // }
    }
}
