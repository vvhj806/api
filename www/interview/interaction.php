<?php 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php");

//displayError();

$method = $_POST['method'];
if ($method == 'setInteraction') { // ============================ ��ȣ��ȭ�� ���� ���� ============================
    $speech_text = isset($_POST['speech_text']) ? trim($_POST['speech_text']) : ''; //������ �������� �ٲ� �ؽ�Ʈ��
    $applier_idx = isset($_POST['applier_idx']) ? trim($_POST['applier_idx']) : ''; //������ ����
    $question_count = isset($_POST['question_count']) ? trim($_POST['question_count']) : ''; //���� �������� ���ͺ� ����(��/�ۿ��� 1������ ������)
    $server = isset($_POST['server']) ? trim($_POST['server']) : ''; //�������� test, real

    if ($speech_text == '' || $applier_idx == '' || $question_count == '' || $server == '') {
        $msg = "[API_ERROR]��� : /interview/20/interaction.php\n���� : request ���� ��������\n".$speech_text."/".$applier_idx."/".$question_count."/".$server;
        telegram_send($msg, "LABELING");
        echo json_encode(array('status' => 400, 'message' => '�ùٸ� ������ �ƴմϴ�.'));
        return;
    }
    
    if ($server == 'webtest') { //�׽�Ʈ�������� ������ ��� DB Ŀ�ؼ� ����
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
        $msg = "[".$server." ��ȣ������ ����͸�]\n�����ڹ�ȣ : ".$applier_idx."\n\n���� : ".$question."\n\n�亯 : ".$speech_text."\n\n��ȣ��ȭ���� : ". $interactive_question."\n".$sql1;
        telegram_send($msg, "LABELING");
    }

    exit;

    if ($interactive_question != '') {
        //��ȣ������ ������ �����Ǹ� iv_question�� ��ϵǾ��ִ��� Ȯ����. ��ϵǾ������� question_idx ����
        $sql = "SELECT idx FROM iv_question WHERE que_question = '".$interactive_question."'";
        $rst = mysqli_query($conn_temp, $sql);
        $row = mysqli_fetch_assoc($rst);
        if (isset($row['idx'])) { //��ȣ������ ������ ������ �����Ѵٸ�
            $question_idx = $row['idx'];
        } else { //��ȣ������ ������ �������� �ʾƼ� �߰��ؾ���
            $sql1 = "INSERT INTO iv_question SET que_type = 'I', que_question = '".$interactive_question."', que_wait_time = 30, que_answer_time = 30, que_reg_date = NOW()";
            mysqli_query($conn_temp, $sql1);
            $question_idx = mysqli_insert_id($conn_temp);
        }

        //22.04.29 ��ȣ������ ������ �ڱ�Ұ� �亯�� �ν��Ͽ� �� ���� �������θ� ����
        //�̹� ��ȣ������ �����̵���ִٸ� ������������
        //���� �������� ������ ���� ������ ������ ��� ������
        //������ ������ �������� ���� insert�ϰ� ���õ� idx�� ��ȣ������ �������� ������

        $sql1 = "SELECT B.idx FROM `iv_report_result` as A INNER JOIN iv_question as B ON A.que_idx = B.idx WHERE applier_idx = '".$applier_idx."' AND B.que_type = 'I'";
        $rst1 = mysqli_query($conn_temp, $sql1);
        $row1 = mysqli_fetch_assoc($rst1);
        if(isset($row1['code'])) { //�̹� ��ȣ������ ������ �ִٸ� ������������X
            echo json_encode(array('status' => -2, 'message' => '�̹� ��ȣ������ ���� ������. ����X'));
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
        
        //����� selectedIdx �ڸ��� ���� ������ ���� INSERT��
        $sql2 = "INSERT INTO iv_report_result SET applier_idx = '".$applier_idx."', que_type = '".$origin_code."', que_idx = '".$origin_question_idx."', repo_process = 0, repo_reg_date = NOW()";
        mysqli_query($conn_temp, $sql2);
        
        //selectedIdx �ε��� ������ ã�Ƽ� q_idx�� ������Ʈ ����
        $sql2 = "UPDATE iv_report_result SET que_idx = '".$question_idx."', que_type = 'I' WHERE idx = '".$selectedIdx."'";
        mysqli_query($conn_temp, $sql2);

        echo json_encode(array('status' => 200));
    } else {
        echo json_encode(array('status' => -1, 'message' => '��ȣ������ ���� ���� ����'));
    }

} else {
    echo json_encode(array('status' => 400, 'message' => '�ùٸ� ������ �ƴմϴ�.'));
}