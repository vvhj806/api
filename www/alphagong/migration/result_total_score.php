<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

exit;


function generate_sums( $value, $key ) {
    global $a_helper;

    if( !isset($a_helper[$key]) ) {
        $a_helper[$key] = [0,0];    
    }

    $a_helper[$key][0] += $value; 

    if($value == 0){
        return;
    }

    $a_helper[$key][1] += 1;      
}

displayError();
/*
22.02.16 igg 작업
알파공 iv_applier 모든 데이터의 idx 기준으로 iv_result에 데이터가 기존에는 total에 값이 다 있고, 나머지 질문에는 3개씩밖에 없었음
그래서 나머지 질문들의 3개 값고 total 값을 조합하여, 질문별로 값을 만드는 작업임
----------------
result_baisc_score.php 에서 개별 질문값은 만들었고, 여기는 개별 질문값들을 다시 읽어서 평균치를 구해서 total값을 업데이트 해야함
먼저 total에 있는 성별,나이,피부,수염,머리길이,안경착용여부값을 가져오고, 나머지 값들은 기존 값들을 가지고 평균치를 구함, 분석불가는 카운트X
*/

$sql = "SELECT * FROM iv_applier ORDER BY idx DESC";
//$sql = "SELECT * FROM iv_applier ORDER BY RAND() LIMIT 1";
$rst = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($rst)) {
    $idx = $row['idx'];

    $sql1 = "SELECT * FROM iv_result_new WHERE applier_idx = '".$idx."' AND process = 2 AND question_idx = 'total'";
    $rst1 = mysqli_query($conn, $sql1);
    while ($row1 = mysqli_fetch_array($rst1)) {
        $score = explode('##', $row1['score']); 
        $gender = $score[0]; //gender (0은 분석불가)
        $age = $score[1]; //age (0은 분석불가)
        $data1 = array_map('intval', explode(",", $score[2])); //표정분석(0스마일비중, 1반복습관, 2홍조현상, 3눈깜빡임, 4입술떨림, 5시선)
        $data2 = array_map('intval', explode(",", $score[3])); //얼굴분석(0피부톤, 1수염비중, 2헤어스타일, 3안경착용여부) 음성분석(4발음정확도, 5방언, 6목소리톤, 7소리떨림, 8외국어빈도)

        // ===================== 성별 =====================
        if (strpos($gender, ',') !== false) { 
            $gender2 = array_map('intval', explode(",", $gender));
        } else { //값이 있을경우
            $gender2 = 0;
        }
        // ===================== 나이 =====================
        if (strpos($age, ',') !== false) {
            $age2 = array_map('intval', explode(",", $age));
        } else {
            $age2 = 0;
        }
        $skin = $data2[0]; //피부톤
        $beard = $data2[1]; //수염비중
        $hair_length = $data2[2]; //머리길이
        $glasses = ($data2[3] == 0) ? 'false' : 'true'; //안경착용여부
    }

    $mergeJSON = array();
    $sql2 = "SELECT * FROM iv_result_new WHERE applier_idx = '".$idx."' AND process = 2 AND question_idx != 'total'";
    $rst2 = mysqli_query($conn, $sql2);
    while ($row2 = mysqli_fetch_array($rst2)) {

        $json = $row2["score"];
        $mergeJSON[] = json_decode($row2["score"], true);
    }

    if (count($mergeJSON)) { //iv_result에 score값이 있는것들만 진행
        $resultJSON =  json_encode($mergeJSON);
        $data = json_decode($resultJSON);
        $a_helper = []; 
        $a_avgs = []; 
        foreach( $data as $obj ) {
            $loc_array = (array) $obj;     
            array_walk($loc_array, 'generate_sums');
        }

        foreach( $a_helper as $key => $pair ) {
            if($pair[1] == 0){
                $a_avgs[$key] = 0;
            } else {
                $a_avgs[$key] = round($pair[0] / $pair[1]);
            }
        }

        $json = $a_avgs;
        $total_value = array(
            'gender' => $gender2,
            'age' => $age2,
            'skin' => $skin,
            'beard' => $beard,
            'hair_length' => $hair_length,
            'glasses' => $glasses
        );

        $json2 = array_merge($total_value, $json);

        $sql3 = "UPDATE iv_result_new SET score = '".json_encode($json2)."' WHERE applier_idx = '".$idx."' AND question_idx = 'total'";
        mysqli_query($conn, $sql3);
        $rst3 = mysqli_affected_rows($conn);
        if ($rst3 > 0) {
            echo $idx.'/success<br>';
        } else {
            echo $idx.'/fail<br>';
        }
    }

    
}


/*

//        $dialect = $json['dialect'];
//        $quiver = $json['quiver'];
//        $volume = $json['volume'];
//        $tone = $json['tone'];
//        $speed = $json['speed'];
//        $diction = $json['diction'];
//        $sincerity = $json['sincerity'];
//        $understanding = $json['understanding'];
//        $eyes = $json['eyes'];
//        $smile = $json['smile'];
//        $mouth_motion = $json['mouth_motion'];
//        $blink = $json['blink'];
//        $gesture = $json['gesture'];
//        $head_motion = $json['head_motion'];
//        $glow = $json['glow'];
//        $foreign = $json['foreign'];
//
//        $json = json_encode(array(
//    //        'gender' => $gender2,
//    //        'age' => $age2,
//    //        'skin' => $skin,
//    //        'beard' => $beard,
//    //        'hair_length' => $hair_length,
//    //        'glasses' => $glasses,
//            'dialect' => $dialect*2,
//            'quiver' => $quiver*2,
//            'volume' => $volume*2,
//            'tone' => $tone*2,
//            'speed' => $speed*2,
//            'diction' => $diction*2,
//            'sincerity' => $sincerity*2,
//            'understanding' => $understanding*2,
//            'eyes' => $eyes*2,
//            'smile' => $smile*2,
//            'mouth_motion' => $mouth_motion*2,
//            'blink' => $blink*2,
//            'gesture' => $gesture*2,
//            'head_motion' => $head_motion*2,
//            'glow' => $glow*2,
//            'foreign' => $foreign*2
//        ));
//
//        $sql3 = "UPDATE iv_result_new SET score = '".$json."' WHERE idx = '".$idx2."'";
//        mysqli_query($conn, $sql3);
//        $rst3 = mysqli_affected_rows($conn);
//        if ($rst3 > 0) {
//            echo 'success';
//        } else {
//            echo 'fail';
//        }

*/