<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 
displayError();
$method = $_POST['method'];
if ($method == 'getReport') { // ============================ 인터뷰 종합분석 ============================
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        $sql_search = "WHERE user_id = '".$result['user_id']."' AND process >= 2";
        $sql_order = "ORDER BY dates DESC";

        if (isset($_POST['sort'])) {
            switch ($_POST['sort']) {
                case 'grade.desc':
                    $sql_order = "ORDER BY (CASE WHEN grade = 'S' then 1 WHEN grade = 'A' then 2 WHEN grade = 'B' then 3 WHEN grade = 'C' then 4 WHEN grade = 'D' then 5 WHEN grade is NULL then 6 ELSE 7 END)";
                    break;
                case 'grade.asc':
                    $sql_order = "ORDER BY (CASE WHEN grade = 'D' then 1 WHEN grade = 'C' then 2 WHEN grade = 'B' then 3 WHEN grade = 'A' then 4 WHEN grade = 'S' then 5 WHEN grade is NULL then 6 ELSE 7 END)";
                    break;
                case 'date.asc':
                    $sql_order = "ORDER BY dates ASC";
                    break;
                default:
                    $sql_order = "ORDER BY dates DESC";
                    break;
            }
        }
        
        $sql = "SELECT count(*) as cnt FROM `iv_applier` ".$sql_search;
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        $total_count = $row['cnt'];

        $rows = 10;
        $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
        $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
        $from_record = ($page - 1) * $rows; // 시작 열을 구함
            
        $rank_list = array();
        $report_list = array();
        $sql = "SELECT A.idx, A.code, A.thumbnail, REPLACE(CAST(JSON_EXTRACT(B.analysis, '$.grade') as CHAR), '\"', '') as grade, REPLACE(A.step, '##', ' / ') as category, CAST(JSON_EXTRACT(B.analysis, '$.sum') as DECIMAL(4,2)) as score, REPLACE(CAST(JSON_EXTRACT(B.score, '$.sincerity') as DECIMAL(1)), '\"', '') as sincerity, A.dates FROM `iv_applier` as A INNER JOIN `iv_result` as B ON A.idx = B.applier_idx WHERE A.user_id = '".$result['user_id']."' AND A.process >= 2 AND B.question_idx = 'total' ".$sql_order." LIMIT ".$from_record.", ".$rows;

        //$sql = "SELECT A.idx, A.code, A.thumbnail, (SELECT REPLACE(CAST(JSON_EXTRACT(B.analysis, '$.grade') as CHAR), '\"', '') FROM iv_result as B WHERE B.applier_idx = A.idx AND question_idx = 'total' LIMIT 1) as grade, REPLACE(A.step, '##', ' / ') as category, (SELECT CAST(JSON_EXTRACT(B.analysis, '$.sum') as DECIMAL(4,2)) FROM iv_result as B WHERE B.applier_idx = A.idx AND question_idx = 'total' LIMIT 1) as score, (SELECT REPLACE(CAST(JSON_EXTRACT(B.score, '$.sincerity') as DECIMAL(1)), '\"', '') FROM iv_result as B WHERE B.applier_idx = A.idx AND question_idx = 'total' LIMIT 1) as sincerity, A.dates FROM `iv_applier` as A WHERE A.user_id = '".$result['user_id']."' AND A.process >= 2 ".$sql_order." LIMIT ".$from_record.", ".$rows;

        $rst = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($rst)) {
            //카테고리별 랭킹 계산
            $code = $row['code'];
            $score = $row['score'];
            if (!isset($rank_list[$code])) { //해당 코드값 array 셋팅전이면
                $rank_list[$code] = array();
                $sql1 = "SELECT CAST(JSON_EXTRACT(analysis, '$.sum') as DECIMAL(4,2)) as score FROM iv_result WHERE analysis IS NOT NULL AND code = '".$code."' AND question_idx = 'total' ORDER BY score DESC";
                $rst1 = mysqli_query($conn, $sql1);
                while ($row1 = mysqli_fetch_assoc($rst1)) {
                   array_push($rank_list[$code], $row1['score']);
                }
            }
            
            if (count($rank_list[$code]) == 0) { //해당 코드가 평가된게 없다면
                $rank = null;
                $rank_percent = null;
            } else {
                $rank = (array_search($score, $rank_list[$code]) == false) ? (count($rank_list[$code])) : (array_search($score, $rank_list[$code])); //카테고리별 랭킹표에서 현재 점수가 몇번째 인덱스인지를 구해서 랭킹을 구함.
                $rank_percent = ((round($rank/count($rank_list[$code])*100, 1)) > 100) ? 100 : (round($rank/count($rank_list[$code])*100, 1));
            }

            array_push($report_list, array('idx' => $row['idx'], 'profile' => 'https://alphagong.highbuff.com/data/uploads_thumbnail/'.$row['thumbnail'], 'grade' => $row['grade'], 'category' => $row['category'], 'score' => $score, 'rank' => $rank_percent, 'sincerity' => $row['sincerity'], 'date' => $row['dates']));
        }

        echo json_encode(array('status' => 200, 'report' => $report_list, 'page' => $page, 'total_page' => $total_page));
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'deleteReport') { // ============================ 인터뷰 종합분석-삭제 ============================
    $idx = isset($_POST['idx']) ? trim($_POST['idx']) : '';
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($idx == '' || $access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }   

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        mysqli_autocommit($conn, FALSE); //commit false
        $error1 = false;
        $item_list = explode('|^|', $idx);
        for($i=0; $i<count($item_list)-1; $i++) {
            //해당 idx의 자료가 있는지 조회
            $sql = "SELECT idx FROM iv_applier WHERE idx = '".$item_list[$i]."'";
            $rst = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($rst);
            if (!isset($row)) {
                $error1 = true;
            }
            
            //삭제할 인터뷰의 process를 -1로 변경
            $sql1 = "UPDATE iv_applier SET process = -1 WHERE idx = '".$item_list[$i]."'";
            mysqli_query($conn, $sql1);
        }

        if ($error1) { //update fail
            echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
            return;
        } else { //success
            mysqli_commit($conn);
            echo json_encode(array('status' => 200));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'getReportDetailTotal') { // ============================ 인터뷰 종합분석-상세페이지(전체) ============================
    $idx = isset($_POST['idx']) ? trim($_POST['idx']) : '';
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($idx == '' || $access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        //해당 idx의 종합분석 값 가져오기(단일행)
        $sql = "SELECT B.thumbnail as profile, A.analysis, A.score, B.dates, B.code, A.process FROM `iv_result` as A INNER JOIN iv_applier as B ON A.applier_idx = B.idx  WHERE A.applier_idx = ".$idx." AND A.question_idx = 'total'";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (!isset($row)) {
            echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
            return;
        } else if ($row['process'] == 0){
            echo json_encode(array('status' => -201, 'message' => '현재 분석중인 인터뷰입니다.'));
            return;
        }
        $profile = 'https://alphagong.highbuff.com/data/uploads_thumbnail/'.$row['profile'];
        $analysis = json_decode($row['analysis'], true);
        $grade = $analysis['grade'];
        $sum = number_format($analysis['sum'], 2);
        $score = json_decode($row['score'], true);

        if (is_array($score['gender'])) { //성별 분석완료시
            $gender =  (array_search(max($score['gender']), $score['gender']) == 0) ? 'man' : 'woman';
            $gender_percent = max($score['gender']);
        } else { //분석불가시
            $gender =  'gender';
            $gender_percent = -1;
        }
        
        if (is_array($score['age'])) { //연령예측 분석 완료시
            $age = (array_search(max($score['age']), $score['age'])+1).'0대';
            $age_percent = max($score['age']);
        } else { //분석불가시
            $age = 'age';
            $age_percent = -1;
        }

        $date = $row['dates'];
        //---------------------------- get grade_chart ----------------------------
        $grade_list = array();
        $grade_list['S'] = "0";
        $grade_list['A'] = "0";
        $grade_list['B'] = "0";
        $grade_list['C'] = "0";
        $grade_list['D'] = "0";
        $sql2 = "SELECT REPLACE(CAST(JSON_EXTRACT(analysis, '$.grade') as CHAR), '\"', '') as grade, count(*) as cnt FROM iv_result WHERE analysis IS NOT NULL AND code = '".$row['code']."' AND question_idx = 'total' GROUP BY grade";
        $rst2 = mysqli_query($conn, $sql2);
        while ($row2 = mysqli_fetch_assoc($rst2)) {
            $grade_list[$row2['grade']] = $row2['cnt'];
        }
        //---------------------------- get grade_chart ----------------------------

        //get rank
        $rank_list = array();
        $sql1 = "SELECT CAST(JSON_EXTRACT(analysis, '$.sum') as DECIMAL(4,2)) as score FROM iv_result WHERE analysis IS NOT NULL AND code = '".$row['code']."' AND question_idx = 'total' ORDER BY score DESC";
        $rst1 = mysqli_query($conn, $sql1);
        while ($row1 = mysqli_fetch_assoc($rst1)) {
           array_push($rank_list, $row1['score']);
        }
        $rank_num = (array_search($sum, $rank_list))+1; 
        $rank_percent = ((round($rank_num/count($rank_list)*100, 1)) > 100) ? 100 : (round($rank_num/count($rank_list)*100, 1));
        $rank = array('percent' => $rank_percent, 'rank' => $rank_num, 'total' => count($rank_list));

        //get point
        unset($analysis['sum'], $analysis['grade']);
        $point = $analysis;

        //----------------- get score sort -------------------
        if ($score["quiver"] == "0") { //음성떨림
            $quiver = 0;
        } else {
            $quiver = 11 - (int)$score["quiver"];
        }
        $volume = (int)$score["volume"];   //음성크기
        $tone = (int)$score["tone"];   //목소리톤
        $speed = (int)$score["speed"];   //음성속도
        $diction = (int)$score["diction"];   //발음정확도
        $sincerity = (int)$score["sincerity"];   //성싱답변률
        $understanding = (int)$score["understanding"];   //질문이해도
        $eyes = (int)$score["eyes"];   //시선처리
        $smile = (int)$score["smile"];   //긍정적표정
        $mouth_motion = (int)$score["mouth_motion"];   //입움직임
        if ($score["blink"] == "0") {  //눈깜빡임
            $blink = 0;
        } else {
            $blink = 11 - (int)$score["blink"];
        }
        $gesture = (int)$score["gesture"];   //제스처빈도
        if ($score["head_motion"] == "0") {  //머리움직임
            $head_motion = 0;
        } else {
            $head_motion = 11 - (int)$score["head_motion"];
        }
        $foreign = (int)$score["foreign"];   //외국어빈도
        $glow = (int)$score["glow"];   //홍조현상
        if ($speed == 1 || $speed == 10) {
            $score_speed = 2;
        } else if ($speed == 2 || $speed == 9) {
            $score_speed = 4;
        } else if ($speed == 3 || $speed == 8) {
            $score_speed = 6;
        } else if ($speed == 4 || $speed == 7) {
            $score_speed = 8;
        } else if ($speed == 5 || $speed == 6) {
            $score_speed = 10;
        } else {
            $score_speed = 0;
        }

        if ($glow == 1 || $glow == 10) {
            $score_glow = 2;
        } else if ($glow == 2 || $glow == 9) {
            $score_glow = 4;
        } else if ($glow == 3 || $glow == 8) {
            $score_glow = 6;
        } else if ($glow == 4 || $glow == 7) {
            $score_glow = 8;
        } else if ($glow == 5 || $glow == 6) {
            $score_glow = 10;
        } else {
            $score_glow = 0;
        }
        $score_list = array("eyes" => $eyes, "glow" => $score_glow, "tone" => $tone, "blink" => $blink, "smile" => $smile, "speed" => $score_speed, "quiver" => $quiver, "volume" => $volume, "diction" => $diction, "gesture" => $gesture, "sincerity" => $sincerity, "head_motion" => $head_motion, "mouth_motion" => $mouth_motion, "understanding" => $understanding);

        arsort($score_list); //내림차순
        $sort_score = array_keys($score_list);
        $best_score = array_splice($sort_score, 0, 3);
        $worst_score = array_splice($sort_score, -3);

        $best_list = array(
            'dialect' => array('방언빈도', '방언 사용률이 낮습니다.'), 
            'quiver' => array('음성떨림', '최소한의 떨린 목소리로 인터뷰를 안정되게 진행하였습니다.'),
            'volume' => array('음성크기', '적절한 성량으로 인터뷰를 안정적으로 진행하였습니다.'), 
            'tone' => array('목소리톤', '일정한 목소리 톤으로 안정적이게 답변하였습니다.'),
            'speed' => array('음성속도', '적절한 속도로 답변하여 인터뷰를 안정적으로 진행하였습니다.'), 
            'diction' => array('발음 정확도', '답변을 알아듣기 쉽게 정확한 발음으로 인터뷰를 진행하였습니다.'),
            'sincerity' => array('성실 답변률', '답변시간을 충분히 활용하여 인터뷰를 진행하였습니다.'), 
            'understanding' => array('질문이해도', '어려운 질문에도 머뭇거림 없이 즉각적인 답변을 하였습니다.'),
            'eyes' => array('시선처리', '인터뷰를 진행하는 동안 최소한의 시선 흔들림을 보였습니다.'), 
            'smile' => array('긍정적표정', '밝은 표정으로 인터뷰를 진행하였습니다.'),
            'mouth_motion' => array('입움직임', '입을 크게 움직이며 적극적으로 인터뷰를 진행하였습니다.'), 
            'blink' => array('눈 깜빡임', '최소한의 눈깜빡임으로 안정적이게 인터뷰를 진행하였습니다.'),
            'gesture' => array('제스쳐빈도', '몸을 활발히 사용하여 질문에 적극적으로 답변하였습니다.'), 
            'head_motion' => array('머리 움직임', '최소한으로 머리를 움직이며 안정되게 인터뷰를 진행하였습니다.'),
            'foreign' => array('외국어 사용빈도', '외국어를 많이 사용하지 않습니다.'), 
            'glow' => array('홍조현상', '얼굴색의 변화 없이 안정적으로 인터뷰를 진행하였습니다.'),
        );


        $worst_list = array(
            'dialect' => array('방언 사용률이 높습니다.'), 
            'quiver' => array('음성떨림', '떨리는 목소리로 인터뷰를 진행하여 긴장과 불안정함을 보였습니다.'),
            'volume' => array('음성크기', '너무 크거나 작은 성량으로 인터뷰를 불안정하게 진행하였습니다.'), 
            'tone' => array('목소리톤', '일정하지 않은 목소리 톤으로 불안정하게 답변하였습니다. '),
            'speed' => array('음성속도', '너무 빠르거나 느리게 답변하여 인터뷰를 불안정하게 진행하였습니다. '), 
            'diction' => array('발음 정확도', '답변을 알아듣기 힘들 정도로 발음이 부정확합니다.'),
            'sincerity' => array('성실 답변률', '답변시간을 충분히 활용하지 못한 채 인터뷰를 진행하였습니다. '), 
            'understanding' => array('질문이해도', '답변을 즉시 하지 못하거나 시작하기까지 시간이 소요되었습니다.'),
            'eyes' => array('시선처리', '인터뷰를 진행하는 동안 시선이 불안정하게 흔들렸습니다.'), 
            'smile' => array('긍정적표정', '다소 어두운 표정으로 인터뷰를 진행하였습니다.'),
            'mouth_motion' => array('입움직임', '입을 거의 움직이지 않으며 다소 소극적으로 인터뷰를 진행하였습니다.'), 
            'blink' => array('눈 깜빡임', '잦은 눈 깜빡임으로 불안정하게 인터뷰를 진행하였습니다. '),
            'gesture' => array('제스쳐빈도', '몸을 거의 움직이지 않고 인터뷰를 진행하였습니다.'), 
            'head_motion' => array('머리 움직임', '머리를 자주 움직여 집중력이 분산되고 다소 불안정해 보입니다.'),
            'foreign' => array('외국어 사용빈도', '외국어를 너무 많이 사용하셨습니다'), 
            'glow' => array('홍조현상', '붉은 얼굴색을 띄며 긴장감과 불안정함을 보였습니다.'),
        );
        $pick_best = array();
        foreach ($best_score as $best) {
            array_push($pick_best, array('point' => $best_list[$best][0], 'text' => $best_list[$best][1]));
        }
        $pick_worst = array();
        foreach ($worst_score as $worst) {
            array_push($pick_worst, array('point' => $worst_list[$worst][0], 'text' => $worst_list[$worst][1]));
        }
        //----------------- get score sort -------------------
        echo json_encode(array(
            'status' => 200,
            'profile' => $profile, 
            'grade' => $grade,
            'sum' => $sum,
            'gender' => $gender,
            'gender_percent' => $gender_percent,
            'age' => $age,
            'age_percent' => $age_percent,
            'date' => $date,
            'grade_chart' => array_values($grade_list),
            'rank' => $rank,
            'point' => $point,
            'pick_best' => $pick_best,
            'pick_worst' => $pick_worst,
        ));
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else if ($method == 'getReportDetail') { // ============================ 인터뷰 종합분석-상세페이지(개별) ============================
    $idx = isset($_POST['idx']) ? trim($_POST['idx']) : '';
    $access_token = isset($_POST['access_token']) ? trim($_POST['access_token']) : '';
    if ($idx == '' || $access_token == '') {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }

    $result = getUserId($access_token);
    if (isset($result['user_id'])) { //success
        //질문별 영상 가져오기
        $sql = "SELECT code, thumbnail, record_01, record_02, record_03, record_04, record_05, record_06, record_07, record_08, record_09, record_10  FROM `iv_applier` WHERE idx = '".$idx."'";
        $rst = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($rst);
        $profile = 'https://alphagong.highbuff.com/data/uploads_thumbnail/'.$row['thumbnail'];

        $part_list = array();
        $sql1 = "SELECT B.idx, A.analysis, A.speech_text, A.speech_text_detail, B.question, REPLACE(CAST(JSON_EXTRACT(analysis, '$.grade') as CHAR), '\"', '') as grade FROM `iv_result` as A INNER JOIN iv_question as B ON A.question_idx = B.idx WHERE A.applier_idx = '".$idx."' AND A.question_idx != 'total' AND A.process = 2 ORDER BY A.idx";
        $rst1 = mysqli_query($conn, $sql1);
        for ($i=0; $row1 = mysqli_fetch_assoc($rst1); $i++) {
            $grade = $row1['grade'];
            $question = $row1['question'];
            $analysis = json_decode($row1['analysis'], true);
            $sum = number_format($analysis['sum'], 2);

            //---------------------------- get grade_chart ----------------------------
            $grade_list = array();
            $grade_list['S'] = "0";
            $grade_list['A'] = "0";
            $grade_list['B'] = "0";
            $grade_list['C'] = "0";
            $grade_list['D'] = "0";
            $sql2 = "SELECT REPLACE(CAST(JSON_EXTRACT(analysis, '$.grade') as CHAR), '\"', '') as grade, count(*) as cnt FROM iv_result WHERE analysis IS NOT NULL AND code = '".$row['code']."' AND question_idx = '".$row1['idx']."' GROUP BY grade";
            $rst2 = mysqli_query($conn, $sql2);
            while ($row2 = mysqli_fetch_assoc($rst2)) {
                $grade_list[$row2['grade']] = $row2['cnt'];
            }
            //---------------------------- get grade_chart ----------------------------

            //get rank
            $rank_list = array();
            $sql3 = "SELECT CAST(JSON_EXTRACT(analysis, '$.sum') as DECIMAL(4,2)) as score FROM iv_result WHERE analysis IS NOT NULL AND code = '".$row['code']."' AND question_idx = '".$row1['idx']."' ORDER BY score DESC";

            $rst3 = mysqli_query($conn, $sql3);
            while ($row3 = mysqli_fetch_assoc($rst3)) {
               array_push($rank_list, $row3['score']);
            }

            $rank_num = (array_search($sum, $rank_list))+1; 
            $rank_percent = ((round($rank_num/count($rank_list)*100, 1)) > 100) ? 100 : (round($rank_num/count($rank_list)*100, 1));
            $rank = array('percent' => $rank_percent, 'rank' => $rank_num, 'total' => count($rank_list));

            //get point
            unset($analysis['sum'], $analysis['grade']);
            $point = $analysis;
            
            //get video matching
            $index = str_pad($i+1, 2, 0, STR_PAD_LEFT);
            $video = 'https://alphagong.highbuff.com/data/uploads/'.$row['record_'.$index];

            
            array_push($part_list, array('question' => $question, 'video' => $video, 'point' => $point, 'grade' => $grade, 'grade_chart' => array_values($grade_list), 'rank' => $rank, 'sum' => $sum, 'speech_text' => $row1['speech_text'], 'speech_text_detail' => json_decode($row1['speech_text_detail'], true)));
        }

        if (count($part_list) > 0) {
            echo json_encode(array('status' => 200, 'profile' => $profile, 'part' => $part_list));
        } else {
            echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        }
    } else if ($result == -10) {
        echo json_encode(array('status' => -10, 'message' => '올바르지 않은 토큰입니다.'));
    } else if ($result == -11) {
        echo json_encode(array('status' => -11, 'message' => '토큰 유효기간이 만료되었습니다.'));
    } else {
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
    }
} else {
    echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
}