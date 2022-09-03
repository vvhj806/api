<?php 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php");

//displayError();

$method = $_POST['method'];
if ($method == 'setInteraction') { // ============================ 상호대화형 질문 생성 ============================
    $speech_text = isset($_POST['speech_text']) ? trim($_POST['speech_text']) : ''; //음성을 문장으로 바꾼 텍스트값
    $applier_idx = isset($_POST['applier_idx']) ? trim($_POST['applier_idx']) : ''; //지원자 정보
    $question_count = isset($_POST['question_count']) ? trim($_POST['question_count']) : ''; //현재 진행중인 인터뷰 순서(웹/앱에서 1번부터 시작함)
    $server = isset($_POST['server']) ? trim($_POST['server']) : ''; //서버정보 test, real

    if ($speech_text == '' || $applier_idx == '' || $question_count == '' || $server == '') {
        $msg = "[API_ERROR]경로 : /interview/interaction.php\n에러 : request 값이 빠져있음\n".$speech_text."/".$applier_idx."/".$question_count."/".$server;
        telegram_send($msg, "LABELING");
        echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
        return;
    }
    
    if ($server == 'webtest') { //테스트서버에서 접근할 경우 DB 커넥션 변경
        $conn_temp = $conn_iv_20_webtest;
    } else {
        $conn_temp = $conn_iv_20;
    }

    //$conn_temp = $conn_iv_20_webtest;
    
    $wordList = incodingMecab($speech_text, array('NNG', 'NNP', 'VV', 'VA', 'MAG', 'MAJ', 'VCN'));
    $interactive_question = '';
    $sql = 'SELECT * FROM iv_interactive_question'; 
    $rst = mysqli_query($conn_temp, $sql);
    while ($row = mysqli_fetch_array($rst)) {
        $unique_point_words = array_unique(explode(',', $row['point_word']));
        $unique_negative_words = array_unique(explode(',', $row['negative_word']));

        $intersect_point_words = array_intersect($wordList, $unique_point_words);
        $intersect_negative_words = array_intersect($wordList, $unique_negative_words);
        
        if (count(array_diff($unique_point_words, $intersect_point_words)) == 0 && count($intersect_negative_words) == 0) {
            $interactive_question = $row['question'];
            break;
        }
    }
    $temp_question_count = $question_count-1;
    $sql1 = "SELECT B.que_question FROM `iv_report_result` as A INNER JOIN iv_question as B ON A.que_idx = B.idx WHERE A.applier_idx = '".$applier_idx."' ORDER BY A.idx ASC LIMIT 1 OFFSET ".$temp_question_count;
    $rst1 = mysqli_query($conn_temp, $sql1);
    $row1 = mysqli_fetch_assoc($rst1);
    
    if(isset($row1)) {
        $question = $row1['que_question'];
        $msg = "[".$server." 상호응답형 모니터링]\n지원자번호 : ".$applier_idx."\n\n질문 : ".$question."\n\n답변 : ".$speech_text."\n\n상호대화질문 : ". $interactive_question;
        telegram_send($msg, "LABELING");
    }

    exit;

    if ($interactive_question != '') {
        //상호응답형 질문이 생성되면 iv_question에 등록되어있는지 확인함. 등록되어있으면 question_idx 리턴
        $sql = "SELECT idx FROM iv_question WHERE que_question = '".$interactive_question."'";
        $rst = mysqli_query($conn_temp, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (isset($row['idx'])) { //상호응답형 질문이 기존에 존재한다면
            $question_idx = $row['idx'];
        } else { //상호응답형 질문이 존재하지 않아서 추가해야함
            $sql1 = "INSERT INTO iv_question SET que_type = 'I', que_question = '".$interactive_question."', que_wait_time = 30, que_answer_time = 30, que_reg_date = NOW()";
            mysqli_query($conn_temp, $sql1);
            $question_idx = mysqli_insert_id($conn_temp);
        }

        //22.04.29 상호응답형 질문은 자기소개 답변을 인식하여 그 다음 질문으로만 한정
        //이미 상호응답형 질문이들어있다면 진행하지않음
        //현재 진행중인 질문의 다음 질문의 정보를 모두 가져옴
        //가져온 정보를 바탕으로 새로 insert하고 선택된 idx는 상호응답형 질문으로 변경함

        $sql1 = "SELECT B.idx FROM `iv_report_result` as A INNER JOIN iv_question as B ON A.que_idx = B.idx WHERE applier_idx = '".$applier_idx."' AND B.que_type = 'I'";
        $rst1 = mysqli_query($conn_temp, $sql1);
        $row1 = mysqli_fetch_assoc($rst1);
        if(isset($row1['code'])) { //이미 상호응답형 질문이 있다면 진행하지않음X
            echo json_encode(array('status' => -2, 'message' => '이미 상호응답형 질문 존재함. 진행X'));
            return;
        }
        
        $selectedIdx = '';
        $sql1 = "SELECT * FROM iv_report_result WHERE applier_idx = '".$applier_idx."' ORDER BY idx ASC LIMIT 1 OFFSET ".$question_count;
        $rst1 = mysqli_query($conn_temp, $sql1);
        $row1 = mysqli_fetch_assoc($rst1);
        
        $selectedIdx = $row1['idx'];
        $origin_user_id = $row1['user_id'];
        $origin_code = $row1['que_type'];
        $origin_question_idx = $row1['que_idx'];
        
        //변경될 selectedIdx 자리의 질문 정보를 새로 INSERT함
        $sql2 = "INSERT INTO iv_report_result SET applier_idx = '".$applier_idx."', que_type = '".$origin_code."', que_idx = '".$origin_question_idx."', repo_process = 0, repo_reg_date = NOW()";
        mysqli_query($conn_temp, $sql2);
        
        //selectedIdx 인덱스 정보를 찾아서 q_idx를 업데이트 해줌
        $sql2 = "UPDATE iv_report_result SET que_idx = '".$question_idx."', que_type = 'I' WHERE idx = '".$selectedIdx."'";
        mysqli_query($conn_temp, $sql2);

        echo json_encode(array('status' => 200));
    } else {
        echo json_encode(array('status' => -1, 'message' => '상호응답형 질문 생성 실패'));
    }

} else {
    echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
}