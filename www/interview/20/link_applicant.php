<?php
include_once('common.php');
//displayError();
$type = $_POST['type'];
$ap_idx = $_POST['ap_idx'];

if ($type == 'start') {
    //URL 생성
    //1.5 DB select 
    //applicant, final_interview, interview, company, member
    $sql1 = "SELECT * FROM applicant a 
			LEFT JOIN final_interview b 
			ON a.f_idx = b.f_idx 
			LEFT JOIN interview c 
			ON b.i_idx = c.i_idx
			LEFT JOIN company d
			ON c.c_idx = d.c_idx
			LEFT JOIN member e
			ON b.f_writer = e.m_id
			WHERE a.ap_idx =  " . $ap_idx;

    if (!$rst1 = mysqli_query($conn_iv_15, $sql1)) {
        $error1 = mysqli_error($conn_iv_15);
        return_error('1', 'DB select 1', $error1, $sql1);
        return;
    } else {
        $row1 = mysqli_fetch_assoc($rst1);

        if ($row1['ap_status'] == 'A.I. 면접 요청') {
            $sug_type = 'A';
        } else if ($ap_status == 'A.I. 면접 완료') {
            $sug_type = 'B';
        } else if ($ap_status == '대면 면접 요청') {
            $sug_type = 'I';
        } else if ($ap_status == '대면 면접 완료') {
            $sug_type = 'J';
        } else if ($ap_status == '공고마감') {
            $sug_type = 'Z';
        } else {
            $sug_type = '';
        }
        $manager = $row1['f_manager'];
        $manager_phone = $row1['f_manager_phone'];
        $reg_date = $row1['f_date'];

        $start_date = explode(' ', $row1['f_start_date']);
        $end_date = explode(' ', $row1['f_end_date']);

        $sDateTime = date_create($start_date[0]);
        $newStartDate = date_format($sDateTime, "Ymd");
        $sDateTime2 = date_create($start_date[1]);
        $newStartTime = date_format($sDateTime2, "H:i:s");

        $eDateTime = date_create($end_date[0]);
        $newEndDate = date_format($eDateTime, "Ymd");
        $eDateTime2 = date_create($end_date[1]);
        $newEndTime = date_format($eDateTime2, "H:i:s");

        $ap_title = $row1['ap_title'];
        $ap_name = $row1['ap_name'];
        $ap_phone = $row1['ap_phone'];
        $ap_idx = $row1['ap_idx'];

        $answer_time = $row1['f_answer_time'];
        $i_title = $row1['i_title'];
        $i_memo = $row1['i_memo'];
        $i_date = $row1['i_date'];
        $i_idx = $row1['i_idx'];
        // 재응시여부
        if ($row1['i_opportunity'] == 1) {
            $opportunity = 'Y';
        } else if ($row1['i_opportunity'] == 0) {
            $opportunity = 'N';
        } else {
            $opportunity = null;
        }

        $c_name = $row1['c_name'];
        $c_number = $row1['c_number'];
        $c_address = $row1['c_address'];

        $c_ceo_name = $row1['c_ceo_name'];
        $c_serial_number = $row1['c_serial_number'];
        $c_resigter_date = $row1['c_resigter_date'];
        $c_cp_use_cnt = $row1['c_cp_use_cnt'];

        $m_id = $row1['m_id'];
        $m_pwd = $row1['m_pwd'];
        $m_mobile = $row1['m_mobile'];
        $m_mail = $row1['m_mail'];
        $m_name = $row1['m_name'];
        $m_resigter_date = $row1['m_resigter_date'];
        $m_visit_date = $row1['m_visit_date'];
        $job_idx = $row1['ap_jidx'];
        if ($job_idx == '' || $job_idx == null) {

            $sql33 = "SELECT * FROM iv_job_category WHERE job_depth_text = '{$row1['ap_job_category1']}' AND job_depth_2 IS NULL";

            if (!$rst33 = mysqli_query($conn_iv_20, $sql33)) {
                $error3 = mysqli_error($conn_iv_20);
                return_error('322', 'DB select 322', $error33, $sql33);
                return;
            } else {
                $row33 = mysqli_fetch_assoc($rst33);
                if ($row33['idx'] > 0) {
                    $sql333 = "SELECT * FROM iv_job_category WHERE job_depth_text = '{$row1['ap_job_category2']}' AND job_depth_1 = '{$row33['job_depth_1']}'";

                    if (!$rst333 = mysqli_query($conn_iv_20, $sql333)) {
                        $error333 = mysqli_error($conn_iv_20);
                        return_error('3', 'DB select 3', $error333, $sql333);
                        return;
                    } else {
                        $row333 = mysqli_fetch_assoc($rst333);
                        if ($row333['idx'] > 0) {

                            $job_idx = $row333['idx'];
                        }
                    }
                }
            }
        }

        //없을시 
        //    IV_MEMBER, IV_COMPANY. IV_INTERVIEW SELECT
        $sql3 = "SELECT * FROM iv_member WHERE mem_id = '{$row1['m_id']}'";

        if (!$rst3 = mysqli_query($conn_iv_20, $sql3)) {
            $error3 = mysqli_error($conn_iv_20);
            return_error('3', 'DB select 3', $error3, $sql3);
            return;
        } else {
            $row3 = mysqli_fetch_assoc($rst3);
            if ($row3['idx'] > 0) {
                if ($row3['com_idx'] > 0) {
                    //iv_company 있음
                    //iv_interview select

                    if ($i_idx == '1') {
                        $sql4 = "SELECT * FROM iv_interview WHERE idx = '1'";
                    } else {
                        $sql4 = "SELECT * FROM iv_interview WHERE mem_idx = '{$row3['idx']}' 
                        AND inter_name = '{$row1['i_title']}' AND inter_reg_date = '{$row1['i_date']}' AND inter_answer_time = '{$answer_time}'";
                    }

                    if (!$rst4 = mysqli_query($conn_iv_20, $sql4)) {
                        $error4 = mysqli_error($conn_iv_20);
                        return_error('4', 'DB select 4', $error4, $sql4);
                        return;
                    } else {
                        $row4 = mysqli_fetch_assoc($rst4);
                        if ($row4['idx'] > 0) {


                            //1.5 question -> 2.0 iv_question 있는지 확인
                            $str_arr = explode(',', $row1['i_question_list']);

                            if ($row1['ap_jidx'] == '' || $row1['ap_jidx'] == null) {
                                $idx003 = '';
                                for ($k = 0; $k < count($str_arr); $k++) {
                                    $sql001 = "SELECT q_question FROM question WHERE q_idx = '" . $str_arr[$k] . "'";
                                    $rst001 = mysqli_query($conn_iv_15, $sql001);
                                    $row001 = mysqli_fetch_array($rst001);
                                    //array_push($queArr, $row001['q_question']);
                                    $sql002 = "SELECT idx FROM iv_question WHERE que_question = '" . $row001['q_question'] . "' AND que_type ='B'";
                                    $rst002 = mysqli_query($conn_iv_20, $sql002);
                                    $row002 = mysqli_fetch_array($rst002);
                                    $bar = ',';
                                    if ($k == count($str_arr) - 1) $bar = '';

                                    if ($row002['idx'] > 0) {
                                        $idx003 .= $row002['idx'] . $bar;
                                    } else {
                                        $sql003 = "INSERT INTO iv_question (que_question, que_type, delyn) VALUES 
													('{$row001['q_question']}', 'B','N')";
                                        if (!$rst003 = mysqli_query($conn_iv_20, $sql003)) {
                                            $error003 = mysqli_error($conn_iv_20);

                                            return_error('003', '111111', $error003, $sql003);
                                            return;
                                        } else {
                                            $idx003 .= mysqli_insert_id($conn_iv_20) . $bar;
                                        }
                                    }
                                }
                            } else {
                                $idx003 = $row1['i_question_list'];
                            }



                            $sql00211 = "SELECT idx FROM iv_company_suggest_applicant WHERE old_ap_idx = '" . $ap_idx . "' ";
                            $rst00211 = mysqli_query($conn_iv_20, $sql00211);
                            $row00211 = mysqli_fetch_array($rst00211);

                            if ($row00211['idx'] > 0 || isset($row00211['idx'])) {
                                $sql4 = "UPDATE iv_company_suggest SET sug_end_date ='" . $newEndDate . "' WHERE idx='" . $row00211['sug_idx'] . "'";

                                if (!$rst4 = mysqli_query($conn_iv_20, $sql4)) {
                                    $error4 = mysqli_error($conn_iv_20);
                                    return_error('4', 'DB update 4', $error4, $sql4);
                                    return;
                                } else {
                                    //echo '3완료';
                                    $last_ap_idx = $row00211['idx'];
                                }
                            } else {

                                $sql0021 = "SELECT idx FROM iv_company_suggest WHERE 
									inter_idx = '" . $row4['idx'] . "' AND
									com_idx = '" . $row3['com_idx'] . "' AND
									sug_type = '" . $sug_type . "' AND
									sug_manager_name = '" . $manager . "' AND
									sug_manager_tel = '" . $manager_phone . "' AND
									sug_tel = '" . $manager_phone . "' AND
									sug_reg_date = '" . $reg_date . "' AND
									sug_start_date = '" . $newStartDate . "' AND
									sug_end_date = '" . $newEndDate . "' ";
                                $rst0021 = mysqli_query($conn_iv_20, $sql0021);
                                $row0021 = mysqli_fetch_array($rst0021);

                                if ($row0021['idx'] > 0) {

                                    $sql401 = "INSERT INTO iv_company_suggest_applicant SET
                                    inter_idx = '" . $row4['idx'] . "',
                                    sug_idx = '" . $row0021['idx'] . "',
                                    sug_app_title = '" . $ap_title . "',
                                    sug_app_name = '" . $ap_name . "',
                                    sug_app_phone = '" . $ap_phone . "',
                                    old_ap_idx = '" . $ap_idx . "',
                                    job_idx = '" . $job_idx . "',
                                    sug_app_reg_date = NOW()
                                    ";

                                    $rst401 = mysqli_query($conn_iv_20, $sql401);
                                    if ($rst401) {

                                        $last_ap_idx = mysqli_insert_id($conn_iv_20);
                                    }
                                } else {

                                    //iv_company_suggest, iv_company_suggest_applicant insert
                                    $sql30 = "INSERT INTO iv_company_suggest SET
											inter_idx = '" . $row4['idx'] . "',
											com_idx = '" . $row3['com_idx'] . "',
											sug_type = '" . $sug_type . "',
											sug_manager_name = '" . $manager . "',
											sug_manager_tel = '" . $manager_phone . "',
											sug_tel = '" . $manager_phone . "',
											sug_reg_date = '" . $reg_date . "',
											sug_start_date = '" . $newStartDate . "',
											sug_end_date = '" . $newEndDate . "',
                                            sug_start_time = '" . $newStartTime . "',
											sug_end_time = '" . $newEndTime . "'
									";
                                    $rst30 = mysqli_query($conn_iv_20, $sql30);
                                    if ($rst30) {

                                        $last_sug_idx = mysqli_insert_id($conn_iv_20);

                                        $sql40 = "INSERT INTO iv_company_suggest_applicant SET
													inter_idx = '" . $row4['idx'] . "',
													sug_idx = '" . $last_sug_idx . "',
													sug_app_title = '" . $ap_title . "',
													sug_app_name = '" . $ap_name . "',
													sug_app_phone = '" . $ap_phone . "',
													old_ap_idx = '" . $ap_idx . "',
                                                    job_idx = '" . $job_idx . "',
													sug_app_reg_date = NOW()
												";

                                        $rst40 = mysqli_query($conn_iv_20, $sql40);
                                        if ($rst40) {

                                            $last_ap_idx = mysqli_insert_id($conn_iv_20);
                                        }
                                    }
                                }
                            }
                        } else {
                            //1.5 question -> 2.0 iv_question 있는지 확인
                            $str_arr = explode(',', $row1['i_question_list']);
                            if ($row1['ap_jidx'] == '' || $row1['ap_jidx'] == null) {
                                $idx003 = '';
                                for ($k = 0; $k < count($str_arr); $k++) {
                                    $sql001 = "SELECT q_question FROM question WHERE q_idx = '" . $str_arr[$k] . "'";
                                    $rst001 = mysqli_query($conn_iv_15, $sql001);
                                    $row001 = mysqli_fetch_array($rst001);

                                    //array_push($queArr, $row001['q_question']);
                                    $sql002 = "SELECT idx FROM iv_question WHERE que_question = '" . $row001['q_question'] . "' AND que_type ='B'";
                                    $rst002 = mysqli_query($conn_iv_20, $sql002);
                                    $row002 = mysqli_fetch_array($rst002);
                                    $bar = ',';
                                    if ($k == count($str_arr) - 1) $bar = '';

                                    if ($row002['idx'] > 0) {
                                        $idx003 .= $row002['idx'] . $bar;
                                    } else {
                                        $sql003 = "INSERT INTO iv_question (que_question, que_type, delyn) VALUES 
													('{$row001['q_question']}', 'B','N')";

                                        if (!$rst003 = mysqli_query($conn_iv_20, $sql003)) {
                                            $error003 = mysqli_error($conn_iv_20);

                                            return_error('003', 'DB 2222', $error003, $sql003);
                                            return;
                                        } else {
                                            $idx003 .= mysqli_insert_id($conn_iv_20) . $bar;
                                        }
                                    }
                                }
                            } else {
                                $idx003 = $row1['i_question_list'];
                            }
                            if ($i_idx == '1') {
                                $sql2 = "SELECT * FROM iv_interview WHERE idx = '1'";
                            } else {
                                $sql2 = "INSERT INTO iv_interview SET
											mem_idx = '" . $row3['idx'] . "',
											inter_answer_time = '" . $answer_time . "',
											inter_name = '" . $i_title . "',
											inter_type = 'C',
											inter_opportunity_yn = '" . $opportunity . "',
											inter_question = '" . $idx003 . "',
											inter_memo = '" . $i_memo . "',
											inter_reg_date = '" . $i_date . "'
										";
                            }

                            //없음 iv_interview, iv_interview_info, iv_company_suggest, iv_company_suggest_applicant insert
                            $rst2 = mysqli_query($conn_iv_20, $sql2);
                            if ($rst2) {
                                if ($i_idx == '1') {
                                    $row2 = mysqli_fetch_assoc($rst2);
                                    if ($row2['idx'] > 0) {
                                        $last_inter_idx = $row2['idx'];
                                    }
                                } else {
                                    $last_inter_idx = mysqli_insert_id($conn_iv_20);

                                    $sql900 = "INSERT INTO iv_interview_info SET
                                    com_idx = '" . $row3['com_idx'] . "',
                                    reg_mem_idx = '" . $row3['idx'] . "',
                                    inter_idx = '" . $last_inter_idx . "',
                                    info_name = '" . $manager . "',
                                    info_tel = '" . $manager_phone . "',
                                    info_start_date = '" . $newStartDate . "',
                                    info_end_date = '" . $newEndDate . "',
                                    info_reg_date = '" . $reg_date . "'
                                    ";
                                    $rst900 = mysqli_query($conn_iv_20, $sql900);
                                }


                                //iv_company_suggest, iv_company_suggest_applicant insert
                                $sql30 = "INSERT INTO iv_company_suggest SET
											inter_idx = '" . $last_inter_idx . "',
											com_idx = '" . $row3['com_idx'] . "',
											sug_type = '" . $sug_type . "',
											sug_manager_name = '" . $manager . "',
											sug_manager_tel = '" . $manager_phone . "',
											sug_tel = '" . $manager_phone . "',
											sug_reg_date = '" . $reg_date . "',
											sug_start_date = '" . $newStartDate . "',
											sug_end_date = '" . $newEndDate . "',
                                            sug_start_time = '" . $newStartTime . "',
											sug_end_time = '" . $newEndTime . "'
										";
                                $rst30 = mysqli_query($conn_iv_20, $sql30);
                                if ($rst30) {
                                    $last_sug_idx = mysqli_insert_id($conn_iv_20);

                                    $sql40 = "INSERT INTO iv_company_suggest_applicant SET
														inter_idx = '" . $last_inter_idx . "',
														sug_idx = '" . $last_sug_idx . "',
														sug_app_title = '" . $ap_title . "',
														sug_app_name = '" . $ap_name . "',
														sug_app_phone = '" . $ap_phone . "',
														old_ap_idx = '" . $ap_idx . "',
                                                        job_idx = '" . $job_idx . "',
														sug_app_reg_date = NOW()
													";
                                    $rst40 = mysqli_query($conn_iv_20, $sql40);
                                    $last_ap_idx = mysqli_insert_id($conn_iv_20);
                                }
                            }
                        }
                    }
                } else {
                    //없음 iv_company ,iv_interview, iv_interview_info, iv_company_suggest, iv_company_suggest_applicant insert
                    $sql0031 = "INSERT INTO iv_company SET 
										com_name = '" . $c_name . "',
										com_reg_number = '" . $c_number . "',
										com_address = '" . $c_address . "',
										com_ceo_name = '" . $c_ceo_name . "',
										com_reg_date = '" . $c_resigter_date . "'
							";

                    if (!$rst0031 = mysqli_query($conn_iv_20, $sql0031)) {
                        $error0031 = mysqli_error($conn_iv_20);

                        return_error('003', '333333', $error0031, $sql0031);
                        return;
                    } else {
                        $idx0031 = mysqli_insert_id($conn_iv_20);

                        $sql0011 = "SELECT idx,pay_end_date FROM iv_pay WHERE pay_coupon = '" . $c_serial_number . "'";
                        $rst0011 = mysqli_query($conn_iv_20, $sql0011);
                        $row0011 = mysqli_fetch_array($rst0011);

                        $sql0032 = "INSERT INTO iv_pay_service SET 
										com_idx = '" . $idx0031 . "',
										pay_idx = '" . $row0011['idx'] . "',
										pay_ser_use_count = '" . $c_cp_use_cnt . "',
										pay_ser_end_date = '" . $row0011['pay_end_date'] . "',
										pay_ser_reg_date = '" . $c_resigter_date . "'
								";
                        $rst0032 = mysqli_query($conn_iv_20, $sql0032);
                        $row0032 = mysqli_fetch_array($rst0032);
                    }

                    //1.5 question -> 2.0 iv_question 있는지 확인
                    $str_arr = explode(',', $row1['i_question_list']);
                    if ($row1['ap_jidx'] == '' || $row1['ap_jidx'] == null) {
                        $idx003 = '';
                        for ($k = 0; $k < count($str_arr); $k++) {
                            $sql001 = "SELECT q_question FROM question WHERE q_idx = '" . $str_arr[$k] . "'";
                            $rst001 = mysqli_query($conn_iv_15, $sql001);
                            $row001 = mysqli_fetch_array($rst001);

                            //array_push($queArr, $row001['q_question']);
                            $sql002 = "SELECT idx FROM iv_question WHERE que_question = '" . $row001['q_question'] . "' AND que_type ='B'";
                            $rst002 = mysqli_query($conn_iv_20, $sql002);
                            $row002 = mysqli_fetch_array($rst002);
                            $bar = ',';
                            if ($k == count($str_arr) - 1) $bar = '';

                            if ($row002['idx'] > 0) {
                                $idx003 .= $row002['idx'] . $bar;
                            } else {
                                $sql003 = "INSERT INTO iv_question (que_question, que_type, delyn) VALUES 
											('{$row001['q_question']}', 'B','N')";
                                if (!$rst003 = mysqli_query($conn_iv_20, $sql003)) {
                                    $error003 = mysqli_error($conn_iv_20);

                                    return_error('003', '444444', $error003, $sql003);
                                    return;
                                } else {
                                    $idx003 .= mysqli_insert_id($conn_iv_20) . $bar;
                                }
                            }
                        }
                    } else {
                        $idx003 = $row1['i_question_list'];
                    }
                    if ($i_idx == '1') {
                        $sql2 = "SELECT * FROM iv_interview WHERE idx = '1'";
                    } else {
                        //없음 iv_interview, iv_interview_info, iv_company_suggest, iv_company_suggest_applicant insert
                        $sql2 = "INSERT INTO iv_interview SET
									mem_idx = '" . $row3['idx'] . "',
									inter_answer_time = '" . $answer_time . "',
									inter_name = '" . $i_title . "',
									inter_type = 'C',
									inter_opportunity_yn = '" . $opportunity . "',
									inter_question = '" . $idx003 . "',
									inter_memo = '" . $i_memo . "',
									inter_reg_date = '" . $i_date . "'
								";
                    }
                    $rst2 = mysqli_query($conn_iv_20, $sql2);
                    if ($rst2) {
                        if ($i_idx == '1') {
                            $row2 = mysqli_fetch_assoc($rst2);
                            if ($row2['idx'] > 0) {
                                $last_inter_idx = $row2['idx'];
                            }
                        } else {
                            $last_inter_idx = mysqli_insert_id($conn_iv_20);
                            $sql900 = "INSERT INTO iv_interview_info SET
										com_idx = '" . $row3['com_idx'] . "',
										reg_mem_idx = '" . $row3['idx'] . "',
										inter_idx = '" . $last_inter_idx . "',
										info_name = '" . $manager . "',
										info_tel = '" . $manager_phone . "',
										info_start_date = '" . $newStartDate . "',
										info_end_date = '" . $newEndDate . "',
										info_reg_date = '" . $reg_date . "'
										";
                            $rst900 = mysqli_query($conn_iv_20, $sql900);
                        }
                        //iv_company_suggest, iv_company_suggest_applicant insert
                        $sql30 = "INSERT INTO iv_company_suggest SET
										inter_idx = '" . $last_inter_idx . "',
										com_idx = '" . $row3['com_idx'] . "',
										sug_type = '" . $sug_type . "',
										sug_manager_name = '" . $manager . "',
										sug_manager_tel = '" . $manager_phone . "',
										sug_tel = '" . $manager_phone . "',
										sug_reg_date = '" . $reg_date . "',
										sug_start_date = '" . $newStartDate . "',
										sug_end_date = '" . $newEndDate . "',
                                        sug_start_time = '" . $newStartTime . "',
                                        sug_end_time = '" . $newEndTime . "'
									";
                        $rst30 = mysqli_query($conn_iv_20, $sql30);
                        if ($rst30) {
                            $last_sug_idx = mysqli_insert_id($conn_iv_20);
                            $sql40 = "INSERT INTO iv_company_suggest_applicant SET
												inter_idx = '" . $last_inter_idx . "',
												sug_idx = '" . $last_sug_idx . "',
												sug_app_title = '" . $ap_title . "',
												sug_app_name = '" . $ap_name . "',
												sug_app_phone = '" . $ap_phone . "',
												old_ap_idx = '" . $ap_idx . "',
                                                job_idx = '" . $job_idx . "',
												sug_app_reg_date = NOW()
											";
                            $rst40 = mysqli_query($conn_iv_20, $sql40);
                            $last_ap_idx = mysqli_insert_id($conn_iv_20);
                        }
                    }
                }
            } else {
                $sql0021 = "SELECT idx FROM iv_company WHERE com_name = '" . $c_name . "' AND 
						com_reg_number = '" . $c_number . "' AND com_address = '" . $c_address . "'";
                $rst0021 = mysqli_query($conn_iv_20, $sql0021);
                $row0021 = mysqli_fetch_array($rst0021);

                if ($row0021['idx'] > 0) {

                    $sql2 = "INSERT INTO iv_member SET
										com_idx = '" . $row0021['idx'] . "',
										mem_type = 'C',
										mem_id = '" . $m_id . "',
										mem_password = '" . $m_pwd . "',
										mem_name = '" . $m_name . "',
										mem_tel = '" . $m_mobile . "',
										mem_visit_date = '" . $m_visit_date . "',
										mem_reg_date = '" . $m_resigter_date . "',
										mem_email = '" . $m_mail . "'
									";
                    $rst2 = mysqli_query($conn_iv_20, $sql2);
                    if ($rst2) {
                        $mem_idx = mysqli_insert_id($conn_iv_20);
                    }

                    //1.5 question -> 2.0 iv_question 있는지 확인
                    $str_arr = explode(',', $row1['i_question_list']);
                    if ($row1['ap_jidx'] == '' || $row1['ap_jidx'] == null) {
                        $idx003 = '';
                        for ($k = 0; $k < count($str_arr); $k++) {
                            $sql001 = "SELECT q_question FROM question WHERE q_idx = '" . $str_arr[$k] . "'";
                            $rst001 = mysqli_query($conn_iv_15, $sql001);
                            $row001 = mysqli_fetch_array($rst001);
                            //array_push($queArr, $row001['q_question']);
                            $sql002 = "SELECT idx FROM iv_question WHERE que_question = '" . $row001['q_question'] . "' AND que_type ='B'";
                            $rst002 = mysqli_query($conn_iv_20, $sql002);
                            $row002 = mysqli_fetch_array($rst002);
                            $bar = ',';
                            if ($k == count($str_arr) - 1) $bar = '';

                            if ($row002['idx'] > 0) {
                                $idx003 .= $row002['idx'] . $bar;
                            } else {
                                $sql003 = "INSERT INTO iv_question (que_question, que_type, delyn) VALUES 
											('{$row001['q_question']}', 'B','N')";
                                if (!$rst003 = mysqli_query($conn_iv_20, $sql003)) {
                                    $error003 = mysqli_error($conn_iv_20);

                                    return_error('003', '55555', $error003, $sql003);
                                    return;
                                } else {
                                    $idx003 .= mysqli_insert_id($conn_iv_20) . $bar;
                                }
                            }
                        }
                    } else {
                        $idx003 = $row1['i_question_list'];
                    }
                    if ($i_idx == '1') {
                        $sql2 = "SELECT * FROM iv_interview WHERE idx = '1'";
                    } else {
                        //없음 iv_interview, iv_interview_info, iv_company_suggest, iv_company_suggest_applicant insert
                        $sql2 = "INSERT INTO iv_interview SET
									mem_idx = '" . $mem_idx . "',
									inter_answer_time = '" . $answer_time . "',
									inter_name = '" . $i_title . "',
									inter_type = 'C',
									inter_opportunity_yn = '" . $opportunity . "',
									inter_question = '" . $idx003 . "',
									inter_memo = '" . $i_memo . "',
									inter_reg_date = '" . $i_date . "'
									";
                    }
                    $rst2 = mysqli_query($conn_iv_20, $sql2);
                    if ($rst2) {
                        if ($i_idx == '1') {
                            $row2 = mysqli_fetch_assoc($rst2);
                            if ($row2['idx'] > 0) {
                                $last_inter_idx = $row2['idx'];
                            }
                        } else {
                            $last_inter_idx = mysqli_insert_id($conn_iv_20);
                            $sql900 = "INSERT INTO iv_interview_info SET
										com_idx = '" . $row0021['com_idx'] . "',
										reg_mem_idx = '" . $row3['idx'] . "',
										inter_idx = '" . $last_inter_idx . "',
										info_name = '" . $manager . "',
										info_tel = '" . $manager_phone . "',
										info_start_date = '" . $newStartDate . "',
										info_end_date = '" . $newEndDate . "',
										info_reg_date = '" . $reg_date . "'
										";
                            $rst900 = mysqli_query($conn_iv_20_webtest, $sql900);
                        }
                        //iv_company_suggest, iv_company_suggest_applicant insert
                        $sql30 = "INSERT INTO iv_company_suggest SET
									inter_idx = '" . $last_inter_idx . "',
									com_idx = '" . $row0021['com_idx'] . "',
									sug_type = '" . $sug_type . "',
									sug_manager_name = '" . $manager . "',
									sug_manager_tel = '" . $manager_phone . "',
									sug_tel = '" . $manager_phone . "',
									sug_reg_date = '" . $reg_date . "',
									sug_start_date = '" . $newStartDate . "',
									sug_end_date = '" . $newEndDate . "',
                                    sug_start_time = '" . $newStartTime . "',
                                    sug_end_time = '" . $newEndTime . "'
								";
                        $rst30 = mysqli_query($conn_iv_20, $sql30);
                        if ($rst30) {
                            $last_sug_idx = mysqli_insert_id($conn_iv_20);
                            $sql40 = "INSERT INTO iv_company_suggest_applicant SET
												inter_idx = '" . $last_inter_idx . "',
												sug_idx = '" . $last_sug_idx . "',
												sug_app_title = '" . $ap_title . "',
												sug_app_name = '" . $ap_name . "',
												sug_app_phone = '" . $ap_phone . "',
												old_ap_idx = '" . $ap_idx . "',
                                                job_idx = '" . $job_idx . "',
												sug_app_reg_date = NOW()
											";
                            $rst40 = mysqli_query($conn_iv_20, $sql40);
                            $last_ap_idx = mysqli_insert_id($conn_iv_20);
                        }
                    }
                } else {
                    //없음 iv_member, iv_company, iv_interview, iv_interview_info, iv_company_suggest, iv_company_suggest_applicant insert

                    $sql0031 = "INSERT INTO iv_company SET 
										com_name = '" . $c_name . "',
										com_reg_number = '" . $c_number . "',
										com_address = '" . $c_address . "',
										com_ceo_name = '" . $c_ceo_name . "',
										com_reg_date = '" . $c_resigter_date . "'
							";

                    if (!$rst0031 = mysqli_query($conn_iv_20, $sql0031)) {
                        $error0031 = mysqli_error($conn_iv_20);

                        return_error('003', '6666', $error0031, $sql0031);
                        return;
                    } else {
                        $idx0031 = mysqli_insert_id($conn_iv_20);

                        $sql2 = "INSERT INTO iv_member SET
											com_idx = '" . $idx0031 . "',
											mem_type = 'C',
											mem_id = '" . $m_id . "',
											mem_password = '" . $m_pwd . "',
											mem_name = '" . $m_name . "',
											mem_tel = '" . $m_mobile . "',
											mem_visit_date = '" . $m_visit_date . "',
											mem_reg_date = '" . $m_resigter_date . "',
											mem_email = '" . $m_mail . "'
										";
                        $rst2 = mysqli_query($conn_iv_20, $sql2);
                        if ($rst2) {
                            $mem_idx = mysqli_insert_id($conn_iv_20);
                            $sql2 = "UPDATE iv_company SET
											mem_idx = '" . $mem_idx . "' WHERE idx = '" . $idx0031 . "';
										";
                            $rst2 = mysqli_query($conn_iv_20, $sql2);
                        }

                        $sql0011 = "SELECT idx,pay_end_date FROM iv_pay WHERE pay_coupon = '" . $c_serial_number . "'";
                        $rst0011 = mysqli_query($conn_iv_20, $sql0011);
                        $row0011 = mysqli_fetch_array($rst0011);

                        $sql0032 = "INSERT INTO iv_pay_service SET 
										com_idx = '" . $idx0031 . "',
										pay_idx = '" . $row0011['idx'] . "',
										pay_ser_use_count = '" . $c_cp_use_cnt . "',
										pay_ser_end_date = '" . $row0011['pay_end_date'] . "',
										pay_ser_reg_date = '" . $c_resigter_date . "'
								";
                        $rst0032 = mysqli_query($conn_iv_20, $sql0032);
                        $row0032 = mysqli_fetch_array($rst0032);
                    }

                    //1.5 question -> 2.0 iv_question 있는지 확인
                    $str_arr = explode(',', $row1['i_question_list']);
                    if ($row1['ap_jidx'] == '' || $row1['ap_jidx'] == null) {
                        $idx003 = '';
                        for ($k = 0; $k < count($str_arr); $k++) {
                            $sql001 = "SELECT q_question FROM question WHERE q_idx = '" . $str_arr[$k] . "'";
                            $rst001 = mysqli_query($conn_iv_15, $sql001);
                            $row001 = mysqli_fetch_array($rst001);

                            //array_push($queArr, $row001['q_question']);
                            $sql002 = "SELECT idx FROM iv_question WHERE que_question = '" . $row001['q_question'] . "' AND que_type ='B'";
                            $rst002 = mysqli_query($conn_iv_20, $sql002);
                            $row002 = mysqli_fetch_array($rst002);
                            $bar = ',';
                            if ($k == count($str_arr) - 1) $bar = '';
                            if ($row002['idx'] > 0) {
                                $idx003 .= $row002['idx'] . $bar;
                            } else {
                                $sql003 = "INSERT INTO iv_question (que_question, que_type, delyn) VALUES 
											('{$row001['q_question']}', 'B','N')";
                                if (!$rst003 = mysqli_query($conn_iv_20, $sql003)) {
                                    $error003 = mysqli_error($conn_iv_20);

                                    return_error('003', '677777', $error003, $sql003);
                                    return;
                                } else {
                                    $idx003 .= mysqli_insert_id($conn_iv_20) . $bar;
                                }
                            }
                        }
                    } else {
                        $idx003 = $row1['i_question_list'];
                    }
                    if ($i_idx == '1') {
                        $sql2 = "SELECT * FROM iv_interview WHERE idx = '1'";
                    } else {
                        //없음 iv_interview, iv_interview_info, iv_company_suggest, iv_company_suggest_applicant insert
                        $sql2 = "INSERT INTO iv_interview SET
									mem_idx = '" . $mem_idx . "',
									inter_answer_time = '" . $answer_time . "',
									inter_name = '" . $i_title . "',
									inter_type = 'C',
									inter_opportunity_yn = '" . $opportunity . "',
									inter_question = '" . $idx003 . "',
									inter_memo = '" . $i_memo . "',
									inter_reg_date = '" . $i_date . "'
								";
                    }
                    $rst2 = mysqli_query($conn_iv_20, $sql2);
                    if ($rst2) {
                        if ($i_idx == '1') {
                            $row2 = mysqli_fetch_assoc($rst2);
                            if ($row2['idx'] > 0) {
                                $last_inter_idx = $row2['idx'];
                            }
                        } else {
                            $last_inter_idx = mysqli_insert_id($conn_iv_20);
                            $sql900 = "INSERT INTO iv_interview_info SET
										com_idx = '" . $idx0031 . "',
										reg_mem_idx = '" . $mem_idx . "',
										inter_idx = '" . $last_inter_idx . "',
										info_name = '" . $manager . "',
										info_tel = '" . $manager_phone . "',
										info_start_date = '" . $newStartDate . "',
										info_end_date = '" . $newEndDate . "',
										info_reg_date = '" . $reg_date . "'
										";
                            $rst900 = mysqli_query($conn_iv_20_webtest, $sql900);
                        }
                        //iv_company_suggest, iv_company_suggest_applicant insert
                        $sql30 = "INSERT INTO iv_company_suggest SET
										inter_idx = '" . $last_inter_idx . "',
										com_idx = '" . $idx0031 . "',
										sug_type = '" . $sug_type . "',
										sug_manager_name = '" . $manager . "',
										sug_manager_tel = '" . $manager_phone . "',
										sug_tel = '" . $manager_phone . "',
										sug_reg_date = '" . $reg_date . "',
										sug_start_date = '" . $newStartDate . "',
										sug_end_date = '" . $newEndDate . "',
                                        sug_start_time = '" . $newStartTime . "',
                                        sug_end_time = '" . $newEndTime . "'
									";
                        $rst30 = mysqli_query($conn_iv_20, $sql30);
                        if ($rst30) {
                            $last_sug_idx = mysqli_insert_id($conn_iv_20);
                            $sql40 = "INSERT INTO iv_company_suggest_applicant SET
											inter_idx = '" . $last_inter_idx . "',
											sug_idx = '" . $last_sug_idx . "',
											sug_app_title = '" . $ap_title . "',
											sug_app_name = '" . $ap_name . "',
											sug_app_phone = '" . $ap_phone . "',
											old_ap_idx = '" . $ap_idx . "',
                                            job_idx = '" . $job_idx . "',
											sug_app_reg_date = NOW()
											";
                            $rst40 = mysqli_query($conn_iv_20, $sql40);
                            $last_ap_idx = mysqli_insert_id($conn_iv_20);
                        }
                    }
                }
            }
        }
    }
    $response_data = array(
        "status" => 200,
        "ap_idx" => $last_ap_idx,
        "msg" => "ok",
    );
    return_response($response_data);
} else if ($type == 'end') {

    //$encrpt_ap_idx = setEncrypt222($ap_idx, "bluevisorencrypt");
    $encrpt_ap_idx = base64url_encode(opensslEncrypt(json_encode($ap_idx)));
    //$report_url = 'https://interview.highbuff.com/report/detail/' . $encrpt_ap_idx;
    //https://interview.highbuff.com/company/itv_view.php?index=bHRkRVlIVUZZR0hZUVQ1dVdlRG9tQT09
    //$report_url = 'https://localinterviewr.highbuff.com/report/detail/'.$encrpt_ap_idx;
    //인터뷰 상태 확인
    $sql0 = "SELECT app_iv_stat FROM iv_applier WHERE idx = " . $ap_idx;
    if (!$rst0 = mysqli_query($conn_iv_20, $sql0)) {
        $error0 = mysqli_error($conn_iv_20);
        return_error('0', 'DB select 0', $error0, $sql0);
        return;
    } else {
        $row0 = mysqli_fetch_assoc($rst0);
    }
    //인터뷰 완료
    //2.0 db SELECT
    //IV_COMPANY_SUGGEST_APPLICANT
    $sql1 = "SELECT *, a.idx ap_idx, b.idx i_idx, c.idx m_idx, d.idx c_idx FROM iv_company_suggest_applicant a
			LEFT JOIN iv_interview b
			ON a.inter_idx = b.idx
			LEFT JOIN iv_member c
			ON b.mem_idx = c.idx
			LEFT JOIN iv_company d
			ON c.com_idx = d.idx
			WHERE a.app_idx = " . $ap_idx;

    if (!$rst1 = mysqli_query($conn_iv_20, $sql1)) {
        $error1 = mysqli_error($conn_iv_20);
        return_error('1', 'DB select 1', $error1, $sql1);
        return;
    } else {
        $row1 = mysqli_fetch_assoc($rst1);

        if ($row0['app_iv_stat'] == 5) {
            $report_url = '분석불가';
        } else {
            $report_url = 'https://interview.highbuff.com/company/itv_view.php?index=' . setEncrypt222($row1['old_ap_idx'], "bluevisorencrypt");
        }
        //1.5 db SELECT
        //APPLICANT AP_IDX 
        $sql2 = "SELECT *, d.c_idx com_idx FROM applicant a 
				LEFT JOIN final_interview b 
				ON a.f_idx = b.f_idx 
				LEFT JOIN interview c 
				ON b.i_idx = c.i_idx
				LEFT JOIN company d
				ON c.c_idx = d.c_idx
				LEFT JOIN member e
				ON b.f_writer = e.m_id
				WHERE a.ap_idx = '{$row1['old_ap_idx']}'";

        if (!$rst2 = mysqli_query($conn_iv_15, $sql2)) {
            $error2 = mysqli_error($conn_iv_15);
            return_error('2', 'DB select 2', $error2, $sql2);
            return;
        } else {
            $row2 = mysqli_fetch_assoc($rst2);
            echo $row2['ap_idx'];
            //  1. INTERVIEW_MANAGER db의 APPLICANT  TABLE에 면접 상태 업데이트 
            //         UPDATE APPLICANT SET  AP_STATUS ='a.i. 면접 완료' , AP_AI_RESULT ='레포트 url'  WHERE AP_IDX='사용자기본키'
            $sql3 = "UPDATE applicant SET  ap_status ='A.I. 면접 완료' , ap_ai_result ='{$report_url}' , ap_ai_result_date = CURRENT_TIMESTAMP(6)  WHERE ap_idx='{$row2['ap_idx']}'";
            if (!$rst3 = mysqli_query($conn_iv_15, $sql3)) {
                $error3 = mysqli_error($conn_iv_15);
                return_error('3', 'DB update 3', $error3, $sql3);
                return;
            } else {
                echo '1완료';
                //  2. 면접url INTERVIEW_MANAGER db의 COUPON  TABLE에 이용권횟수 차감 
                //        UPDATE  COMPANY SET  C_CP_USE_CNT =  C_CP_USE_CNT +1  WHERE C_IDX= '회사기본키'
                //        *C_IDX는  APPLICANT  TABLE에 있습니다.
                if ($row0['app_iv_stat'] == 5) { // 분석불가일때 이용권 미 카운팅
                } else {
                    $sql4 = "UPDATE  company SET  c_cp_use_cnt =  c_cp_use_cnt+1  WHERE c_idx = '{$row2['com_idx']}'";
                    if (!$rst4 = mysqli_query($conn_iv_15, $sql4)) {
                        $error4 = mysqli_error($conn_iv_15);
                        return_error('4', 'DB update 4', $error4, $sql4);
                        return;
                    } else {
                        echo '2완료';
                    }
                }
            }
            return $row2['ap_idx'];
        }
    }
} else if ($type == 'reload') {

    //https://interview.highbuff.com/company/itv_view.php?index=UUthQjhSWjh6RlNYZStiNGUrY2FhZz09&c_idx=46
    //https://localinterviewr.highbuff.com/company/itv_view.php?index=bHRkRVlIVUZZR0hZUVQ1dVdlRG9tQT09&c_idx=57
    //https://localinterviewr.highbuff.com/company/itv_view.php?index=UEtyakM1M3JLeVlQeHBlYnQ3b0N6Zz09&c_idx=56
    //https://interview.highbuff.com/company/itv_view.php?index=RmZUa0c5YWlFNFRmQnFCTXBpRVdQUT09&c_idx=56
    //echo $ap_idx;
    $sql0 = "SELECT ap_idx FROM applicant WHERE ap_idx =  " . $ap_idx;

    if (!$rst0 = mysqli_query($conn_iv_15, $sql0)) {
        // $error0 = mysqli_error($conn_iv_15);
        // return_error('1', 'DB select 1', $error0, $sql0);
        // return;
        $row0['ap_idx'] = 0;
    } else {
        $row0 = mysqli_fetch_assoc($rst0);
    }

    if ($row0['ap_idx'] > 0) {

        $sql1 = "SELECT * FROM applicant a 
			LEFT JOIN final_interview b 
			ON a.f_idx = b.f_idx 
			LEFT JOIN interview c 
			ON b.i_idx = c.i_idx
			LEFT JOIN company d
			ON c.c_idx = d.c_idx
			LEFT JOIN member e
			ON b.f_writer = e.m_id
			WHERE a.ap_idx =  " . $row0['ap_idx'];


        if (!$rst1 = mysqli_query($conn_iv_15, $sql1)) {
            $error1 = mysqli_error($conn_iv_15);
            return_error('1', 'DB select 12', $error1, $sql1);
            return;
        } else {
            $row1 = mysqli_fetch_assoc($rst1);
            //2.0 DB select
            //iv_company_suggest_applicant

            $sql2 = "SELECT *, a.idx ap_idx, b.idx i_idx, c.idx m_idx, d.idx c_idx 
				FROM iv_company_suggest_applicant a
				LEFT JOIN iv_interview b
				ON a.inter_idx = b.idx
				LEFT JOIN iv_member c
				ON b.mem_idx = c.idx
				LEFT JOIN iv_company d
				ON c.com_idx = d.idx
				WHERE a.old_ap_idx = " . $ap_idx;

            if (!$rst2 = mysqli_query($conn_iv_20, $sql2)) {
                $error2 = mysqli_error($conn_iv_20);
                return_error('2', 'DB select 3', $error2, $sql2);
                return;
            } else {
                $row2 = mysqli_fetch_assoc($rst2);
                if ($row2['ap_idx'] > 0) {
                    //있을때
                    //$encrpt_ap_idx = setEncrypt222($row2['ap_idx'], "bluevisorencrypt");
                    $encrpt_ap_idx = base64url_encode(opensslEncrypt(json_encode($row2['app_idx'])));
                    $report_url = 'https://interview.highbuff.com/report/detail2/' . $encrpt_ap_idx;
                    //$report_url = 'https://localinterviewr.highbuff.com/report/detail/'.$encrpt_ap_idx;
                    //echo $report_url;
                    //echo $row2['app_idx'];
                    $response_data = array(
                        "status" => 200,
                        "report_url" => $report_url,
                        "mem_id" => $row2['mem_id'],
                        "app_idx" => $row2['app_idx'],
                        "msg" => "ok",
                    );
                    return_response($response_data);
                } else {
                    //echo 'error';
                    //     $response_data = array(
                    //         "status" => 400,
                    //         "msg" => "error11",
                    //     );
                    //     return_response($response_data);

                    $sql04 = "SELECT * FROM iv_applier WHERE ap_idx =  " . $row0['ap_idx'];

                    if (!$rst04 = mysqli_query($conn_iv, $sql04)) {
                        $error04 = mysqli_error($conn_iv);
                        return_error('1', 'DB select 1', $error04, $sql04);
                        return;
                    } else {
                        $row04 = mysqli_fetch_assoc($rst04);
                    }
                    if ($row04['idx'] > 0) {
                        $sql21 = "SELECT a.idx, b.mem_id FROM iv_applier a LEFT JOIN iv_member b ON a.mem_idx = b.idx
                        WHERE b.mem_id = '{$row04['user_id']}' AND a.app_reg_date = '{$row04['dates']}'";

                        if (!$rst21 = mysqli_query($conn_iv_20, $sql21)) {
                            $error2 = mysqli_error($conn_iv_20);
                            return_error('2', 'DB select 2', $error2, $sql21);
                            return;
                        } else {
                            $row21 = mysqli_fetch_assoc($rst21);
                            if ($row21['idx'] > 0) {
                                //있을때
                                //$encrpt_ap_idx = setEncrypt222($row2['ap_idx'], "bluevisorencrypt");
                                $encrpt_ap_idx = base64url_encode(opensslEncrypt(json_encode($row21['idx'])));
                                $report_url = 'https://interview.highbuff.com/report/detail2/' . $encrpt_ap_idx;
                                //$report_url = 'https://localinterviewr.highbuff.com/report/detail/'.$encrpt_ap_idx;
                                //echo $report_url;
                                //echo $row2['app_idx'];
                                $response_data = array(
                                    "status" => 200,
                                    "report_url" => $report_url,
                                    "mem_id" => $row21['mem_id'],
                                    "app_idx" => $row21['idx'],
                                    "msg" => "ok",
                                );
                                return_response($response_data);
                            } else {
                                $response_data = array(
                                    "status" => 400,
                                    "msg" => "error22 - 2.0 applier 에 mem_idx 일치 없음",
                                );
                                return_response($response_data);
                            }
                        }
                    } else {
                        $response_data = array(
                            "status" => 400,
                            "msg" => "error44",
                        );
                        return_response($response_data);
                    }
                }
            }
        }
    } else {

        $sql04 = "SELECT * FROM iv_applier WHERE idx =  " . $ap_idx;

        if (!$rst04 = mysqli_query($conn_iv, $sql04)) {
            $error04 = mysqli_error($conn_iv);
            return_error('1', 'DB select 1', $error04, $sql04);
            return;
        } else {
            $row04 = mysqli_fetch_assoc($rst04);
        }
        if ($row04['idx'] > 0) {
            $sql21 = "SELECT a.idx, b.mem_id FROM iv_applier a LEFT JOIN iv_member b ON a.mem_idx = b.idx
                WHERE b.mem_id = '{$row04['user_id']}' AND a.app_reg_date = '{$row04['dates']}'";

            if (!$rst21 = mysqli_query($conn_iv_20, $sql21)) {
                $error2 = mysqli_error($conn_iv_20);
                return_error('2', 'DB select 2', $error2, $sql21);
                return;
            } else {
                $row21 = mysqli_fetch_assoc($rst21);
                if ($row21['idx'] > 0) {
                    //있을때
                    //$encrpt_ap_idx = setEncrypt222($row2['ap_idx'], "bluevisorencrypt");
                    $encrpt_ap_idx = base64url_encode(opensslEncrypt(json_encode($row21['idx'])));
                    $report_url = 'https://interview.highbuff.com/report/detail/' . $encrpt_ap_idx;
                    //$report_url = 'https://localinterviewr.highbuff.com/report/detail/'.$encrpt_ap_idx;
                    //echo $report_url;
                    //echo $row2['app_idx'];
                    $response_data = array(
                        "status" => 200,
                        "report_url" => $report_url,
                        "mem_id" => $row21['mem_id'],
                        "app_idx" => $row21['idx'],
                        "msg" => "ok",
                    );
                    return_response($response_data);
                } else {
                    $response_data = array(
                        "status" => 400,
                        "msg" => "error22",
                    );
                    return_response($response_data);
                }
            }
        } else {
            $response_data = array(
                "status" => 400,
                "msg" => "error44",
            );
            return_response($response_data);
        }
    }
} else if ($type == 'reload2') {

    //https://interview.highbuff.com/company/itv_view.php?index=UUthQjhSWjh6RlNYZStiNGUrY2FhZz09&c_idx=46
    //https://localinterviewr.highbuff.com/company/itv_view.php?index=bHRkRVlIVUZZR0hZUVQ1dVdlRG9tQT09&c_idx=57
    //https://localinterviewr.highbuff.com/company/itv_view.php?index=UEtyakM1M3JLeVlQeHBlYnQ3b0N6Zz09&c_idx=56
    //echo $ap_idx;
    $sql0 = "SELECT ap_idx FROM iv_applier WHERE idx =  " . $ap_idx;

    if (!$rst0 = mysqli_query($conn_iv, $sql0)) {
        $error0 = mysqli_error($conn_iv);
        return_error('1', 'DB select 1', $error0, $sql0);
        return;
    } else {
        $row0 = mysqli_fetch_assoc($rst0);
    }

    $sql1 = "SELECT * FROM applicant a 
			LEFT JOIN final_interview b 
			ON a.f_idx = b.f_idx 
			LEFT JOIN interview c 
			ON b.i_idx = c.i_idx
			LEFT JOIN company d
			ON c.c_idx = d.c_idx
			LEFT JOIN member e
			ON b.f_writer = e.m_id
			WHERE a.ap_idx =  " . $row0['ap_idx'];

    if (!$rst1 = mysqli_query($conn_iv_15, $sql1)) {
        $error1 = mysqli_error($conn_iv_15);
        return_error('1', 'DB select 1', $error1, $sql1);
        return;
    } else {
        $row1 = mysqli_fetch_assoc($rst1);
        //2.0 DB select
        //iv_company_suggest_applicant
        $sql2 = "SELECT *, a.idx ap_idx, b.idx i_idx, c.idx m_idx, d.idx c_idx 
				FROM iv_company_suggest_applicant a
				LEFT JOIN iv_interview b
				ON a.inter_idx = b.idx
				LEFT JOIN iv_member c
				ON b.mem_idx = c.idx
				LEFT JOIN iv_company d
				ON c.com_idx = d.idx
				WHERE a.old_ap_idx = " . $row0['ap_idx'];

        if (!$rst2 = mysqli_query($conn_iv_20, $sql2)) {
            $error2 = mysqli_error($conn_iv_20);
            return_error('2', 'DB select 2', $error2, $sql2);
            return;
        } else {
            $row2 = mysqli_fetch_assoc($rst2);
            if ($row2['ap_idx'] > 0) {
                //있을때
                $encrpt_ap_idx = setEncrypt222($row2['ap_idx'], "bluevisorencrypt");
                //$encrpt_ap_idx = base64url_encode(opensslEncrypt(json_encode($row2['app_idx'])));
                //$report_url = 'https://interview.highbuff.com/report/detail/'.$encrpt_ap_idx;
                //echo $report_url;
                //echo $row2['app_idx'];
                $response_data = array(
                    "status" => 200,
                    "encrpt_ap_idx" => $encrpt_ap_idx,
                    //		"report_url" => $report_url,
                    //		"mem_id" => $row2['mem_id'],
                    "msg" => "ok",
                );
                return_response($response_data);
            } else {
                //echo 'error';
                $response_data = array(
                    "status" => 400,
                    "msg" => "error33",
                );
                return_response($response_data);
            }
        }
    }
} else if ($type == 'apupdate') {
    $old_ap_idx = $_POST['old_ap_idx'];
    $sql4 = "UPDATE  iv_company_suggest_applicant SET  old_ap_idx =  " . $old_ap_idx . "  WHERE idx = '" . $ap_idx . "'";
    if (!$rst4 = mysqli_query($conn_iv_20, $sql4)) {
        $error4 = mysqli_error($conn_iv_20);
        return_error('4', 'DB update 4', $error4, $sql4);
        return;
    } else {
        echo '2완료';
    }
} else if ($type == 'restart') {
    $ag_req_reason = $_POST['ag_req_reason'];
    $com_idx = $_POST['com_idx'];


    $sql40 = "INSERT INTO config_again_request SET
				sug_app_idx = '" . $ap_idx . "',
				ag_req_reason = '" . $ag_req_reason . "',
				com_idx = '" . $com_idx . "',
				ag_req_com = 'N',
				delyn = 'N',
				ag_req_reg_date = NOW()
			";

    $rst40 = mysqli_query($conn_iv_20, $sql40);
    //$last_ap_idx = mysqli_insert_id($conn_iv_20);
    echo '1완료';
    $sql2 = "SELECT old_ap_idx FROM iv_company_suggest_applicant where idx = " . $ap_idx;

    if (!$rst2 = mysqli_query($conn_iv_20, $sql2)) {
        $error2 = mysqli_error($conn_iv_20);
        return_error('2', 'DB select 2', $error2, $sql2);
        return;
    } else {
        $row2 = mysqli_fetch_assoc($rst2);
        if ($row2['old_ap_idx'] > 0) {
            echo '2완료';
            $sql4 = "UPDATE applicant SET ap_ai_result ='재응시요청/" . $ag_req_reason . "' WHERE ap_idx='" . $row2['old_ap_idx'] . "'";

            if (!$rst4 = mysqli_query($conn_iv_15, $sql4)) {
                $error4 = mysqli_error($conn_iv_15);
                return_error('4', 'DB update 4', $error4, $sql4);
                return;
            } else {
                echo '3완료';
            }
        }
    }
}
