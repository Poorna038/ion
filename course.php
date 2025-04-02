<?php
/*
 * File Description: Controller Logic for Course Module(List, Add, Edit & Delete)
 */
?>

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Course extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('curriculum/course/course_model');
        $this->load->model('curriculum/course/addcourse_model');
        $this->load->model('session_model');
        $this->load->model('survey/import_student_data_model', '', TRUE);
		$this->lang_student_usn = $this->lang->line('student_usn');
        $this->CI = & get_instance();

        // Session implementation
		if(!isset($_SESSION) || ($_SESSION) == NULL) 
		{ 
			session_start();
		}
    }

    /* Function is used to check the user logged_in & his user group & to load course list view.
     * @param-
     * @retuns - the list view of all courses.
     */

    public function index($curriculum_id = '0') {
        //permission_start 
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $curriculum_id = base64_decode($curriculum_id);
            $data['std_crclm_data']=$this->course_model->fetch_crclm_list();
            $data['std_pgm_data']=$this->course_model->fetch_course_programs(); 
            $data['curriculum_id'] = $curriculum_id;
            $data['title'] = 'Course List Page';
            $this->load->view('curriculum/course/list_course_vw', $data);
        }
    }

    /* Function is used to load static list view of course.
     * @param-
     * @retuns - the static (read only) list view of all course details.
     */

    public function static_index() {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        }
        //permission_end 
        else {
            $crclm_list = $this->course_model->crclm_fill();
            $data['curriculum_data'] = $crclm_list['res2'];
            $data['title'] = 'Course List Page';
            $this->load->view('curriculum/course/static_list_course_vw', $data);
        }
    }

    /* Function is used to load the add view of course.
     * @param-
     * @returns - add view of course.
     */

    public function add_course() {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            if ((!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && $this->ion_auth->in_group('Course Owner'))) {
                $course_owner = $this->course_model->course_owner_login_details();
                $data['course_owner_name'] = $course_owner['title'] . ' ' . $course_owner['first_name'] . ' ' . $course_owner['last_name'];
                $data['course_owner_id'] = $course_owner['id'];
            }
            $data['crs_id'] = array(
                'name' => 'crs_id',
                'id' => 'crs_id',
                'class' => 'required ',
                'type' => 'hidden',
                'value' => '',
                'size' => '1'
            );
            $data['crclm_id'] = array(
                'name' => 'crclm_id',
                'id' => 'crclm_id',
                'class' => 'required input-medium',
                'type' => 'text',
                'placeholder' => 'Select Curriculum',
                'value' => '',
                'size' => '20',
                'onchange' => 'select_term();',
                'required' => ''
            );
            $data['crclm_term_id'] = array(
                'name' => 'crclm_term_id',
                'id' => 'crclm_term_id',
                'class' => 'required input-medium',
                'type' => 'text',
                'value' => '',
                'placeholder' => 'Select Term',
                'size' => '20',
                'readonly' => ''
            );
            $data['crs_type_id'] = array(
                'name' => 'crs_type_id',
                'id' => 'crs_type_id',
                'class' => 'required input-medium',
                'type' => 'text',
                'value' => '',
                'placeholder' => 'Select Course Type',
                'size' => '20',
                'required' => ''
            );
            $data['crs_domain_id'] = array(
                'name' => 'crs_domain_id',
                'id' => 'crs_domain_id',
                'class' => 'required input-medium',
                'type' => 'text',
                'value' => '',
                'placeholder' => 'Select Course Domain',
                'size' => '20',
                'required' => ''
            );
            $data['crs_code'] = array(
                'name' => 'crs_code',
                'id' => 'crs_code',
                'class' => 'input-medium noSpecialChars2',
                'type' => 'text',
                'placeholder' => 'Enter Course Code',
                'value' => '',
                'size' => '20',
                'required' => ''
            );
            $data['crs_mode'] = array(
                'name' => 'crs_mode',
                'id' => 'crs_mode',
                'class' => 'required ',
                'type' => 'hidden',
                'value' => '',
                'size' => '20'
            );
            $data['crs_title'] = array(
                'name' => 'crs_title',
                'id' => 'crs_title',
                'class' => 'input-medium',
                'type' => 'text',
                'placeholder' => 'Enter Course Title',
                'value' => '',
                'size' => '20',
                'required' => ''
            );
            $data['crs_acronym'] = array(
                'name' => 'crs_acronym',
                'id' => 'crs_acronym',
                'class' => 'required input-medium',
                'type' => 'text',
                'placeholder' => 'Enter Acronym',
                'value' => '',
                'size' => '20',
                'required' => ''
            );
            $data['co_crs_owner'] = array(
                'name' => 'co_crs_owner',
                'id' => 'co_crs_owner',
                'class' => '',
                'type' => 'textarea',
                'placeholder' => 'Enter Co-Course Owner Name(s)'
            );
            $data['clo_owner_id'] = array(
                'name' => 'clo_owner_id',
                'id' => 'clo_owner_id',
                'class' => 'required ',
                'type' => 'text',
                'placeholder' => 'Select User',
                'size' => '20',
                'value' => '',
                'required' => ''
            );
            $data['dept_id'] = array(
                'name' => 'dept_id',
                'id' => 'dept_id',
                'class' => 'required input-medium',
                'type' => 'text',
                'placeholder' => 'Select Reviewer Department ',
                'size' => '20',
                'value' => '',
                'required' => ''
            );
            $data['course_designer'] = array(
                'name' => 'course_designer',
                'id' => 'course_designer',
                'class' => 'required ',
                'type' => 'text',
                'placeholder' => 'Select User ',
                'size' => '20',
                'value' => '',
                'required' => ''
            );
            $data['course_reviewer'] = array(
                'name' => 'course_reviewer',
                'id' => 'course_reviewer',
                'class' => 'required span8',
                'type' => 'text',
                'placeholder' => 'Select User ',
                'size' => '20',
                'value' => '',
                'required' => ''
            );
            $data['last_date'] = array(
                'name' => 'last_date',
                'id' => 'last_date',
                'class' => 'required input-medium rightJustified ',
                'type' => 'text',
                'placeholder' => 'Enter Date ',
                'size' => '20',
                'value' => '',
                'required' => ''
            );
            $data['lect_credits'] = array(
                'name' => 'lect_credits',
                'id' => 'lect_credits',
                'class' => 'required onlyDigit rightJustified span2 credits',
                'type' => 'text',
                'placeholder' => '',
                'value' => '',
                'size' => '1'
            );
            $data['tutorial_credits'] = array(
                'name' => 'tutorial_credits',
                'id' => 'tutorial_credits',
                'class' => 'required span2 onlyDigit rightJustified credits',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['practical_credits'] = array(
                'name' => 'practical_credits',
                'id' => 'practical_credits',
                'class' => 'required onlyDigit span2   rightJustified credits',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['self_study_credits'] = array(
                'name' => 'self_study_credits',
                'id' => 'self_study_credits',
                'class' => 'required span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['total_credits'] = array(
                'name' => 'total_credits',
                'id' => 'total_credits',
                'class' => 'span2 onlyDigit rightJustified',
                'type' => 'text',
                'placeholder' => '',
                'size' => '1',
                'readonly' => ''
            );
            $data['contact_hours'] = array(
                'name' => 'contact_hours',
                'id' => 'contact_hours',
                'class' => 'required span2 onlyDigit rightJustified',
                'type' => 'text',
                'placeholder' => '',
                'value' => '',
                'size' => '1',
                'required' => ''
            );
            $data['cie_marks'] = array(
                'name' => 'cie_marks',
                'id' => 'cie_marks',
                'class' => 'required span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['mid_term_marks'] = array(
                'name' => 'mid_term_marks',
                'id' => 'mid_term_marks',
                'class' => 'required span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['see_marks'] = array(
                'name' => 'see_marks',
                'id' => 'see_marks',
                'class' => 'required span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['attendance_marks'] = array(
                'name' => 'attendance_marks',
                'id' => 'attendance_marks',
                'class' => 'span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1'
            );
            $data['ss_marks'] = array(
                'name' => 'ss_marks',
                'id' => 'ss_marks',
                'class' => 'required span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1',
                'required' => ''
            );
            $data['total_cia_weightage'] = array(
                'name' => 'total_cia_weightage',
                'id' => 'total_cia_weightage',
                'class' => 'span2 onlyDigit rightJustified total_wt allownumericwithdecimal',
                'value' => '',
                'placeholder' => '',
                'size' => '1'
            );
            $data['total_mte_weightage'] = array(
                'name' => 'total_mte_weightage',
                'id' => 'total_mte_weightage',
                'class' => 'span2 onlyDigit rightJustified total_wt',
                'value' => '',
                'placeholder' => '',
                'size' => '1'
            );
            $data['total_tee_weightage'] = array(
                'name' => 'total_tee_weightage',
                'id' => 'total_tee_weightage',
                'class' => 'span2 onlyDigit rightJustified total_wt',
                'value' => '',
                'placeholder' => '',
                'size' => '1'
            );
            $data['total_weightage'] = array(
                'name' => 'total_weightage',
                'id' => 'total_weightage',
                'class' => 'span2 onlyDigit rightJustified  percentage  credits_validation',
                'value' => '',
                'placeholder' => '',
                'size' => '5',
                'readonly' => ''
            );
            $data['total_marks'] = array(
                'name' => 'total_marks',
                'id' => 'total_marks',
                'class' => 'span2 onlyDigit rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '',
                'size' => '1'
            );
            $data['see_duration'] = array(
                'name' => 'see_duration',
                'id' => 'see_duration',
                'class' => 'required span2 durationRestrict rightJustified',
                'type' => 'text',
                'value' => '',
                'placeholder' => '0:00',
                'size' => '1',
                'required' => ''
            );

            $data['bloom_domain'] = $this->addcourse_model->get_all_bloom_domain();
            $data['std_crclm_data']=$this->course_model->fetch_crclm_list();
            $data['std_pgm_data']=$this->course_model->fetch_course_programs();  
            $data['departmentlist'] = $this->course_model->dropdown_department();
            $data['userlist'] = $this->course_model->dropdown_userlist();
            $data['courselist'] = $this->addcourse_model->dropdown_course_type_name();
            $data['mte_flag'] = $this->addcourse_model->fetch_organisation_data();
            $dept_id = $this->ion_auth->user()->row()->user_dept_id;
            $data['course_domain_list'] = $this->addcourse_model->dropdown_course_domain_name($dept_id);
            $data['termlist'] = $this->addcourse_model->dropdown_term_name();
            $data['ionlms_flag'] = $this->course_model->fetch_lms_flag();
            $val = $this->ion_auth->user()->row();
            $data['student_passing_marks'] = $val->org_name->student_passing_marks;
            $data['title'] = 'Course Add Page';
            $this->load->view('curriculum/course/add_course_vw', $data);
        }
    }

    public function fetch_clo_bl_flag() {
        $crclm_id = $this->input->post('crclm_id');
        $clo_bl_flag = $this->addcourse_model->fetch_clo_bl_flag_add($crclm_id);
        echo ($clo_bl_flag[0]['clo_bl_flag']);
    }

    public function fetch_clo_bl_flag_edit($crclm_id, $crs_id) {
        $clo_bl_flag = $this->addcourse_model->fetch_clo_bl_flag($crclm_id, $crs_id);
        return ($clo_bl_flag[0]['clo_bl_flag']);
    }

    /* Function is used to search a course from course table.
     * @param - 
     * @returns- a string value.
     */

    public function course_title_search() {
        $display_crs_code = $display_crs_title = '';

        $crclm_id = $this->input->post('crclm_id');
        $crs_code = $this->input->post('crs_code');
        $crs_title = $this->input->post('crs_title');
        $results = $this->addcourse_model->course_title_search($crclm_id, $crs_title, $crs_code);

        if (empty($results)) {
            echo json_encode(array('result'=>'valid'));
        } else {
            if(! empty($results[0]->crs_code))
            {
                $display_crs_code = $results[0]->crs_code;
            }
            else
            {
                $display_crs_title = $results[0]->crs_title;
            }

            echo json_encode(array('result'=>'invalid',
                                   'crs_code' => $display_crs_code,
                                   'crs_title' => $display_crs_title,
                                   'term_name'=>$results[0]->term_name));
        }
    }

    /* Function is used to search a course from course table.
     * @param - 
     * @returns- a string value.
     */

    public function course_title_search_edit() {
        $display_crs_code = $display_crs_title = '';

        $crs_id = $this->input->post('crs_id');
        $crclm_id = $this->input->post('crclm_id');
        $crs_code = $this->input->post('crs_code');
        $crs_title = $this->input->post('crs_title');
        $results = $this->course_model->course_title_search_edit($crclm_id, $crs_title, $crs_code, $crs_id);
        $data = $this->course_model->fetch_first_year_flag($crclm_id);
        $data_result = $this->course_model->fetch_course_domain($crs_id);
        
        if (empty($results)) {
            echo json_encode(array('result'=>'valid'));
        } else {
            if(! empty($results[0]->crs_code))
            {
                $display_crs_code = $results[0]->crs_code;
            }
            else
            {
                $display_crs_title = $results[0]->crs_title;
            }

            echo json_encode(array('result'=>'invalid',
                                   'crs_code' => $display_crs_code,
                                   'crs_title' => $display_crs_title,
                                   'term_name'=>$results[0]->term_name,
                                    'first_year_flag'=>$data,
                                    'crs_domain_id'=>$data_result));
        }
    }

    /* Function is used to fetch a curriculum id, term id & course id from course table.
     * @param - curriculum id.
     * @returns- an object.
     */

    public function course_details_by_crclm_id($crclm_id = NULL) {
        $course_details = $this->addcourse_model->course_details_by_crclm_id($crclm_id);
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($course_details));
    }

    /* Function is used to fetch a term id & term name from crclm_terms table.
     * @param - curriculum id.
     * @returns- an object.
     */

    public function term_details_by_crclm_id($crclm_id = NULL) {
        $data['termlist'] = $this->addcourse_model->dropdown_term_name();
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($data['termlist']));
    }

    /* Function is used to add a new course details.
     * @param-
     * @returns - updated list view of course.
     */

    public function insert_course() {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {

            $pre_courses_exploded = explode(",", $_POST['hidden-tags']);
            $pre_courses = implode('<>', $pre_courses_exploded);
            //course table data
            $crclm_id = $this->input->post('crclm_id');
            $crclm_term_id = $this->input->post('crclm_term_id');
            $crs_type_id = $this->input->post('crs_type_id');
            $crs_code = $this->input->post('crs_code');
            $crs_mode = $this->input->post('crs_mode');
            $crs_title = $this->input->post('crs_title');
            $crs_acronym = $this->input->post('crs_acronym');
            $co_crs_owner = $this->input->post('co_crs_owner');
            $crs_domain_id = $this->input->post('crs_domain_id');
            $lect_credits = $this->input->post('lect_credits');
            $tutorial_credits = $this->input->post('tutorial_credits');
            $practical_credits = $this->input->post('practical_credits');
            $self_study_credits = 0;
            $total_credits = $this->input->post('total_credits');
            $contact_hours = $this->input->post('contact_hours');
            $cie_marks = $this->input->post('cie_marks');
            $see_marks = $this->input->post('see_marks');
            $ss_marks = 0;
            $total_marks = $this->input->post('total_marks');
            $see_duration = $this->input->post('see_duration');
            //course_clo_owner
            $course_designer = $this->input->post('course_designer');
            //course_clo_reviewer
            $course_reviewer = $this->input->post('course_reviewer');
            $review_dept = $this->input->post('review_dept');
            $last_date = $this->input->post('last_date');
            $last_date = \PHPExcel_Style_NumberFormat::toFormattedString($last_date, 'DD-MM-YYYY');
            //predecessor courses array
            $pre_courses;
        }
    }

    /* Function is used to generate table of course details.
     * @param- 
     * @returns - an object.
     */

    public function display_course_details() {
        $crclm_id = $this->input->post('crclm_id');
        $crclm_term_id = $this->input->post('crclm_term_id');
        $course_data = $this->addcourse_model->course_details($crclm_id);
        $blank = ' ';
        $output = ' ';
        $term_id = ' ';
        $i = 1;
        $total = 0;
        $data['course_details'] = $course_data;
        $count = sizeof($data['course_details']);
        for ($k = 0; $k < $count; $k++) {
            if ($term_id != $data['course_details'][$k]['crclm_term_id']) {
                $output.= '<tr style="font-size:12px;">';
                $total = $data['course_details'][$k]['total_theory_courses'] + $data['course_details'][$k]['total_practical_courses'];
                $output.= '<td colspan="2" style="color : blue"><b>' . $data['course_details'][$k]['term_name'] . '</b></td>' .
                        '<td colspan="2" style="color : blue"><b>Term Total Courses:-  ' . $total . ' ( ' . $data['course_details'][$k]['total_theory_courses'] . '-(Theory) + ' . $data['course_details'][$k]['total_practical_courses'] . '-(Practical)' . ' )</b></td>' .
                        '<td colspan="5" style="color : blue"><b>Term Total Credits:-  ' . $data['course_details'][$k]['term_credits'] . '</b></td>' .
                        '<td colspan="2" style="color : blue"><b>Term Duration:-  ' . $data['course_details'][$k]['term_duration'] . '(weeks)</b></td>';
                $output.= '</tr>';
                $term_id = $data['course_details'][$k]['crclm_term_id'];
                $output.= '<tr style="font-size:12px;">';
                $output.= '<td><b>Sl No.' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Code' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Course' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Core / Elective ' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Acronym' . $blank . '</b></td>' .
                        '<td colspan="1"><b>L' . $blank . '</b></td>' .
                        '<td colspan="1"><b>T' . $blank . '</b></td>' .
                        '<td colspan="1"><b>P' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Credits' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Course Designer' . $blank . '</b></td>' .
                        '<td colspan="1"><b>Mode' . $blank . '</b></td>';
                $output.= '</tr>';
            }
            if ($data['course_details'][$k]['crs_mode'] == 1) {
                $msg = 'Practical';
            } else {
                $msg = 'Theory';
            }
            $output.= '<tr style="font-size:12px;">';
            $output.= '<td>' . $i . '</td>' .
                    '<td>' . $data['course_details'][$k]['crs_code'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['crs_title'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['crs_type_name'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['crs_acronym'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['lect_credits'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['tutorial_credits'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['practical_credits'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['total_credits'] . '</td>' .
                    '<td>' . $data['course_details'][$k]['title'] . ' ' . ucfirst($data['course_details'][$k]['first_name']) . ' ' . ucfirst($data['course_details'][$k]['last_name']) . '</td>' .
                    '<td>' . $msg . '</td>';
            $i++;
            $total = 0;
            $output.= '</tr>';
        }
        echo $output;
    }

    /* Function is used to generate table of PEO details.
     * @param- 
     * @returns - an object.
     */

    public function display_peo_details() {
        $crclm_id = $this->input->post('crclm_id');
        $peo_data = $this->addcourse_model->display_peo_details($crclm_id);
        $output = ' ';
        $i = 1;
        $data['peo_details'] = $peo_data;
        $count = sizeof($data['peo_details']);
        for ($k = 0; $k < $count; $k++) {
            $output.= '<tr style="font-size:12px;">';
            $output.= '<td>' . $i . '</td>';
            $output.= '<td>' . $data['peo_details'][$k]['peo_statement'] . '</td>';
            $output.= '</tr>';
            $i++;
        }
        echo $output;
    }

    /* Function is used to generate table of PO details.
     * @param- 
     * @returns - an object.
     */

    public function display_po_details() {
        $crclm_id = $this->input->post('crclm_id');
        $po_data = $this->addcourse_model->display_po_details($crclm_id);
        $i = 1;
        $output = ' ';
        $data['po_details'] = $po_data;
        $count = sizeof($data['po_details']);
        for ($k = 0; $k < $count; $k++) {
            $output.= '<tr style="font-size:12px;">';
            $output.= '<td>' . $i . '</td>';
            $output.= '<td>' . $data['po_details'][$k]['po_statement'] . '</td>';
            $output.= '</tr>';
            $i++;
        }
        echo $output;
    }

    /* Function is used to generate table of Curriculum details.
     * @param- 
     * @returns - an object.
     */

    public function display_crclm_details() {
        $crclm_id = $this->input->post('crclm_id');
        $crclm_data = $this->addcourse_model->display_crclm_details($crclm_id);
        $output = ' ';
        $data['crclm_details'] = $crclm_data;
        $count = sizeof($data['crclm_details']);
        for ($k = 0; $k < $count; $k++) {
            $output.= '<tr style="font-size:12px;">';
            $output.= '<td>' . $data['crclm_details'][$k]['crclm_name'] . '</td>';
            $output.= '<td>' . $data['crclm_details'][$k]['crclm_description'] . '</td>';
            $output.= '<td>' . $data['crclm_details'][$k]['total_credits'] . '</td>';
            $output.= '<td>' . $data['crclm_details'][$k]['total_terms'] . '</td>';
            $output.= '<td>' . $data['crclm_details'][$k]['start_year'] . '</td>';
            $output.= '<td>' . $data['crclm_details'][$k]['end_year'] . '</td>';
            $output.= '<td>' . $data['crclm_details'][$k]['title'] . ' ' . ucfirst($data['crclm_details'][$k]['first_name']) . ' ' . ucfirst($data['crclm_details'][$k]['last_name']) . '</td>';
            $output.= '</tr>';
        }
        echo $output;
    }

    /* Function is used to fetch course names from course table.
     * @param- live data (live search data)
     * @returns - an object.
     */

    public function course_name() {
        $data['courselist'] = $this->addcourse_model->autoCompleteDetails();
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($data['courselist']));
    }


    /* Function is used to fetch term names from crclm_terms table.
     * @param- 
     * @returns - an object.
     */

    public function select_termlist() {
        $crclm_id = $this->input->post('crclm_id');
        $term_data = $this->course_model->term_fill($crclm_id);
        $term_data = $term_data['res2'];
        $i = 0;
        $list[$i] = '<option value="">Select Term</option>';
        $i++;
        foreach ($term_data as $data) {
            $list[$i] = "<option value = " . $data['crclm_term_id'] . ">" . $data['term_name'] . "</option>";
            $i++;
        }
        $list = implode(" ", $list);
        echo $list;
    }


    /* Function is used to delete a course from course table.
     * @param- curriculum id
     * @returns - a boolean value.
     */

    public function course_delete($crs_id) {
        $password = $this->input->post('password');
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $flag_course_owner = 0;
            if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && $this->ion_auth->in_group('Course Owner')) {
                $flag_course_owner = 1;
            }
            /*delete permission for course only for admin and Chairman and Program Owner */
            if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && ($this->ion_auth->in_group('Course Owner') )) {
                $role_set = "1";
            } else {
                $role_set = "2";
            }

            $survey_exist = $this->course_model->course_delete_manage($crs_id,$role_set);

            if(isset($survey_exist) && $survey_exist[0]['survey_count'] > 0){
                echo "-1";
            }else{
                
                $results = $this->course_model->send_mail_course_details($crs_id);
                
                if($results['crs_owner_data']){
                    
                $receiver_id = $results['crs_owner_data']['clo_owner_id'];
                $cc = '';
                $links = '';
                $entity_id = 4;
                $state = 12;
                $crclm_id = $results['crs_owner_data']['crclm_id'];
                $addition_data['course'] = $results['crs_data']['crs_title'];
                $addition_data['term'] = $results['crs_data']['term_name'];
                $addition_data['username'] = $results['crs_data']['username'];
            
                $this->ion_auth->send_email($receiver_id, $cc, $links, $entity_id, $state, $crclm_id, $addition_data);
                
                } 
                 if($results['crs_instr_data']) {
                    foreach($results['crs_instr_data'] as $value) {
                        $receiver_id = $value['course_instructor_id'];
                        $cc = '';
                        $links = '';
                        $entity_id = 4;
                        $state = 12;
                        $crclm_id = $value['crclm_id'];
                        $addition_data['course'] = $results['crs_data']['crs_title'];
                        $addition_data['term'] = $results['crs_data']['term_name'];
                        $addition_data['username'] = $results['crs_data']['username'];
                        $this->ion_auth->send_email($receiver_id, $cc, $links, $entity_id, $state, $crclm_id, $addition_data);  
                        }
                    }
                    
                    $check_result = $this->course_model->check_co_creation($crs_id);
                    if($check_result == 1) {
                         /* password authenticaton for course delete */
                        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner'))) {
                            echo "error";
                        } else {
                            $remember = (bool) $this->input->post('remember');
                            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));
                            if (!$this->ion_auth->login($identity, $password, $remember)) {
                                echo  "2";
                            } else {
                                $result = $this->course_model->course_delete($crs_id, $flag_course_owner,$role_set);
                                echo TRUE;
                                redirect('curriculum/course', 'refresh');
                            }
                        }
                    } else {
                        $result = $this->course_model->course_delete($crs_id, $flag_course_owner,$role_set);
                        echo TRUE;
                        redirect('curriculum/course', 'refresh');
                    }
            }

        }
    }


    /* Function is used to initiate CLO creation process.
     * @param- course id
     * @returns - a boolean value.
     */

    function publish_course($crs_id) {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $data = $this->course_model->publish_course_curriculum($crs_id);
            $crclm_term_id = $data['0']['crclm_term_id'];
            $crclm_id = $data['0']['crclm_id'];
            $entity_id = '4';
            $particular_id = $crs_id;
            $sender_id = $this->ion_auth->user()->row()->id;
            $data = $this->course_model->publish_course_receiver($crs_id, $crclm_term_id);
            $reviewer_data = $this->course_model->publish_course_reviewer($crs_id, $crclm_term_id);
            $term_name = $data['term'][0]['term_name'];
            $course_name = $data['course'][0]['crs_title'];
            $description = 'Term(Semester):- ' . $term_name;
            $reviewer_description = $description . ', Course:- ' . $course_name . ' is created, you have been chosen as a Course Reviewer.';
            $description = $description . ', Course:- ' . $course_name . ' is created, proceed to create COs.';
            $receiver_id = $data['0']['clo_owner_id'];   // Course Owner User-id
            $reviewer_id = $reviewer_data['0']['validator_id']; // Course Reviewer User-id
            $reviewer_url = base_url('');
            $crclmid = base64_encode($crclm_id);
            $crclmtermid = base64_encode($crclm_term_id);
            $crsid = base64_encode($crs_id);
            $url = base_url('curriculum/clo/clo_add/' . $crclmid . '/' . $crclmtermid . '/' . $crsid);
            // $reviewer_description = $reviewer_description;
            // $description = $description;
            $state = '1';
            $status = '1';

            $results = $this->course_model->publish_course($crclm_id, $crclm_term_id, $crs_id, $entity_id, $particular_id, $sender_id, $receiver_id, $url, $description, $state, $status, $crclm_term_id, $reviewer_description, $reviewer_id);
            $term_name = $results['term'][0]['term_name'];
            $course_name = $results['course'][0]['crs_title'];
            $addition_data = array();

            //mail items
            $receiver_id = $results['receiver_id'];
            $cc = '';
            $links = $results['url'];
            $entity_id = $results['entity_id'];
            $state = $results['state'];
            $crclm_id = $results['crclm_id'];
            $addition_data['term'] = $term_name;
            $reviewer_state = 7;
            $addition_data['course'] = $course_name;
            $status_update = $this->course_model->publish_course_update_status($crs_id);

           
            $this->ion_auth->send_email($receiver_id, $cc, $links, $entity_id, $state, $crclm_id, $addition_data);
            // $this->ion_auth->send_email($reviewer_id, $cc, $reviewer_url, $entity_id, $reviewer_state, $crclm_id, $addition_data);

            // redirect('curriculum/course', 'refresh');
            // return true;
            $val = $this->ion_auth->in_group('Course Owner') && !$this->ion_auth->in_group('admin') && !$this->ion_auth->in_group('Chairman') && !$this->ion_auth->in_group('Program Owner');

            echo $val;
        }
    }


    /* Function is used to generate List of Course Grid (Table).
     * @param- 
     * @returns - an object.
     */

    public function show_course() {
        $crclm_id = $this->input->post('crclm_id');
        $term_id = $this->input->post('crclm_term_id');
        $course_designer = 0;
        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && $this->ion_auth->in_group('Course Owner')) {
            //course_clo_owner
            $course_designer = 1;
        }
        $course_data = $this->course_model->course_list($crclm_id, $term_id, $course_designer);
        
        $i = 1;
        $msg = '';
        $del = '';
        $publish = '';
        $data = $course_data['crs_list_data'];
        $crs_list = array();
        
        foreach ($data as $crs_data) {
            $check_stud_to_crs_reg_set_or_not = $this->course_model->check_stud_to_crs_reg_set_or_not($crs_data['crs_id']);
            $crsid = base64_encode($crs_data['crs_id']);
            if ($crs_data['crs_mode'] == 1) {
                $msg = 'Practical';
            } else if ($crs_data['crs_mode'] == 0) {
                $msg = 'Theory';
            } else {
                $msg = 'Theory with Lab';
            }

            $check_mandatory_data_set_or_not = $this->course_model->check_mandatory_data_set_or_not($crs_data['crs_id']);
            if ($crs_data['clo_bl_flag'] == 0) {
                if ($check_mandatory_data_set_or_not[0]['flag'] == 1) {
                    $bloom_option_mandatory = '<a role = "button"  data-clo_bl_flag = "' . $crs_data['clo_bl_flag'] . '" data-toggle = "modal" title = "Course - COs to Bloom\'s Level Mapping Status" href = "" data-confirmation_msg = "Are you sure that you want to make Course Outcome Bloom\'s Level (s) mandatory ? " class = " bloom_option_mandatory btn btn-small btn-warning myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" ><i></i>Optional</a>';
                } else {
                    $bloom_option_mandatory = '<a role = "button"   href = "#myModal_mandatory_error" data-confirmation_msg = "" class = " bloom_option_mandatory_error btn btn-small btn-warning myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" ><i></i>Optional</a>';
                }
            } else {
                $bloom_option_mandatory = '<a role = "button"    data-confirmation_msg = "Are you sure that you want to make Course Outcome Bloom\'s Level (s) Optional ? " data-toggle = "modal" title = "Course - COs to Bloom\'s Level Mapping Status" href = "" class = " bloom_option_mandatory  btn btn-small btn-success myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" data-clo_bl_flag = "' . $crs_data['clo_bl_flag'] . '" ><i></i>Mandatory</a>';
                
            }

           $weightage_flag = 0;
            if ($crs_data['cia_flag'] == 1 || $crs_data['mte_flag'] == 1 || $crs_data['tee_flag'] == 1) {
                $weightage_flag = 1;
            }

            $check_stud_count = $this->course_model->check_stud_count($crs_data['crs_id']);
                if(empty($check_stud_count['stud_count'])) {
                            $stud_count = 'Students not registered for any section';
                } else {
                            $stud_count = 'Total number of Students registered for&#013;'.$check_stud_count['stud_count']['section_wise_stud_count'];
                }
                
            if ($crs_data['status'] == 0) {
                $del = '<center><a role = "button"  data-toggle = "modal" title = "Remove"  class = "cursor_pointer myTagRemover icon-remove delete_crs_data" id = "' . $crs_data['crs_id'] . '" ></a></center>';
                if ($crs_data['crs_domain_id'] == NULL) {
                    $publish = '<a role = "button"  data-toggle = "modal" title = "Assign "' . $this->lang->line('course_owner_full') . '" & Course Reviewer" href = "#myModal4" class = "btn btn-small btn-danger myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" ><i></i>Incomplete</a>';
                    $stud_registration = '';
                    $stud_to_crs_reg_option_mandatory = '<a role = "button"     data-toggle = "modal" title = "Student to course registration Status" href = "" class = " stud_to_crs_reg_option_mandatory  btn btn-small btn-success myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" data-edu_sys_flag = "0" ><i></i>Mandatory</a><br><a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '"></a>';         ;
                } else {
                   
                    if ($check_stud_to_crs_reg_set_or_not == 1) {
                    $stud_registration = '<a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '">Manage Students</a>';
                    } elseif ($check_stud_to_crs_reg_set_or_not == 0){
                        $stud_registration = 'N/A';
                    }
                    
                    $publish = '<a role = "button"  data-toggle = "modal" title = "CO creation Pending" href = "" class = " check_weightage btn btn-small btn-warning myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" data-weightage = "' . $weightage_flag . '" data-crs_mode="' . $crs_data['crs_mode'] . '"><i></i>Pending</a>';
                }
            } else {
                    if ($check_stud_to_crs_reg_set_or_not == 1) {
                    if ($crs_data['crs_attainment_finalize_flag'] == 1){
                        $stud_registration = '<a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '">Attainment finalized</a>';
                    }else{ 
                            $stud_registration = '<a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '">Manage Students</a>';    
                    }
                    }elseif ($check_stud_to_crs_reg_set_or_not == 0){
                            $stud_registration = 'N/A';
                    }           
                
                    $del = '<center><a role = "button"  data-toggle = "modal" title = "Remove"  class = "cursor_pointer myTagRemover icon-remove delete_crs_data delete_lms" id = "' . $crs_data['crs_id'] . '" ></a></center>';
                    $publish = '<a role = "button"  data-toggle = "modal" title = "CO creation Initiated" href = "#" class = "btn btn-small btn-success myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" ><i></i>Initiated</a>';
            }
            if ($crs_data['clo_bl_flag'] == "0") {
                    $color = "#000000";
                    $title = "";
                    $title = ' Blooms Level(s) not Mandatory .';
            } else {
                    $color = "#000000";
                    $title = ' Blooms Level(s) Mandatory .';
            }

            if ($check_stud_to_crs_reg_set_or_not == 1) {
                if ($crs_data['crs_attainment_finalize_flag'] == 1){
                    
                    $stud_to_crs_reg_option_mandatory = '<a role = "button"     data-toggle = "modal" title = "Student to course registration Status" href = "" class = " stud_to_crs_reg_option_mandatory  btn btn-small btn-success myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" data-edu_sys_flag = "0" ><i></i>Mandatory</a><br><a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '">Attainment finalized</a>';
                
                } else {
                    if($crs_data['crs_domain_id']!=NULL) {
                        $stud_to_crs_reg_option_mandatory = '<a role = "button"     data-toggle = "modal" title = "Student to course registration Status" href = "" class = " stud_to_crs_reg_option_mandatory  btn btn-small btn-success myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" data-edu_sys_flag = "0" ><i></i>Mandatory</a><br><a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '">Manage Students</a>';         
                    } else {
                        $stud_to_crs_reg_option_mandatory = '<a role = "button"     data-toggle = "modal" title = "Student to course registration Status" href = "" class = " stud_to_crs_reg_option_mandatory  btn btn-small btn-success myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" data-edu_sys_flag = "0" ><i></i>Mandatory</a><br><a href="'.base_url('curriculum/course/manage_students'). '/' . $crsid . '" class="manage_students" title="' . $stud_count . '" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '"></a>N/A';         
                    }
                }
            } elseif ($check_stud_to_crs_reg_set_or_not == 0) {
                if ($crs_data['crs_attainment_finalize_flag'] == 1){
                   
                        $stud_to_crs_reg_option_mandatory = '<a role = "button"  data-edu_sys_flag = "1" data-toggle = "modal" title = "Student to course registration Status" href = ""  class = " stud_to_crs_reg_option_mandatory btn btn-small btn-warning myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" ><i></i>Optional</a><br>Attainment Finalized';
                    
                    } else {
                    
                        $stud_to_crs_reg_option_mandatory = '<a role = "button"  data-edu_sys_flag = "1" data-toggle = "modal" title = "Student to course registration Status" href = ""  class = " stud_to_crs_reg_option_mandatory btn btn-small btn-warning myTagRemover get_crs_id" id = "' . $crs_data['crs_id'] . '" ><i></i>Optional</a><br>N/A';
                }
            }

            $crs_title = "<a title ='" . $title . "' style='text-decoration:none;'><span style=\"color: $color\"> " . $crs_data['crs_title'] . "</span></a>";
            $crs_code = "<a title ='" . $title . "' style='text-decoration:none;'><span style=\"color: $color\"> " . $crs_data['crs_code'] . "</span></a>";
            $reviewer_id = $crs_data['validator_id'];
            $user = $this->ion_auth->user($reviewer_id)->row();
            $total_credit_details = "L : " . $crs_data['lect_credits'] . "\r\n" . "T : " . $crs_data['tutorial_credits'] . "\r\n" . "P : " . $crs_data['practical_credits'];
            $total_credits = "<a style='text-decoration:none;' class = 'cursor_pointer' title='" . $total_credit_details . "' >" . $crs_data['total_credits'] . "</a>";
            $crs_id = base64_encode($crs_data['crs_id']);
            $crs_list[] = array(
                'sl_no' => $i,
                'crs_id' => $crs_data['crs_id'],
                'crs_code' => $crs_code,
                'crs_title' => $crs_title,
                'crs_type_name' => $crs_data['crs_type_name'],
                'crs_acronym' => $crs_data['crs_acronym'],
                'lect_credits' => $crs_data['lect_credits'],
                'tutorial_credits' => $crs_data['tutorial_credits'],
                'practical_credits' => $crs_data['practical_credits'],
                'self_study_credits' => $crs_data['self_study_credits'],
                'total_credits' => $total_credits,
                'contact_hours' => $crs_data['contact_hours'],
                'cie_marks' => $crs_data['cie_marks'],
                'see_marks' => $crs_data['see_marks'],
                'total_marks' => $crs_data['total_marks'],
                'crs_mode' => $msg,
                'mng_section' => '<a class="cursor_pointer assign_course_instructor" data-crs_id="' . $crs_data['crs_id'] . '" data-crs_name="' . $crs_data['crs_title'] . '" data-crs_code="' . $crs_data['crs_code'] . '" >Add / Edit</a>',
                'see_duration' => $crs_data['see_duration'],
                'username' => $crs_data['title'] . ' ' . ucfirst($crs_data['first_name']) . ' ' . ucfirst($crs_data['last_name']),
                'reviewer' => $crs_data['usr_title'] . ' ' . ucfirst($crs_data['usr_first_name']) . ' ' . ucfirst($crs_data['usr_last_name']),
                'stud_registration' => $stud_registration,
                'crs_id_edit' => '<center><a title = "Edit" href = "' . base_url('curriculum/course/edit_course') . '/' . $crs_id . '"><i class = "myTagRemover icon-pencil"></i></a></center>',
                'crs_id_delete' => $del,
                'publish' => $publish,
                'bloom_option_mandatory' => $bloom_option_mandatory,
                'stud_to_crs_reg_option_mandatory' => $stud_to_crs_reg_option_mandatory
            );
            $i++;
        }
        
        echo json_encode($crs_list);
    }

    public function fetch_crclm_edu_sys_flag() {
        $crclm_id = $this->input->post('crclm_id');
        $crclm_edu_sys_flag = $this->course_model->fetch_crclm_edu_sys_flag($crclm_id);
        
        echo $crclm_edu_sys_flag;
    }

    public function check_marks_uploaded_or_not() {
        $crs_id = $this->input->post('crs_id');
        $marks_uploaded = $this->course_model->check_marks_uploaded_or_not($crs_id);
        
        echo $marks_uploaded;
    }

    public function check_stud_to_crs_reg_set_or_not() {
        $crs_id = $this->input->post('crs_id');
        $crs_reg_set_or_not = $this->course_model->check_stud_to_crs_reg_set_or_not($crs_id);
        
        echo $crs_reg_set_or_not;
    }
            
        
    public function bloom_option_mandatory() {
        $crs_id = $this->input->post('crs_id');
        $clo_bl_flag = $this->input->post('clo_bl_flag');
        $result = $this->course_model->bloom_option_mandatory($crs_id, $clo_bl_flag);
    }

    public function stud_to_crs_reg_option_mandatory() {
        $crs_id = $this->input->post('crs_id');
        $stud_to_crs_reg_flag = $this->input->post('stud_to_crs_reg_flag');

        $result = $this->course_model->stud_to_crs_reg_option_mandatory($crs_id, $stud_to_crs_reg_flag);
    }


    /* Function is used to load edit view of course.
     * @param - course id
     * @returns- edit view of course.
     */

    public function edit_course($crs_id) {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $crs_id = base64_decode($crs_id);
            $course_designer = 0;
            if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && $this->ion_auth->in_group('Course Owner')) {
                //course_clo_owner
                $course_designer = 1;
                $course_owner = $this->course_model->course_owner_login_details();
                $data['course_owner_name'] = $course_owner['title'] . ' ' . $course_owner['first_name'] . ' ' . $course_owner['last_name'];
                $data['course_owner_id'] = $course_owner['id'];
            }
            $data['course_details'] = $this->course_model->course_details($crs_id);
            $crclm_id = $data['course_details']['0']['crclm_id'];
            $data['crs_bl_sugg_flag'] = $data['course_details']['0']['crs_bl_sugg_flag'];
            
            $data['curriculum_details'] = $this->course_model->curriculum_details($crclm_id);
            $data['po_list'] = $this->course_model->po_list($crclm_id);
            $data['peo_list'] = $this->course_model->peo_list($crclm_id);
            $data['term_details'] = $this->course_model->term_details($crclm_id);
            $data['course_detailslist'] = $this->course_model->course_detailslist($crclm_id);
            $dept_id = $this->ion_auth->user()->row()->user_dept_id;
            $data['course_domain_list'] = $this->addcourse_model->dropdown_course_domain_name($dept_id);
            $data['std_crclm_data']=$this->course_model->fetch_crclm_list();
            $data['std_pgm_data']=$this->course_model->fetch_course_programs(); 
            $data['coursetypelist'] = $this->course_model->fetch_course_type($crclm_id);
            $data['departmentlist'] = $this->course_model->dropdown_department();
            $data['crs_id'] = $crs_id;
            $data['course_owner_details'] = $this->course_model->course_owner_details($crs_id);
            $data['ionlms_flag'] = $this->course_model->fetch_lms_flag();
            $data['std_crs_register'] = $this->course_model->check_student_register($crs_id);
            //crs_attainment_finalize_flag = 1 if CO Attainment is finalized;
            $data['crs_attainment_finalize_flag'] = $data['course_details']['0']['crs_attainment_finalize_flag'];
			$data['stud_mks_chk'] = $this->course_model->check_stud_marks_exist($crs_id);
			
            if (!empty($data['course_domain_list'])) {
                foreach ($data['course_domain_list'] as $domain_item) {
                    @$select_options[$domain_item['crs_domain_id']] = $domain_item['crs_domain_name']; //course domain name column index
                }
            } else {
                @$select_options[] = 'nodomain'; //course domain name column index
            }
            $data['domain_options'] = $select_options;
            $reviewer_dept_id = @$data['course_owner_details']['reviewer_details']['0']['dept_id'];
            $reviewer_date = @$data['course_owner_details']['reviewer_details']['0']['last_date'];
            $data['userlist'] = $this->course_model->reviewer_dropdown_userlist($reviewer_dept_id);
            $loggedin_user_id = $this->ion_auth->user()->row()->id;
            $data['co_userlist'] = $this->course_model->owner_dropdown_userlist($loggedin_user_id);
            //to fetch Prerequisite Courses
            $data['predessor_data'] = $this->course_model->predessor_details($crs_id);
            if ($this->form_validation->run() == false) {
                $data['crs_id'] = array(
                    'name' => 'crs_id',
                    'id' => 'crs_id',
                    'class' => 'required',
                    'type' => 'hidden',
                    'value' => @$data['course_details']['0']['crs_id']
                );
                $data['crclm_id'] = array(
                    'name' => 'crclm_id',
                    'id' => 'crclm_id',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select Curriculum',
                    'value' => @$data['course_details']['0']['crclm_id'],
                    'onchange' => 'select_term();'
                );
                $data['crclm_term_id'] = array(
                    'name' => 'crclm_term_id',
                    'id' => 'crclm_term_id',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select Term',
                    'value' => @$data['course_details']['0']['crclm_term_id']
                );
                $data['crs_type_id'] = array(
                    'name' => 'crs_type_id',
                    'id' => 'crs_type_id',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select Course Type',
                    'value' => @$data['course_details']['0']['crs_type_id']
                );
                $data['crs_domain_id'] = array(
                    'name' => 'crs_domain_id',
                    'id' => 'crs_domain_id',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select Course Type',
                    'value' => @$data['course_details']['0']['crs_domain_id']
                );
                $data['crs_code'] = array(
                    'name' => 'crs_code',
                    'id' => 'crs_code',
                    'class' => 'input-medium noSpecialChars2 required',
                    'type' => 'text',
                    'placeholder' => 'Enter Course Code',
                    'value' => @$data['course_details']['0']['crs_code'],
                    'required' => ''
                );
                $data['crs_mode'] = array(
                    'name' => 'crs_mode',
                    'id' => 'crs_mode',
                    'class' => 'noSpecialChars required',
                    'type' => 'hidden',
                    'value' => @$data['course_details']['0']['crs_mode']
                );
                $data['crs_title'] = array(
                    'name' => 'crs_title',
                    'id' => 'crs_title',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Enter Course Title',
                    'value' => @$data['course_details']['0']['crs_title'],
                    'required' => ''
                );
                $data['crs_acronym'] = array(
                    'name' => 'crs_acronym',
                    'id' => 'crs_acronym',
                    'class' => 'input-medium required',
                    'type' => 'text',
                    'placeholder' => 'Enter Acronym',
                    'value' => @$data['course_details']['0']['crs_acronym'],
                    'required' => ''
                );

                $data['predecessor_course_id'] = array(
                    'name' => 'predecessor_course_id',
                    'id' => 'predecessor_course_id',
                    'class' => 'input-medium',
                    'type' => 'text',
                    'placeholder' => '',
                    'required' => ''
                );
                $data['co_crs_owner_edit'] = array(
                    'name' => 'co_crs_owner_edit',
                    'id' => 'co_crs_owner_edit',
                    'class' => 'char-counter',
                    'cols' => '20',
                    'rows' => '2',
                    'type' => 'textarea',
                    'maxlength' => "2000",
                    'placeholder' => '',
                    'value' => @$data['course_details']['0']['co_crs_owner']
                );
                $data['clo_owner_id'] = array(
                    'name' => 'clo_owner_id',
                    'id' => 'clo_owner_id',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select User',
                    'value' => @$data['course_owner_details']['owner_details']['0']['clo_owner_id']
                );
                $data['review_dept'] = array(
                    'name' => 'review_dept',
                    'id' => 'review_dept',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select Department ',
                    'value' => @$data['course_owner_details']['reviewer_details']['0']['dept_id']
                );
                $data['validator_id'] = array(
                    'name' => 'validator_id',
                    'id' => 'validator_id',
                    'class' => 'required input-medium',
                    'type' => 'text',
                    'placeholder' => 'Select User ',
                    'value' => @$data['course_owner_details']['reviewer_details']['0']['validator_id']
                );
                $data['last_date'] = array(
                    'name' => 'last_date',
                    'id' => 'last_date',
                    'class' => 'required input-medium rightJustified key-data ',
                    'type' => 'text',
                    'placeholder' => 'Enter Date ',
                    'value' => @$data['course_owner_details']['reviewer_details']['0']['last_date'],
                    'required' => ''
                );
                $data['lect_credits'] = array(
                    'name' => 'lect_credits',
                    'id' => 'lect_credits',
                    'class' => 'required onlyDigit rightJustified span2 credits',
                    'type' => 'text',
                    'placeholder' => '',
                    'value' => @$data['course_details']['0']['lect_credits']
                );
                $data['tutorial_credits'] = array(
                    'name' => 'tutorial_credits',
                    'id' => 'tutorial_credits',
                    'class' => 'required onlyDigit rightJustified span2 credits',
                    'type' => 'text',
                    'placeholder' => '',
                    'value' => @$data['course_details']['0']['tutorial_credits']
                );
                // As per code review, comment given by the reviewer code has been updated.
                $data['total_credits'] = array(
                    'name' => 'total_credits',
                    'id' => 'total_credits',
                    'class' => 'span2 onlyDigit rightJustified credits',
                    'type' => 'text',
                    'placeholder' => '',
                    'readonly' => '',
                    'value' => @$data['course_details']['0']['total_credits']
                );
                $data['practical_credits'] = array(
                    'name' => 'practical_credits',
                    'id' => 'practical_credits',
                    'class' => 'span2 onlyDigit rightJustified required credits',
                    'type' => 'text',
                    'placeholder' => '',
                    'value' => @$data['course_details']['0']['practical_credits']
                );
        
                
                $data['self_study_credits'] = array(
                    'name' => 'self_study_credits',
                    'id' => 'self_study_credits',
                    'class' => 'required span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['self_study_credits'],
                    'placeholder' => '',
                    'size' => '1',
                    'required' => ''
                );
                $data['contact_hours'] = array(
                    'name' => 'contact_hours',
                    'id' => 'contact_hours',
                    'class' => 'required span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['contact_hours'],
                    'placeholder' => '',
                    'size' => '1',
                    'required' => ''
                );
                $data['cie_marks'] = array(
                    'name' => 'cie_marks',
                    'id' => 'cie_marks',
                    'class' => 'required span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['cie_marks'],
                    'placeholder' => '',
                    'size' => '1',
                    'required' => ''
                );
                $data['mid_term_marks'] = array(
                    'name' => 'mid_term_marks',
                    'id' => 'mid_term_marks',
                    'class' => 'required span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['mid_term_marks'],
                    'placeholder' => '',
                    'size' => '1',
                    'required' => ''
                );
                $data['see_marks'] = array(
                    'name' => 'see_marks',
                    'id' => 'see_marks',
                    'class' => 'required span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['see_marks'],
                    'placeholder' => '',
                    'size' => '1',
                    'required' => ''
                );
                $data['attendance_marks'] = array(
                    'name' => 'attendance_marks',
                    'id' => 'attendance_marks',
                    'class' => 'span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['attendance_marks'],
                    'placeholder' => '',
                    'size' => '1'
                );
                $data['ss_marks'] = array(
                    'name' => 'ss_marks',
                    'id' => 'ss_marks',
                    'class' => 'required span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['ss_marks'],
                    'placeholder' => '',
                    'size' => '1',
                    'required' => ''
                );
                $data['total_marks'] = array(
                    'name' => 'total_marks',
                    'id' => 'total_marks',
                    'class' => 'span2 onlyDigit rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['total_marks'],
                    'placeholder' => '',
                    'size' => '1'
                );
                $data['see_duration'] = array(
                    'name' => 'see_duration',
                    'id' => 'see_duration',
                    'class' => 'required span2 durationRestrict rightJustified',
                    'type' => 'text',
                    'value' => @$data['course_details']['0']['see_duration'],
                    'placeholder' => '0:00',
                    'size' => '1',
                    'required' => ''
                );
                $data['curriculum'] = array(
                    'name' => 'curriculum',
                    'id' => 'curriculum',
                    'class' => 'required',
                    'type' => 'hidden',
                    'value' => @$data['course_details']['0']['crclm_id']
                );
                // If CO Attainment is finalized, disable weightage & flags
                if($data['crs_attainment_finalize_flag']){
                    $data['total_cia_weightage'] = array(
                        'name' => 'total_cia_weightage',
                        'id' => 'total_cia_weightage',
                        'class' => 'span2 onlyDigit rightJustified total_wt allownumericwithdecimal ',
                        'value' => @$data['course_details']['0']['total_cia_weightage'],
                        'placeholder' => '',
                        'size' => '1',
                        'readonly' => 'readonly' 
                    );

                    $data['total_mte_weightage'] = array(
                        'name' => 'total_mte_weightage',
                        'id' => 'total_mte_weightage',
                        'class' => 'span2 onlyDigit rightJustified total_wt',
                        'value' => @$data['course_details']['0']['total_mte_weightage'],
                        'placeholder' => ' ',
                        'size' => '1',
                        'readonly' => 'readonly' 
                    );

                    $data['total_tee_weightage'] = array(
                        'name' => 'total_tee_weightage',
                        'id' => 'total_tee_weightage',
                        'class' => 'span2 onlyDigit rightJustified total_wt',
                        'value' => @$data['course_details']['0']['total_tee_weightage'],
                        'placeholder' => '',
                        'size' => '1',
                        'readonly' => 'readonly' 
                    );                    

                } else {
                    $data['total_cia_weightage'] = array(
                        'name' => 'total_cia_weightage',
                        'id' => 'total_cia_weightage',
                        'class' => 'span2 onlyDigit rightJustified total_wt allownumericwithdecimal ',
                        'value' => @$data['course_details']['0']['total_cia_weightage'],
                        'placeholder' => '',
                        'size' => '1'
                    );

                    $data['total_mte_weightage'] = array(
                        'name' => 'total_mte_weightage',
                        'id' => 'total_mte_weightage',
                        'class' => 'span2 onlyDigit rightJustified total_wt',
                        'value' => @$data['course_details']['0']['total_mte_weightage'],
                        'placeholder' => ' ',
                        'size' => '1'
                    );
                
                    $data['total_tee_weightage'] = array(
                        'name' => 'total_tee_weightage',
                        'id' => 'total_tee_weightage',
                        'class' => 'span2 onlyDigit rightJustified total_wt',
                        'value' => @$data['course_details']['0']['total_tee_weightage'],
                        'placeholder' => '',
                        'size' => '1'
                    );    
                }              
                            

                $total_wt = (float) ($data['course_details']['0']['total_cia_weightage'] + $data['course_details']['0']['total_tee_weightage']);
                $data['total_weightage'] = array(
                    'name' => 'total_weightage',
                    'id' => 'total_weightage',
                    'class' => 'span2 onlyDigit rightJustified percentage credits_validation',
                    'value' => $total_wt,
                    'placeholder' => '',
                    'size' => '5',
                    'readonly' => ''
                );
                $data['title'] = 'Course Edit Page';
                $data['bld_active'][] = $data['course_details']['0']['cognitive_domain_flag'];
                $data['bld_active'][] = $data['course_details']['0']['affective_domain_flag'];
                $data['bld_active'][] = $data['course_details']['0']['psychomotor_domain_flag'];

                $data['cia_flag'] = $data['course_details']['0']['cia_flag'];
                $data['mte_flag'] = $data['course_details']['0']['mte_flag'];
                $data['tee_flag'] = $data['course_details']['0']['tee_flag'];
                $data['tutorial'] = $data['course_details']['0']['tutorial'];
                $data['elective_crs_flag'] = $data['course_details']['0']['elective_crs_flag'];
				$data['indirect_flag'] = $data['course_details']['0']['indirect_flag'];
                $data['clo_bl_flag'] = $this->fetch_clo_bl_flag_edit($crclm_id, $crs_id);
                $data['mte_flag_org'] = $this->addcourse_model->fetch_organisation_data();
				$data['crs_domain'] = @$data['course_details']['0']['crs_domain_id'];
                $data['bloom_domain'] = $this->addcourse_model->get_all_bloom_domain();
                $data['cia_passing_marks'] = $data['course_details']['0']['cia_passing_marks'];
                $data['tee_passing_marks'] = $data['course_details']['0']['tee_passing_marks'];
                $data['crs_finalize_flag'] = $this->course_model->fetch_crs_finalize_flag($crs_id);
                $val = $this->ion_auth->user()->row();
                $data['student_passing_marks'] = $val->org_name->student_passing_marks;
                $this->load->view('curriculum/course/edit_course_vw', $data);
            }
        }
    }


    /* Function is used to update the details of a course.
     * @param -
     * @returns- updated list view of course.
     */

    public function update() {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $pre_courses_exploded = explode(",", $_POST['hidden-tags']);
            $pre_courses = implode('<>', $pre_courses_exploded);
            //course table
            $crs_id = $this->input->post('crs_id');
            $crclm_id = $this->input->post('crclm_id');
            $crclm_term_id = $this->input->post('crclm_term_id');
            $crs_type_id = $this->input->post('crs_type_id');
            $crs_code = $this->input->post('crs_code');
            $crs_mode = $this->input->post('crs_mode');
            $crs_title = $this->input->post('crs_title');
            $crs_acronym = $this->input->post('crs_acronym');
            $co_crs_owner = $this->input->post('co_crs_owner_edit');
            $crs_domain_id = $this->input->post('crs_domain_id');
            $lect_credits = $this->input->post('lect_credits');
            $tutorial_credits = $this->input->post('tutorial_credits');
            $practical_credits = $this->input->post('practical_credits');
            $self_study_credits = $this->input->post('self_study_credits');
            $total_credits = $this->input->post('total_credits');
            $contact_hours = $this->input->post('contact_hours');
            $cie_marks = $this->input->post('cie_marks');
            $see_marks = $this->input->post('see_marks');
            $ss_marks = 0;
            $total_marks = $this->input->post('total_marks');
            $see_duration = $this->input->post('see_duration');
            //course_clo_owner
            $course_designer = $this->input->post('clo_owner_id');
            //course_clo_reviewer
            $review_dept = $this->input->post('review_dept');
            $course_reviewer = $this->input->post('validator_id');
            $last_date = date("Y-m-d", strtotime($this->input->post('last_date')));
            //predecessor courses array
            $pre_courses;
            // delete predecessor courses array
            $del_pre_courses = $this->input->post('delete_crs_id');
            $tutorial = $this->input->post('tutorial');
            if ($tutorial) {
                $tutorial = 1;
            }
            $course_added = $this->course_model
                    ->update_course(
                    //course table data
                    $crs_id, $crclm_id, $crclm_term_id, $crs_type_id, $crs_code, $crs_mode, $crs_title, $crs_acronym, $co_crs_owner, $crs_domain_id, $lect_credits, $tutorial_credits, $practical_credits, $self_study_credits, $total_credits, $contact_hours, $cie_marks, $see_marks, $ss_marks, $total_marks, $see_duration,
                    //course_clo_owner
                    $course_designer,
                    //course_clo_reviewer
                    $review_dept, $course_reviewer, $last_date,
                    //predecessor courses array
                    $pre_courses,
                    // delete predecessor courses array
                    $del_pre_courses , $cia_check, $mte_check, $tee_check,$tutorial
            );
            redirect('curriculum/course', 'refresh');
        }
    }

// End of function update.

    /* Function is used to fetch term names from crclm_terms table.
     * @param- 
     * @returns - an object.
     */

    public function select_term() {
        $crclm_id = $this->input->post('crclm_id');
        $term_data = $this->course_model->term_details($crclm_id);
        $i = 0;
        $list[$i] = '<option value="">Select Term</option>';
        $i++;
         
        foreach ($term_data as $data) {
            $list[$i] = "<option value=" . $data['crclm_term_id'] . ">" . $data['term_name'] . "</option>";
            $i++;
        }
        $list = implode(" ", $list);
        echo $list;
    }

// End of function select_term.

    /* Function is used to fetch course type names from crclm_crs_type_map table.
     * @param- 
     * @returns - an object.
     */

    public function select_course_type() {
        $crclm_id = $this->input->post('crclm_id');
        $result = $this->course_model->fetch_course_type($crclm_id);
        $i = 0;
        $list[$i] = '<option value="">Select Course Type</option>';
        $i++;
        foreach ($result as $data) {
            $list[$i] = "<option value=" . $data['crs_type_id'] . " data-elective_crs_flag=" . $data['elective_crs_flag'] . ">" . $data['crs_type_name'] . "</option>";
            $i++;
        }
        $list = implode(" ", $list);
        echo $list;
    }

// End of function select_course_type.

    /* Function is used to fetch user names from users table.
     * @param- department id
     * @returns - an object.
     */

    public function reviewer_list() {
        $dept_id = $this->input->post('dept_id');
        $user_data = $this->course_model->dropdown_userlist2($dept_id);
        if ($user_data) {
            $i = 0;
            $list[$i] = '<option value="">Select User</option>';
            $i++;
            foreach ($user_data as $data) {
                $list[$i] = "<option value=" . $data['id'] . " title='" . $data['email'] . "'>" . $data['title'] . " " . ucfirst($data['first_name']) . " " . ucfirst($data['last_name']) . "</option>";
                $i++;
            }
            $list = implode(" ", $list);
            echo $list;
        } else {
            $i = 0;
            $list[$i] = '<option value="">No User in this Department </option>';
            $list = implode(" ", $list);
            echo $list;
        }
    }

// End of function reviewer_list.

    /* Function is used to fetch user names from users table.
     * @param- 
     * @returns - an object.
     */

    public function designer_list() {
        $crclm_id = $this->input->post('crclm_id');
        $user_data = $this->course_model->dropdown_userlist3($crclm_id);
        if ($user_data) {
            $i = 0;
            $list[$i] = '<option value="">Select User</option>';
            $i++;
            foreach ($user_data as $data) {
                $list[$i] = "<option value=" . $data['id'] . " title='" . $data['email'] . "'>" . $data['title'] . " " . ucfirst($data['first_name']) . " " . ucfirst($data['last_name']) . "</option>";
                $i++;
            }
            $list = implode(" ", $list);
            echo $list;
        } else {
            $i = 0;
            $list[$i] = '<option value="">No User in this Department </option>';
            $list = implode(" ", $list);
            echo $list;
        }
    }

// End of function designer_list.

    /*
     * Function to fetch help details related to course
     * @return: an object
     */
    function course_help() {
        $help_list = $this->course_model->course_help();

        if (!empty($help_list['help_data'])) {
            foreach ($help_list['help_data'] as $help) {
                $clo_po_id = '<i class="icon-black icon-file"> </i><a target="_blank" href="' . base_url('curriculum/course/help_content') . '/' . $help['serial_no'] . '">' . $help['entity_data'] . '</a></br>';
                echo $clo_po_id;
            }
        }

        if (!empty($help_list['file'])) {
            foreach ($help_list['file'] as $file_data) {
                $file = '<i class="icon-black icon-book"> </i><a target="_blank" href="' . base_url('uploads') . '/' . $file_data['file_path'] . '">' . $file_data['file_path'] . '</a></br>';
                echo $file;
            }
        }
    }

    /* Function to display help related to course in a new page
     * @parameters: help id
     * @return: load help view page
     */

    public function help_content($help_id) {
        $help_content = $this->course_model->help_content($help_id);
        $help['help_content'] = $help_content;
        $data['title'] = "Course Page";
        $this->load->view('curriculum/course/course_help_vw', $help);
    }

// Added by bhagyalaxmi S S

    /* Function is used to add a new course details.
     * @param-
     * @returns - updated list view of course.
     */
    public function insert_course_details() {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            
            $cia_check = $this->input->post('cia_check');
            $mte_check = $this->input->post('mte_check');
            $tee_check = $this->input->post('tee_check');
            $tutorial = $this->input->post('tutorial');
            if ($tutorial) {
                $tutorial = 1;
            }
            if (($cia_check) && ($this->input->post('total_cia_weightage') > 0)) {
                $cia_flag = 1;
            } else {
                $cia_flag = 0;
            }
            if (($mte_check) && ($this->input->post('total_mte_weightage') > 0)) {
                $mte_flag = 1;
            } else {
                $mte_flag = 0;
            }
            if (($tee_check) && ($this->input->post('total_tee_weightage') > 0)) {
                $tee_flag = 1;
            } else {
                $tee_flag = 0;
            }
            $bld_1 = $this->input->post('bld_1');
            $bld_2 = $this->input->post('bld_2');
            $bld_3 = $this->input->post('bld_3');
            if($bld_1 == '') {
                $bld_1 = 0;
            }
            if($bld_2 == '') {
                $bld_2 = 0;
            }
            if($bld_3 == '') {
                $bld_3 = 0;
            }
            $pre_courses_exploded = explode(",", $_POST['hidden-tags']);
            $pre_courses = implode('<>', $pre_courses_exploded);
            //course table data
            $crclm_id = $this->input->post('crclm_id');
            $crclm_term_id = $this->input->post('crclm_term_id');
            $crs_type_id = $this->input->post('crs_type_id');
            $crs_code = $this->input->post('crs_code');
            $crs_mode = $this->input->post('crs_mode');
            $crs_title = $this->input->post('crs_title');
            $crs_acronym = $this->input->post('crs_acronym');
            $co_crs_owner = $this->input->post('co_crs_owner');
            $crs_domain_id = $this->input->post('crs_domain_id');
            $lect_credits = $this->input->post('lect_credits');
            $tutorial_credits = $this->input->post('tutorial_credits');
            $practical_credits = $this->input->post('practical_credits');
            $self_study_credits = 0;
            $total_credits = $this->input->post('total_credits');
            $contact_hours = $this->input->post('contact_hours');
            $cie_marks = $this->input->post('cie_marks');
            $mid_term_marks = $this->input->post('mid_term_marks');
            $see_marks = $this->input->post('see_marks');
            $attendance_marks = (int) $this->input->post('attendance_marks');
            $ss_marks = 0;
            $total_marks = $this->input->post('total_marks');
            $see_duration = $this->input->post('see_duration');
            $total_cia_weightage = (float) $this->input->post('total_cia_weightage');
            $total_mte_weightage = (float) $this->input->post('total_mte_weightage');
            $total_tee_weightage = (float) $this->input->post('total_tee_weightage');
            $cia_passing_marks = $this->input->post('cia_passing_marks');
            $tee_passing_marks = $this->input->post('tee_passing_marks');
            //course_clo_owner
            $course_designer = $this->input->post('course_designer');
            //course_clo_reviewer
            $course_reviewer = $this->input->post('course_reviewer');
            $review_dept = $this->input->post('review_dept');
            $last_date = $this->input->post('last_date');
            if ($last_date != '')
            {
                $last_date = date("Y-m-d", strtotime($this->input->post('last_date')));
            }
            $clo_bl_flag = $this->input->post('fetch_clo_bl_flag_val');
            $elective_crs_flag = $this->input->post('elective_crs_flag');
            $blm_suggestion = $this->input->post('blm_suggestion');
            if ($elective_crs_flag) {
                $elective_crs_flag = 1;
            }
			$indirect_flag = $this->input->post('indirect');	
            //predecessor courses array
            $pre_courses;
            $course_added = $this->addcourse_model
                    ->insert_course_details(
                    //course table data
                    $crclm_id, $crclm_term_id, $crs_type_id, $crs_code, $crs_mode, $crs_title, $crs_acronym, 
                    
                    $crs_domain_id, $lect_credits, $tutorial_credits, $practical_credits, $self_study_credits, 
                    
                    $total_credits, $contact_hours, $cie_marks, $see_marks, $attendance_marks, $ss_marks, $mid_term_marks,
                    
                    $total_marks, $see_duration, $co_crs_owner, $cia_passing_marks, $tee_passing_marks,
                    //course_clo_owner
                    $course_designer,
                    //course_clo_reviewer
                    $course_reviewer, $review_dept, $last_date,
                    //predecessor courses array
                    $pre_courses,
                    //total weightage of cia and tee 		// added by bhagya
                    $total_cia_weightage, $total_mte_weightage, $total_tee_weightage, $bld_1, $bld_2, $bld_3, $clo_bl_flag, $cia_flag,
                    
                    $mte_flag, $tee_flag, $elective_crs_flag, $blm_suggestion,$tutorial, $indirect_flag
            );

            // if($this->ion_auth->user()->row()->org_name->education_system_flag == 1){
            //     redirect('curriculum/course/manage_students/'.$course_added, 'refresh');
            // }else{
                redirect('curriculum/course', 'refresh');
            //}
        }
    }

//End of function insert_course


    /* Function is used to update the details of a course.
     * @param -
     * @returns- updated list view of course.
     */

    public function update_course() {
        //permission_start
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && $this->ion_auth->in_group('Course Owner')) {
                $course_owner = $this->course_model->course_owner_login_details();
                $data['course_owner_name'] = $course_owner['title'] . ' ' . $course_owner['first_name'] . ' ' . $course_owner['last_name'];
                $data['course_owner_id'] = $course_owner['id'];
            }

            $cia_check = $this->input->post('cia_check');
            $mte_check = $this->input->post('mte_check');
            $tee_check = $this->input->post('tee_check');
            

            if (($cia_check)) {
                $cia_flag = 1;
            } else {
                $cia_flag = 0;
            }
            if (($mte_check) ) {
                $mte_flag = 1;
            } else {
                $mte_flag = 0;
            }
            if (($tee_check)) {
                $tee_flag = 1;
            } else {
                $tee_flag = 0;
            }
            $total_cia_weightage = (float) $this->input->post('total_cia_weightage');
            $total_mte_weightage = (float) $this->input->post('total_mte_weightage');
            $total_tee_weightage = (float) $this->input->post('total_tee_weightage');

            $bld_1 = $this->input->post('bld_1');
            $bld_2 = $this->input->post('bld_2');
            $bld_3 = $this->input->post('bld_3');
            $pre_courses_exploded = explode(",", $_POST['hidden-tags']);
            $pre_courses = implode('<>', $pre_courses_exploded);
            //course table
            $crs_id = $this->input->post('crs_id');
            $crclm_id = $this->input->post('crclm_id');
            $crclm_term_id = $this->input->post('crclm_term_id');
            $crs_type_id = $this->input->post('crs_type_id');
            $crs_code = $this->input->post('crs_code');
            $crs_mode = $this->input->post('crs_mode');
            $crs_title = $this->input->post('crs_title');
            $crs_acronym = $this->input->post('crs_acronym');
            $co_crs_owner = $this->input->post('co_crs_owner_edit');
            $crs_domain_id = $this->input->post('crs_domain_id');
            $preset_crs_domain = $this->input->post('crs_domain');
            $lect_credits = $this->input->post('lect_credits');
            $tutorial_credits = $this->input->post('tutorial_credits');
            $practical_credits = $this->input->post('practical_credits');
            $self_study_credits = 0;
            $total_credits = $this->input->post('total_credits');
            $contact_hours = $this->input->post('contact_hours');
            $cie_marks = $this->input->post('cie_marks');
            $mid_term_marks = $this->input->post('mid_term_marks');
            $see_marks = $this->input->post('see_marks');
            $attendance_marks = (int) $this->input->post('attendance_marks');
            $ss_marks = 0;
            $total_marks = $this->input->post('total_marks');
            $see_duration = $this->input->post('see_duration');

            //course_clo_owner
            $course_designer = $this->input->post('clo_owner_id');
            //course_clo_reviewer
            $review_dept = $this->input->post('review_dept');
            $course_reviewer = $this->input->post('validator_id');
            $last_date = $this->input->post('last_date');
            if ($last_date != '')
            {
            $last_date = date("Y-m-d", strtotime($this->input->post('last_date')));
            }
            $clo_bl_flag = $this->input->post('fetch_clo_bl_flag_val');
            $elective_crs_flag = $this->input->post('elective_crs_flag');
            $new_bloom_suggestion_flag = $this->input->post('new_bloom_suggestion');
            if ($elective_crs_flag) {
                $elective_crs_flag = 1;
            }

            //predecessor courses array
            $pre_courses;
            // delete predecessor courses array
            $del_pre_courses = $this->input->post('delete_crs_id');

            $crs_attainment_finalize_flag = $this->input->post('crs_attainment_finalize_flag');
            $cia_passing_marks = $this->input->post('cia_passing_marks');
            $tee_passing_marks = $this->input->post('tee_passing_marks');
            $tutorial = $this->input->post('tutorial');
            if ($tutorial) {
                $tutorial = 1;
            }
            $indirect_flag = $this->input->post('edit_indirect');
            $course_added = $this->course_model
                    ->update_course_details(
                    //course table data
                    $crs_id, $crclm_id, $crclm_term_id, $crs_type_id, $crs_code, $crs_mode, $crs_title, $crs_acronym,
                    
                    $co_crs_owner, $cia_passing_marks, $tee_passing_marks, $crs_domain_id, $lect_credits, $tutorial_credits,
                    
                    $practical_credits, $self_study_credits, $total_credits, $contact_hours, $cie_marks, $see_marks, $attendance_marks, 
                    
                    $ss_marks, $mid_term_marks, $total_marks, $see_duration,
                    //course_clo_owner
                    $course_designer,
                    //course_clo_reviewer
                    $review_dept, $course_reviewer, $last_date,
                    //predecessor courses array
                    $pre_courses,
                    // delete predecessor courses array
                    $del_pre_courses,
                    // total weightage of cia and tee
                    $total_cia_weightage, $total_mte_weightage, $total_tee_weightage, $bld_1, $bld_2, $bld_3, $clo_bl_flag, $cia_flag, 
                    $mte_flag, $tee_flag, $elective_crs_flag, $preset_crs_domain,
                    $crs_attainment_finalize_flag, $new_bloom_suggestion_flag,$tutorial, $indirect_flag
            );
            
            redirect('curriculum/course', 'refresh');
        }
    }

// End of function update.

    /* .
     * @param-
     * @retuns - the list view of all courses.
     */

    public function course_index($curriculum_id) {
        //permission_start 
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $crclm_list = $this->course_model->crclm_fill();
            $data['curriculum_id'] = $curriculum_id;
            $data['curriculum_data'] = $crclm_list['res2'];
            
            $data['title'] = 'Course List Page';
            

                    
            $this->load->view('curriculum/course/list_course_vw', $data);
        }
    }

    /* Function is used to check whether bloom's domain is using in the co or tlo.
     * @param - 
    * @returns- a boolean value.
     */

    public function check_disable_bloom_domain() {
        $bld_id = $this->input->post('bld_id');
        $crs_id = $this->input->post('crs_id');
        $data['result'] = $this->course_model->check_disable_bloom_domain($bld_id, $crs_id);
        if($data['result']['course_result']){
            $result = $this->load->view('curriculum/course/bloom_domain_modal_vw', $data, true);
        } else {
            $result = " ";
        }
        
        echo json_encode(array('result' => $result, 'modal_display' => $data['result']['modal_display']));
    }

    // End of function index.

    /*
     * Function to allot course instructor for section
     */
    public function assign_course_instructor() {
        $crs_id = $this->input->post('course_id');
        $trm_id = $this->input->post('term_id');
        $crclm_id = $this->input->post('crclm_id');
        $section_id = $this->input->post('section_id');
        $assign_course_instructor = $this->course_model->assign_course_instructor($crs_id, $trm_id, $crclm_id);
     
        $section_list = $this->course_model->fetch_section_list($crclm_id, $trm_id, $crs_id);

        // Displaying user list
        if ($assign_course_instructor['course_instructor_list']) {
            $i = 0;
            //$list[$i] = '<option value="">Select User</option>';
            $i++;
            foreach ($assign_course_instructor['course_instructor_list'] as $data) {
                $list[$i] = "<option value=" . $data['id'] . " title='" . $data['email'] . "'>" . $data['title'] . " " . ucfirst($data['first_name']) . " " . ucfirst($data['last_name']) . "</option>";
                $i++;

                $course_instructor_array[$data['id']] = $data['title']  . ucfirst($data['first_name']) . " " . ucfirst($data['last_name']);
            }

            $list = implode(" ", $list);
            $course_instructor_data['user_list'] = $list;
        } else {
            $i = 0;
            $list[$i] = '<option value="">No User in this Department </option>';
            $list = implode(" ", $list);
            $course_instructor_data['user_list'] = $list;
        }

        //Displaying Section Dropdown box
        $sectionList = '';
        $section_array_list = array();
        if ($section_list) {

            $sectionList .= '<option value="">Select</option>';

            foreach ($section_list as $section) {
                $sectionList .= "<option value=" . $section['mt_details_id'] . " title='Section/Divsion " . $section['mt_details_name'] . "'>" . $section['mt_details_name'] . "</option>";
                $section_array[$section['mt_details_id']] = $section['mt_details_name'];
                $section_array_list[$section['mt_details_id']] = $section['mt_details_name'];
            }
            $course_instructor_data['section_list'] = $sectionList;
        } else {
            $sectionList .= '<option value="">No Data </option>';
            $course_instructor_data['section_list'] = $sectionList;
        }

        $table_row = '';
        $counter = 1;
        // Displaying Course Instructor List
        foreach ($assign_course_instructor['course_instructor_display'] as $ci) {
            empty($available_section_list);
            $available_section_list = $section_array_list;
            $available_section_list[$ci['section_id']] = $ci['section'];
            asort($available_section_list);
            $section_array[$ci['section_id']] = $ci['section'];
            $table_row .= '<tr>';
            $table_row .= '<td style="text-align:right;">' . $counter . '</td>';
            $table_row .= '<td>';
            $table_row .= '<font id="section_name_text_' . $counter . '" >';
            $table_row .= $ci['section'];
            $table_row .= '</font>';
            $table_row .= '<div id="section_name_list_' . $counter . '" class="input-append" style="display:none;"> ';
            $table_row .= form_dropdown('section_list', $available_section_list,'', 'id="section_list_' . $counter . '" style="" class="section_list"');
            $table_row .= '</div> ';
            $table_row .= '</td> ';

            $table_row .= '<td><font id="instructor_name_' . $counter . '">' . $ci['user_name'] . '</font> '
                            . ' <div id="show_instructor_dropdown_' . $counter . '" class="input-append" '
                            . ' style="display:none;">' . form_dropdown('instructor_list', $course_instructor_array,$ci['course_instructor_id'], ' '
                                    . ' id="instructor_list_' . $counter . '" multiple="multiple" style=""   class="instructor_list"') . ''
                            . ' <button type="button" name="save_data"  id="save_data_' . $counter . '"'
                            . ' class="btn btn-primary save_data_button" data-save_counter = "' . $counter . '" '
                            . ' data-edit_id="' . $ci['mcci_id'] . '"><i class="icon-file icon-white"></i> Update</button></div></td>';
           
            $table_row .= '<td><center><a crclm-id="' . $ci['crclm_id'] . '" term-id="' . $ci['crclm_term_id'] . '" id="edit_instructor_' . $counter . '" data-crs_id="' . $crs_id . '" data-section_id = "' . $ci['section_id'] . '"  data-edit_counter="' . $counter . '" class="cursor_pointer edit_instructor"><i class="icon-pencil"></i></a></center></td>';
            $table_row .= '<td><center><a data-crs_id="' . $ci['crs_id'] . '" data-section_name = "' . $ci['section'] . '" data-section_id = "' . $ci['section_id'] . '"  id="delete_instructor_' . $counter . '" data-delete_id = "' . $ci['mcci_id'] . '"  class="cursor_pointer delete_instructor"><i class="icon-remove"></i></a></center></td>';
            $table_row .= '</tr>';
            $counter++;
        }

        $course_instructor_data['course_instructor_display'] = $table_row;

        echo json_encode($course_instructor_data);
    }

    /*
     * Function to assign course instructors to different sections.
     */

     public function add_new_course_instructor() {
        $instructor_id = $this->input->post('instructor_id');
        $section_id = $this->input->post('section_id');
        $ci_crclm_id = $this->input->post('ci_crclm_id');
        $ci_term_id = $this->input->post('ci_term_id');
        $ci_crs_id = $this->input->post('ci_crs_id');
        $assign_course_instructor = $this->course_model->add_course_instructor($section_id, $instructor_id, $ci_crclm_id, $ci_term_id, $ci_crs_id);
        $section_list = $this->course_model->fetch_section_list($ci_crclm_id, $ci_term_id, $ci_crs_id);
        $get_section_name = $this->course_model->get_section_name($section_id);
        if ($assign_course_instructor == '-1') {
            echo '-1';
        } else {
           
            $data['populate_table'] = $this->populate_table($ci_crs_id, $ci_crclm_id, $ci_term_id, $section_id, $instructor_id, $get_section_name);
            $data['section_options'] = '';
            if (!empty($section_list)) {

                $data['section_options'] .= '<option value="">Select</option>';

                foreach ($section_list as $section) {
                    $data['section_options'] .= "<option value=" . $section['mt_details_id'] . " title='Section/Divsion " . $section['mt_details_name'] . "'>" . $section['mt_details_name'] . "</option>";
                }

                $data['section_list'] = $data['section_options'];
            } else {
                $data['section_options'] .= '<option value="">No Data </option>';
                $data['section_list'] = $data['section_options'];
            }
            
            echo json_encode($data);
        }
    }

    /*
     * Function to populate table.
     */

    public function populate_table($ci_crs_id = NULL, $ci_crclm_id = NULL, $ci_term_id = NULL, $section_id=NULL, $instructor_id=NULL, $section_name=NULL) {
        $populate_table = $this->course_model->generate_table($ci_crs_id, $ci_crclm_id, $ci_term_id);
        $section_list = $this->course_model->fetch_section_list($ci_crclm_id, $ci_term_id, $ci_crs_id);
        if ($populate_table['ins_list']) {
            foreach ($populate_table['ins_list'] as $data) {
                $course_instructor_array[$data['id']] = $data['title'] . " " . ucfirst($data['first_name']) . " " . ucfirst($data['last_name']);
            }
        }

        //Displaying Section Dropdown box
        $sectionList = '';
        $section_array_list = array();
        if ($section_list) {
            $sectionList .= '<option value="">Select</option>';

            foreach ($section_list as $section) {
                $sectionList .= "<option value=" . $section['mt_details_id'] . " title='Section/Divsion " . $section['mt_details_name'] . "'>" . $section['mt_details_name'] . "</option>";
                $section_array[$section['mt_details_id']] = $section['mt_details_name'];
                $section_array_list[$section['mt_details_id']] = $section['mt_details_name'];
            }
            $course_instructor_data['section_list'] = $sectionList;
        } else {
            $sectionList .= '<option value="">No Data </option>';
            $course_instructor_data['section_list'] = $sectionList;
        }

        $counter = 1;
        $table_row = '';

        // Displaying Course Instructor List
        foreach ($populate_table['instructor_data'] as $ci) {
            empty($available_section_list);
            $available_section_list = $section_array_list;
            $available_section_list[$ci['section_id']] = $ci['section'];
            asort($available_section_list);
            $section_array[$ci['section_id']] = $ci['section'];
            $table_row .= '<tr>';
            $table_row .= '<td>' . $counter . '</td>';
            $table_row .= '<td>';
            $table_row .= '<font id="section_name_text_' . $counter . '" >';
            $table_row .= $ci['section'];
            $table_row .= '</font>';
            $table_row .= '<div id="section_name_list_' . $counter . '" class="input-append" style="display:none; color:red;"> ';
            $table_row .= form_dropdown('section_list', $available_section_list, $ci['section_id'], 'id="section_list_' . $counter . '" style="" class="section_list"');
            $table_row .= '</div> ';
            $table_row .= '</td> ';
            $table_row .= '<td><font id="instructor_name_' . $counter . '">' . $ci['user_name'] . '</font> '
                    . ' <div id="show_instructor_dropdown_' . $counter . '" class="input-append" '
                    . ' style="display:none;">' . form_dropdown('instructor_list', $course_instructor_array, $ci['course_instructor_id'], ' '
                            . ' id="instructor_list_' . $counter . '" multiple="multiple" style="" class="instructor_list"') . ''
                    . ' <button type="button" name="save_data" id="save_data_' . $counter . '"'
                    . ' class="btn btn-primary save_data_button" data-save_counter = "' . $counter . '" '
                    . ' data-edit_id="' . $ci['mcci_id'] . '"><i class="icon-file icon-white"></i> Update</button></div></td>';

            $table_row .= '<td><center><a data-crs_id = "' . $ci_crs_id . '" data-section_name="' . $ci['section'] . '" data-section_id="' . $ci['section_id'] . '" id="edit_instructor_' . $counter . '" data-edit_counter="' . $counter . '" class="cursor_pointer edit_instructor"><i class="icon-pencil"></i></a></center></td>';
            $table_row .= '<td><center><a data-crs_id = "' . $ci_crs_id . '" data-section_name="' . $ci['section'] . '" data-section_id="' . $ci['section_id'] . '" id="delete_instructor_' . $counter . '" data-delete_id = "' . $ci['mcci_id'] . '" class="cursor_pointer delete_instructor"><i class="icon-remove"></i></a></center></td>';
            $table_row .= '</tr>';
            $counter++;
        }
        return $table_row;
    }

    /*
     * Function to Edit Save of Course Instructor.
     */

     public function edit_save_course_instructor() {
        $instructor_id = $this->input->post('instructor_id');
        $section_id = $this->input->post('section_id');
        $mcci_id= $this->input->post('mcci_id');
        $ci_crclm_id = $this->input->post('ci_crclm_id');
        $ci_term_id = $this->input->post('ci_term_id');
        $ci_crs_id = $this->input->post('ci_crs_id');
        $edit_result = $this->course_model->edit_save_instructor($ci_crclm_id,$ci_term_id,$instructor_id, $section_id, $mcci_id, $ci_crs_id);
        $section_list = $this->course_model->fetch_section_list($ci_crclm_id, $ci_term_id, $ci_crs_id);

        $data['section_options'] = '<option value>Select</option>';
        foreach ($section_list as $section) {
            $data['section_options'] .= "<option value=" . $section['mt_details_id'] . " title='Section/Division " . $section['mt_details_name'] . "'>" . $section['mt_details_name'] . "</option>";
        }
        echo $data['section_options'];
    }

    /*
     * Function to delete to course instructor.
     */

    public function delete_course_instructor() {
        $mcci_id = $this->input->post('delete_instructor');
        $crclm_id = $this->input->post('crclm_id');
        $term_id = $this->input->post('term_id');
        $course_id = $this->input->post('course_id');
        $sec_id = $this->input->post('section_id');
        $password = $this->input->post('password');

        $lms_flag = $this->course_model->fetch_lms_attendance($course_id,$sec_id);
        $chk_marks_uploded = $this->course_model->chk_marks_uploded($course_id,$sec_id);
        
            /* delete of course_instructor with password confirmation */
        if ($lms_flag == 4 || !empty($chk_marks_uploded) && $chk_marks_uploded[0]['crs_occasion_upload_status'] >= 1  ) {
            if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
                echo "error";
            } else {
                $remember = (bool) $this->input->post('remember');
                $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));
                if (!$this->ion_auth->login($identity, $password, $remember)) {
                    echo  "2";
                } else {
                    $this->delete_crs_instr_section($mcci_id,$crclm_id,$term_id,$course_id,$sec_id);
                }
            }
        } else {
            /* delete of course_instructor without password */
            $this->delete_crs_instr_section($mcci_id,$crclm_id,$term_id,$course_id,$sec_id);
        }
    }

    function delete_crs_instr_section($mcci_id,$crclm_id,$term_id,$course_id,$sec_id) {
        $delete_record = $edit_result = $this->course_model->delete_instructor($mcci_id, $crclm_id, $term_id, $course_id, $sec_id);
        $section_list = $this->course_model->fetch_section_list($crclm_id, $term_id, $course_id);
        $data['populate_table'] = $this->populate_table($course_id, $crclm_id, $term_id);
    
        $data['section_options'] = '';
            if (!empty($section_list)) {
    
                $data['section_options'] .= '<option value="">Select</option>';
    
                foreach ($section_list as $section) {
                    $data['section_options'] .= "<option value=" . $section['mt_details_id'] . " title='Section/Divsion " . $section['mt_details_name'] . "'>" . $section['mt_details_name'] . "</option>";
                }       
                $data['section_list'] = $data['section_options'];
            } else {
                    $data['section_options'] .= '<option value="">No Data </option>';
                    $data['section_list'] = $data['section_options'];
            }       
        echo json_encode($data);
    }

    /*
     * Function to check the co finalized or not
     */

    public function section_co_finalize() {
        $course_id = $this->input->post('crs_id');
        $sec_id = $this->input->post('section_id');
        $check_finalize = $edit_result = $this->course_model->section_co_finalize($course_id, $sec_id);
        echo $check_finalize;
    }

    /*
      Function to check the assessment occasions defined for the section.
      @param: course id, section id.
      @return: 0:False, 1:True.
     */

     public function check_assessment_occasion() {
        $course_id =$this->input->post('course_id');
        $sec_id = $this->input->post('section_id');
        $check_finalize = $edit_result = $this->course_model->check_occasion($course_id, $sec_id);
        if ($check_finalize != 1) {
            $data['result'] = 0;
            $data['msg'] = 'You Cannot Edit this Section as there are Assessment Ocassions are already define. <br> To Edit this section, please delete all the Occasions and then try to Edit the Section.';
        } else {
            $data['result'] = 1;
            $data['msg'] = '';
            $data['sec'] = $sec_id;
        }

        echo json_encode($data);
    }
	
	/*
      Function to check if the assessments exists for the course
      @param: 
      @return: 
     */

    public function check_course_assessments() {
		$params['assessment_type'] = $assessment_type = $this->input->post('assessment_type');
		$params['crs_id'] = $this->input->post('crs_id');
		$data = array();
		$data['assessment_content'] = '';
		$course_assessments = $this->course_model->check_course_assessments($params);
		if($course_assessments[0]['assessment_exists'] > 0) {
			$data['assessment_weightage'] = $course_assessments[0]['assessment_weightage'];
			switch($assessment_type) {
				case 'cia': $data['assessment_content'] = $this->lang->line('cia_assessment_exists');
						break;
				case 'mte': $data['assessment_content'] = $this->lang->line('mte_assessment_exists');
						break;
				case 'tee': $data['assessment_content'] = $this->lang->line('tee_assessment_exists');
						break;
				default: $data['assessment_content'] = $this->lang->line('assessment_exists');
			}
		}
		echo json_encode($data);
	}

	/*
      Function to fetch the assessment weightage
      @param: 
      @return: 
     */

    public function fetch_course_assessment_weightage() {
		$params['crs_id'] = $this->input->post('crs_id');
		$data = array();
		$data = $this->course_model->fetch_course_assessment_weightage($params);
		echo json_encode($data);
    }
    
    /*
      Function to check the co po mapping 
      @param: 
      @return: 
     */
    public function check_co_po_mapping_completed(){
        $params['crs_id'] = $this->input->post('crs_id');
		$data = array();
		$data = $this->course_model->check_co_po_mapping_completed($params);
		echo $data;
    }


    /*
      Function to check the co po mapping 
      @param: 
      @return: 
     */
    public function check_marks_imported_course(){
        $params['crs_id'] = $this->input->post('crs_id');
        $result ;
        $data['assessment_marks_import'] = $this->course_model->check_marks_imported_course($params);
        
        $assessment_created = $data['assessment_marks_import']['assessment_created'];
        if($data['assessment_marks_import']['assessment_result']) {
            $result= $this->load->view('curriculum/course/course_marks_imported_modal_vw', $data, true);
        }  else {
            $result = 0;
        }
        if (($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner'))) {
           $logged_in = 1;
        } else 
        {
            $logged_in = 0;
        }
         echo json_encode(array('result' => $result, 'logged_in' =>$logged_in, 'assessment_created' => $assessment_created));
    }
    
    /*
      Function to fetch CIA & TEE Passing Marks
      @return: 
     */
    public function fetch_passing_marks(){
        $crclm_id = $this->input->post('crclm_id');
		$data = array();
        $data = $this->addcourse_model->fetch_passing_marks($crclm_id);
        
		echo json_encode($data);
    }

    /*
     * Function to render a view to register or add students / remove students to/from the course
     */
    public function manage_students($crs_id = NULL) {
        //permission_start 
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        }
        //permission_end 
        else {
            $crs_id = base64_decode($crs_id);
            if($crs_id != NULL) {
                $course_details = $this->course_model->course_details($crs_id);
				$data['crs_id'] = $crs_id;
                $data['title'] = "Manage Student List";
                $data['crs_title'] = $course_details[0]['crs_code']." - ".$course_details[0]['crs_title'];
                $data['crs_crclm_id'] = $course_details[0]['crclm_id'];
                $data['crs_pgm_id'] = $course_details[0]['pgm_id'];
                $data['crs_crclm_name'] = $course_details[0]['crclm_name'];
                $data['crs_crclm_term_id'] = $course_details[0]['crclm_term_id'];
                $data['crs_crclm_term_name'] = $course_details[0]['term_name'];
                $data['crs_credits'] = $course_details[0]['total_credits'];
                $sections = $this->course_model->fetch_sections($crs_id);
                $section_list = array();
                foreach ($sections as $section) {
                        $section_list[$section['mt_details_id']] = $section['mt_details_name'];
                }
                $data['section_list'] = $section_list;
                $data['std_crclm_data']=$this->course_model->fetch_crclm_list_course_registration();
                $data['std_pgm_data']=$this->course_model->fetch_all_programs_course_registration();
               
                $this->load->view('curriculum/course/register_studentsto_course_vw', $data);
            }
        }
    }

    /*
     * Function to render a view to register or add students / remove students to/from the course
     */
    public function get_manage_student_module() {
        $crs_id = $this->input->post('crs_id');
        $section_id = $this->input->post('section_id');
        $_SESSION['remember_section'] = $section_id;
        $data = array();
        $data['crs_id'] = $crs_id;
        $data['section_id'] = $section_id;
        $data['assessment_data_import_status'] = $this->course_model->check_crs_assessment_marks_uploaded($data);
        $data['batch_wise_section_flag'] = $this->course_model->check_batch_under_section($data);
        $data['finalized_student_list'] = $this->course_model->get_finalized_student_list($data);

        $this->load->view('curriculum/course/manage_student_module_vw', $data);
    }

    /* Function is used to delete the selected students 
     * @param- 
     * @return: 
     */
    public function delete_students() {
        $data = array();
        $password = $this->input->post('password');
        
        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman')) && ($this->ion_auth->in_group('Course Owner') || $this->ion_auth->in_group('Program Owner'))){
            $role_set = 1;
        } else {
            $role_set = 2;
        }
        /* permission for the student delete only for admin and hod */
        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman'))) {
            echo "error";
        } else {
            $remember = (bool) $this->input->post('remember');
            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));

            if (!$this->ion_auth->login($identity, $password, $remember)) {
                echo "2";
            } else {
                $data['delete_values'] = implode(",", $this->input->post('delete_records'));
                $deleted = $this->course_model->delete_students($data,$role_set);
                echo $deleted;
            }
        }
    }
    /* Function is used to fetch batch details.
     * @param- crs id, section id
     * @returns - an object.
     */

    public function batch_list() {
        $data = array();
        $data['crs_id'] = $this->input->post('crs_id');
        $data['section_id'] = $this->input->post('section_id');
        $batch_data = $this->course_model->fetch_batch($data);
        $i = 0;
        $list = array();
        foreach ($batch_data as $batch) {
            $list[$i] = '<li><a href="#" data-section_id="'.$batch['parent_id'].'" data-value="'. $batch['mt_details_id'] .'">'. $batch['mt_details_name'] .'</a></li>';
            $i++;
        }
        $list = implode(" ", $list);
        echo $list;
    }

    /* Function is used to change batch for selected students 
     * @param- 
     * @return: 
     */
    public function change_batch() {
        $data = array();
        $data['batch_id'] = $this->input->post('batch_id');
        $data['section_id'] = $this->input->post('section_id');

        $data['student_records'] = implode(",", $this->input->post('student_records'));
        $updated = $this->course_model->change_batch($data);
        echo $updated;
    }

    /* Function is used to change student status
     * @param- 
     * @return: 
     */
    public function change_student_status() {
        $data = array();
        $data['user_mapping_id'] = $this->input->post('user_mapping_id');
        $data['status'] = $this->input->post('status');
        $updated = $this->course_model->change_student_status($data);
        echo $updated;
    }

    /*
	* Function is to download excel template file
	* @parameters: 
	* @return: 
	*/
	public function download_stud_reg_template(){
		$crs_id = $this->input->get('crs_id'); 	
		$section_id = $this->input->get('section_id');  

        $data = array();
        $data['crs_id'] = $crs_id;
        $data['section_id'] = $section_id;
        $batch_wise_section_flag = $this->course_model->check_batch_under_section($data);

        $this->load->library('excel');
		//activate worksheet number 1
		$this->excel->setActiveSheetIndex(0);
		//name the worksheet
		$this->excel->getActiveSheet()->setTitle('Student Registration Template');
		//set cell A1 content with some text
		$this->excel->getActiveSheet()->setCellValue('A1', 'Student '.$this->lang_student_usn);
		$this->excel->getActiveSheet()->setCellValue('B1', 'Student Name');
        if($batch_wise_section_flag == 1){
            $batch_data = $this->course_model->fetch_batch($data);
            $batchs = array();
            foreach ($batch_data as $batch) {
                $batchs[] = $batch["mt_details_name"];
            }
            $batch_list = implode(",", $batchs);

            $this->excel->getActiveSheet()->setCellValue('C1', 'Batch');
		
            /* Batch Dropdown */
            for ($i = 2; $i <= 150; $i++)
            {
                $objValidation1 = $this->excel->getActiveSheet()->getCell('C' . $i)->getDataValidation();
                $objValidation1->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                $objValidation1->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                $objValidation1->setAllowBlank(false);
                $objValidation1->setShowInputMessage(true);
                $objValidation1->setShowDropDown(true);
                $objValidation1->setPromptTitle('Pick from list');
                $objValidation1->setPrompt('Please pick a value from the drop-down list.');
                $objValidation1->setErrorTitle('Input error');
                $objValidation1->setError('Value is not in list');
                $objValidation1->setFormula1('"' . $batch_list . '"');
            }
            for ($i = 2; $i <= 150; $i++){
                $this->excel->getActiveSheet()->setCellValue('C'.$i.'', @$batch_data[0]['mt_details_name']);
            }
        }
        
        $filename = $this->course_model->create_file_name($data); 
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        //force user to download the Excel file without writing it to server's HD
        ob_end_clean();
        ob_start();
        $objWriter->save('php://output');
	}

    /*
     * Function is to check if there is any data inside the .csv file
     * @parameters: file path
     * @return: flag
     */
    public function csv_file_check_data($full_path) {
        //Fetch file headers
        if (($file_handle = fopen($full_path, "r")) != FALSE) {
            $row = 0;
            while (($data = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
                $row++;
            }
        }

        // close the file
        fclose($file_handle);
        return $row;
    }

    /*
     * Function is to store excel imported data to database table
     * @parameters: 
     * @return: flag
     */
    public function excel_to_database() {
        $this->load->library('excel');
		if (!empty($_FILES)) {
            $tmp_file_name = $_FILES['Filedata']['tmp_name'];
            $name = $_FILES['Filedata']['name'];
            $tmp = explode(".", $name);
            $ext = end($tmp);
			if ($ext == 'xls') {
                $file_uploaded = move_uploaded_file($tmp_file_name, "./uploads/$name");
				if($file_uploaded) {
					$crs_id = $this->input->post('crs_id');
                    $section_id = $this->input->post('section_id');
                    $crclm_term_id = $this->input->post('crs_crclm_trm_id');
					$params['crs_id'] = $crs_id;
					$params['section_id'] = $section_id;
                    $params['crclm_term_id'] = $crclm_term_id;
                    $batch_wise_section_flag = $this->course_model->check_batch_under_section($params);
					if (($file_handle = fopen("./uploads/$name", "r")) != FALSE) {
						//read file from path
						$objPHPExcel = PHPExcel_IOFactory::load("./uploads/$name");

						//get only the Cell Collection
                        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                        if(!empty($cell_collection)){
                            //extract to a PHP readable array format
                            foreach ($cell_collection as $cell) {
                                $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                                $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();                      
                                //header will/should be in row 1 only. of course this can be modified to suit your need.
                                
                                if ($row == 1) {
                                    $header[$row][$column] = $data_value;
                                } else {
                                    $arr_data[$row][$column] = $data_value;
                                }
                            }
                            $file_header_array = array();
                            foreach ($header[1] as $head) {
                                array_push($file_header_array, preg_replace('/\s+/', '', $head));
                            }
                            
                            $comma_separated = implode(',', $file_header_array);
                            
                            $list = array();
                            if($batch_wise_section_flag == 1){
                                $list = array('Student'.$this->lang_student_usn, 'StudentName', 'Batch');
                            }else{
                                $list = array('Student'.$this->lang_student_usn, 'StudentName');
                            }

                            //compare both header fields
                            $header_str_cmp = strcmp($comma_separated, implode(",", $list));
                        }else{
                            $header_str_cmp = 1;
                        }	
						 
						if ($header_str_cmp == 0) {
							// If headers of the file matches
							$csv_data_count = $this->csv_file_check_data("./uploads/$name");
							if ($csv_data_count == 1) {
								echo '4';
							} else {
								$params['upload_location'] = "./uploads/$name";
								$params['name'] = $name;
								$params['file_header_array'] = $file_header_array;
								$temp_tab_name = $this->course_model->load_excel_to_temp_table($params); // upload file to database
                                $params['table_name'] = $temp_tab_name;
                                $data = array();
								$data['stud_crs_data'] = $this->course_model->get_temp_student_crs_data($params);
                                if(empty($data['stud_crs_data'])){
                                    $this->course_model->drop_temp_table($crs_id, $section_id);
                                    echo 4;
                                }else{
                                    $data['batch_wise_section_flag'] = $batch_wise_section_flag;

                                    $data = $this->load->view('curriculum/course/import_student_to_course_table', $data, true);
                                    echo $data;
                                }
								
								// close the file
								fclose($file_handle);
							}
						} else {
							// If headers of the file mismatch
							echo 3; //File does not match with downloaded file format
						}
					} else {
						echo 2; //Unable to open and read data from the uploaded file
					}
				} else {
					echo 1; //File could not be uploaded to the designated location
				}
			}else{
                echo 6;
            }
		}
	}

    /*
     * Function is to discard temporary table on cancel
     * @parameters:
     * @return: boolean value
     */
    public function drop_temp_table() {
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('login', 'refresh');
        } elseif (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') || $this->ion_auth->in_group('Course Owner'))) {
            //redirect them to the home page because they must be an administrator or owner to view this
            redirect('curriculum/peo/blank', 'refresh');
        } else {
            $crs_id = $this->input->post('crs_id');
            $section_id = $this->input->post('section_id');
            $drop_result = $this->course_model->drop_temp_table($crs_id, $section_id);

            return true;
        }
    }

    /*
     * Function is to register the student to course from bulk registering using standard template
     * @parameters: 
     * @return: 
     */
	public function import_student_course_register() {
        $params['crclm_id'] = $this->input->post('crclm_id');
        $params['crclm_trm_id'] = $this->input->post('crclm_trm_id');
        $params['crs_id'] = $this->input->post('crs_id');
        $params['section_id'] = $this->input->post('section_id');
		$register_student = $this->course_model->import_student_course_register($params);
		echo $register_student;
		exit;
    }

    /*
     * Function is to check Remarks exist or not
     * @parameters: 
     * @return: 
     */
    public function check_remarks_exists() {
        $params['crs_id'] = $this->input->post('crs_id');
        $params['section_id'] = $this->input->post('section_id');
        $result = $this->course_model->check_remarks_exists($params);

        echo $result;
    }

    public function get_students_by_crclm() {
        $params['crclm_id'] = $this->input->post('crclm_id');
        $params['crs_id'] = $this->input->post('crs_id');
        $params['section_id'] = $this->input->post('section_id');
        $student_list = $this->course_model->get_students_by_crclm($params);

        echo json_encode($student_list);
    }

    /*
     * Function is to register the student to course from same curriculum
     * @parameters: 
     * @return: 
     */
	public function register_selected_students() {
        $params['crclm_id'] = $this->input->post('crclm_id');
        $params['std_crclm_id'] = $this->input->post('std_crclm_id');
        $params['crclm_trm_id'] = $this->input->post('crclm_trm_id');
        $params['crs_id'] = $this->input->post('crs_id');
        $params['section_id'] = $this->input->post('section_id');
        $params['selectedStudent'] = $this->input->post('selectedStudent');
		$register_student = $this->course_model->register_selected_students($params);

		echo json_encode($register_student);
    }



    function download_excel() {

        $crclm_id = $this->input->get('crclm_id');
        $term_id = $this->input->get('term_id');
		$this->load->library('excel');
		//activate worksheet number 1
		$this->excel->setActiveSheetIndex(0);
		//name the worksheet
		$this->excel->getActiveSheet()->setTitle('Course Outcome');
		//set cell  content with some text
		$this->excel->getActiveSheet()->setCellValue('A1', 'Course Code');
		$this->excel->getActiveSheet()->setCellValue('B1', 'CO Code');
        $this->excel->getActiveSheet()->setCellValue('C1', 'Course Outcome(CO)');
        $clo_excel_data = $this->course_model->download_crs_code_data($crclm_id,$term_id);
        $z = 2;
        $y = 0;
        $m = 2;
        for($i=0;$i<count($clo_excel_data['crs_code_list'][0]);$i++) { //no of course
            foreach ($clo_excel_data['crs_code_list'] as $key=>$val) {     
                $this->excel->getActiveSheet()->setCellValue('A'.$m, $val[$y]['crs_code']);
                $m++;
            }  
            $z++;
            $y++;
        }
       
        foreach ($clo_excel_data['crs_code_list'] as $crs) {
            $co_data = $this->course_model->fetch_co_codes($crclm_id);
                $cos = array();
                foreach ($co_data as $co) {
                    $cos[] = $co["co_code"];
                }
                $co_data = implode(",", $cos);
                $this->excel->getActiveSheet()->setCellValue('B1', 'CO Code');
                for ($i = 2; $i < $m; $i++) {
                    $objValidation2 = $this->excel->getActiveSheet()->getCell('B' . $i)->getDataValidation();
                    $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                    $objValidation2->setAllowBlank(false);
                    $objValidation2->setShowInputMessage(true);
                    $objValidation2->setShowDropDown(true);
                    $objValidation2->setPromptTitle('Pick from list');
                    $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation2->setErrorTitle('Input error');
                    $objValidation2->setError('Value is not in list');
                    
                    $objValidation2->setFormula1('"' . $co_data . '"');
                }
                $cn = 2;
                for ($i = 0; $i < count($crs); $i++){
                    for($n=0;$n<=5;$n++) {
                        $codata = explode(',', trim($co_data ))[$n];
                        $this->excel->getActiveSheet()->setCellValue('B'.$cn.'', $codata);
                        $cn++;
                    }
                }
        }
      
     
        $bloom_level_data = $this->course_model->get_all_bloom_level_clo();
        $levels = array();
        foreach ($bloom_level_data as $level) {
            $levels[] = $level["level"];
        }
        $bloom_level = implode(",", $levels);
        $bloom_level = '-,'.$bloom_level;
        $this->excel->getActiveSheet()->setCellValue('D1', 'Bloom\'s Level1');
        for ($j = 2; $j < $m; $j++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('D' . $j)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $objValidation2->setFormula1('"' . $bloom_level . '"');
        }
        for ($j = 2; $j < $m; $j++) {
            $this->excel->getActiveSheet()->setCellValue('D'.$j.'', '-');
        }
       
        $this->excel->getActiveSheet()->setCellValue('E1', 'Bloom\'s Level2');
        for ($k = 2; $k < $m; $k++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('E' . $k)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $objValidation2->setFormula1('"' . $bloom_level . '"');
        }
        for ($k = 2; $k < $m; $k++){
            $this->excel->getActiveSheet()->setCellValue('E'.$k.'', '-');
        }
       
        $delivery_method_data = $this->course_model->get_all_delivery_method($crclm_id);
        $methods = array();
        foreach ($delivery_method_data as $method) {
            $methods[] = $method["delivery_mtd_name"];
        }
        $delivery_method = implode(",", $methods);
        $this->excel->getActiveSheet()->setCellValue('F1', 'Delivery Method');
        for ($l = 2; $l < $m; $l++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('F' . $l)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $deliverymethod = explode(',', trim($delivery_method ))[0];
            $objValidation2->setFormula1('"' . $delivery_method . '"');
        }
        for ($l = 2; $l < $m; $l++){
            $this->excel->getActiveSheet()->setCellValue('F'.$l.'', 'Class Room Delivery');
        }

        $po_data = $this->course_model->get_all_po($crclm_id);
        $pos = array();
        foreach ($po_data as $po) {
            $pos[] = $po["po_reference"];
        }
        $po_data = implode(",",$pos);
        $po_data = '-,'.$po_data;

        $this->excel->getActiveSheet()->setCellValue('G1', 'PO mapping1');
        for ($l = 2; $l < $m; $l++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('G' . $l)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $podata = explode(',', trim($po_data ))[0];
            $objValidation2->setFormula1('"' . $po_data . '"');
        }
        for ($pn = 2; $pn < $m; $pn++){
            $this->excel->getActiveSheet()->setCellValue('G'.$pn.'', '-');
        }
                     
        $this->excel->getActiveSheet()->setCellValue('H1', 'PO mapping2');
        for ($l = 2; $l < $m; $l++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('H' . $l)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $podata = explode(',', trim($po_data ))[0];
            $objValidation2->setFormula1('"' . $po_data . '"');
        }
        for ($n = 2; $n < $m; $n++){
            $this->excel->getActiveSheet()->setCellValue('H'.$n.'', '-');
        }


        $pso_data = $this->course_model->get_all_pso($crclm_id);
        $pso = array();
        foreach ($pso_data as $pso) {
            $psoo[] = $pso["po_reference"];
        }
        $pso_data = implode(",", $psoo);
        $po_data = '-,'.$po_data;
        $this->excel->getActiveSheet()->setCellValue('I1', 'PSO mapping1');
        for ($l = 2; $l < $m; $l++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('I' . $l)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $psodata = explode(',', trim($pso_data ))[0];
            $objValidation2->setFormula1('"' . $pso_data . '"');
        }
        for ($o = 2; $o < $m; $o++){
            $this->excel->getActiveSheet()->setCellValue('I'.$o.'', '-');
        }

        $this->excel->getActiveSheet()->setCellValue('J1', 'PSO mapping2');
        for ($l = 2; $l < $m; $l++) {
            $objValidation2 = $this->excel->getActiveSheet()->getCell('J' . $l)->getDataValidation();
            $objValidation2->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation2->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation2->setAllowBlank(false);
            $objValidation2->setShowInputMessage(true);
            $objValidation2->setShowDropDown(true);
            $objValidation2->setPromptTitle('Pick from list');
            $objValidation2->setPrompt('Please pick a value from the drop-down list.');
            $objValidation2->setErrorTitle('Input error');
            $objValidation2->setError('Value is not in list');
            $psodata = explode(',', trim($pso_data ))[0];
            $objValidation2->setFormula1('"' . $pso_data . '"');
        }
        for ($p = 2; $p < $m; $p++){
            $this->excel->getActiveSheet()->setCellValue('J'.$p.'', '-');
        }
       
		$filename='Course_Outcome_POMapping_Standard_Template_'.$term_id.'.xls'; 
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
		
		//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
		//if you want to save it as .XLSX Excel 2007 format
		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
		//force user to download the Excel file without writing it to server's HD
        ob_end_clean();
        ob_start();
		$objWriter->save('php://output');
    }

    
     /*
     * Function is to drop temporary table created during bulk import of CO
     * @parameters: crclm_id,term_id
     * @return: flag
     */
    function drop_co_temp_table() {
        // As per code review, comment given by the reviewer code has been updated.
        $crclm_id = $this->input->post('crclm_id');
        $term_id = $this->input->post('term_id');
        $status = $this->course_model->drop_co_temp_table($crclm_id,$term_id);
        echo $status;
    }

    /*
     * Function is to store excel imported data to database table
     * @parameters: 
     * @return: flag
     */
    function co_excel_to_database() {
        $this->load->library('excel');
        if (!empty($_FILES)) {
            $tmp_file_name = $_FILES['Filedata']['tmp_name'];
            $name = $_FILES['Filedata']['name'];
			$name_exploded = explode(".", $name);
            $crclm_id = $this->input->get('crclm_id');
            $term_id = $this->input->get('term_id');
            $ext = end($name_exploded);
            $uploaded_file = $name_exploded[0];
			
            

            $filename = substr($uploaded_file, strpos($uploaded_file, '_'.$term_id));
            $newfilename = preg_replace('/\([^)]*\)|[()]/', '', $filename);
            if(trim($newfilename) == '_'.$term_id) {

            
            if ($ext == 'xls') {
                $ok = move_uploaded_file($tmp_file_name, "./uploads/$name");
                if (($file_handle = fopen("./uploads/$name", "r")) != FALSE) {
                    //read file from path
                    $objPHPExcel = PHPExcel_IOFactory::load("./uploads/$name");

                    //get only the Cell Collection
                    $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                    //extract to a PHP readable array format
                    foreach ($cell_collection as $cell) {
                        $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                        $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                        $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();     
                        //header will/should be in row 1 only. of course this can be modified to suit your need.
                        if ($row == 1) {
                            $header[$row][$column] = $data_value;
                        } else {
                            $arr_data[$row][$column] = $data_value;
                        }
                    }
                    $file_header_array = array();

                    foreach ($header[1] as $head) {
                        array_push($file_header_array, preg_replace('/\s+/', '', $head));
                    }
                    $comma_separated = implode(',' , $file_header_array);
                    $list = array();
                    $col_head_keys = array('CourseCode', 'COCode','CourseOutcome(CO)','Bloom\'sLevel1','Bloom\'sLevel2','DeliveryMethod','POmapping1','POmapping2','PSOmapping1','PSOmapping2');
                   
                    //compare both header fields
                    $header_str_cmp = strcmp($comma_separated, implode(",", $col_head_keys));
					
                }
                if ($header_str_cmp == 0) {
                    $csv_data_count = $this->co_csv_file_check_data("./uploads/$name");			
                    if ($csv_data_count == 1) {
                        echo '4';
                    } else {
                        $temp_tab_name = $this->course_model->co_load_excel_to_temp_table("./uploads/$name", $name, $file_header_array, $term_id, $crclm_id); // upload file to database   
                        $clo_data = $this->course_model->get_temp_clo_data($temp_tab_name); 
                        $this->co_generate_table_data($clo_data);                 
                        // close the file
                        fclose($file_handle);
                        if (!empty($clo_data)) {
                            echo "<h4 style='color:green; font-size:16px;'>File imported successfully. Kindly verify / check the uploaded data, if there are any remarks correct those and re-upload the file.<br/>If no remarks were found then click on Accept button for final upload to the database</h4>";
                            exit;
                        }
                    }
                } else {
                    echo '1';
                }
            } else {
                echo '2';
            }
        }else {
            echo "5";
        }
        
        } else {
            echo "3";
        }
       
    }

     /*
     * Function is to check if there is any data inside the .csv file
     * @parameters: file path
     * @return: flag
     */
    public function co_csv_file_check_data($full_path) {
        //Fetch file headers
        if (($file_handle = fopen($full_path, "r")) != FALSE) {
            $row = 0;
            while (($data = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
                $row++;
            }
        }

        // close the file
        fclose($file_handle);
        return $row;
    }

    /*
     * Function is to generate table for uploaded clo data from csv
     * @parameters: result set array
     * @return: table
     */
    function co_generate_table_data($clo_data) {
        if (empty($clo_data)) {
            echo "<h4 style='color:red; font-size:16px;'>The Columns: CO Code, Course Outcome fields are Mandatory and cannot be left blank. Kindly verify and re-upload.</h4>";
        } else {
            $table = '';
            $table.= "<table class='table table-bordered'>";
            $table.= "<tr><th>Sl.No</th><th>Remarks</th><th>Course Code</th><th>CO Code</th><th>Course Outcome</th><th>Bloom's Level1</th><th>Bloom's Level2</th><th>Delivery Method</th><th>PO mapping1</th><th>PO mapping2</th><th>PSO mapping1</th><th>PSO mapping2</th></tr>";
            $i = 0;
            foreach ($clo_data as $data) {
                $i++;
                $table.= "<tr>";
                $table.= "<td style='text-align: right;'>" . $i . "</td>";
                $table.= "<td>" . $data['Remarks'] . "</td>";
                $table.= "<td>" . $data['lg_crs_code'] . "</td>";
                $table.= "<td>" . $data['clo_code'] . "</td>";
                $table.= "<td>" . $data['clo_statement'] . "</td>";
                $table.= "<td>" . $data['lg_blooms_level1'] . "</td>";
                $table.= "<td>" . $data['lg_blooms_level2'] . "</td>";
                $table.= "<td>" . $data['delivery_method'] . "</td>";
                $table.= "<td>" . $data['po_map1'] . "</td>";
                $table.= "<td>" . $data['po_map2'] . "</td>";
                $table.= "<td>" . $data['pso_map1'] . "</td>";
                $table.= "<td>" . $data['pso_map2'] . "</td>";
                $table.= "</tr>";
            }
            echo $table;
        }
    }

    /*
     * Function is to insert student stakeholder data to main table from temp table
     * @parameters: crclm_id,term_id
     * @return: flag
     */
    function co_insert_to_main_table() {
        $crclm_id = $this->input->post('crclm_id');
        $term_id = $this->input->post('term_id');
        $status = $this->course_model->co_insert_to_main_table($crclm_id,$term_id);
        echo $status;
    }

    /*---------------------------------------Export / Download CLO START ------------------------------------*/
    function download_clo_data() {
            $crclm_id = $this->input->get('crclm_id');
            $term_id = $this->input->get('term_id');
            $clo_excel_data = $this->course_model->download_clo_data($crclm_id,$term_id);
            $this->load->library('excel');
            //activate worksheet number 1
            $this->excel->setActiveSheetIndex(0);
            //name the worksheet
            $this->excel->getActiveSheet()->setTitle('Course Outcome');
            //set cell A1 content with some text
            $this->excel->getActiveSheet()->setCellValue('A1', 'Course Code');
            $this->excel->getActiveSheet()->setCellValue('B1', 'CO Code');
            $this->excel->getActiveSheet()->setCellValue('C1', 'Course Outcome');
            $this->excel->getActiveSheet()->setCellValue('D1', 'Bloom\'s Level1');
            $this->excel->getActiveSheet()->setCellValue('E1', 'Bloom\'s Level2');
            $this->excel->getActiveSheet()->setCellValue('F1', 'Delivery Method');
            $this->excel->getActiveSheet()->setCellValue('G1', 'PO Mapping1');
            $this->excel->getActiveSheet()->setCellValue('H1', 'PO Mapping2');
            $this->excel->getActiveSheet()->setCellValue('I1', 'PSO Mapping1');
            $this->excel->getActiveSheet()->setCellValue('J1', 'PSO Mapping2');

            $crs_excel_data = $this->course_model->download_clo_crs_code_data($crclm_id,$term_id);
            $z = 2;
            foreach ($crs_excel_data as $key=>$val){                
                $this->excel->getActiveSheet()->setCellValue('A'.$z, $val['crs_code']);
                $z++;
            }
        
           
            $i = 2;
            foreach ($clo_excel_data as $key=>$val){                
                $this->excel->getActiveSheet()->setCellValue('B'.$i, $val['clo_code']);
                $this->excel->getActiveSheet()->setCellValue('C'.$i, $val['clo_statement']);
                $i++;
            }

            $bloom_lvl_data = $this->course_model->download_blooms_level($crclm_id,$term_id);
           
            $j = 2;
            foreach ($bloom_lvl_data['level'] as $key=>$val){ 
                $mark=explode(',', $val['level']);  
                if(isset($mark['0'])) {
                    $this->excel->getActiveSheet()->setCellValue('D'.$j, $mark['0']);
                }else{
                    $this->excel->getActiveSheet()->setCellValue('D'.$j, '');
                }
                $j++;
            }

            $k = 2;
            foreach ($bloom_lvl_data['level'] as $key=>$val){   
                $mark=explode(',', $val['level']);  
                if(isset($mark['1'])) {
                    $this->excel->getActiveSheet()->setCellValue('E'.$k, $mark['1']);
                } else {
                    $this->excel->getActiveSheet()->setCellValue('E'.$k, '');
                }
                $k++;
            }

            $l = 2;
            foreach ($bloom_lvl_data['delivery_method'] as $key=>$val){  
                $mark=explode(',', $val['delivery_method']);  
                if(isset($mark['0'])) {
                    $this->excel->getActiveSheet()->setCellValue('F'.$l, $mark['0']);
                } else {
                    $this->excel->getActiveSheet()->setCellValue('F'.$l, '');
                }
                $l++;
            }

            $m = 2;
            foreach ($bloom_lvl_data['po_map'] as $key=>$val){   
                $mark=explode(',', $val['po_map']);  
                if(isset($mark['0'])) {
                    $this->excel->getActiveSheet()->setCellValue('G'.$m, $mark['0']);
                } else {
                    $this->excel->getActiveSheet()->setCellValue('G'.$m, '');
                }
                $m++;
            }

            $n = 2;
            foreach ($bloom_lvl_data['po_map'] as $key=>$val){   
                $mark=explode(',', $val['po_map']);  
                if(isset($mark['1'])) {
                    $this->excel->getActiveSheet()->setCellValue('H'.$n, $mark['1']);
                } else {
                    $this->excel->getActiveSheet()->setCellValue('H'.$n, '');
                }
                $n++;
            }

            $o = 2;
            foreach ($bloom_lvl_data['po_map'] as $key=>$val){  
                $mark=explode(',', $val['po_map']);
                if(isset($mark['2'])) {   
                    $this->excel->getActiveSheet()->setCellValue('I'.$o, $mark['2']);
                } else {
                    $this->excel->getActiveSheet()->setCellValue('I'.$o,'');
                }
                $o++;
            }

            $p = 2;
            foreach ($bloom_lvl_data['po_map'] as $key=>$val){  
                $mark=explode(',', $val['po_map']);   
                if(isset($mark['3'])) {
                    $this->excel->getActiveSheet()->setCellValue('J'.$p, $mark['3']);
                } else {
                    $this->excel->getActiveSheet()->setCellValue('J'.$p, '');
                }
                $p++;
            }
            $this->excel->getActiveSheet()->getStyle('G1:G250')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $this->excel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->excel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $this->excel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
            $filename = 'Sample_Course_Outcome_POMapping_Standard_Template.xls'; 
            header('Content-Type: application/vnd.ms-excel'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
            //if you want to save it as .XLSX Excel 2007 format
            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
            //force user to download the Excel file without writing it to server's HD
            ob_end_clean();
            ob_start();
            $objWriter->save('php://output');
    }

    public function get_course_list_for_import()
    {
        if ($this->input->is_ajax_request())
        {
            $crs_id = $this->input->post('crs_id');

            $to_send_array['course_list'] = $this->course_model->get_course_list_for_import($crs_id);

            $course_list_html_str = $this->load->view('curriculum/course/course_import_table_vw', $to_send_array, TRUE);

            echo json_encode(array('course_list_html_str' => $course_list_html_str));
        }
        else
        {
            redirect('login', 'refresh');
        }
    }
	
	public function import_course_data()
    {
        $this->load->model('curriculum/import_curriculum/import_curriculum_model');
        $this->load->model('curriculum/clo/clo_model');
        $this->load->model('curriculum/clo/clo_po_map_model');
        $this->load->model('assessment_attainment/cia/cia_model');
        $this->load->model('question_paper/cia_qp/manage_cia_qp_model');
        $this->load->model('assessment_attainment/cia/cia_rubrics_model');
        
        $from_crclm_id = $this->input->post('from_crclm_id');
        $from_term_id = $this->input->post('from_term_id');
        $from_course_id = $this->input->post('from_course_id');
        $to_crclm_id = $this->input->post('to_crclm_id');
        $to_term_id = $this->input->post('to_term_id');
        $to_course_id = $this->input->post('to_course_id');
        $is_without_workflow = $this->input->post('is_without_workflow');
        $crs_mode = $this->input->post('crs_mode');
        $course_entity_ids = array(11, 14, 10, 12, 17);

        $common_sections = $this->course_model->get_common_sections($from_course_id, $to_course_id);

        // Entire curriculum design import starts here    
        $data['crs_owner_loggedin'] = 0;
        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner') ) && ($this->ion_auth->in_group('Course Owner'))) {
            $data['crs_owner_loggedin_user_id'] = $this->ion_auth->user()->row()->id;
            $data['crs_owner_loggedin'] = 1;
        }

        $this->import_curriculum_model->course_entity_import_insert($to_crclm_id, $to_term_id, $to_course_id, $from_crclm_id, $from_term_id, $from_course_id, $course_entity_ids, $crs_mode, $data);

        if ($is_without_workflow)
        {
            $this->clo_model->approve_publish_db($to_crclm_id, $to_term_id, $to_course_id);

            $this->clo_po_map_model->accept_dashboard_data($to_crclm_id, $to_term_id, $to_course_id);
        }
        // Entire curriculum design import starts here

        // Importing occasions starts here
        for ($x=0; $x<count($common_sections); $x++)
        {
            // Getting occasions list
            $occasion_list = $this->cia_model->occasion_list($from_crclm_id, $from_term_id, $from_course_id, $common_sections[$x]);

            // Importing occasions list
            if (! empty($occasion_list))
            {
                $ao_id = array();
                $ao_name = array();
                for ($i=0; $i<count($occasion_list); $i++)
                {
                    $ao_id[] = $occasion_list[$i]['ao_id'];
                    $ao_name[] = $occasion_list[$i]['ao_name'];
                }

                $occasion_import = $this->cia_model->import_occasion($to_crclm_id, $to_term_id, $to_course_id, $ao_id, $ao_name, $common_sections[$x], $from_course_id);
            }
        }
        // Importing occasions ends here
        
        for ($x=0; $x<count($common_sections); $x++)
        {
            // Getting to occasions list
            $to_occasion_list = $this->cia_model->occasion_list($to_crclm_id, $to_term_id, $to_course_id, $common_sections[$x]);

            // Importing CIA QP starts here
            // Getting CIA QP list
            $qp_list = $this->manage_cia_qp_model->fetch_qp_list(null, null, $from_crclm_id, $from_term_id, $from_course_id, null);
            
            if (! empty($qp_list))
            {
                // Import CIA QPs
                for ($i=0; $i<count($to_occasion_list); $i++)
                {
                    for ($j=0; $j<count($qp_list); $j++)
                    {
                        if ($to_occasion_list[$i]['ao_description'] == $qp_list[$j]['ao_desc'])
                        {
                            $qpd_id = $qp_list[$j]['qpd_id'];
                            $ao_id = $to_occasion_list[$i]['ao_id'];
                            $this->manage_cia_qp_model->get_qp_data_import($qpd_id, $ao_id, $to_crclm_id, $to_term_id, $to_course_id,$from_course_id);
                        }
                    }
                }
            }
            // Importing CIA QP ends here

            // Importing CIA Rubrics starts here
            // Getting rubrics list
            $rubric_list = $this->manage_cia_qp_model->fetch_rubric_list($from_crclm_id, $from_term_id, $from_course_id);

            // Importing CIA rubrics
            if (! empty($rubric_list))
            {
                for ($i=0; $i<count($to_occasion_list); $i++)
                {
                    for ($j=0; $j<count($rubric_list); $j++)
                    {
                        if ($to_occasion_list[$i]['ao_description'] == $rubric_list[$j]['ao_desc'])
                        {
                            $from_ao_id = $rubric_list[$j]['ao_id'];
                            $from_ao_method_id = $rubric_list[$j]['ao_method_id'];
                            $to_ao_id = $to_occasion_list[$i]['ao_id'];
                            $to_ao_method_id = $to_occasion_list[$i]['ao_method_id'];

                            // Importing Rubrics
                            $this->manage_cia_qp_model->get_rubric_data_import($to_ao_id, $to_course_id, $to_term_id, $to_crclm_id, $from_course_id, $from_ao_method_id, 1);

                            // Finalizing Rubrics
                            if ($this->course_model->is_cia_rubrics_finalized($from_ao_id))
                            {
                                $this->cia_rubrics_model->generate_question_paper($to_ao_method_id, NULL, $to_crclm_id, $to_term_id, $to_course_id, $to_ao_id, NULL, NULL);
                            }
                        }
                    }
                }
            }
            // Importing CIA Rubrics ends here
        }
    }

    public function loadSectionList() {
        $crclm_id = $this->input->post('crclm_id');
        $section_list = $this->course_model->loadSectionList($crclm_id);

        if (empty($section_list) || empty($crclm_id)) {
            echo "<option value=''>No Section data</option>";
        } else {
            $list = "";
            $list .= "<option value=''>Select Section</option>";
            foreach ($section_list as $section) {
                $list .= "<option value='" . $section->section_id . "'>" . $section->mt_details_name . "</option>";
            }
            echo $list;
        }
    }

    public function registered_student_data()
    {
        $crs_id = $this->input->get('crs_id');
        $section_id = $this->input->get('section_id');
       
        $data['student_export_data'] = $this->course_model->export_student_data($crs_id, $section_id);
        $student_crclm = $this->course_model->course_details($crs_id);
        $data['student_section'] = $this->course_model->fetch_section($crs_id);
        $data['crclm_data'] =  $student_crclm[0]['crclm_name'];
        $data['term_data'] =  $student_crclm[0]['term_name'];
        $data['student_crs_code'] = $student_crclm[0]['crs_code'];
        $data['student_crs_title'] = $student_crclm[0]['crs_title'];
        $data['section_name'] = $data['student_export_data'][0]['section_name'];
        $data['section_id'] = $section_id;
        $pgm_id = $student_crclm[0]['pgm_id'];
        $data['department'] = $this->course_model->fetch_department($pgm_id);
        $data['dept_name'] = $data['department'][0]['dept_name'];

        $this->load->helper('to_excel');
        export_excel($data);
    
    }

    /* function is to check course is exist in the LMS time table */
    public function check_lms_crs_data($crs_id){

        $check_data = $this->course_model->check_lms_crs_data($crs_id);
        $result = $check_data;
       
        $check_result = $this->course_model->check_co_creation($crs_id);
        
        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman')|| $this->ion_auth->in_group('Program Owner')) && ($this->ion_auth->in_group('Course Owner') )) {
            $role_set = 1;
        } else {
            $role_set = 2;
        }

        echo json_encode(array('result'=> $result,'role_set'=> $role_set,'check_result'=> $check_result));
    }

    /* function is used to check selected all students are associated with Manage student attendance (LMS) */
    public function check_student_data(){
        $data = array();

        if (!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman')) && ($this->ion_auth->in_group('Course Owner') || $this->ion_auth->in_group('Program Owner'))) {
            $role_set = "1";
        } else {
            $role_set = "2";
        }

        if(!($this->ion_auth->is_admin() || $this->ion_auth->in_group('Chairman') || $this->ion_auth->in_group('Program Owner')) && $this->ion_auth->in_group('Course Owner')){
            $data['student_data'] = implode(",",$this->input->post('student_data'));
            $check_lms_student = $this->course_model->check_student_data($data);

                if($check_lms_student != 0 && $check_lms_student != 1 ){
                    $student_usn = array_column($check_lms_student,'student_usn');
                    $student_data = implode(", ",$student_usn); 
                    $result = $student_data;
                } else {
                    $result =  $check_lms_student;
                }   
        } else {
            $result = "0";
        }
        echo json_encode(array('result'=>$result,'role_set'=>$role_set));
    }

    // function to fetch section list
    function fetch_change_section_list() {
        $data = array();
        $data['crs_id'] = $this->input->post('crs_id');
        $data['section_id'] = $this->input->post('section_id');

        $section_data = $this->course_model->fetch_change_section_list($data);

        $i = 0;
        $list = array();
        foreach ($section_data as $section) {
            $list[$i] = '<li><a href="#" data-value="'. $section['mt_details_id'] .'">'. $section['mt_details_name'] .'</a></li>';
            $i++;
        }
        $list = implode(" ", $list);

        echo $list;
    }

    // function to change registered student to different section
    function change_reg_stud_section() {
        $data = array();
        $data['section_id'] = $this->input->post('section_id');
        $data['student_records'] = implode(",", $this->input->post('student_records'));

        $updated = $this->course_model->change_reg_stud_section($data);
        
        echo $updated;
    }

    // function to check marks uploaded for the section or not
    function check_section_marks_upld() {
        $data = array();
        $data['crs_id'] = $this->input->post('crs_id');
        $data['section_id'] = $this->input->post('section_id');
        $data['students'] = $this->input->post('students');
        $upld_cnt = $this->course_model->check_section_marks_upld($data);
        
        if($upld_cnt == "0") {
            echo "0";
        } else {
            echo "1";
        }
    }

    // function to check marks uploaded for the section or not
    function check_lms_attendance() {
        $crs_id = $this->input->post('crs_id');
        $from_section_id = $this->input->post('from_section_id');
        $to_section_id = $this->input->post('to_section_id');
        $student = $this->input->post('student');
        $upld_cnt = $this->course_model->check_lms_attendance($crs_id,$from_section_id,$to_section_id,$student);
        echo $upld_cnt;
       
    }

    public function check_stud_crs_red_credits() {
        $params['crclm_id'] = $this->input->post('crclm_id');
        $params['std_crclm_id'] = $this->input->post('std_crclm_id');
        $params['crclm_trm_id'] = $this->input->post('crclm_trm_id');
        $params['crs_id'] = $this->input->post('crs_id');
        $params['section_id'] = $this->input->post('section_id');
        $params['selectedStudent'] = $this->input->post('selectedStudent');
        $params['crs_credits'] = $this->input->post('crs_credits');
		$register_student = $this->course_model->check_stud_crs_red_credits($params);

		echo json_encode($register_student);
    }

    public function fetch_ci() {
        $course_id =$this->input->post('course_id');
        $sec_id = $this->input->post('section_id');
        $ci_list = $this->course_model->fetch_ci($course_id, $sec_id);
        echo json_encode($ci_list);
    }
}
?>