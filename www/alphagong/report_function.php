<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/db_config.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/function.php");

function getRealScore($speed, $quiver, $glow, $head_motion, $blink)
{
    if ($speed == 1) {  //음성속도
        $speed = 5;
    } else if ($speed == 2 || $speed == 10) {
        $speed = 6;
    } else if ($speed == 3 || $speed == 9) {
        $speed = 7;
    } else if ($speed == 4 || $speed == 8) {
        $speed = 8;
    } else if ($speed == 5 || $speed == 6) {
        $speed = 10;
    } else if ($speed == 7) {
        $speed = 9;
    } else if ($speed == 0) {
        $speed = 0;
    }

    if ($quiver == 0) { //목소리떨림
        $quiver = 0;
    } else {
        $quiver = 11 - $quiver;
    }

    if ($glow == 0) {   //홍조현상
        $glow = 0;
    } else {
        $glow = 11 - $glow;
    }

    if ($head_motion == 0) {  //머리움직임
        $head_motion = 0;
    } else {
        $head_motion = 11 - $head_motion;
    }

    if ($blink == 0) { //눈깜빡임
        $blink = 0;
    } else {
        $blink = 11 - $blink;
    }

    $getScore = array();
    array_push($getScore, array('speed' => $speed, 'quiver' => $quiver, 'glow' => $glow, 'head_motion' => $head_motion, 'blink' => $blink));

    return $getScore;
}

//MBTI 정보
function getMbti($user_id, $conn)
{
    $sql_mbti = "SELECT iv_member.mbti, iv_mbti_msg.msg, iv_mbti_recommend_job.type1, iv_mbti_recommend_job.type2, iv_mbti_recommend_job.type3, iv_mbti_recommend_job.keyword 
            FROM iv_member
            LEFT JOIN iv_mbti_msg ON iv_mbti_msg.mbti = iv_member.mbti
            LEFT JOIN iv_mbti_recommend_job ON iv_mbti_recommend_job.mbti = iv_member.mbti
            WHERE iv_member.user_id = '" . $user_id . "'";
    $rst_mbti = mysqli_query($conn, $sql_mbti);
    $row_mbti = mysqli_fetch_assoc($rst_mbti);
    $mbti = $row_mbti['mbti'];

    // $mbtiJobArr = array();
    // //mbti 직업 점수높은 순서로 4개 출력
    // $sql_mbti_job = "SELECT replace(iv_type.step, '##', ' ') as job
    //             FROM iv_mbti_score
    //             LEFT JOIN iv_type ON iv_type.idx = iv_mbti_score.type_idx
    //             WHERE iv_mbti_score.mbti = '" . $mbti . "'
    //             AND iv_type.process = 1
    //             ORDER BY iv_mbti_score.`value` DESC
    //             LIMIT 4";
    // $rst_mbti_job = mysqli_query($conn, $sql_mbti_job);
    // while ($row_mbti_job = mysqli_fetch_assoc($rst_mbti_job)) {
    //     array_push($mbtiJobArr, $row_mbti_job);
    // }

    $sql_mbti_per = "SELECT iv_mbti_score.`value`
                FROM iv_applier 
                LEFT JOIN iv_type ON iv_applier.`code` = iv_type.`code`
                LEFT JOIN iv_mbti_score ON iv_mbti_score.type_idx = iv_type.idx
                LEFT JOIN iv_member ON iv_member.user_id = iv_applier.user_id
                WHERE iv_applier.idx = 6062
                AND iv_member.mbti IS NOT NULL
                AND iv_mbti_score.mbti = '" . $mbti . "'";
    $rst_mbti_per = mysqli_query($conn, $sql_mbti_per);
    $row_mbti_per = mysqli_fetch_assoc($rst_mbti_per);


    $mbtiArr = array();
    array_push($mbtiArr, array('info' => $row_mbti, 'associate' => $row_mbti_per['value']));

    return $mbtiArr[0];
}

//면접종합점수(전체 평균)
function getPointAvg($conn)
{
    $totalActivity = $totalAlacrity = $totalStability = $totalWillpower = $totalAttraction = $totalAffirmative = $totalReliability = $totalAggressiveness = 0;
    $totalCnt = 0;

    $sql_point_avg = "SELECT iv_result.analysis
                      FROM iv_result
                      LEFT JOIN iv_applier ON iv_applier.idx = iv_result.applier_idx
                      WHERE iv_applier.process = 4 AND iv_result.question_idx = 'total' AND iv_result.process = 2
                     ";
    $rst_point_avg = mysqli_query($conn, $sql_point_avg);
    while ($row_point_avg = mysqli_fetch_assoc($rst_point_avg)) {
        $avgAnalysis = json_decode($row_point_avg['analysis'], true);

        //능동성
        $activity = $avgAnalysis['activity'] ?? 0;
        $totalActivity += (int)$activity;
        //대응성
        $alacrity = $avgAnalysis['alacrity'] ?? 0;
        $totalAlacrity += (int)$alacrity;
        //안정성
        $stability = $avgAnalysis['stability'] ?? 0;
        $totalStability += (int)$stability;
        //의지력
        $willpower = $avgAnalysis['willpower'] ?? 0;
        $totalWillpower += (int)$willpower;
        //매력도
        $attraction = $avgAnalysis['attraction'] ?? 0;
        $totalAttraction += (int)$attraction;
        //긍정성
        $affirmative = $avgAnalysis['affirmative'] ?? 0;
        $totalAffirmative += (int)$affirmative;
        //신뢰성
        $reliability = $avgAnalysis['reliability'] ?? 0;
        $totalReliability += (int)$reliability;
        //적극성
        $aggressiveness = $avgAnalysis['aggressiveness'] ?? 0;
        $totalAggressiveness += (int)$aggressiveness;

        $totalCnt++;
    }

    $avgCapacity = array();
    array_push($avgCapacity, array(
        'activity' => round($totalActivity / $totalCnt, 1),
        'alacrity' => round($totalAlacrity / $totalCnt, 1),
        'stability' => round($totalStability / $totalCnt, 1),
        'willpower' => round($totalWillpower / $totalCnt, 1),
        'attraction' => round($totalAttraction / $totalCnt, 1),
        'affirmative' => round($totalAffirmative / $totalCnt, 1),
        'reliability' => round($totalReliability / $totalCnt, 1),
        'aggressiveness' => round($totalAggressiveness / $totalCnt, 1)
    ));

    return $avgCapacity[0];
}

//목소리분석
function getVoice($conn, $appIdx)
{
    $sql_voice = "SELECT audio_detail FROM iv_result WHERE applier_idx = '" . $appIdx . "' AND question_idx != 'total' ORDER BY idx ASC"; //applier_idx 값 
    $rst_voice = mysqli_query($conn, $sql_voice);
    while ($row_voice = mysqli_fetch_assoc($rst_voice)) {
        $voiceAnalysis = json_decode($row_voice['audio_detail'], true);
        $voiceAnalysisArr[] = $voiceAnalysis;
    }

    return $voiceAnalysisArr;
}

// 표정분석-본인점수
function getfacialAnalysis($conn, $appIdx)
{
    $sql_facial_analysis = "SELECT score FROM iv_result WHERE applier_idx = '" . $appIdx . "' AND question_idx = 'total' ORDER BY idx ASC"; //applier_idx 값 
    $rst_facial_analysis = mysqli_query($conn, $sql_facial_analysis);
    $row_facial_analysis = mysqli_fetch_assoc($rst_facial_analysis);

    $facialAnalysis = json_decode($row_facial_analysis['score'], true);

    $quiver = (int)$facialAnalysis["quiver"];   //음성떨림
    $volume = (int)$facialAnalysis["volume"];   //음성크기
    $tone = (int)$facialAnalysis["tone"];   //목소리톤
    $speed = (int)$facialAnalysis["speed"];   //음성속도
    $diction = (int)$facialAnalysis["diction"];   //발음정확도
    $eyes = (int)$facialAnalysis["eyes"];   //시선처리
    $blink = (int)$facialAnalysis["blink"];   //눈깜빡임
    $gesture = (int)$facialAnalysis["gesture"];   //제스처빈도
    $head_motion = (int)$facialAnalysis["head_motion"];   //머리움직임
    $glow = (int)$facialAnalysis["glow"];   //홍조현상

    $realScore = getRealScore($speed, $quiver, $glow, $head_motion, $blink);

    $confidence = ((int)$realScore[0]['quiver'] + (int)$volume + (int)$tone + (int)$realScore[0]['speed']) / 4; //자신감
    $Attitude = ((int)$realScore[0]['head_motion'] + (int)$gesture) / 2; //태도

    $facial_analysis = array();
    array_push($facial_analysis, array('complexion' => $glow, 'blinking' => $blink, 'pronunciation' => $diction, 'eye_contact' => $eyes, 'confidence' => $confidence, 'Attitude' => $Attitude));

    return $facial_analysis[0];
}

// 표정분석-평균점수
function getfacialAnalysisAvg($conn)
{
    $complexion = $blinking = $pronunciation = $eye_contact = $confidence = $Attitude = 0;
    $facial_total = 0;

    $sql_facial_analysis_avg = "SELECT iv_result.score 
                            FROM iv_result 
                            LEFT JOIN iv_applier ON iv_applier.idx = iv_result.applier_idx
                            WHERE iv_result.question_idx = 'total'
                            AND iv_applier.process = 4
                            AND iv_result.score IS NOT NULL";
    $rst_facial_analysis_avg = mysqli_query($conn, $sql_facial_analysis_avg);
    while ($row_facial_analysis_avg = mysqli_fetch_assoc($rst_facial_analysis_avg)) {
        $facialAnalysisAvg = json_decode($row_facial_analysis_avg['score'], true);

        $quiver = (int)$facialAnalysisAvg["quiver"] ?? 0;   //음성떨림
        $volume = (int)$facialAnalysisAvg["volume"] ?? 0;   //음성크기
        $tone = (int)$facialAnalysisAvg["tone"] ?? 0;   //목소리톤
        $speed = (int)$facialAnalysisAvg["speed"] ?? 0;   //음성속도
        $diction = (int)$facialAnalysisAvg["diction"] ?? 0;   //발음정확도
        $eyes = (int)$facialAnalysisAvg["eyes"] ?? 0;   //시선처리
        $blink = (int)$facialAnalysisAvg["blink"] ?? 0;   //눈깜빡임
        $gesture = (int)$facialAnalysisAvg["gesture"] ?? 0;   //제스처빈도
        $head_motion = (int)$facialAnalysisAvg["head_motion"] ?? 0;   //머리움직임
        $glow = (int)$facialAnalysisAvg["glow"] ?? 0;   //홍조현상

        $realScore = getRealScore($speed, $quiver, $glow, $head_motion, $blink);
        $confidence_cate = ((int)$realScore[0]['quiver'] + (int)$volume + (int)$tone + (int)$realScore[0]['speed']) / 4; //자신감
        $Attitude_cate = ((int)$realScore[0]['head_motion'] + (int)$gesture) / 2; //태도

        $complexion += (int)$glow;   //안색
        $blinking += (int)$blink;    //눈깜빡임
        $pronunciation += (int)$diction; //발음정확도
        $eye_contact += (int)$eyes; //시선마주침
        $confidence += (int)$confidence_cate;   //자신감
        $Attitude += (int)$Attitude_cate;   //태도
        $facial_total++;
    }

    $facial_result = array();
    array_push($facial_result, array(
        'complexion' => round($complexion / $facial_total, 1),
        'blinking' => round($blinking / $facial_total, 1),
        'pronunciation' => round($pronunciation / $facial_total, 1),
        'eye_contact' => round($eye_contact / $facial_total, 1),
        'confidence' => round($confidence / $facial_total, 1),
        'Attitude' => round($Attitude / $facial_total, 1),
    ));

    return $facial_result[0];
}

//응답신뢰성
function getSincerity($conn, $appIdx)
{
    $sql_sincerity = "SELECT CAST(JSON_EXTRACT(score, '$.sincerity')as unsigned) as sincerity
                    FROM iv_result 
                    WHERE question_idx = 'total'
                    AND analysis IS NOT NULL
                    AND applier_idx = '" . $appIdx . "'
                    ";
    $rst_sincerity = mysqli_query($conn, $sql_sincerity);
    $row_sincerity = mysqli_fetch_assoc($rst_sincerity);

    if ($row_sincerity['sincerity'] >= 1 && $row_sincerity['sincerity'] <= 3) {
        $response = '낮음';
    } else if ($row_sincerity['sincerity'] >= 4 && $row_sincerity['sincerity'] <= 6) {
        $response = '보통';
    } else if ($row_sincerity['sincerity'] >= 7) {
        $response = '높음';
    } else {
        $response = '분석불가';
    }

    return $response;
}

//면접영상 STT 분석 (단어분포표)
function getWordStt($conn, $appIdx)
{
    $positiveWord = 0;  //긍정단어
    $negativeWord = 0;  //부정단어
    $complexWord = 0;   //복합단어
    $neutralWord = 0;   //중립단어
    $wordDistribution = array();
    $wordStt = array();
    $wordList = array();

    $sql_stt_word = "SELECT iv_result.question_idx, iv_result.speech_text_detail
                FROM `iv_applier` 
                LEFT JOIN `iv_result` ON `iv_applier`.`idx` = `iv_result`.`applier_idx` 
                WHERE `iv_applier`.`idx` = '" . $appIdx . "' 
                AND `iv_result`.`question_idx` != 'total'";
    $rst_stt_word = mysqli_query($conn, $sql_stt_word);
    while ($row_stt_word = mysqli_fetch_assoc($rst_stt_word)) {
        $sttDetail = json_decode($row_stt_word['speech_text_detail'], true);

        for ($i = 0; $i < count($sttDetail); $i++) {
            $words = $sttDetail[$i]['alternatives'][0]['words'];

            if (count($words) > 1) {
                $complexWord++;
            }

            for ($j = 0; $j < count($words); $j++) {
                $word = $words[$j]['word'];
                $pos = $words[$j]['pos'];
                $score = (int)$words[$j]['score'];

                if ($score > 0) {
                    $positiveWord++;
                } else if ($score == 0) {
                    $neutralWord++;
                } else if ($score < 0) {
                    $negativeWord++;
                }

                //워드클라우드 단어
                if ($pos == 'Noun' || $pos == 'Verb' || $pos == 'Adjective') {
                    if (in_array($wordList, $wordList[$word])) {
                        $wordList[$word] = ++$wordList[$word];
                    } else {
                        $wordList[$word] = 1;
                    }
                }
            }
        }

        $wordDistributionTotCnt = $positiveWord + $negativeWord + $complexWord + $neutralWord;
        $positiveWordPer = round(($positiveWord / $wordDistributionTotCnt) * 100, 1);
        $negativeWordPer = round(($negativeWord / $wordDistributionTotCnt) * 100, 1);
        $complexWordPer = round(($complexWord / $wordDistributionTotCnt) * 100, 1);
        $neutralWordPer = round(($neutralWord / $wordDistributionTotCnt) * 100, 1);

        array_push($wordDistribution, array('positiveWordPer' => $positiveWordPer, 'negativeWordPer' => $negativeWordPer, 'complexWordPer' => $complexWordPer, 'neutralWordPer' => $neutralWordPer));
    }

    // $wordListArr = array();
    // foreach ($wordList as $key => $val) {
    //     array_push($wordListArr, array("word" => $key, "count" => $val));
    // }

    array_push($wordStt, array('wordDistribution' => $wordDistribution[0], 'wordList' => $wordList));

    return $wordStt[0];
}

//지원분야
function getApplyType($conn, $appIdx)
{
    $sql_type = "SELECT `code`, step FROM iv_applier WHERE idx = '" . $appIdx . "'";
    $rst_type = mysqli_query($conn, $sql_type);
    $row_type = mysqli_fetch_assoc($rst_type);

    $code = $row_type['code'];
    $step = explode('##', $row_type['step']);

    $steps = array();
    for ($i = 0; $i < count($step) - 1; $i++) {
        array_push($steps, $step[$i]);
    }

    return $steps;
}


function getWordStt2($conn, $appIdx)
{
    $positiveWord = 0;  //긍정단어
    $negativeWord = 0;  //부정단어
    $complexWord = 0;   //복합단어
    $neutralWord = 0;   //중립단어
    $wordDistribution = array();
    $wordStt = array();
    $wordList = array();

    $sql_stt_word = "SELECT iv_result.question_idx, iv_result.speech_text_detail
                FROM `iv_applier` 
                LEFT JOIN `iv_result` ON `iv_applier`.`idx` = `iv_result`.`applier_idx` 
                WHERE `iv_applier`.`idx` = '" . $appIdx . "' 
                AND `iv_result`.`question_idx` != 'total'";
    $rst_stt_word = mysqli_query($conn, $sql_stt_word);
    while ($row_stt_word = mysqli_fetch_assoc($rst_stt_word)) {
        $sttDetail = json_decode($row_stt_word['speech_text_detail'], true);

        for ($i = 0; $i < count($sttDetail); $i++) {
            $words = $sttDetail[$i]['alternatives'][0]['words'];

            if (count($words) > 1) {
                $complexWord++;
            }

            for ($j = 0; $j < count($words); $j++) {
                $word = $words[$j]['word'];
                $pos = $words[$j]['pos'];
                $score = (int)$words[$j]['score'];

                if ($score > 0) {
                    $positiveWord++;
                } else if ($score == 0) {
                    $neutralWord++;
                } else if ($score < 0) {
                    $negativeWord++;
                }


                // array_push($wordList, array("word" => '', "count" => ''));

                // print_r($wordList);
                // echo '<br>';
                // print_r($word);
                // echo '<br><br>';

                //워드클라우드 단어
                if ($pos == 'Noun' || $pos == 'Verb' || $pos == 'Adjective') {
                    if (in_array($wordList, $wordList[$word])) {
                        $wordList[$word] = ++$wordList[$word];
                    } else {
                        $wordList[$word] = 1;
                    }
                }
            }
        }

        $wordDistributionTotCnt = $positiveWord + $negativeWord + $complexWord + $neutralWord;
        $positiveWordPer = round(($positiveWord / $wordDistributionTotCnt) * 100, 1);
        $negativeWordPer = round(($negativeWord / $wordDistributionTotCnt) * 100, 1);
        $complexWordPer = round(($complexWord / $wordDistributionTotCnt) * 100, 1);
        $neutralWordPer = round(($neutralWord / $wordDistributionTotCnt) * 100, 1);

        array_push($wordDistribution, array('positiveWordPer' => $positiveWordPer, 'negativeWordPer' => $negativeWordPer, 'complexWordPer' => $complexWordPer, 'neutralWordPer' => $neutralWordPer));
    }

    $wordListArr = array();

    foreach ($wordList as $key => $val) {
        array_push($wordListArr, array("word" => $key, "count" => $val));
    }

    print_r($wordListArr);

    array_push($wordStt, array('wordDistribution' => $wordDistribution[0], 'wordList' => $wordList));

    // return $wordStt[0];
}
