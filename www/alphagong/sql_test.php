<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/db_config.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/function.php");
include_once("report_function.php");

// $getMbti = getMbti('vvhj_806@naver.com', $conn);
// print_r($getMbti);
// echo '<br><br>';

// $getPointAvg = getPointAvg($conn);
// print_r($getPointAvg);
// echo '<br><br>';

// $getVoice = getVoice($conn, 6040);
// print_r($getVoice);
// echo '<br><br>';

// $getfacialAnalysis = getfacialAnalysis($conn, 6040);
// print_r($getfacialAnalysis);
// echo '<br><br>';

// $getfacialAnalysisAvg = getfacialAnalysisAvg($conn);
// print_r($getfacialAnalysisAvg);
// echo '<br><br>';

// $getSincerity = getSincerity($conn, 6040);
// print_r($getSincerity);
// echo '<br><br>';

// $getWordStt = getWordStt($conn, 2900);
// print_r($getWordStt['wordList']);
// echo '<br><br>';

// $getApplyType = getApplyType($conn, 6062);
// print_r($getApplyType);
// echo '<br><br>';


//----------------- 상세페이지(전체) -------------------
//----------------- 응답신뢰성 -------------------

// $sql_sincerity = "SELECT CAST(JSON_EXTRACT(score, '$.sincerity')as unsigned)
//                     FROM iv_result 
//                     WHERE question_idx = 'total'
//                     AND analysis IS NOT NULL
//                     AND applier_idx = 6040
//                     ";
// $rst_sincerity = mysqli_query($conn, $sql_sincerity);
// $row_sincerity = mysqli_fetch_assoc($rst_sincerity);

//----------------- get type (지원분야) -------------------

// $sql_type = "SELECT `code`, step FROM iv_applier WHERE idx = 6040";
// $rst_type = mysqli_query($conn, $sql_type);
// $row_type = mysqli_fetch_assoc($rst_type);

// $code = $row_type['code'];
// $step = explode('##', $row_type['step']);

// $steps = array();
// for ($i = 0; $i < count($step) - 1; $i++) {
//     array_push($steps, $step[$i]);
// }

// print_r($steps);

//----------------- get mbti percent (mbti 직무연관도) -------------------
// $sql_mbti = "SELECT mbti FROM iv_member WHERE idx = 1";
// $rst_mbti = mysqli_query($conn, $sql_mbti);
// $row_mbti = mysqli_fetch_assoc($rst_mbti);

// $sql_mbti_per = "SELECT iv_mbti_score.`value`
//                 FROM iv_applier 
//                 LEFT JOIN iv_type ON iv_applier.`code` = iv_type.`code`
//                 LEFT JOIN iv_mbti_score ON iv_mbti_score.type_idx = iv_type.idx
//                 LEFT JOIN iv_member ON iv_member.user_id = iv_applier.user_id
//                 WHERE iv_applier.idx = 6062
//                 AND iv_member.mbti IS NOT NULL
//                 AND iv_mbti_score.mbti = '".$row_mbti['mbti']."'";
// $rst_mbti_per = mysqli_query($conn, $sql_mbti_per);
// $row_mbti_per = mysqli_fetch_assoc($rst_mbti_per);

// print_r($row_mbti_per);

//----------------- get point avg (전체 평균) -------------------

// $totalActivity = $totalAlacrity = $totalStability = $totalWillpower = $totalAttraction = $totalAffirmative = $totalReliability = $totalAggressiveness = 0;
// $totalCnt = 0;

// $sql_point_avg = "SELECT iv_result.analysis
//                           FROM iv_result
//                           LEFT JOIN iv_applier ON iv_applier.idx = iv_result.applier_idx
//                           WHERE iv_applier.process = 4 AND iv_result.question_idx = 'total' AND iv_result.process = 2
//                          ";
// $rst_point_avg = mysqli_query($conn, $sql_point_avg);
// while ($row_point_avg = mysqli_fetch_assoc($rst_point_avg)) {
//     $avgAnalysis = json_decode($row_point_avg['analysis'], true);
//     print_r($avgAnalysis);
//     echo '<br>';

//     //능동성
//     $activity = $avgAnalysis['activity'] ?? 0;
//     $totalActivity += (int)$activity;
//     //대응성
//     $alacrity = $avgAnalysis['alacrity'] ?? 0;
//     $totalAlacrity += (int)$alacrity;
//     //안정성
//     $stability = $avgAnalysis['stability'] ?? 0;
//     $totalStability += (int)$stability;
//     //의지력
//     $willpower = $avgAnalysis['willpower'] ?? 0;
//     $totalWillpower += (int)$willpower;
//     //매력도
//     $attraction = $avgAnalysis['attraction'] ?? 0;
//     $totalAttraction += (int)$attraction;
//     //긍정성
//     $affirmative = $avgAnalysis['affirmative'] ?? 0;
//     $totalAffirmative += (int)$affirmative;
//     //신뢰성
//     $reliability = $avgAnalysis['reliability'] ?? 0;
//     $totalReliability += (int)$reliability;
//     //적극성
//     $aggressiveness = $avgAnalysis['aggressiveness'] ?? 0;
//     $totalAggressiveness += (int)$aggressiveness;

//     $totalCnt++;
// }

// $avgCapacity = array();
// array_push($avgCapacity, array(
//     'activity' => round($totalActivity / $totalCnt, 1),
//     'alacrity' => round($totalAlacrity / $totalCnt, 1),
//     'stability' => round($totalStability / $totalCnt, 1),
//     'willpower' => round($totalWillpower / $totalCnt, 1),
//     'attraction' => round($totalAttraction / $totalCnt, 1),
//     'affirmative' => round($totalAffirmative / $totalCnt, 1),
//     'reliability' => round($totalReliability / $totalCnt, 1),
//     'aggressiveness' => round($totalAggressiveness / $totalCnt, 1)
// ));

// echo '<br><br>';
// print_r($avgCapacity);
// echo '<br>';
// print_r(json_encode($avgCapacity, true));    //최종값
// echo '<br>';


//----------------- get voice (time, hz, dB) -------------------

// $sql_voice = "SELECT audio_detail FROM iv_result WHERE applier_idx = 6040 AND question_idx != 'total' ORDER BY idx ASC"; //applier_idx 값 
// $rst_voice = mysqli_query($conn, $sql_voice);
// while ($row_voice = mysqli_fetch_assoc($rst_voice)) {
//     $voiceAnalysis = json_decode($row_voice['audio_detail'], true);
//     print_r($voiceAnalysis);
//     echo '<br>';

//     $voiceAnalysisArr[] = $voiceAnalysis;
// }

// echo '<br>';
// print_r($voiceAnalysisArr);  //최종값
// echo '<br>';


//----------------- get facial_analysis (표정분석-본인점수) -------------------

// $sql_facial_analysis = "SELECT score FROM iv_result WHERE applier_idx = 6040 AND question_idx = 'total' ORDER BY idx ASC"; //applier_idx 값 
// $rst_facial_analysis = mysqli_query($conn, $sql_facial_analysis);
// $row_facial_analysis = mysqli_fetch_assoc($rst_facial_analysis);

// $facialAnalysis = json_decode($row_facial_analysis['score'], true);

// $quiver = $facialAnalysis["quiver"];   //음성떨림
// $volume = $facialAnalysis["volume"];   //음성크기
// $tone = $facialAnalysis["tone"];   //목소리톤
// $speed = $facialAnalysis["speed"];   //음성속도
// $diction = $facialAnalysis["diction"];   //발음정확도
// $eyes = $facialAnalysis["eyes"];   //시선처리
// $blink = $facialAnalysis["blink"];   //눈깜빡임
// $gesture = $facialAnalysis["gesture"];   //제스처빈도
// $head_motion = $facialAnalysis["head_motion"];   //머리움직임
// $glow = $facialAnalysis["glow"];   //홍조현상

// $realScore = getRealScore($speed, $quiver, $glow, $head_motion, $blink);

// $confidence = ((int)$realScore[0]['quiver'] + (int)$volume + (int)$tone + (int)$realScore[0]['speed']) / 4; //자신감
// $Attitude = ((int)$realScore[0]['head_motion'] + (int)$gesture) / 2; //태도

// $facial_analysis = array();
// array_push($facial_analysis, array('complexion' => $glow, 'blinking' => $blink, 'pronunciation' => $diction, 'eye_contact' => $eyes, 'confidence' => $confidence, 'Attitude' => $Attitude));

// print_r($facial_analysis[0]); //최종값
// echo '<br>';


//----------------- get facial_analysis_avg (표정분석-평균점수) -------------------

// $complexion = $blinking = $pronunciation = $eye_contact = $confidence = $Attitude = 0;
// $facial_total = 0;

// $sql_facial_analysis_avg = "SELECT iv_result.score 
//                             FROM iv_result 
//                             LEFT JOIN iv_applier ON iv_applier.idx = iv_result.applier_idx
//                             WHERE iv_result.question_idx = 'total'
//                             AND iv_applier.process = 4
//                             AND iv_result.score IS NOT NULL";
// $rst_facial_analysis_avg = mysqli_query($conn, $sql_facial_analysis_avg);
// while ($row_facial_analysis_avg = mysqli_fetch_assoc($rst_facial_analysis_avg)) {
//     $facialAnalysisAvg = json_decode($row_facial_analysis_avg['score'], true);

//     // print_r($facialAnalysisAvg);
//     // echo '<br><br>';

//     $quiver = $facialAnalysisAvg["quiver"] ?? 0;   //음성떨림
//     $volume = $facialAnalysisAvg["volume"] ?? 0;   //음성크기
//     $tone = $facialAnalysisAvg["tone"] ?? 0;   //목소리톤
//     $speed = $facialAnalysisAvg["speed"] ?? 0;   //음성속도
//     $diction = $facialAnalysisAvg["diction"] ?? 0;   //발음정확도
//     $eyes = $facialAnalysisAvg["eyes"] ?? 0;   //시선처리
//     $blink = $facialAnalysisAvg["blink"] ?? 0;   //눈깜빡임
//     $gesture = $facialAnalysisAvg["gesture"] ?? 0;   //제스처빈도
//     $head_motion = $facialAnalysisAvg["head_motion"] ?? 0;   //머리움직임
//     $glow = $facialAnalysisAvg["glow"] ?? 0;   //홍조현상

//     $realScore = getRealScore($speed, $quiver, $glow, $head_motion, $blink);
//     $confidence_cate = ((int)$realScore[0]['quiver'] + (int)$volume + (int)$tone + (int)$realScore[0]['speed']) / 4; //자신감
//     $Attitude_cate = ((int)$realScore[0]['head_motion'] + (int)$gesture) / 2; //태도

//     $complexion += (int)$glow;   //안색
//     $blinking += (int)$blink;    //눈깜빡임
//     $pronunciation += (int)$diction; //발음정확도
//     $eye_contact += (int)$eyes; //시선마주침
//     $confidence += (int)$confidence_cate;   //자신감
//     $Attitude += (int)$Attitude_cate;   //태도
//     $facial_total++;
// }

// $facial_result = array();
// array_push($facial_result, array(
//     'complexion' => round($complexion / $facial_total, 1),
//     'blinking' => round($blinking / $facial_total, 1),
//     'pronunciation' => round($pronunciation / $facial_total, 1),
//     'eye_contact' => round($eye_contact / $facial_total, 1),
//     'confidence' => round($confidence / $facial_total, 1),
//     'Attitude' => round($Attitude / $facial_total, 1),
// ));

// print_r($facial_result[0]); //최종값


//----------------- get MBTI -------------------

// $sql_mbti = "SELECT iv_member.mbti, iv_mbti_msg.msg, iv_mbti_recommend_job.type1, iv_mbti_recommend_job.type2, iv_mbti_recommend_job.type3, iv_mbti_recommend_job.keyword 
//             FROM iv_member
//             LEFT JOIN iv_mbti_msg ON iv_mbti_msg.mbti = iv_member.mbti
//             LEFT JOIN iv_mbti_recommend_job ON iv_mbti_recommend_job.mbti = iv_member.mbti
//             WHERE iv_member.user_id = 'vvhj_806@naver.com'";  //user_id
// $rst_mbti = mysqli_query($conn, $sql_mbti);
// $row_mbti = mysqli_fetch_assoc($rst_mbti);
// $mbti = $row_mbti['mbti'];

// $mbtiJobArr = array();
// //mbti 직업 점수좊은 순서로 출력
// $sql_mbti_job = "SELECT replace(iv_type.step, '##', ' ') as job
//                 FROM iv_mbti_score
//                 LEFT JOIN iv_type ON iv_type.idx = iv_mbti_score.type_idx
//                 WHERE iv_mbti_score.mbti = '".$mbti."'
//                 AND iv_type.process = 1
//                 ORDER BY iv_mbti_score.`value` DESC
//                 LIMIT 4";
// $rst_mbti_job = mysqli_query($conn, $sql_mbti_job);
// while($row_mbti_job = mysqli_fetch_assoc($rst_mbti_job)) {
//     array_push($mbtiJobArr, $row_mbti_job);
// }

// $mbtiArr = array();
// array_push($mbtiArr, array('info' => $row_mbti, 'mbti_job' => $mbtiJobArr));
// print_r($mbtiArr);
// echo '<br><br>';

// function getRealScore($speed, $quiver, $glow, $head_motion, $blink)
// {
//     if ($speed == 1) {
//         $speed = 5;
//     } else if ($speed == 2 || $speed == 10) {
//         $speed = 6;
//     } else if ($speed == 3 || $speed == 9) {
//         $speed = 7;
//     } else if ($speed == 4 || $speed == 8) {
//         $speed = 8;
//     } else if ($speed == 5 || $speed == 6) {
//         $speed = 10;
//     } else if ($speed == 7) {
//         $speed = 9;
//     } else if ($speed == 0) {
//         $speed = 0;
//     }

//     if ($quiver == 0) { //목소리떨림
//         $quiver = 0;
//     } else {
//         $quiver = 11 - $quiver;
//     }

//     if ($glow == 0) {   //홍조현상
//         $glow = 0;
//     } else {
//         $glow = 11 - $glow;
//     }

//     if ($head_motion == 0) {  //머리움직임
//         $head_motion = 0;
//     } else {
//         $head_motion = 11 - $head_motion;
//     }

//     if ($blink == 0) { //눈깜빡임
//         $blink = 0;
//     } else {
//         $blink = 11 - $blink;
//     }

//     $getScore = array();
//     array_push($getScore, array('speed' => $speed, 'quiver' => $quiver, 'glow' => $glow, 'head_motion' => $head_motion, 'blink' => $blink));

//     return $getScore;
// }



//----------------- 상세페이지(개별) -------------------
//----------------- 면접영상 STT 분석 (단어분포표) -------------------

// $positiveWord = 0;  //긍정단어
// $negativeWord = 0;  //부정단어
// $complexWord = 0;   //복합단어
// $neutralWord = 0;   //중립단어

// $wordDistribution = array();
// $wordStt = array();
// $wordList = array();

// $sql_stt_word = "SELECT iv_result.question_idx, iv_result.speech_text_detail
//                 FROM `iv_applier` 
//                 LEFT JOIN `iv_result` ON `iv_applier`.`idx` = `iv_result`.`applier_idx` 
//                 WHERE `iv_applier`.`idx` = 6062 
//                 AND `iv_result`.`question_idx` != 'total'";
// $rst_stt_word = mysqli_query($conn, $sql_stt_word);
// while ($row_stt_word = mysqli_fetch_assoc($rst_stt_word)) {
//     $sttDetail = json_decode($row_stt_word['speech_text_detail'], true);

//     for ($i = 0; $i < count($sttDetail); $i++) {
//         $words = $sttDetail[$i]['alternatives'][0]['words'];
//         if(count($words) > 1) {
//             $complexWord++;
//         }

//         for($j = 0; $j < count($words); $j++) {
//             $word = $words[$j]['word'];
//             $pos = $words[$j]['pos'];
//             $score = $words[$j]['score'];

//             if($score > 0) {
//                 $positiveWord++;
//             } else if($score == 0) {
//                 $neutralWord++;
//             } else if($score < 0) {
//                 $negativeWord++;
//             }

//             if ($pos == 'Noun' || $pos == 'Verb' || $pos == 'Adjective') {
//                 if(in_array($wordList, $word)) {
//                     $wordList[$word] = ++$wordList[$word];
//                 } else {
//                     $wordList[$word] = 1;
//                 }
//             }
//         }
//     }

//     $wordDistributionTotCnt = $positiveWord + $negativeWord + $complexWord + $neutralWord;
//     $positiveWordPer = round(($positiveWord/$wordDistributionTotCnt)*100, 1);
//     $negativeWordPer = round(($negativeWord/$wordDistributionTotCnt)*100, 1);
//     $complexWordPer = round(($complexWord/$wordDistributionTotCnt)*100, 1);
//     $neutralWordPer = round(($neutralWord/$wordDistributionTotCnt)*100, 1);

//     array_push($wordDistribution, array('positiveWordPer' => $positiveWordPer, 'negativeWordPer' => $negativeWordPer, 'complexWordPer' => $complexWordPer, 'neutralWordPer' => $neutralWordPer, 'wordDistributionTotCnt' => $wordDistributionTotCnt));
// }

// array_push($wordStt, array('wordDistribution' => $wordDistribution, 'wordList' => $wordList));
// // print_r($wordStt[0]);

$getWordStt = getWordStt($conn, 6062);
print_r($getWordStt['wordList']);
echo '<br><br>';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js"></script>
<script src="https://cdn.anychart.com/releases/v8/js/anychart-tag-cloud.min.js"></script>

<script>
    $(document).ready(function() {
        createWordcloud();
        console.log('<?= $getWordStt?>');
    });

    function createWordcloud(data) {
        let content = new Array();
        for (key in data) {
            if (key != '하다') {
                content.push({
                    "x": key,
                    "value": data[key]
                })
            }
        }

        anychart.onDocumentReady(function() {
            var chart = anychart.tagCloud(content);
            chart.angles([0]);
            chart.container("container");
            chart.draw();
        });
    }
</script>