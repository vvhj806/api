<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

displayError();

exit; 

/*
22.02.16 igg 작업
알파공 iv_applier 모든 데이터의 idx 기준으로 iv_result에 데이터가 기존에는 total에 값이 다 있고, 나머지 질문에는 3개씩밖에 없었음
그래서 나머지 질문들의 3개 값고 total 값을 조합하여, 질문별로 값을 만드는 작업임
*/

$sql = "SELECT * FROM iv_applier ORDER BY idx DESC";
$rst = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_array($rst)) {
    $idx = $row['idx'];

    $sql1 = "SELECT * FROM iv_result WHERE applier_idx = '".$idx."' AND process = 2 AND question_idx = 'total'";
    $rst1 = mysqli_query($conn, $sql1);
    while ($row1 = mysqli_fetch_array($rst1)) {
        $score = explode('##', $row1['score']); 
        $gender = $score[0]; //gender (0은 분석불가)
        $age = $score[1]; //age (0은 분석불가)
        $data1 = array_map('intval', explode(",", $score[2])); //표정분석(0스마일비중, 1반복습관, 2홍조현상, 3눈깜빡임, 4입술떨림, 5시선)
        $data2 = array_map('intval', explode(",", $score[3])); //얼굴분석(0피부톤, 1수염비중, 2헤어스타일, 3안경착용여부) 음성분석(4발음정확도, 5방언, 6목소리톤, 7소리떨림, 8외국어빈도)

        // ===================== 성별 =====================
//        if ($gender == 0) { //분석불가
//            $gender2 = 0;
//        } else { //값이 있을경우
//            $gender2 = array_map('intval', explode(",", $gender));
//        }
//        
//        // ===================== 나이 =====================
//        if ($age == 0) { //분석불가
//            $age2 = 0;
//        } else { //값이 있을경우
//            $age2 = array_map('intval', explode(",", $age));
//        }
        //$skin = $data2[0]; //피부톤
        //$beard = $data2[1]; //수염비중
        //$hair_length = $data2[2]; //머리길이
        //$glasses = ($data2[3] == 0) ? false : true; //안경착용여부

        $dialect = $data2[5]; //방언빈도
        $quiver = $data2[7]; //음성떨림
        $volume = $data2[6]; //*음성크기
        $tone = $data2[6]; //목소리톤
        $speed = $data2[4]; //*음성속도
        $diction = $data2[4]; //발음정확도
        $eyes = $data1[5]; //시선처리
        $smile = $data1[0]; //긍정적표정
        $mouth_motion = $data1[4]; //입술떨림
        $blink = $data1[3]; //눈깜빡임
        $gesture = $data1[1]; //제스쳐빈도(반복습관)
        $head_motion = $data1[5]; //*머리 움직임
        $glow = $data1[2]; //홍조현상
        $foreign = $data2[8]; //외국어빈도

        $sincerity = 0; //성실답변률
        $understanding = 0; //질문이해도
    }

    $sql2 = "SELECT * FROM iv_result WHERE applier_idx = '".$idx."' AND process = 2 AND question_idx != 'total'";
    $rst2 = mysqli_query($conn, $sql2);
    while ($row2 = mysqli_fetch_array($rst2)) {
        $idx2 = $row2['idx'];
        $score = array_map('intval', explode(",", $row2['score'])); //0성실답변률, 1긍정적응답률, 2질문이해도
        $sincerity = $score[0];
        $understanding = $score[2];

        echo $idx."/".$idx2."<Br>";

        $json = json_encode(array(
    //        'gender' => $gender2,
    //        'age' => $age2,
    //        'skin' => $skin,
    //        'beard' => $beard,
    //        'hair_length' => $hair_length,
    //        'glasses' => $glasses,
            'dialect' => $dialect*2,
            'quiver' => $quiver*2,
            'volume' => $volume*2,
            'tone' => $tone*2,
            'speed' => $speed*2,
            'diction' => $diction*2,
            'sincerity' => $sincerity*2,
            'understanding' => $understanding*2,
            'eyes' => $eyes*2,
            'smile' => $smile*2,
            'mouth_motion' => $mouth_motion*2,
            'blink' => $blink*2,
            'gesture' => $gesture*2,
            'head_motion' => $head_motion*2,
            'glow' => $glow*2,
            'foreign' => $foreign*2
        ));

        $sql3 = "UPDATE iv_result_new SET score = '".$json."' WHERE idx = '".$idx2."'";
        mysqli_query($conn, $sql3);
        $rst3 = mysqli_affected_rows($conn);
        if ($rst3 > 0) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }
}