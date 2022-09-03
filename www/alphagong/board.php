<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/db_config.php"); 
include_once($_SERVER["DOCUMENT_ROOT"]."/function.php"); 

$method = $_POST['method'];

if ($method == 'getMainView') {
    
    //최근 등록된 공지사항 1개 제목
    $notice_list = array();
    $sql1 = 'SELECT wr_subject,wr_id FROM g5_write_notice ORDER BY wr_id DESC LIMIT 1';
    $rst1 = mysqli_query($conn, $sql1);
    $row1 = mysqli_fetch_assoc($rst1);
    array_push($notice_list, array('idx' => $row1['wr_id'], 'subject' => $row1['wr_subject']));

    //시험일정 최근 5개
    $schedule_list = array();
    $sql2 = 'SELECT wr_id, wr_subject FROM g5_write_schedule ORDER BY wr_id DESC LIMIT 5';
    $rst2 = mysqli_query($conn, $sql2);
    while($row2 = mysqli_fetch_assoc($rst2)) {
        array_push($schedule_list, array('idx' => $row2['wr_id'], 'subject' => $row2['wr_subject']));
    }

    //합격자 발표 최근 5개
    $pass_list = array();
    $sql3 = 'SELECT wr_id, wr_subject FROM g5_write_pass ORDER BY wr_id DESC LIMIT 5';
    $rst3 = mysqli_query($conn, $sql3);
    while($row3 = mysqli_fetch_assoc($rst3)) {
        array_push($pass_list, array('idx' => $row3['wr_id'], 'subject' => $row3['wr_subject']));
    }

    //진행중인 이벤트 배너
    $timestamp = time();
    $now_date = date("Y-m-d H:i:s");
    $banner_list = array();
    $sql4 = "SELECT bn_id, bn_url FROM g5_shop_banner WHERE bn_begin_time <= '".$now_date."' AND bn_end_time >= '".$now_date."' ORDER BY bn_order LIMIT 5";
    $rst4 = mysqli_query($conn, $sql4);
    while($row4 = mysqli_fetch_assoc($rst4)) {
        array_push($banner_list, array('link' => $row4['bn_url'], 'image' => 'https://alphagong.highbuff.com/board/data/banner/'.$row4['bn_id'].'?'.$timestamp));
    }

    echo json_encode(array('status' => 200, 'notice' => $notice_list, 'schedule' => $schedule_list, 'pass' => $pass_list, 'banner' => $banner_list));

} else if ($method == 'getFaq') {
    $sql = "SELECT count(*) as cnt FROM g5_write_faq ORDER BY wr_id DESC";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    $total_count = $row['cnt'];

    $rows = 10;
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $faq_list = array();
    $sql = 'SELECT wr_subject, wr_content FROM g5_write_faq ORDER BY wr_id DESC LIMIT '.$from_record.', '.$rows;
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($faq_list, array('question' => $row['wr_subject'], 'answer' => strip_tags(str_replace('&nbsp;', ' ', $row['wr_content']))));
    }
    echo json_encode(array('status' => 200, 'faq' => $faq_list, 'page' => $page, 'total_page' => $total_page));

} else if ($method == 'getMainFaq') {
    $faq_list = array();
    $sql = 'SELECT wr_subject, wr_content FROM g5_write_faq ORDER BY wr_id LIMIT 5';
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($faq_list, array('question' => $row['wr_subject'], 'answer' => strip_tags(str_replace('&nbsp;', ' ', $row['wr_content']))));
    }
    echo json_encode(array('status' => 200, 'faq' => $faq_list));

} else if ($method == 'getNotice') { //공지사항 리스트
    $sql_search = '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    if ($keyword != '') { //검색 키워드가 있을 경우에는
        $sql_search = " WHERE wr_subject LIKE '%".$keyword."%'";
    }

    $sql = "SELECT count(*) as cnt FROM g5_write_notice ".$sql_search." ORDER BY wr_id DESC";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    $total_count = $row['cnt'];

    $rows = 10;
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $notice_list = array();
    $sql = "SELECT * FROM g5_write_notice ".$sql_search." ORDER BY wr_id DESC LIMIT ".$from_record.", ".$rows;
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($notice_list, array('idx' => $row['wr_id'], 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit'])));
    }
    echo json_encode(array('status' => 200, 'board' => $notice_list, 'page' => $page, 'total_page' => $total_page));

} else if ($method == 'getNoticeDetail') { //공지사항 상세페이지
    $idx = isset($_POST['idx']) ? $_POST['idx'] : '';
    if ($idx == '') { //전달받은 인덱스 값이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }

    $sql = 'SELECT * FROM g5_write_notice WHERE wr_id = '.$idx;
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        //22.03.08 게시글 조회수 증가
        $sql4 = 'UPDATE g5_write_notice SET wr_hit = wr_hit + 1 WHERE wr_id = '.$idx;
        mysqli_query($conn, $sql4);

        //첨부파일 가져오기
        $sql1 = "SELECT * FROM g5_board_file WHERE bo_table = 'notice' AND wr_id = '".$row['wr_id']."' ORDER BY bf_no";
        $rst1 = mysqli_query($conn, $sql1);
        $attached_list = array();
        while ($row1 = mysqli_fetch_assoc($rst1)) {
            array_push($attached_list, array('filename' => $row1['bf_source'], 'filelink' => 'https://alphagong.highbuff.com/board/data/file/notice/'.$row1['bf_file']));
        }
        
        //이전 게시글 1개 가져오기
        $sql2 = "SELECT wr_id, wr_subject FROM g5_write_notice WHERE wr_id < '".$row['wr_id']."' ORDER BY wr_id DESC LIMIT 1";
        $rst2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($rst2);
        $prev_list = array('idx' => $row2['wr_id'], 'subject' => $row2['wr_subject']);

        //다음 게시글 1개 가져오기
        $sql3 = "SELECT wr_id, wr_subject FROM g5_write_notice WHERE wr_id > '".$row['wr_id']."' ORDER BY wr_id LIMIT 1";
        $rst3 = mysqli_query($conn, $sql3);
        $row3 = mysqli_fetch_assoc($rst3);
        $next_list = array('idx' => $row3['wr_id'], 'subject' => $row3['wr_subject']);
        
        echo json_encode(array('status' => 200, 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit']), 'link' => $row['wr_link1'], 'content' => $row['wr_content'], 'attached' => $attached_list, 'prev' => $prev_list, 'next' => $next_list));
    } else { //입력받은 인덱스 값에 게시물이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }
} else if ($method == 'getSchedule') { //공무원 시험일정 리스트
    $sql_search = '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    if ($keyword != '') { //검색 키워드가 있을 경우에는
        $sql_search = " WHERE wr_subject LIKE '%".$keyword."%'";
    }

    $sql = "SELECT count(*) as cnt FROM g5_write_schedule ".$sql_search." ORDER BY wr_id DESC";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    $total_count = $row['cnt'];

    $rows = 10;
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $schedule_list = array();
    $sql = "SELECT * FROM g5_write_schedule ".$sql_search." ORDER BY wr_id DESC LIMIT ".$from_record.", ".$rows;
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($schedule_list, array('idx' => $row['wr_id'], 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit'])));
    }
    echo json_encode(array('status' => 200, 'board' => $schedule_list, 'page' => $page, 'total_page' => $total_page));

} else if ($method == 'getScheduleDetail') { //공무원 시험일정 상세페이지
    $idx = isset($_POST['idx']) ? $_POST['idx'] : '';
    if ($idx == '') { //전달받은 인덱스 값이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }

    $sql = 'SELECT * FROM g5_write_schedule WHERE wr_id = '.$idx;
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        //22.03.08 게시글 조회수 증가
        $sql4 = 'UPDATE g5_write_schedule SET wr_hit = wr_hit + 1 WHERE wr_id = '.$idx;
        mysqli_query($conn, $sql4);

        //첨부파일 가져오기
        $sql1 = "SELECT * FROM g5_board_file WHERE bo_table = 'schedule' AND wr_id = '".$row['wr_id']."' ORDER BY bf_no";
        $rst1 = mysqli_query($conn, $sql1);
        $attached_list = array();
        while ($row1 = mysqli_fetch_assoc($rst1)) {
            array_push($attached_list, array('filename' => $row1['bf_source'], 'filelink' => 'https://alphagong.highbuff.com/board/data/file/schedule/'.$row1['bf_file']));
        }
        
        //이전 게시글 1개 가져오기
        $sql2 = "SELECT wr_id, wr_subject FROM g5_write_schedule WHERE wr_id < '".$row['wr_id']."' ORDER BY wr_id DESC LIMIT 1";
        $rst2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($rst2);
        $prev_list = array('idx' => $row2['wr_id'], 'subject' => $row2['wr_subject']);

        //다음 게시글 1개 가져오기
        $sql3 = "SELECT wr_id, wr_subject FROM g5_write_schedule WHERE wr_id > '".$row['wr_id']."' ORDER BY wr_id LIMIT 1";
        $rst3 = mysqli_query($conn, $sql3);
        $row3 = mysqli_fetch_assoc($rst3);
        $next_list = array('idx' => $row3['wr_id'], 'subject' => $row3['wr_subject']);
        
        echo json_encode(array('status' => 200, 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit']), 'link' => $row['wr_link1'], 'content' => $row['wr_content'], 'attached' => $attached_list, 'prev' => $prev_list, 'next' => $next_list));
    } else { //입력받은 인덱스 값에 게시물이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }
} else if ($method == 'getPass') { //합격자 발표
    $sql_search = '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    if ($keyword != '') { //검색 키워드가 있을 경우에는
        $sql_search = " WHERE wr_subject LIKE '%".$keyword."%'";
    }

    $sql = "SELECT count(*) as cnt FROM g5_write_pass ".$sql_search." ORDER BY wr_id DESC";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    $total_count = $row['cnt'];

    $rows = 10;
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $pass_list = array();
    $sql = "SELECT * FROM g5_write_pass ".$sql_search." ORDER BY wr_id DESC LIMIT ".$from_record.", ".$rows;
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($pass_list, array('idx' => $row['wr_id'], 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit'])));
    }
    echo json_encode(array('status' => 200, 'board' => $pass_list, 'page' => $page, 'total_page' => $total_page));

} else if ($method == 'getPassDetail') { //합격자 발표 상세페이지
    $idx = isset($_POST['idx']) ? $_POST['idx'] : '';
    if ($idx == '') { //전달받은 인덱스 값이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }

    $sql = 'SELECT * FROM g5_write_pass WHERE wr_id = '.$idx;
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        //22.03.08 게시글 조회수 증가
        $sql4 = 'UPDATE g5_write_pass SET wr_hit = wr_hit + 1 WHERE wr_id = '.$idx;
        mysqli_query($conn, $sql4);

        //첨부파일 가져오기
        $sql1 = "SELECT * FROM g5_board_file WHERE bo_table = 'pass' AND wr_id = '".$row['wr_id']."' ORDER BY bf_no";
        $rst1 = mysqli_query($conn, $sql1);
        $attached_list = array();
        while ($row1 = mysqli_fetch_assoc($rst1)) {
            array_push($attached_list, array('filename' => $row1['bf_source'], 'filelink' => 'https://alphagong.highbuff.com/board/data/file/pass/'.$row1['bf_file']));
        }
        
        //이전 게시글 1개 가져오기
        $sql2 = "SELECT wr_id, wr_subject FROM g5_write_pass WHERE wr_id < '".$row['wr_id']."' ORDER BY wr_id DESC LIMIT 1";
        $rst2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($rst2);
        $prev_list = array('idx' => $row2['wr_id'], 'subject' => $row2['wr_subject']);

        //다음 게시글 1개 가져오기
        $sql3 = "SELECT wr_id, wr_subject FROM g5_write_pass WHERE wr_id > '".$row['wr_id']."' ORDER BY wr_id LIMIT 1";
        $rst3 = mysqli_query($conn, $sql3);
        $row3 = mysqli_fetch_assoc($rst3);
        $next_list = array('idx' => $row3['wr_id'], 'subject' => $row3['wr_subject']);
        
        echo json_encode(array('status' => 200, 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit']), 'link' => $row['wr_link1'], 'content' => $row['wr_content'], 'attached' => $attached_list, 'prev' => $prev_list, 'next' => $next_list));
    } else { //입력받은 인덱스 값에 게시물이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }
} else if ($method == 'getPayscale') { //공무원 봉급표
    $sql_search = '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    if ($keyword != '') { //검색 키워드가 있을 경우에는
        $sql_search = " WHERE wr_subject LIKE '%".$keyword."%'";
    }

    $sql = "SELECT count(*) as cnt FROM g5_write_payscale ".$sql_search." ORDER BY wr_id DESC";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    $total_count = $row['cnt'];

    $rows = 10;
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $pass_list = array();
    $sql = "SELECT * FROM g5_write_payscale ".$sql_search." ORDER BY wr_id DESC LIMIT ".$from_record.", ".$rows;
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($pass_list, array('idx' => $row['wr_id'], 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit'])));
    }
    echo json_encode(array('status' => 200, 'board' => $pass_list, 'page' => $page, 'total_page' => $total_page));

} else if ($method == 'getPayscaleDetail') { //공무원 봉급표 상세페이지
    $idx = isset($_POST['idx']) ? $_POST['idx'] : '';
    if ($idx == '') { //전달받은 인덱스 값이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }

    $sql = 'SELECT * FROM g5_write_payscale WHERE wr_id = '.$idx;
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        //22.03.08 게시글 조회수 증가
        $sql4 = 'UPDATE g5_write_payscale SET wr_hit = wr_hit + 1 WHERE wr_id = '.$idx;
        mysqli_query($conn, $sql4);

        //첨부파일 가져오기
        $sql1 = "SELECT * FROM g5_board_file WHERE bo_table = 'payscale' AND wr_id = '".$row['wr_id']."' ORDER BY bf_no";
        $rst1 = mysqli_query($conn, $sql1);
        $attached_list = array();
        while ($row1 = mysqli_fetch_assoc($rst1)) {
            array_push($attached_list, array('filename' => $row1['bf_source'], 'filelink' => 'https://alphagong.highbuff.com/board/data/file/payscale/'.$row1['bf_file']));
        }
        
        //이전 게시글 1개 가져오기
        $sql2 = "SELECT wr_id, wr_subject FROM g5_write_payscale WHERE wr_id < '".$row['wr_id']."' ORDER BY wr_id DESC LIMIT 1";
        $rst2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($rst2);
        $prev_list = array('idx' => $row2['wr_id'], 'subject' => $row2['wr_subject']);

        //다음 게시글 1개 가져오기
        $sql3 = "SELECT wr_id, wr_subject FROM g5_write_payscale WHERE wr_id > '".$row['wr_id']."' ORDER BY wr_id LIMIT 1";
        $rst3 = mysqli_query($conn, $sql3);
        $row3 = mysqli_fetch_assoc($rst3);
        $next_list = array('idx' => $row3['wr_id'], 'subject' => $row3['wr_subject']);
        
        echo json_encode(array('status' => 200, 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit']), 'link' => $row['wr_link1'], 'content' => $row['wr_content'], 'attached' => $attached_list, 'prev' => $prev_list, 'next' => $next_list));
    } else { //입력받은 인덱스 값에 게시물이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }
} else if ($method == 'getNotification') { //-------------------- 공무원 공지사항
    $sql_search = '';
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
    if ($keyword != '') { //검색 키워드가 있을 경우에는
        $sql_search = " WHERE wr_subject LIKE '%".$keyword."%'";
    }

    $sql = "SELECT count(*) as cnt FROM g5_write_notification ".$sql_search." ORDER BY wr_id DESC";
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    $total_count = $row['cnt'];

    $rows = 10;
    $total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
    $page = isset($_POST["page"]) ? trim($_POST["page"]) : 1;
    $from_record = ($page - 1) * $rows; // 시작 열을 구함

    $pass_list = array();
    $sql = "SELECT * FROM g5_write_notification ".$sql_search." ORDER BY wr_id DESC LIMIT ".$from_record.", ".$rows;
    $rst = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rst)) {
        array_push($pass_list, array('idx' => $row['wr_id'], 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit'])));
    }
    echo json_encode(array('status' => 200, 'board' => $pass_list, 'page' => $page, 'total_page' => $total_page));

} else if ($method == 'getNotificationDetail') { //-------------------- 공무원 공지사항 상세페이지
    $idx = isset($_POST['idx']) ? $_POST['idx'] : '';
    if ($idx == '') { //전달받은 인덱스 값이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }

    $sql = 'SELECT * FROM g5_write_notification WHERE wr_id = '.$idx;
    $rst = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($rst);
    if (isset($row)) {
        //22.03.08 게시글 조회수 증가
        $sql4 = 'UPDATE g5_write_notification SET wr_hit = wr_hit + 1 WHERE wr_id = '.$idx;
        mysqli_query($conn, $sql4);

        //첨부파일 가져오기
        $sql1 = "SELECT * FROM g5_board_file WHERE bo_table = 'notification' AND wr_id = '".$row['wr_id']."' ORDER BY bf_no";
        $rst1 = mysqli_query($conn, $sql1);
        $attached_list = array();
        while ($row1 = mysqli_fetch_assoc($rst1)) {
            array_push($attached_list, array('filename' => $row1['bf_source'], 'filelink' => 'https://alphagong.highbuff.com/board/data/file/notification/'.$row1['bf_file']));
        }
        
        //이전 게시글 1개 가져오기
        $sql2 = "SELECT wr_id, wr_subject FROM g5_write_notification WHERE wr_id < '".$row['wr_id']."' ORDER BY wr_id DESC LIMIT 1";
        $rst2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($rst2);
        $prev_list = array('idx' => $row2['wr_id'], 'subject' => $row2['wr_subject']);

        //다음 게시글 1개 가져오기
        $sql3 = "SELECT wr_id, wr_subject FROM g5_write_notification WHERE wr_id > '".$row['wr_id']."' ORDER BY wr_id LIMIT 1";
        $rst3 = mysqli_query($conn, $sql3);
        $row3 = mysqli_fetch_assoc($rst3);
        $next_list = array('idx' => $row3['wr_id'], 'subject' => $row3['wr_subject']);
        
        echo json_encode(array('status' => 200, 'subject' => $row['wr_subject'], 'writer' => $row['wr_name'], 'date' => substr($row['wr_datetime'], 2, 14), 'hits' => number_format($row['wr_hit']), 'link' => $row['wr_link1'], 'content' => $row['wr_content'], 'attached' => $attached_list, 'prev' => $prev_list, 'next' => $next_list));
    } else { //입력받은 인덱스 값에 게시물이 없을 경우
        echo json_encode(array('status' => -201, 'message' => '해당 자료를 찾을수 없습니다.'));
        return;
    }
} else {
    echo json_encode(array('status' => 400, 'message' => '올바른 접근이 아닙니다.'));
}

?>