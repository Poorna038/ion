<?php
/**
 * File Description	:	Model(Database) Logic for Course Module(List, Edit & Delete)
 */
?>

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Course_model extends CI_Model {
    /* Function is used to fetch the course, course type, term,course designer & course reviewer details 
     * from course, crclm_terms, course_clo_owner, course_validator, users & curse_type tables.
     * @param - curriculum id & term id.
     * @returns- a array of values of all the course details.
     */

    public function __construct() {
        parent::__construct();
        $this->load->helper('array_helper');
    }

    public function course_list($crclm_id, $term_id, $course_designer = NULL) {
        if ($course_designer == NULL || $course_designer == 0) {
            $crs_list = 'SELECT c.crs_id, c.crs_title, c.crs_type_id, c.crs_acronym, c.crs_code, lect_credits,crs_domain_id,
                                                            tutorial_credits, practical_credits, c.self_study_credits, c.total_credits, c.cie_marks,
                                                            c.see_marks,c.cia_flag, c.mte_flag, c.tee_flag, c.total_marks, c.contact_hours, c.see_duration, c.crs_mode, c.co_crs_owner, c.ss_marks, 
                                                            c.status, t.term_name,c.clo_bl_flag,c.edu_sys_flag,c.crs_attainment_finalize_flag,
                                                            u.clo_owner_id, r.validator_id, s.title, s.username, s.first_name, s.last_name, usr.title as usr_title, usr.username as usr_usr_name, usr.first_name as usr_first_name, usr.last_name as usr_last_name, ct.crs_type_name
                                            FROM  course AS c
                                            LEFT JOIN crclm_terms t ON t.crclm_term_id = "' . $term_id . '"
                                            LEFT JOIN course_clo_owner u ON u.crs_id = c.crs_id
                                            LEFT JOIN course_clo_validator r ON r.crs_id = c.crs_id 
                                            LEFT JOIN users s ON s.id = u.clo_owner_id
                                            LEFT JOIN users usr ON usr.id = r.validator_id
                                            LEFT JOIN course_type ct ON ct.crs_type_id = c.crs_type_id
                                            WHERE c.crclm_id = "' . $crclm_id . '"
                                            AND c.crclm_term_id = "' . $term_id . '"
											ORDER BY LENGTH(c.crs_code), c.crs_code ASC';
            $crs_list_result = $this->db->query($crs_list);
            $crs_list_data = $crs_list_result->result_array();
            $crs_list_return['crs_list_data'] = $crs_list_data;

            return $crs_list_return;
        } else { //Only Course Owner login
            $loggedin_user_id = $this->ion_auth->user()->row()->id;
            $crs_list = 'SELECT c.crs_id, c.crs_title, c.crs_type_id, c.crs_acronym, c.crs_code, lect_credits,crs_domain_id,
                                                            tutorial_credits, practical_credits, c.self_study_credits, c.total_credits, c.cie_marks,
                                                            c.see_marks,c.cia_flag, c.mte_flag, c.tee_flag, c.total_marks, c.contact_hours, c.see_duration, c.crs_mode, c.co_crs_owner, c.ss_marks, 
                                                            c.status, t.term_name,c.clo_bl_flag,c.edu_sys_flag,c.crs_attainment_finalize_flag,
                                                            u.clo_owner_id, r.validator_id, s.title, s.username, s.first_name, s.last_name, usr.title as usr_title, usr.username as usr_usr_name, usr.first_name as usr_first_name, usr.last_name as usr_last_name, ct.crs_type_name
                                            FROM  course AS c
                                            LEFT JOIN crclm_terms t ON t.crclm_term_id = "' . $term_id . '"
                                            LEFT JOIN course_clo_owner u ON u.crs_id = c.crs_id
                                            LEFT JOIN course_clo_validator r ON r.crs_id = c.crs_id 
                                            LEFT JOIN users s ON s.id = u.clo_owner_id
                                            LEFT JOIN users usr ON usr.id = r.validator_id
                                            LEFT JOIN course_type ct ON ct.crs_type_id = c.crs_type_id
                                            WHERE c.crclm_id = "' . $crclm_id . '"
                                            AND c.crclm_term_id = "' . $term_id . '"
                                            AND u.clo_owner_id = "' . $loggedin_user_id . '"
                                            ORDER BY LENGTH(c.crs_code), c.crs_code ASC';
            $crs_list_result = $this->db->query($crs_list);
            $crs_list_data = $crs_list_result->result_array();
            $crs_list_return['crs_list_data'] = $crs_list_data;

            return $crs_list_return;
        }
    }

    // End of function course_list.

    /* Function is used to fetch the course, course type, term & course designer details from course table.
     * @param - curriculum id.
     * @returns- a array of values of all the course details.
     */

    public function course_detailslist($crclm_id) {
        return $this->db->select('course.crs_id, course.crclm_term_id, course.crs_type_id, crs_title, crs_acronym, crs_code,
	                              crs_mode, co_crs_owner, cia_passing_marks, tee_passing_marks, lect_credits, tutorial_credits, practical_credits, self_study_credits, 
								  total_credits, contact_hours, cie_marks, see_marks, attendance_marks, ss_marks, total_marks, see_duration, tutorial')
                        ->select('crs_type_name')
                        ->select('clo_owner_id')
                        ->select('title, username, first_name, last_name')
                        ->select('term_name, term_duration, term_credits, total_theory_courses, total_practical_courses')
                        ->join('course_type', 'course_type.crs_type_id = course.crs_type_id')
                        ->join('course_clo_owner', 'course_clo_owner.crs_id = course.crs_id')
                        ->join('users', 'users.id = course_clo_owner.clo_owner_id')
                        ->join('crclm_terms', 'crclm_terms.crclm_term_id = course.crclm_term_id')
                        ->order_by('course.crclm_term_id', 'asc')
                        ->where('course.crclm_id', $crclm_id)
                        ->get('course')
                        ->result_array();
    }

// End of function course_list.

    /* Function is used to fetch the curriculum id & name from curriculum table.
     * @param - 
     * @returns- a array of values of the curriculum details.
     */

    public function crclm_fill() {
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        if ($this->ion_auth->is_admin()) {
            $curriculum_list = 'SELECT DISTINCT c.crclm_id, c.crclm_name 
								FROM curriculum AS c, dashboard AS d 
								WHERE d.crclm_id = c.crclm_id 
								AND d.entity_id = 4 
								AND c.status = 1 AND c.crclm_release_status = 2
								ORDER BY c.crclm_name ASC';
        } else {
            $curriculum_list = 'SELECT DISTINCT c.crclm_id, c.crclm_name 
							FROM curriculum AS c, users AS u, program AS p, dashboard AS d 
							WHERE u.id = "' . $loggedin_user_id . '" 
							AND u.user_dept_id = p.dept_id 
							AND c.pgm_id = p.pgm_id 
							AND d.crclm_id = c.crclm_id 
							AND d.entity_id = 4 
							AND c.status = 1 AND c.crclm_release_status = 2
							ORDER BY c.crclm_name ASC';
        }
        $resx = $this->db->query($curriculum_list);
        $res2 = $resx->result_array();
        $crclm_data['res2'] = $res2;
        return $crclm_data;
    }

// End of function crclm_fill.

    /* Function is used to fetch the term id & name from crclm_terms table.
     * @param - curriculum id.
     * @returns- a array of values of the term details.
     */

    public function term_fill($crclm_id) {
        $term_name = 'SELECT crclm_term_id, term_name FROM crclm_terms WHERE crclm_id = "' . $crclm_id . '" ';
        $result = $this->db->query($term_name);
        $data = $result->result_array();
        $term_data['res2'] = $data;

        return $term_data;
    }

// End of function term_fill.

    /* Function is used to fetch the curriculum id & name from curriculum table.
     * @param - 
     * @returns- a array of values of the curriculum details.
     */

    public function dropdown_curriculumlist() {
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        if ($this->ion_auth->is_admin()) {
            $curriculum_list = 'SELECT c.crclm_id, c.crclm_name 
								FROM curriculum AS c, dashboard AS d 
								WHERE d.crclm_id = c.crclm_id 
								AND d.entity_id = 4 
								AND c.status = 1 
								AND d.status = 1 
								ORDER BY crclm_name ASC';
        } else {
            $curriculum_list = 'SELECT c.crclm_id, c.crclm_name 
								FROM curriculum AS c, users AS u, program AS p, dashboard AS d 
								WHERE u.id = "' . $loggedin_user_id . '" 
								AND u.user_dept_id = p.dept_id 
								AND c.pgm_id = p.pgm_id 
								AND d.crclm_id = c.crclm_id 
								AND d.entity_id = 4 
								AND c.status = 1 
								ORDER BY c.crclm_name ASC';
        }
        $curriculum_list = $this->db->query($curriculum_list);
        $curriculum_list = $curriculum_list->result_array();

        return $curriculum_list;
    }

// End of function dropdown_curriculumlist.

    /* Function is used to fetch the department id & name from department table.
     * @param - 
     * @returns- a array of values of the department details.
     */

    public function dropdown_department() {
        $status = 1;
        return $this->db->select('dept_id, dept_name')
                        ->where('status', $status)
                        ->order_by('dept_name', 'asc')
                        ->get('department')
                        ->result_array();
    }

// End of function dropdown_department.

    /* Function is used to fetch the user id, first & last name from users table.
     * @param - 
     * @returns- a array of values of the user details.
     */

    public function dropdown_userlist() {
        $loggedin_user_dept_id = $this->ion_auth->user()->row()->base_dept_id;
        $active = 1;
        return $this->db->select('users.id, users.title, users.username, users.first_name, users.last_name')
                        ->join('users_groups', 'users_groups.user_id = users.id')
                        ->where('base_dept_id', $loggedin_user_dept_id)
                        ->where('active', $active)
                        ->where('users_groups.group_id', 6)
                        ->where('users.active', 1)
                        ->order_by('first_name', 'asc')
                        ->get('users')
                        ->result_array();
    }

// End of function dropdown_userlist.

    /* Function is used to fetch the user id, first & last name from users table.
     * @param - 
     * @returns- a array of values of the user details.
     */

    public function reviewer_dropdown_userlist($loggedin_user_dept_id) {
        $active = 1;
        return $this->db->select('users.id, users.title, users.active,users.username, users.first_name, users.last_name, users.email')
                        ->join('users_groups', 'users_groups.user_id = users.id')
                        ->where('base_dept_id', $loggedin_user_dept_id)
                        ->where('active', $active)
                        ->where('users_groups.group_id', 6)
                        ->order_by('first_name', 'asc')
                        ->get('users')
                        ->result_array();
    }

// End of function reviewer_dropdown_userlist.

    public function owner_dropdown_userlist($loggedin_user_id) {
        $dept_id = $this->ion_auth->user()->row()->base_dept_id;
        $active = 1;
        if (!$this->ion_auth->is_admin()) {

            $data = 'SELECT * FROM
                                (SELECT u.id, u.title,u.active, u.first_name,u.last_name, u.email
									FROM users as u, users_groups as g
									WHERE u.id=g.user_id 
									AND u.active = 1 
									AND u.base_dept_id = ' . $this->ion_auth->user()->row()->base_dept_id . '
									AND g.group_id = 6 

									UNION

									SELECT u.id,u.title,u.active,u.first_name,u.last_name, u.email
									FROM map_user_dept m,map_user_dept_role mdr,`groups` g,users u
									WHERE m.assigned_dept_id = ' . $this->ion_auth->user()->row()->base_dept_id . '
									AND m.user_id = mdr.user_id
									AND mdr.role_id = g.id
									AND m.user_id = u.id
									AND g.id = 6) 
								A ORDER BY A.first_name ASC ';
            $result = $this->db->query($data);
            $user_list = $result->result_array();
            return $user_list;
        } else {
            $data = 'SELECT * FROM
									(SELECT u.id, u.title, u.active ,  u.first_name,u.last_name, u.email
										FROM users as u, users_groups as g
										WHERE u.id=g.user_id 
										AND u.active = 1 
										AND g.group_id = 6 

										UNION

										SELECT u.id,u.title,u.active ,u.first_name,u.last_name, u.email
										FROM map_user_dept m,map_user_dept_role mdr,`groups` g,users u
										WHERE m.user_id = mdr.user_id
										AND u.user_dept_id = ' . $dept_id . '
										AND mdr.role_id = g.id
										AND m.user_id = u.id
										AND g.id = 6) 
									A ORDER BY A.first_name ASC ';
            $result = $this->db->query($data);
            $user_list = $result->result_array();
            return $user_list;
        }
    }

    /* Function is used to fetch the user id, first & last name from users table.
     * @param - department id.
     * @returns- a array of values of the user details.
     */

    public function dropdown_userlist2($dept_id) {
        $active = 1;
        return $this->db->select('users.id, users.title, users.username, users.first_name, users.last_name, users.email')
                        ->join('users_groups', 'users_groups.user_id = users.id')
                        ->where('base_dept_id', $dept_id)
                        ->where('active', $active)
                        ->where('users_groups.group_id', 6)
                        ->order_by('first_name', 'asc')
                        ->get('users')
                        ->result_array();
    }

// End of function dropdown_userlist2.

    /* Function is used to fetch the user id, first & last name from users table.
     * @param - curriculum id.
     * @returns- a array of values of the user details.
     */

    public function dropdown_userlist3($crclm_id) {
        $active = 1;

        $data = 'SELECT * FROM
                                (SELECT u.id, u.title, u.first_name,u.last_name,u.email
                                FROM users as u, users_groups as g, program as p, curriculum as c
                                WHERE u.id=g.user_id AND u.active = 1 AND u.base_dept_id = p.dept_id AND p.pgm_id = c.pgm_id AND g.group_id = 6 AND c.crclm_id = "' . $crclm_id . '"

                                UNION

                                SELECT u.id,u.title,u.first_name,u.last_name,u.email
                                FROM map_user_dept m,map_user_dept_role mdr,`groups` g,users u, program as p, curriculum as c,users_groups as gr
                                WHERE m.assigned_dept_id = p.dept_id AND p.pgm_id = c.pgm_id AND gr.group_id = 6 AND c.crclm_id = "' . $crclm_id . '"
                                AND m.user_id = mdr.user_id
                                AND mdr.role_id = g.id
                                AND m.user_id = u.id
                                AND g.id = 6) A ORDER BY A.first_name ASC ';
        $result = $this->db->query($data);
        $user_list = $result->result_array();
        return $user_list;
    }

// End of function dropdown_userlist3.

    /* Function is used to fetch the curriculum details from curriculum table.
     * @param - curriculum id.
     * @returns- a array of values of the curriculum details.
     */

    public function curriculum_details($crclm_id) {
        return $this->db->select('crclm_id, crclm_name, crclm_description, total_credits, total_terms, 
									start_year, end_year, crclm_owner')
                        ->select('title, username, first_name, last_name')
                        ->join('users', 'users.id = curriculum.crclm_owner')
                        ->where('crclm_id', $crclm_id)
                        ->get('curriculum')
                        ->result_array();
    }

// End of function curriculum_details.

    /* Function is used to fetch the PEO details from PEO table.
     * @param - curriculum id.
     * @returns- a array of values of the PEO details.
     */

    public function peo_list($crclm_id) {
        return $this->db->select('peo_id,peo_statement')
                        ->where('crclm_id', $crclm_id)
                        ->get('peo')
                        ->result_array();
    }

// End of function peo_list.

    /* Function is used to fetch the PO details from PO table.
     * @param - curriculum id.
     * @returns- a array of values of the PO details.
     */

    public function po_list($crclm_id) {
        return $this->db->select('po_id,po_statement')
                        ->where('crclm_id', $crclm_id)
                        ->get('po')
                        ->result_array();
    }

// End of function po_list.	

    /* Function is used to fetch the Predecessor Course details from predecessor_courses table.
     * @param - course id.
     * @returns- a array of values of the Predecessor Course details.
     */

    public function predessor_details($crs_id) {
        return $this->db->select('predecessor_id')
                        ->select('predecessor_course')
                        ->where('predecessor_courses.crs_id', $crs_id)
                        ->get('predecessor_courses')
                        ->result_array();
    }

// End of function predessor_details.	

    /* Function is used to fetch the term details from crclm_terms table.
     * @param - curriculum id.
     * @returns- a array of values of the term details.
     */

    public function term_details($crclm_id) {
        return $this->db->select('crclm_term_id')
                        ->select('term_name')
                        ->select('term_duration')
                        ->select('term_credits')
                        ->select('total_theory_courses')
                        ->select('total_practical_courses')
                        ->where('crclm_id', $crclm_id)
                        ->get('crclm_terms')
                        ->result_array();
    }

// End of function term_details.	

    /* Function is used to fetch course type names from crclm_crs_type_map table.
     * @param - curriculum id.
     * @returns- a array of values of the course type details.
     */

    public function fetch_course_type($crclm_id) {

        $query_pgm_id = $this->db->query('select pgm_id  from curriculum where crclm_id= "' . $crclm_id . '"');
        $pgm_id = $query_pgm_id->result_array();

        $query = $this->db->query('select course_type_weightage_id, course_type_id AS crs_type_id, course_type.crs_type_name,elective_crs_flag
								   from course_type_weightage
								   join course_type on  course_type_weightage.course_type_id = course_type.crs_type_id
								   where course_type_weightage.pgm_id = "' . $pgm_id[0]['pgm_id'] . '"');
        return $query->result_array();
    }

// End of function fetch_course_type.	

    /* Function is used to fetch the course details from course table.
     * @param - course id.
     * @returns- a array of values of the course details.
     */

    public function course_details($crs_id) {
        return $this->db->select('course.crs_id, course.cia_flag, course.mte_flag , course.tee_flag , course.crclm_id, course.mid_term_marks, 
                                    course.crclm_term_id, course.crs_type_id, course.crs_domain_id, course.crs_mode, course.crs_code, 
                                    course.co_crs_owner, course.cia_passing_marks, course.tee_passing_marks, course.crs_title, 
                                    course.crs_acronym, course.lect_credits, course.tutorial_credits, course.practical_credits, 
                                    course.self_study_credits, course.total_credits, course.contact_hours, course.cie_marks, 
                                    course.see_marks, course.attendance_marks, course.ss_marks, course.total_marks, course.see_duration, 
                                    course.total_cia_weightage, course.total_mte_weightage, course.total_tee_weightage, 
                                    course.cognitive_domain_flag, course.affective_domain_flag, course.psychomotor_domain_flag, 
                                    course.elective_crs_flag, course.crs_attainment_finalize_flag, course.crs_bl_sugg_flag, 
                                    crclm.crclm_name, ct.term_name, ct.academic_year, crclm.pgm_id, course.tutorial, course.indirect_flag')
                        ->join('curriculum crclm', 'crclm.crclm_id = course.crclm_id', 'left')
                        ->join('crclm_terms ct', 'course.crclm_term_id = ct.crclm_term_id', 'left')
                        ->where('course.crs_id', $crs_id)
                        ->get('course')
                        ->result_array();
    }

// End of function course_details.	

    /* Function is used to fetch the course designer & course reviewer details from 
     * course_clo_owner & course_clo_validator  table.
     * @param - course id.
     * @returns- a array of values of the course designer & course reviewer details.
     */

    public function course_owner_details($crs_id) {
        $data['owner_details'] = $this->db->select('crclm_id, clo_owner_id')
                ->select('dept_id')
                ->select('last_date')
                ->where('crs_id', $crs_id)
                ->get('course_clo_owner')
                ->result_array();

        $data['reviewer_details'] = $this->db->select('validator_id')
                ->select('dept_id')
                ->select('last_date')
                ->where('crs_id', $crs_id)
                ->get('course_clo_validator')
                ->result_array();
        return $data;
    }

    public function course_delete_manage($course_id,$role_set) {
        if($role_set == 1){
            $query = 'SELECT 
                COUNT(survey_id) as survey_count
                FROM
                su_survey
            WHERE
                crs_id ="' . $course_id . '"';
    $query_data = $this->db->query($query);
    $data = $query_data->result_array();
        return $data;
        } 
        
    }

// End of function course_owner_details.	

    /* Function is used to delete a course from course table.
     * @param - course id.
     * @returns- a boolean value.
     */

    public function course_delete($crs_id, $flag_course_owner = NULL,$role_set) {
        if($role_set == 2){
                // Delete all the earlier records present in dashboard table for this course id
                $delete_dash_noty_query = 'DELETE FROM dashboard WHERE particular_id = "' . $crs_id . '" AND entity_id NOT IN (2, 4, 5, 6, 13)';
                $this->db->query($delete_dash_noty_query);

                $delete_notes_query = 'DELETE FROM notes WHERE particular_id = "' . $crs_id . '"';
                $this->db->query($delete_notes_query);

                $meta_data_query = ' SELECT crs.crs_title, crs.crs_code, crclm.crclm_name, term.term_name
                    FROM course as crs 
                    JOIN curriculum as crclm ON crclm.crclm_id = crs.crclm_id
                    JOIN crclm_terms as term ON term.crclm_term_id = crs.crclm_term_id 
                    WHERE crs.crs_id = "' . $crs_id . '" ';
                $meta_data_data = $this->db->query($meta_data_query);
                $meta_data = $meta_data_data->row_array();

    
                $query = 'SELECT distinct course_instructor_id, crclm_id
                    FROM  map_courseto_course_instructor 
                    WHERE crs_id ="' . $crs_id . '"';
                    $query_ins_data = $this->db->query($query);
                    $map_inst_data = $query_ins_data->result_array();

                $query = 'SELECT crclm_id, clo_owner_id 
                    FROM  course_clo_owner
                    WHERE crs_id ="' . $crs_id . '"';
                    $query_data = $this->db->query($query);
                    $map_crs_data = $query_data->row_array();

                $logged_in_id = $this->ion_auth->user()->row()->id;

                $query = 'SELECT CONCAT(title," ",first_name," ",last_name)  as username
                    FROM  users
                    WHERE id ="' . $logged_in_id . '"';

                $query_user_data = $this->db->query($query);
                $query_user_reslt = $query_user_data->row_array();

                $description = 'Curriculum:' . $meta_data['crclm_name']. ' , Term(Semester): ' . $meta_data['term_name'];
                $reviewer_description = $description . ', Course: ' . $meta_data['crs_title'] .'('.$meta_data['crs_code']. ') is deleted by ' .$query_user_reslt['username'];

                $url = 'clear_notification';

                $course_instrctr_data = array();
                foreach($map_inst_data as $data){
                    array_push($course_instrctr_data, $data['course_instructor_id']);
                }

                if($query_ins_data->num_rows() === 1){
    
                if($course_instrctr_data[0] === $map_crs_data['clo_owner_id']){
                    $dashboard_insert = array(
                        'crclm_id' => $map_crs_data['crclm_id'],
                        'entity_id' => 4,
                        'particular_id' => $crs_id,
                        'sender_id' => $this->ion_auth->user()->row()->id,
                        'receiver_id' => $map_crs_data['clo_owner_id'],
                        'url' => $url,
                        'description' => $reviewer_description,
                        'state' => 1,
                        'status' => 1,
                    );
        
                    $this->db->insert('dashboard', $dashboard_insert);
                } else {
        
                    foreach($map_inst_data as $data){
                        $dashboard_insert = array(
                        'crclm_id' => $data['crclm_id'],
                        'entity_id' => 4,
                        'particular_id' => $crs_id,
                        'sender_id' => $this->ion_auth->user()->row()->id,
                        'receiver_id' => $data['course_instructor_id'],
                        'url' => $url,
                        'description' => $reviewer_description,
                        'state' => 1,
                        'status' => 1,
                    );
        
                        $this->db->insert('dashboard', $dashboard_insert);
                        $dashboard_insert = array(
                            'crclm_id' => $map_crs_data['crclm_id'],
                            'entity_id' => 4,
                            'particular_id' => $crs_id,
                            'sender_id' => $this->ion_auth->user()->row()->id,
                            'receiver_id' => $map_crs_data['clo_owner_id'],
                            'url' => $url,
                            'description' => $reviewer_description,
                            'state' => 1,
                            'status' => 1,
                        );
                        $this->db->insert('dashboard', $dashboard_insert);
                    }
                } 
                } else if(in_array($map_crs_data['clo_owner_id'],$course_instrctr_data)) {
                foreach($map_inst_data as $data){
                    $dashboard_insert = array(
                        'crclm_id' => $data['crclm_id'],
                        'entity_id' => 4,
                        'particular_id' => $crs_id,
                        'sender_id' => $this->ion_auth->user()->row()->id,
                        'receiver_id' => $data['course_instructor_id'],
                        'url' => $url,
                        'description' => $reviewer_description,
                        'state' => 1,
                        'status' => 1,
                    );
    
                    $this->db->insert('dashboard', $dashboard_insert);
                }
                } 

                $this->load->model('log_history/log_history_generic_model');
                    $params = array(
                        'crclm_id' => 'crclm_term_id',
                        'unique_column' => 'crs_title',
                        'column_name' => 'crs_title',
                        'table' => 'course',
                        'unique_id' => 'crs_id',
                        'unique_id_val' => $crs_id,
                        'entity' => 'Course'
                    );
                $this->log_history_generic_model->insert_delete_log($params);

                if ($flag_course_owner == 0) {
                    $crs_del = 'DELETE FROM course WHERE crs_id = "' . $crs_id . '" ';
                } else {
                    $loggedin_user_id = $this->ion_auth->user()->row()->id;
                    $crs_del = 'DELETE FROM course WHERE crs_id = "' . $crs_id . '"';
                // AND created_by = "' . $loggedin_user_id . '"  // This is removed because course can be delete course eventhough created by program owner 
                }
                $result = $this->db->query($crs_del);
                return true;
            }
        }
 
    // End of function course_delete.	

    /* Function is used to fetch the term id & curriculum id from course table.
     * @param - course id.
     * @returns- an array of values of course.
     */

    public function publish_course_curriculum($crs_id) {
        $crclm_id = 'SELECT crclm_id, crclm_term_id FROM course WHERE crs_id = "' . $crs_id . '" ';
        $result = $this->db->query($crclm_id);
        $data = $result->result_array();
        return $data;
    }

// End of function publish_course_curriculum.	

    /* Function is used to fetch the course designer id, term id & course title 
     * from course_clo_owner, crclm_terms & course table.
     * @param - course id & curriculum id.
     * @returns- an array of values of course.
     */

    public function publish_course_receiver($crs_id, $crclm_term_id) {
        $receiver_id = 'SELECT clo_owner_id FROM course_clo_owner WHERE crs_id = "' . $crs_id . '" ';
        $result = $this->db->query($receiver_id);
        $data = $result->result_array();
        $select = 'SELECT term_name FROM crclm_terms WHERE crclm_term_id = "' . $crclm_term_id . '" ';
        $select = $this->db->query($select);
        $row = $select->result_array();
        $data['term'] = $row;
        $select = 'SELECT crs_title FROM course WHERE crs_id = "' . $crs_id . '" ';
        $select = $this->db->query($select);
        $row = $select->result_array();
        $data['course'] = $row;
        return $data;
    }

// End of function publish_course_receiver.	

    /* Function is used to fetch the course reviewer id, term id & course title 
     * from course_clo_validator, crclm_terms & course table.
     * @param - course id & curriculum id.
     * @returns- an array of values of course.
     */

    public function publish_course_reviewer($crs_id, $crclm_term_id) {
        $reviewer_id = 'SELECT validator_id FROM  course_clo_validator WHERE crs_id = "' . $crs_id . '" ';
        $result = $this->db->query($reviewer_id);
        $reviewer_data = $result->result_array();
        return $reviewer_data;
    }

// End of function publish_course_reviewer.	

    /* Function is used to update the course status after initiation of CLO creation is done.
     * @param - course id.
     * @returns- a boolean value.
     */

    public function publish_course_update_status($crs_id) {
        $receiver_id = 'UPDATE course SET status = 1, state_id = 1, modified_by="'.$this->ion_auth->user()->row()->id.'" WHERE crs_id = "' . $crs_id . '" ';
        $result = $this->db->query($receiver_id);
        return $result;
    }

// End of function publish_course_update_status.	

    /* Function is used to insert the entry onto dashboard after the initiation of CLO creation is done.
     * @param - curriculum id, entity id, course id & many more.
     * @returns- an array of values of the term & course details.
     */

    public function publish_course($crclm_id, $term_id, $crs_id, $entity_id, $particular_id, $sender_id, $receiver_id, $url, $description, $state, $status, $crclm_term_id, $reviewer_description, $reviewer_id) {
        // Dashboard entry to Initiate CLO Creation for Course Owner

        $crs_publish_data = array(
            'crclm_id' => $crclm_id,
            'entity_id' => $entity_id,
            'particular_id' => $particular_id,
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'url' => $url,
            'description' => $description,
            'state' => $state,
            'status' => $status
        );
        $this->db->insert('dashboard', $crs_publish_data);

        // Dashboard entry to notify Course Reviewer selected 
        $reviewer_crs_publish_data = array(
            'crclm_id' => $crclm_id,
            'entity_id' => $entity_id,
            'particular_id' => $particular_id,
            'sender_id' => $sender_id,
            'receiver_id' => $reviewer_id,
            'url' => '#',
            'description' => $reviewer_description,
            'state' => $state,
            'status' => $status
        );
        $this->db->insert('dashboard', $reviewer_crs_publish_data);

        $query = $this->db->query('SELECT CONCAT(COALESCE(cognitive_domain_flag,""),",",COALESCE(affective_domain_flag,""),",",COALESCE(psychomotor_domain_flag,""))  crs_id from course where crs_id= "' . $crs_id . '"');
        $re = $query->result_array();
        //7.2 Changes
        //$count = count($re[0]['crs_id']);

        $set_data = (explode(",", $re[0]['crs_id']));
        $sk = 0;
        //7.2 Changes
        //$bld_id = '';
		//Changed to $bld_id = '' to $bld_id array();		
		$bld_id = array();
        $bloom_domain_query = $this->db->query('SELECT bld_id,bld_name,status FROM bloom_domain');
        $bloom_domain_data = $bloom_domain_query->result_array();
        foreach ($bloom_domain_data as $bdd) {

            if ($set_data[$sk] == 1 && $bdd['status'] == 1) {
                $bld_id [] = $bdd['bld_id'];
            }
            $sk++;
        }

        if ($bld_id != ' ' && !empty($bld_id)) {
            $Bld_id_data = implode(",", $bld_id);

            $fetch_map_course_bloom_level = $this->db->query("select DISTINCT bld_id  from map_course_bloomlevel where crs_id = " . $crs_id . "");
            $fetch_map_course_bloom_level_result = $fetch_map_course_bloom_level->result_array();
            if (!empty($fetch_map_course_bloom_level_result)) {
                $this->db->query("delete from map_course_bloomlevel where crs_id = " . $crs_id . " ");
            }

            $bld_id_single = str_replace("'", "", $Bld_id_data);
            $bloom_lvl_query = 'select * from  bloom_level where bld_id IN(' . $bld_id_single . ') ORDER BY LPAD(LOWER(level),5,0) ASC';
            $bloom_level_threshold_data = $this->db->query($bloom_lvl_query);
            $bloom_level_threshold = $bloom_level_threshold_data->result_array();
            foreach ($bloom_level_threshold as $bloom) {
                $data = array(
                    'crclm_id' => $crclm_id,
                    'term_id' => $crclm_term_id,
                    'crs_id' => $crs_id,
                    'bld_id' => $bloom['bld_id'],
                    'bloom_id' => $bloom['bloom_id'],
                    'cia_bloomlevel_minthreshhold' => 50,
                    'mte_bloomlevel_minthreshhold' => 50,
                    'tee_bloomlevel_minthreshhold' => 50,
                    'bloomlevel_studentthreshhold' => 70,
                    'created_by' => $this->ion_auth->user()->row()->id,
                    'created_date' => date('Y-m-d'),
                    'modified_by' => $this->ion_auth->user()->row()->id,
                    'modified_date' => date('Y-m-d'));
                $this->db->insert('map_course_bloomlevel', $data);
            }
        }
        $select = 'SELECT term_name FROM crclm_terms WHERE crclm_term_id = "' . $crclm_term_id . '" ';
        $select = $this->db->query($select);
        $row = $select->result_array();
        $crs_publish_data['term'] = $row;
        $select = 'SELECT crs_title FROM course WHERE crs_id = "' . $particular_id . '" ';
        $select = $this->db->query($select);
        $row = $select->result_array();
        $crs_publish_data['course'] = $row;

        // Insert default set of attainment levels in table
        if ($this->ion_auth->user()->row()->org_name->org_type != 'TIER-II') {
            if($this->ion_auth->user()->row()->org_name->target_set_by == 0) { 
                $select_query = $this->db->query('SELECT * FROM tier1_attainment_levels_crclm WHERE crclm_id = '.$crclm_id.' AND crclm_term_id = '.$term_id.'');
                $result = $select_query->result_array();
                if(!empty($result)){
                    $this->db->query("
                    INSERT INTO tier1_attainment_levels (
                    crclm_id, crclm_term_id, crs_id, direct_lb_marks, direct_hb_marks, attainment_level, 
                    dal_justification, indirect_lb_marks,indirect_hb_marks, ial_justification,created_by,created_date
                )
                (
                    SELECT '".$crclm_id."','".$term_id."','".$crs_id."',direct_lb_marks,direct_hb_marks,attainment_level,dal_justification,
                    indirect_lb_marks,indirect_hb_marks,ial_justification,created_by,'".date('Y-m-d')."'
                    FROM tier1_attainment_levels_crclm WHERE crclm_id = '".$crclm_id."' AND  crclm_term_id = '".$term_id."' ORDER BY attainment_level ASC
                )");
                } 
            } else {
                $query = $this->db->query("INSERT INTO tier1_attainment_levels (crclm_id, crclm_term_id, crs_id, direct_lb_marks, 
                direct_hb_marks, attainment_level, dal_justification, indirect_lb_marks, indirect_hb_marks, ial_justification, 
                created_by, created_date, modified_by, modified_date)
                VALUES
                ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 0, 50, 1, '', 0, 50, '', 1, '".date('Y-m-d')."', NULL, NULL),
                ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 50, 70, 2, '', 50, 70, '', 1, '".date('Y-m-d')."', NULL, NULL),
                ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 70, 100, 3, '', 70, 100, '', 1, '".date('Y-m-d')."', NULL, NULL)"
                );
            }
            
        } else {
            // If Tier-II target set by is Course Owner
            if($this->ion_auth->user()->row()->org_name->target_set_by == 1) { 
                $query = $this->db->query("INSERT INTO attainment_level_course (crclm_id, crclm_term_id, crs_id, assess_level_name, 
                    assess_level_name_alias, assess_level_value, cia_direct_percentage, mte_direct_percentage ,
                    tee_direct_percentage, indirect_percentage, conditional_opr, 
                    cia_target_percentage, mte_target_percentage , tee_target_percentage, justify,
                    created_by, created_date, modified_by, modified_date)
                    VALUES
                    ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 'Zero', 'Zero', 0, 0, 0 ,0, 0, '>=', 50, 50 ,40, 0, 1, '" . date('Y-m-d') . "', NULL, NULL),
                    ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 'Low', 'Low', 1, 50, 50 ,50, 60, '>=', 50, 50 ,40, 1, 1, '" . date('Y-m-d') . "', NULL, NULL),
                    ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 'Medium', 'Medium', 2, 60, 60, 60, 70, '>=', 50, 50,  40, 2, 1, '" . date('Y-m-d') . "', NULL, NULL),
                    ('" . $crclm_id . "', " . $term_id . ", " . $crs_id . ", 'High', 'High', 3, 70, 70 , 70, 80, '>=', 50, 50 ,40, 3, 1, '" . date('Y-m-d') . "', NULL, NULL)");
            } else {
                // Insert Attainment levels
                $this->db->query("
                INSERT INTO attainment_level_course (
                    crclm_id, crclm_term_id, crs_id, assess_level_name, assess_level_name_alias, assess_level_value, 
                    cia_direct_percentage, mte_direct_percentage, tee_direct_percentage, indirect_percentage, conditional_opr, 
                    cia_target_percentage, mte_target_percentage , tee_target_percentage, justify, created_by, created_date
                )
                (
                    SELECT " . $crclm_id . ", " . $term_id . ", " . $crs_id . ", assess_level_name, assess_level_name_alias, assess_level_value, 
                    cia_direct_percentage, mte_direct_percentage, tee_direct_percentage, indirect_percentage, conditional_opr,
                    cia_target_percentage, mte_target_percentage , tee_target_percentage, justify, ".$this->ion_auth->user()->row()->id.", 
                    '" . date('Y-m-d') . "' FROM attainment_level_course WHERE crclm_id = " . $crclm_id . "
                    AND crclm_term_id IS NULL AND crs_id IS NULL ORDER BY assess_level_value ASC
                )");
            }
        }

        // Update status
        $this->db->query('UPDATE course SET target_status = 7 WHERE crs_id = "'.$crs_id.'"');

        return $crs_publish_data;
    }

// End of function publish_course.	

    /* Function is used to update course, course designer, course reviewer & predecessor course details 
     * onto the course, course_clo_owner, course_clo_reviewer & predecessor course tables.
     * @param - course data, course_clo_owner data, course_clo_reviewer data &  predecessor course data.
     * @returns- a boolean value.
     */

    public function update_course(//course table data
    $crs_id, $crclm_id, $crclm_term_id, $crs_type_id, $crs_code, $crs_mode, $crs_title, $crs_acronym, $co_crs_owner, $crs_domain_id, $lect_credits, $tutorial_credits, $practical_credits, $self_study_credits, $total_credits, $contact_hours, $cie_marks, $see_marks, $ss_marks, $total_marks, $see_duration,
    //course_clo_owner
            $course_designer,
    //course_clo_reviewer
            $review_dept, $course_reviewer, $last_date,
    //predecessor courses array
            $pre_courses,
    // delete predecessor courses array
            $del_pre_courses , $cia_check, $mte_check, $tee_check,$tutorial
    ) {
        //update into course table
        $course_data = array(
            'crclm_id' => $crclm_id,
            'crclm_term_id' => $crclm_term_id,
            'crs_type_id' => $crs_type_id,
            'crs_code' => $crs_code,
            'crs_mode' => $crs_mode,
            'crs_title' => $crs_title,
            'crs_acronym' => $crs_acronym,
            'co_crs_owner' => $co_crs_owner,
            'crs_domain_id' => $crs_domain_id,
            'lect_credits' => $lect_credits,
            'tutorial_credits' => $tutorial_credits,
            'practical_credits' => $practical_credits,
            'self_study_credits' => $self_study_credits,
            'total_credits' => $total_credits,
            'contact_hours' => $contact_hours,
            'cie_marks' => $cie_marks,
            'see_marks' => $see_marks,
            'ss_marks' => $ss_marks,
            'total_marks' => $total_marks,
            'see_duration' => $see_duration,
            'cia_flag' => $cia_check,
            'mte_flag' => $mte_check,
            'tee_flag' => $tee_check,
            'modified_by' => $this->ion_auth->user()->row()->id,
            'modify_date' => date('Y-m-d H:m:s'),
            'tutorial' => $tutorial
        );
        
        $this->db->where('crs_id', $crs_id)
                ->update('course', $course_data);
        // Dashboard active links update if Course Owner or Course Reviewer are changed
        // To fetch Course Owner & Course Reviewer query
        $query = 'SELECT co.clo_owner_id, cr.validator_id
						FROM course_clo_owner AS co, course_clo_validator cr 
						WHERE co.crs_id = "' . $crs_id . '" 
						AND cr.crs_id = "' . $crs_id . '" ';
        $query_result = $this->db->query($query);
        $data = $query_result->result_array();
        $crs_owner = $data['0']['clo_owner_id'];
        $crs_reviewer = $data['0']['validator_id'];
        // To fetch Topic Ids for a given Course query
        $topic_query = 'SELECT topic_id, t_unit_id
								FROM topic
								WHERE course_id = "' . $crs_id . '" 
								AND curriculum_id = "' . $crclm_id . '" ';
        $topic_query_result = $this->db->query($topic_query);
        $topic_id_array = $topic_query_result->result_array();

        if ($crs_owner != $course_designer) {
            //update into course_clo_owner table
            $owner_data = array(
                'clo_owner_id' => $course_designer,
                'crclm_id' => $crclm_id,
                'crclm_term_id' => $crclm_term_id,
                'dept_id' => $review_dept,
                'modified_by' => $this->ion_auth->user()->row()->id,
                'modified_date' => date('Y-m-d')
            );
            $this->db->where('crs_id', $crs_id)
                    ->update('course_clo_owner', $owner_data);

            // Dashboard active links update of Course Owner till Mapping between COs to POs
            $query = 'UPDATE dashboard SET receiver_id = "' . $course_designer . '" 
							WHERE state IN (1,3,4,6,7)
							AND receiver_id = "' . $crs_owner . '" 
							AND particular_id = "' . $crs_id . '" 
							AND crclm_id = "' . $crclm_id . '" 
							AND status = 1 ';
            $query_result = $this->db->query($query);

            // Dashboard active links update of Course Owner form Topic to Mapping between TLOs to COs

            foreach ($topic_id_array AS $topic_id) {
                $query = 'UPDATE dashboard SET receiver_id = "' . $course_designer . '" 
								WHERE state IN (1,3,4)
								AND receiver_id = "' . $crs_owner . '" 
								AND particular_id = "' . $topic_id['topic_id'] . '" 
								AND crclm_id = "' . $crclm_id . '" 
								AND entity_id = 17
								AND status = 1 ';
                $query_result = $this->db->query($query);
            }
        }

        if ($crs_reviewer != $course_reviewer) {
            //update into course_clo_reviewer table
            $reviewer_data = array(
                'crclm_id' => $crclm_id,
                'term_id' => $crclm_term_id,
                'dept_id' => $review_dept,
                'validator_id' => $course_reviewer,
                'last_date' => $last_date,
                'modified_by' => $this->ion_auth->user()->row()->id,
                'modified_date' => date('Y-m-d')
            );
            $this->db->where('crs_id', $crs_id)
                    ->update('course_clo_validator', $reviewer_data);

            // Dashboard active links update of Course Reviewer
            $query = 'UPDATE dashboard SET receiver_id = "' . $course_reviewer . '" 
							WHERE state IN (1,2)
							AND receiver_id = "' . $crs_reviewer . '" 
							AND particular_id = "' . $crs_id . '" 
							AND crclm_id = "' . $crclm_id . '" 
							AND status = 1 ';
            $query_result = $this->db->query($query);

            // Dashboard active links update of Course Reviewer form Topic to Mapping between TLOs to COs

            foreach ($topic_id_array AS $topic_id) {
                $query = 'UPDATE dashboard SET receiver_id = "' . $course_reviewer . '" 
								WHERE state = 2
								AND receiver_id = "' . $crs_reviewer . '" 
								AND particular_id = "' . $topic_id['topic_id'] . '" 
								AND crclm_id = "' . $crclm_id . '" 
								AND entity_id = 17
								AND status = 1 ';
                $query_result = $this->db->query($query);
            }
        }
        //update into course_clo_reviewer table
        $reviewer_data = array(
            'crclm_id' => $crclm_id,
            'term_id' => $crclm_term_id,
            'dept_id' => $review_dept,
            'last_date' => $last_date,
            'modified_by' => $this->ion_auth->user()->row()->id,
            'modified_date' => date('Y-m-d')
        );
        $this->db->where('crs_id', $crs_id)
                ->update('course_clo_validator', $reviewer_data);

        //insert predecessor course array into predecessor courses table
        $pre_crs_array = '';
        $pre_crs_array = explode('<>', $pre_courses);
        $lmax = sizeof($pre_crs_array);
        for ($l = 0; $l < $lmax; $l++) {
            $predecessor_data = array(
                'crs_id' => $crs_id,
                'predecessor_course' => $pre_crs_array[$l],
                'created_by' => $this->ion_auth->user()->row()->id,
                'create_date' => date('Y-m-d')
            );
            if ($pre_crs_array[$l] != '') {
                $this->db->insert('predecessor_courses', $predecessor_data);
            }
        }
        // delete predecessor courses array 
        $kmax = sizeof($del_pre_courses);
        for ($k = 0; $k < $kmax; $k++) {
            if ($del_pre_courses[$k] != '') {
                $this->db->where('predecessor_id', $del_pre_courses[$k])
                        ->delete('predecessor_courses');
            }
        }


        $query = $this->db->query('SELECT CONCAT(COALESCE(cognitive_domain_flag,""),",",COALESCE(affective_domain_flag,""),",",COALESCE(psychomotor_domain_flag,""))  crs_id from course where crs_id= "' . $crs_id . '"');
        $re = $query->result_array();
        //7.2 changes
        //$count = count($re[0]['crs_id']);

        $set_data = (explode(",", $re[0]['crs_id']));

        $sk = 0;
        $bloom_domain_query = $this->db->query('SELECT bld_id,bld_name,status FROM bloom_domain');
        $bloom_domain_data = $bloom_domain_query->result_array();
        foreach ($bloom_domain_data as $bdd) {

            if ($set_data[$sk] == 1 && $bdd['status'] == 1) {
                $bld_id [] = $bdd['bld_id'];
            }
            $sk++;
        }
        $Bld_id_data = implode(",", $bld_id);

        $fetch_map_course_bloom_level = $this->db->query("select DISTINCT bld_id  from map_course_bloomlevel where crs_id = " . $crs_id . "");
        $fetch_map_course_bloom_level_result = $fetch_map_course_bloom_level->result_array();

        if (!empty($fetch_map_course_bloom_level_result)) {
            $this->db->query("delete from map_course_bloomlevel where crs_id = " . $crs_id . " ");
        }


        $bld_id_single = str_replace("'", "", $Bld_id_data);
        $bloom_lvl_query = 'select * from  bloom_level where bld_id IN(' . $bld_id_single . ') ORDER BY LPAD(LOWER(level),5,0) ASC';
        $bloom_level_threshold_data = $this->db->query($bloom_lvl_query);
        $bloom_level_threshold = $bloom_level_threshold_data->result_array();
        foreach ($bloom_level_threshold as $bloom) {
            $data = array(
                'crclm_id' => $crclm_id,
                'term_id' => $crclm_term_id,
                'crs_id' => $crs_id,
                'bloom_id' => $bloom['bloom_id'],
                'cia_bloomlevel_minthreshhold' => 50,
                'tee_bloomlevel_minthreshhold' => 50,
                'bloomlevel_studentthreshhold' => 70,
                'created_by' => $this->ion_auth->user()->row()->id,
                'created_date' => date('Y-m-d'),
                'modified_by' => $this->ion_auth->user()->row()->id,
                'modified_date' => date('Y-m-d'));
            $this->db->insert('map_course_bloomlevel', $data);
        }



        return TRUE;
    }

// End of function update_course.

    /* Function is used to find the rows with a same course code & course title from course table.
     * @param - curriculum id, course title, course code & course id.
     * @returns- a row count value.
     */

    public function course_title_search_edit($crclm_id, $crs_title, $crs_code, $crs_id) {
        $crs_title = $this->db->escape_str($crs_title);
        $crs_code = $this->db->escape_str($crs_code);
        $query = 'SELECT crs.crs_code, ct.term_name FROM course crs
                    JOIN crclm_terms ct ON crs.crclm_term_id = ct.crclm_term_id
					WHERE crs.crclm_id = "' . $crclm_id . '" 
					AND crs.crs_code LIKE "' . $crs_code . '" 
					AND crs.crs_id != "' . $crs_id . '" ';
        $result = $this->db->query($query);
        $count = $result->num_rows();

        if ($count == 1) {
            return $result->result();
        } else {
            $query = 'SELECT crs.crs_title, ct.term_name FROM course crs
                        JOIN crclm_terms ct ON crs.crclm_term_id = ct.crclm_term_id
						WHERE crs.crclm_id = "' . $crclm_id . '" 
						AND crs.crs_title LIKE "' . $crs_title . '" 
						AND crs.crs_id != "' . $crs_id . '" ';
            $result = $this->db->query($query);
            return $result->result();
        }
    }

// End of function course_title_search_edit.

    /**
     * Function to fetch help related details for course, which is
      used to provide link to help page
     * @return: serial number (help id), entity data, help description, help entity id and file path
     */
    public function course_help() {
        $help = 'SELECT serial_no, entity_data, help_desc 
				 FROM help_content 
				 WHERE entity_id = 4';
        $result = $this->db->query($help);
        $row = $result->result_array();
        $data['help_data'] = $row;

        if (!empty($data['help_data'])) {
            $help_entity_id = $row[0]['serial_no'];

            $file_query = 'SELECT help_entity_id, file_path
						   FROM uploads 
						   WHERE help_entity_id = "' . $help_entity_id . '"';
            $file_data = $this->db->query($file_query);
            $file_name = $file_data->result_array();
            $data['file'] = $file_name;

            return $data;
        } else {
            return $data;
        }
    }

    /**
     * Function to fetch help related to course to display the help contents in a new window
     * @parameters: help id
     * @return: entity data and help description
     */
    public function help_content($help_id) {
        $help = 'SELECT entity_data, help_desc 
				 FROM help_content 
				 WHERE serial_no = "' . $help_id . '"';
        $result_help = $this->db->query($help);
        $row = $result_help->result_array();

        return $row;
    }

    /* Function is used to update course, course designer, course reviewer & predecessor course details 
     * onto the course, course_clo_owner, course_clo_reviewer & predecessor course tables.
     * @param - course data, course_clo_owner data, course_clo_reviewer data &  predecessor course data.
     * @returns- a boolean value.
     */

    public function update_course_details(//course table data
    $crs_id, $crclm_id, $crclm_term_id, $crs_type_id, $crs_code, $crs_mode, $crs_title, $crs_acronym, $co_crs_owner, $cia_passing_marks, $tee_passing_marks, $crs_domain_id, $lect_credits, $tutorial_credits, $practical_credits, $self_study_credits, $total_credits, $contact_hours, $cie_marks, $see_marks, $attendance_marks, $ss_marks, $mid_term_marks, $total_marks, $see_duration,
    //course_clo_owner
            $course_designer,
    //course_clo_reviewer
            $review_dept, $course_reviewer, $last_date,
    //predecessor courses array
            $pre_courses,
    // delete predecessor courses array
            $del_pre_courses,
    // total weightage of cia and tee
            $total_cia_weightage, $total_mte_weightage, $total_tee_weightage, $bld_1, $bld_2, $bld_3, $clo_bl_flag, $cia_check, $mte_check, $tee_check, $elective_crs_flag, $preset_crs_domain = NULL,
            $crs_attainment_finalize_flag, $new_bloom_suggestion_flag,$tutorial, $indirect_flag
    ) {
	
		$updateCourseAttainment = 'CALL updateCourseAttainment('.$crs_id.', '.$total_cia_weightage.', '.$total_mte_weightage.', '.$total_tee_weightage.')';
		$updateCourseAttainment = $this->db->query($updateCourseAttainment);

        $query_map_exist = $this->db->query('select * from map_course_bloomlevel  where crs_id = ' . $crs_id . ' ');
        $result_query_map_exist = $query_map_exist->result_array();
        // get old course owner id.        
        $old_crs_owner_id_query = 'SELECT clo_owner_id FROM course_clo_owner WHERE crs_id = "' . $crs_id . '"';
        $old_crs_owner = $this->db->query($old_crs_owner_id_query);
        $old_crs_owner_id = $old_crs_owner->row_array();

        $old_crs_mode_query = $this->db->query("SELECT crs_mode FROM course WHERE crs_id=$crs_id")->result_array();
        $old_crs_mode = $old_crs_mode_query[0]['crs_mode'];

        // Insert sections based on course mode when Batchwise Section is set at org level
        if($crs_mode != $old_crs_mode) {
            if($this->ion_auth->user()->row()->org_name->BW_section == 1) {
                $this->db->query("DELETE FROM map_courseto_course_instructor WHERE crs_id = ".$crs_id."");
                if($crs_mode == 1) { // Lab
                    $this->db->query("INSERT INTO map_courseto_course_instructor(crclm_id,crclm_term_id,crs_id,course_instructor_id,section_id,created_by,created_date)
                            SELECT $crclm_id,$crclm_term_id,$crs_id,$course_designer,mt_details_id," . $this->ion_auth->user()->row()->id . ",'" . date('Y-m-d') . "' 
                            FROM master_type_details WHERE org_type='BATCHWISE_SECTION' AND mtd_status=1 LIMIT 1");
                } else if($crs_mode == 2) { // Thery with lab
                    $this->db->query("INSERT INTO map_courseto_course_instructor(crclm_id,crclm_term_id,crs_id,course_instructor_id,section_id,created_by,created_date)
                            SELECT $crclm_id,$crclm_term_id,$crs_id,$course_designer,mt_details_id," . $this->ion_auth->user()->row()->id . ",'" . date('Y-m-d') . "' 
                            FROM master_type_details WHERE org_type='SECTION' AND mtd_status=1 LIMIT 1");
                    $this->db->query("INSERT INTO map_courseto_course_instructor(crclm_id,crclm_term_id,crs_id,course_instructor_id,section_id,created_by,created_date)
                                SELECT $crclm_id,$crclm_term_id,$crs_id,$course_designer,mt_details_id," . $this->ion_auth->user()->row()->id . ",'" . date('Y-m-d') . "' 
                                FROM master_type_details WHERE org_type='BATCHWISE_SECTION' AND mtd_status=1 LIMIT 1");
                } else { // Theory
                    $this->db->query("INSERT INTO map_courseto_course_instructor(crclm_id,crclm_term_id,crs_id,course_instructor_id,section_id,created_by,created_date)
                            SELECT $crclm_id,$crclm_term_id,$crs_id,$course_designer,mt_details_id," . $this->ion_auth->user()->row()->id . ",'" . date('Y-m-d') . "' 
                            FROM master_type_details WHERE org_type='SECTION' AND mtd_status=1 LIMIT 1");
                }
            } else { // Theory
                $this->db->query("DELETE FROM map_courseto_course_instructor WHERE crs_id = ".$crs_id."");
                $this->db->query("INSERT INTO map_courseto_course_instructor(crclm_id,crclm_term_id,crs_id,course_instructor_id,section_id,created_by,created_date)
                        SELECT $crclm_id,$crclm_term_id,$crs_id,$course_designer,mt_details_id," . $this->ion_auth->user()->row()->id . ",'" . date('Y-m-d') . "' 
                        FROM master_type_details WHERE org_type='SECTION' AND mtd_status=1 LIMIT 1");
            }
        } else {
            $this->db->query("UPDATE map_courseto_course_instructor SET crclm_id = ".$crclm_id.", crclm_term_id = ".$crclm_term_id." WHERE crs_id = ".$crs_id);
        }

        if( $crs_attainment_finalize_flag){
             //update into course table
        $course_data = array(
            'crclm_id' => $crclm_id,
            'crclm_term_id' => $crclm_term_id,
            'crs_type_id' => $crs_type_id,
            'crs_code' => $crs_code,
            'crs_mode' => $crs_mode,
            'crs_title' => $crs_title,
            'crs_acronym' => $crs_acronym,
            'co_crs_owner' => $co_crs_owner,
            'crs_domain_id' => $crs_domain_id,
            'lect_credits' => $lect_credits,
            'tutorial_credits' => $tutorial_credits,
            'practical_credits' => $practical_credits,
            'self_study_credits' => $self_study_credits,
            'total_credits' => $total_credits,
            'contact_hours' => $contact_hours,
            'cie_marks' => $cie_marks,
            'mid_term_marks' => $mid_term_marks,
            'see_marks' => $see_marks,
            'attendance_marks' => $attendance_marks,
            'ss_marks' => $ss_marks,
            'total_marks' => $total_marks,
            'see_duration' => $see_duration,
            'total_cia_weightage' => $total_cia_weightage,
            'total_mte_weightage' => $total_mte_weightage,
            'total_tee_weightage' => $total_tee_weightage,
            'cognitive_domain_flag' => $bld_1=='' ? NULL : $bld_1,
            'affective_domain_flag' => $bld_2=='' ? NULL : $bld_2,
            'psychomotor_domain_flag' => $bld_3=='' ? NULL : $bld_3,
            'clo_bl_flag' => $clo_bl_flag,
            'elective_crs_flag' => $elective_crs_flag,
            'crs_bl_sugg_flag' => $new_bloom_suggestion_flag,
            'cia_passing_marks' => $cia_passing_marks,
            'tee_passing_marks' => $tee_passing_marks,
            'created_by' => $this->ion_auth->user()->row()->id,
            'modified_by' => $this->ion_auth->user()->row()->id,
            'modify_date' => date('Y-m-d H:m:s'),
            'tutorial'=>$tutorial,
			'indirect_flag' => $indirect_flag
        );        
        } else {

             //update into course table
        $course_data = array(
            'crclm_id' => $crclm_id,
            'crclm_term_id' => $crclm_term_id,
            'crs_type_id' => $crs_type_id,
            'crs_code' => $crs_code,
            'crs_mode' => $crs_mode,
            'crs_title' => $crs_title,
            'crs_acronym' => $crs_acronym,
            'co_crs_owner' => $co_crs_owner,
            'crs_domain_id' => $crs_domain_id,
            'lect_credits' => $lect_credits,
            'tutorial_credits' => $tutorial_credits,
            'practical_credits' => $practical_credits,
            'self_study_credits' => $self_study_credits,
            'total_credits' => $total_credits,
            'contact_hours' => $contact_hours,
            'cie_marks' => $cie_marks,
            'mid_term_marks' => $mid_term_marks,
            'see_marks' => $see_marks,
            'attendance_marks' => $attendance_marks,
            'ss_marks' => $ss_marks,
            'total_marks' => $total_marks,
            'see_duration' => $see_duration,
            'total_cia_weightage' => $total_cia_weightage,
            'total_mte_weightage' => $total_mte_weightage,
            'total_tee_weightage' => $total_tee_weightage,
            'cognitive_domain_flag' => $bld_1=='' ? NULL : $bld_1,
            'affective_domain_flag' => $bld_2=='' ? NULL : $bld_2,
            'psychomotor_domain_flag' => $bld_3=='' ? NULL : $bld_3,
            'clo_bl_flag' => $clo_bl_flag,
            'cia_flag' => $cia_check,
            'mte_flag' => $mte_check,
            'tee_flag' => $tee_check,
            'elective_crs_flag' => $elective_crs_flag,
            'crs_bl_sugg_flag' => $new_bloom_suggestion_flag,
            'cia_passing_marks' => $cia_passing_marks,
            'tee_passing_marks' => $tee_passing_marks,
            'created_by' => $this->ion_auth->user()->row()->id,
            'modified_by' => $this->ion_auth->user()->row()->id,
            'modify_date' => date('Y-m-d H:m:s'),
            'tutorial' => $tutorial,
			'indirect_flag' => $indirect_flag
        );        

        }

        $this->db->where('crs_id', $crs_id)
                ->update('course', $course_data);

        if (isset($bld_1)) {
            if ($bld_1 == 1) {
                $bld_11 = 1;
            }
            if ($bld_1 == '') {
                $bld_11 = 0;
            }
        } else {
            $bld_11 = 0;
        }
        if (isset($bld_2)) {
            if ($bld_2 == 1) {
                $bld_12 = 1;
            }
            if ($bld_2 == '') {
                $bld_12 = 0;
            }
        } else {
            $bld_11 = 0;
        }

        if (isset($bld_3)) {
            if ($bld_3 == 1) {
                $bld_13 = 1;
            }
            if ($bld_3 == '') {
                $bld_13 = 0;
            }
        } else {
            $bld_13 = 0;
        }

        $bld_array[] = ($bld_11);
        $bld_array[] = ($bld_12);
        $bld_array[] = ($bld_13);

        $query = $this->db->query('SELECT CONCAT(COALESCE(cognitive_domain_flag,""),",",COALESCE(affective_domain_flag,""),",",COALESCE(psychomotor_domain_flag,""))  crs_id from course where crs_id= "' . $crs_id . '"');
        $re = $query->result_array();
        //PHP 7.2 changes
        //$count = count($re[0]['crs_id']);

        $set_data = (explode(",", $re[0]['crs_id']));

        $query_map = $this->db->query('select b.bld_id from map_course_bloomlevel c left join bloom_level as b on c.bloom_id  = b.bloom_id  where crclm_id = "' . $crclm_id . '" and crs_id = "' . $crs_id . '" group by b.bld_id ');
        $re_query_map = $query_map->result_array();


        $sk = 0;
        $bloom_domain_query = $this->db->query('SELECT bld_id,bld_name,status FROM bloom_domain');
        $bloom_domain_data = $bloom_domain_query->result_array();

        foreach ($bloom_domain_data as $bdd) {
            if ($set_data[$sk] == 1 && $bdd['status'] == 1) {
                $bld_id [] = $bdd['bld_id'];
            }
            $sk++;
        }
        $list = array();
        foreach ($re_query_map as $v) {
            array_push($list, $v['bld_id']);
        }
		$domain_val = array();
		if(!empty($bld_id)) {
			foreach ($bld_id as $num) {
				if (!(in_array($num, $list))) {
					$domain_val[] = $num;
				}
			}
		}
        if (!empty($domain_val)) {


            $Bld_id_data = implode(",", $domain_val);
            $bld_id_single = str_replace("'", "", $Bld_id_data);

            $bloom_lvl_query = 'select * from  bloom_level where bld_id IN(' . $bld_id_single . ') ORDER BY LPAD(LOWER(level),5,0) ASC';
            $bloom_level_threshold_data = $this->db->query($bloom_lvl_query);
            $bloom_level_threshold = $bloom_level_threshold_data->result_array();
            foreach ($bloom_level_threshold as $bloom) {
                $data = array(
                    'crclm_id' => $crclm_id,
                    'term_id' => $crclm_term_id,
                    'crs_id' => $crs_id,
                    'bloom_id' => $bloom['bloom_id'],
                    'bld_id' => $bloom['bld_id'],
                    'cia_bloomlevel_minthreshhold' => 50,
                    'mte_bloomlevel_minthreshhold' => 50,
                    'tee_bloomlevel_minthreshhold' => 50,
                    'bloomlevel_studentthreshhold' => 70,
                    'created_by' => $this->ion_auth->user()->row()->id,
                    'created_date' => date('Y-m-d'),
                    'modified_by' => $this->ion_auth->user()->row()->id,
                    'modified_date' => date('Y-m-d'));
                $this->db->insert('map_course_bloomlevel', $data);
            }
            $delete_query = $this->db->query('delete from map_course_bloomlevel where bld_id NOT IN( ' . $bld_id_single . ' ) and crs_id = ' . $crs_id . '');
        }

        // Dashboard active links update if Course Owner or Course Reviewer are changed
        // To fetch Course Owner & Course Reviewer query
        $query = 'SELECT co.clo_owner_id, cr.validator_id
						FROM course_clo_owner AS co, course_clo_validator cr 
						WHERE co.crs_id = "' . $crs_id . '" 
						AND cr.crs_id = "' . $crs_id . '" ';

        $query_result = $this->db->query($query);
        $data = $query_result->result_array();
        $crs_owner = $data['0']['clo_owner_id'];
        $crs_reviewer = $data['0']['validator_id'];
        // To fetch Topic Ids for a given Course query
        $topic_query = 'SELECT topic_id, t_unit_id
								FROM topic
								WHERE course_id = "' . $crs_id . '" 
								AND curriculum_id = "' . $crclm_id . '" ';
        $topic_query_result = $this->db->query($topic_query);
        $topic_id_array = $topic_query_result->result_array();

        if ($crs_owner != $course_designer) {
			
			// If Course is imported or created without course domain then, update map_courseto_course_instructor table with the new course owner id for that particular section.
			if($preset_crs_domain == '' || $preset_crs_domain == NULL) {
				$update_query = 'UPDATE map_courseto_course_instructor SET crclm_id="' . $crclm_id . '" ,crclm_term_id="' . $crclm_term_id . '"  WHERE  crs_id ="' . $crs_id . '" AND course_instructor_id = "'.$course_designer.'"';
            $update = $this->db->query($update_query);
			}

            // Dashboard active links update of Course Owner till Mapping between COs to POs
            $query = 'UPDATE dashboard SET receiver_id = "' . $course_designer . '" 
							WHERE state IN (1,3,4,6,7)
							AND receiver_id = "' . $crs_owner . '" 
							AND particular_id = "' . $crs_id . '" 
							AND crclm_id = "' . $crclm_id . '" 
							AND status = 1 ';
            $query_result = $this->db->query($query);

            // Dashboard active links update of Course Owner form Topic to Mapping between TLOs to COs

            foreach ($topic_id_array AS $topic_id) {
                $query = 'UPDATE dashboard SET receiver_id = "' . $course_designer . '" 
								WHERE state IN (1,3,4)
								AND receiver_id = "' . $crs_owner . '" 
								AND particular_id = "' . $topic_id['topic_id'] . '" 
								AND crclm_id = "' . $crclm_id . '" 
								AND entity_id = 17
								AND status = 1 ';
                $query_result = $this->db->query($query);
            }

            //update into course_clo_owner table
            $owner_data = array(
                'clo_owner_id' => $course_designer,
                'crclm_id' => $crclm_id,
                'crclm_term_id' => $crclm_term_id,
                'dept_id' => $review_dept,
                'modified_by' => $this->ion_auth->user()->row()->id,
                'modified_date' => date('Y-m-d')
            );
            $this->db->where('crs_id', $crs_id)
                    ->update('course_clo_owner', $owner_data);
        } else {

            //update into course_clo_owner table
            $owner_data = array(
                'clo_owner_id' => $course_designer,
                'crclm_id' => $crclm_id,
                'crclm_term_id' => $crclm_term_id,
                'dept_id' => $review_dept,
                'modified_by' => $this->ion_auth->user()->row()->id,
                'modified_date' => date('Y-m-d')
            );
            $this->db->where('crs_id', $crs_id)
                    ->update('course_clo_owner', $owner_data);
        }

        if ($crs_reviewer != $course_reviewer) {

            // Dashboard active links update of Course Reviewer
            $query = 'UPDATE dashboard SET receiver_id = "' . $course_reviewer . '" 
							WHERE state IN (1,2)
							AND receiver_id = "' . $crs_reviewer . '" 
							AND particular_id = "' . $crs_id . '" 
							AND crclm_id = "' . $crclm_id . '" 
							AND status = 1 ';
            $query_result = $this->db->query($query);

            // Dashboard active links update of Course Reviewer form Topic to Mapping between TLOs to COs

            foreach ($topic_id_array AS $topic_id) {
                $query = 'UPDATE dashboard SET receiver_id = "' . $course_reviewer . '" 
								WHERE state = 2
								AND receiver_id = "' . $crs_reviewer . '" 
								AND particular_id = "' . $topic_id['topic_id'] . '" 
								AND crclm_id = "' . $crclm_id . '" 
								AND entity_id = 17
								AND status = 1 ';
                $query_result = $this->db->query($query);
            }

            //update into course_clo_reviewer table
            $reviewer_data = array(
                'crclm_id' => $crclm_id,
                'term_id' => $crclm_term_id,
                'dept_id' => $review_dept,
                'validator_id' => $course_reviewer,
                'last_date' => $last_date,
                'modified_by' => $this->ion_auth->user()->row()->id,
                'modified_date' => date('Y-m-d')
            );
            $this->db->where('crs_id', $crs_id)
                    ->update('course_clo_validator', $reviewer_data);
        }
        //update into course_clo_reviewer table
        $reviewer_data = array(
            'crclm_id' => $crclm_id,
            'term_id' => $crclm_term_id,
            'dept_id' => $review_dept,
            'last_date' => $last_date,
            'modified_by' => $this->ion_auth->user()->row()->id,
            'modified_date' => date('Y-m-d')
        );
        $this->db->where('crs_id', $crs_id)
                ->update('course_clo_validator', $reviewer_data);

        //insert predecessor course array into predecessor courses table
        $pre_crs_array = '';
        $pre_crs_array = explode('<>', $pre_courses);
        $lmax = sizeof($pre_crs_array);
        for ($l = 0; $l < $lmax; $l++) {
            $predecessor_data = array(
                'crs_id' => $crs_id,
                'predecessor_course' => $pre_crs_array[$l],
                'created_by' => $this->ion_auth->user()->row()->id,
                'create_date' => date('Y-m-d')
            );
            if ($pre_crs_array[$l] != '') {
                $this->db->insert('predecessor_courses', $predecessor_data);
            }
        }
        
        // Delete if data passed 
        if(!empty($del_pre_courses)) {
            // delete predecessor courses array 
            $kmax = sizeof($del_pre_courses);
            for ($k = 0; $k < $kmax; $k++) {
                if ($del_pre_courses[$k] != '') {
                    $this->db->where('predecessor_id', $del_pre_courses[$k])
                            ->delete('predecessor_courses');
                }
            }
        }

        return TRUE;
    }

// End of function update_course.

    /* Function is used to check whether bloom's domain is using in the co or tlo.
     * @param - course id & blooms domain id.
     * @returns- a boolean value
     */

    public function check_disable_bloom_domain($bld_id, $crs_id) {
        $check_bld_query = 'select * from clo AS clo inner join map_clo_bloom_level AS map on clo.clo_id=map.clo_id WHERE map.bld_id="' . $bld_id . '" AND clo.crs_id="' . $crs_id . '"';
        $clo = $this->db->query($check_bld_query);
        $clo = $clo->num_rows();
        $check_bld_query = 'select * from tlo AS tlo inner join map_tlo_bloom_level AS map on tlo.tlo_id=map.tlo_id WHERE map.bld_id="' . $bld_id . '" AND tlo.course_id="' . $crs_id . '"';
        $tlo = $this->db->query($check_bld_query);
        $tlo = $tlo->num_rows();

        $qp_exist = $this->db->query('select qmap.actual_mapped_id from qp_definition as q
									   join qp_unit_definition qm ON q.qpd_id = qm.qpd_id
									   join qp_mainquestion_definition qmd ON qm.qpd_unitd_id = qmd.qp_unitd_id
									   join qp_mapping_definition qmap ON qmd.qp_mq_id = qmap.qp_mq_id
									   where q.crs_id = "' . $crs_id . '" and qmap.entity_id = 23
									   group by qmap.actual_mapped_id;');
        $result = $qp_exist->result_array();

        if (!empty($result)) {
            $i = 0;
            for ($i = 0; $i < count($result); $i++) {
                $data [] = $result[$i]['actual_mapped_id'];
            }
            $bloom_id = implode(",", $data);
            $bld_id_single = str_replace("'", "", $bloom_id);

            $bloom_bld_id = $this->db->query(' SELECT bld_id from bloom_level where bloom_id IN (' . $bld_id_single . ') group by bld_id;');
            $re = $bloom_bld_id->result_array();

            for ($i = 0; $i < count($re); $i++) {
                $bld_id_data [] = $re[$i]['bld_id'];
            }

            $key = array_search($bld_id, $bld_id_data);

            if (in_array($bld_id, $bld_id_data)) {
                 $qp = 1;
            } else {
                 $qp = 0;
            }
        } else {
            $qp = 0;
        }

        $course_query = $this->db->query(' SELECT GROUP_CONCAT(A.occassion SEPARATOR ",  ") ao_method,A.clo_code as clo_code
                                            FROM
                                            (SELECT qpd_type,qpd.crs_id,GROUP_CONCAT(distinct co.clo_code) as clo_code,
                                            IF(qpd_type = 5 OR qpd_type = 4 ,"TEE is Defined",GROUP_CONCAT(distinct ao.ao_description SEPARATOR ",  ")) as occassion
                                            FROM qp_definition qpd
                                            LEFT JOIN assessment_occasions ao ON qpd.qpd_id = ao.qpd_id
                                            JOIN clo co ON co.crs_id = qpd.crs_id
                                            LEFT OUTER JOIN map_clo_bloom_level mbc ON mbc.clo_id = co.clo_id
                                            where qpd.crs_id = "' . $crs_id . '" AND mbc.bld_id="'.$bld_id.'"
                                            GROUP BY ao.ao_description
                                            ORDER by qpd.qpd_type) A
                                            GROUP by A.crs_id
                                       ');
        $course_result = $course_query->result_array();
         
        $data['course_result'] = $course_result;
        if ($clo || $tlo || $qp) {
            $data['modal_display'] = 1;
        } else {
            $data['modal_display'] = 0;
        }

        return $data;
    }

    /*
     * Function to asign the course instructor.
     */

    public function assign_course_instructor($crs_id, $trm_id, $crclm_id) {
        // course instructors to display
        // $select_course_instructor = 'SELECT ci.mcci_id, ci.crclm_id, ci.crclm_term_id, ci.crs_id, ci.course_instructor_id, ci.section_id, CONCAT(usr.title, usr.first_name," ", usr.last_name) as user_name, sec.mt_details_name as section   FROM map_courseto_course_instructor as ci'
        //         . ' JOIN users as usr ON id = ci.course_instructor_id'
        //         . ' JOIN master_type_details as sec ON sec.mt_details_id = ci.section_id and master_type_id = 34'
        //         . ' WHERE ci.crs_id="' . $crs_id . '" and ci.crclm_id="' . $crclm_id . '" and ci.crclm_term_id="' . $trm_id . '" ORDER BY sec.mt_details_id';
        // $instructor_data = $this->db->query($select_course_instructor);
        // $instructor_details = $instructor_data->result_array();

        $select_course_instructor = 'SELECT ci.mcci_id, ci.crclm_id, ci.crclm_term_id, ci.crs_id, ci.course_instructor_id, ci.section_id, group_concat(CONCAT(usr.title, usr.first_name," ", usr.last_name)) as user_name, sec.mt_details_name as section   FROM map_courseto_course_instructor as ci'
        . ' JOIN users as usr ON id = ci.course_instructor_id'
        . ' JOIN master_type_details as sec ON sec.mt_details_id = ci.section_id and master_type_id = 34'
        . ' WHERE ci.crs_id="' . $crs_id . '" and ci.crclm_id="' . $crclm_id . '" and ci.crclm_term_id="' . $trm_id . '" 
        GROUP BY section_id
        ORDER BY sec.mt_details_id';
        $instructor_data = $this->db->query($select_course_instructor);
        $instructor_details = $instructor_data->result_array();

        // fetch course owners for the curriculum
        $active = 1;
        $course_instructor_list_query = 'SELECT * FROM
                                (SELECT u.id, u.title, u.first_name,u.last_name,u.email
                                FROM users as u, users_groups as g, program as p, curriculum as c
                                WHERE u.id=g.user_id AND u.active = 1 AND u.base_dept_id = p.dept_id AND p.pgm_id = c.pgm_id AND g.group_id = 6 AND c.crclm_id = "' . $crclm_id . '"

                                UNION

                                SELECT u.id,u.title,u.first_name,u.last_name,u.email
                                FROM map_user_dept m,map_user_dept_role mdr,`groups` g,users u, program as p, curriculum as c,users_groups as gr
                                WHERE m.assigned_dept_id = p.dept_id AND p.pgm_id = c.pgm_id AND gr.group_id = 6 AND c.crclm_id = "' . $crclm_id . '"
                                AND m.user_id = mdr.user_id
                                AND mdr.role_id = g.id
                                AND m.user_id = u.id
                                AND g.id = 6) A ORDER BY A.first_name ASC ';
        $result = $this->db->query($course_instructor_list_query);
        $user_list = $result->result_array();

        $data['course_instructor_display'] = $instructor_details;
        $data['course_instructor_list'] = $user_list;
        return $data;
    }

    /*
     * Function to add new course instructor to the system
     */

     public function add_course_instructor($section_id, $instructor_id, $ci_crclm_id, $ci_term_id, $ci_crs_id) {
        // check course instructor is assigned for the section or not.
        $check_query = 'SELECT COUNT(mcci_id) as counter FROM map_courseto_course_instructor WHERE crclm_id = "' . $ci_crclm_id . '" AND crclm_term_id = "' . $ci_term_id . '" AND crs_id = "' . $ci_crs_id . '" AND section_id = "' . $section_id . '" ';
        $count_data = $this->db->query($check_query);
        $count = $count_data->row_array();
        if ($count['counter'] >= 1) {
            return '-1';
        } else {
            for($i=0;$i<count($instructor_id);$i++) {
            $insert_record = array(
                'crclm_id' => $ci_crclm_id,
                'crclm_term_id' => $ci_term_id,
                'crs_id' => $ci_crs_id,
                'course_instructor_id' => $instructor_id[$i],
                'section_id' => $section_id,
                'created_by' => $this->ion_auth->user()->row()->id,
                'modified_by' => $this->ion_auth->user()->row()->id,
                'created_date' => date('y-m-d'),
                'modified_date' => date('y-m-d'));
            $this->db->insert('map_courseto_course_instructor', $insert_record);
            

            $meta_data_query = ' SELECT crs.crs_title, crs.crs_code, crclm.crclm_name, term.term_name, mt.mt_details_name as section_name FROM course as crs '
                    . ' JOIN curriculum as crclm ON crclm.crclm_id = "' . $ci_crclm_id . '" '
                    . ' JOIN crclm_terms as term ON term.crclm_term_id = "' . $ci_term_id . '" '
                    . ' JOIN master_type_details as mt ON mt.mt_details_id = "' . $section_id . '" '
                    . ' WHERE crs.crs_id = "' . $ci_crs_id . '" ';
            $meta_data_data = $this->db->query($meta_data_query);
            $meta_data = $meta_data_data->row_array();

            $description = 'Term(Semester):- ' . $meta_data['term_name'];
            $reviewer_description = $description . ', Course:- ' . $meta_data['crs_title'] .'('.$meta_data['crs_code'] . ') is created, you have been chosen as a Course Instructor for Section/Devision :- ' . $meta_data['section_name'] . '.';

            $dashboard_insert = array(
                'crclm_id' => $ci_crclm_id,
                'entity_id' => 4,
                'particular_id' => $ci_crs_id,
                'sender_id' => $this->ion_auth->user()->row()->id,
                'receiver_id' => $instructor_id[$i],
                'url' => '#',
                'description' => $reviewer_description,
                'state' => 1,
                'status' => 1,
            );
        }
            $this->db->insert('dashboard', $dashboard_insert);

            return 1;
        }
    }

    /*
     * Function to load table.
     */

     public function generate_table($ci_crs_id, $ci_crclm_id, $ci_term_id) {

        // course instructors to display
        $select_course_instructor = 'SELECT ci.mcci_id, ci.crclm_id, ci.crclm_term_id, ci.crs_id, ci.course_instructor_id, ci.section_id, GROUP_CONCAT(CONCAT(usr.title, usr.first_name," ", usr.last_name)) as user_name, sec.mt_details_name as section   FROM map_courseto_course_instructor as ci'
                                    . ' JOIN users as usr ON id = ci.course_instructor_id'
                                    . ' JOIN master_type_details as sec ON sec.mt_details_id = ci.section_id and master_type_id = 34'
                                    . ' WHERE ci.crs_id="' . $ci_crs_id . '" and ci.crclm_id="' . $ci_crclm_id . '" and ci.crclm_term_id="' . $ci_term_id . '"
                                    GROUP BY section_id
                                    ORDER BY sec.mt_details_id';
        $instructor_data = $this->db->query($select_course_instructor);
        $instructor_details = $instructor_data->result_array();
        $active = 1;
        $course_instructor_list_query = 'SELECT * FROM
                                (SELECT u.id, u.title, u.first_name,u.last_name,u.email
                                FROM users as u, users_groups as g, program as p, curriculum as c
                                WHERE u.id=g.user_id AND u.active = 1 AND u.user_dept_id = p.dept_id AND p.pgm_id = c.pgm_id AND g.group_id = 6 AND c.crclm_id = "' . $ci_crclm_id . '"

                                UNION

                                SELECT u.id,u.title,u.first_name,u.last_name,u.email
                                FROM map_user_dept m,map_user_dept_role mdr,`groups` g,users u, program as p, curriculum as c,users_groups as gr
                                WHERE m.assigned_dept_id = p.dept_id AND p.pgm_id = c.pgm_id AND gr.group_id = 6 AND c.crclm_id = "' . $ci_crclm_id . '"
                                AND m.user_id = mdr.user_id
                                AND mdr.role_id = g.id
                                AND m.user_id = u.id
                                AND g.id = 6) A ORDER BY A.first_name ASC ';
        $result = $this->db->query($course_instructor_list_query);
        $user_list = $result->result_array();

        $instructor_result['instructor_data'] = $instructor_details;
        $instructor_result['ins_list'] = $user_list;
        return $instructor_result;
    }

    /*
     * Function to Edit Save of instructor.
     */

     public function edit_save_instructor($crclm_id,$term_id,$instructor_id, $section_id, $mcci_id, $crs_id) {
        $delete_query = 'DELETE FROM map_courseto_course_instructor WHERE section_id="'.$section_id.'" and crs_id="'.$crs_id.'"';
        $delete_data = $this->db->query($delete_query);
        $instructorid = implode(',', $instructor_id);
        for($i=0;$i<count($instructor_id);$i++) {
            if(!empty($instructor_id)) {
                $instructor_id_size = count($instructor_id);
                            $bl_domain_insertion_data = array(
                                'crclm_id' => $crclm_id,
                                'crclm_term_id' => $term_id,
                                'crs_id' => $crs_id,
                                'course_instructor_id' => $instructor_id[$i],
                                'section_id' => $section_id,
                                'created_date' => $this->ion_auth->user()->row()->id
                            );
                $this->db->insert('map_courseto_course_instructor', $bl_domain_insertion_data);
            }

            $update_crs_instructor_query = 'UPDATE lms_map_instructor_topic SET instructor_id = "' . $instructor_id[$i] . '" WHERE section_id = ' . $section_id . ' AND crs_id = "' . $crs_id . '"';
            $result1 = $this->db->query($update_crs_instructor_query);
        }

        return 1;
    }


    /*
     * Function to delete course instructor
     */

     public function delete_instructor($mcci_id, $crclm_id, $term_id, $course_id, $sec_id) {


        $data_check = 'SELECT qpd_id FROM assessment_occasions WHERE crs_id = "' . $course_id . '" AND section_id = "' . $sec_id . '" ';
        $data_data = $this->db->query($data_check);
        $data_res = $data_data->result_array();

        $occ_check = 'SELECT  ao_id  FROM assessment_occasions  WHERE crs_id = "' . $course_id . '" AND section_id = "' . $sec_id . '" ';
        $occ_data = $this->db->query($occ_check);
        $occ_res = $occ_data->result_array();

        if (!empty($occ_res)) {
            foreach ($occ_res as $res) {
                $delete_occ = 'DELETE  FROM assessment_occasions  WHERE  ao_id = "' . $res['ao_id'] . '"';
                $delete_suc = $this->db->query($delete_occ);
            }
        }

        if (!empty($data_res)) {
            foreach ($data_res as $qp) {
                $delete_qp = 'DELETE FROM  qp_definition where qpd_id = "' . $qp['qpd_id'] . '" ';
                $delete_qp_suc = $this->db->query($delete_qp);
            }
        }

        $ci_query = 'SELECT mt.mt_details_name AS section, CONCAT(u.title, u.first_name," ", u.last_name) AS ci, mci.crclm_term_id AS term_id, 
            c.crs_title AS crs_name FROM users u, master_type_details mt, course c, map_courseto_course_instructor mci WHERE mci.mcci_id = "' . $mcci_id . '" AND 
            mt.mt_details_id=mci.section_id AND u.id=mci.course_instructor_id and c.crs_id=mci.crs_id';
        $ci_data = $this->db->query($ci_query);
        $ci_data = $ci_data->result_array();

        $crclmId = $ci_data[0]['term_id'];
        $entityVal = "Course";
        $uniqueColumn = $ci_data[0]['crs_name'];
        $columnName = $ci_data[0]['section'] . ":" . $ci_data[0]['ci'];

        $this->load->model('log_history/log_history_generic_model');
        $params = array(
            'crclm_id' => $crclmId,
            'entity' => $entityVal,
            'unique_column' => $uniqueColumn,
            'action' => $columnName,
            'unique_id_val' => $mcci_id
        );
        $this->log_history_generic_model->insert_delete_log_action($params);

        $delete_query = 'DELETE FROM map_courseto_course_instructor WHERE section_id = "' . $sec_id . '" AND crs_id= "'.$course_id.'" ';
        $delete = $this->db->query($delete_query);
        return 1;
    }

    public function section_co_finalize($course_id, $sec_id) {
        $number_section = $this->db->query('SELECT section_id FROM map_courseto_course_instructor WHERE crs_id = "' . $course_id . '"group by section_id');
        $number_section = $number_section->num_rows();
        if ($number_section == 1) {
            return 2;
        }
       
        $check_data_fialize = 'SELECT cia_finalise_flag FROM map_courseto_course_instructor WHERE crs_id = "' . $course_id . '" AND section_id ="' . $sec_id . '" ';
        $check_finalize = $this->db->query($check_data_fialize);
        $check_finalize_res = $check_finalize->row_array();        
        
        if ($check_finalize_res['cia_finalise_flag'] == 0) {
            $number_stud_section = $this->db->query('SELECT mcstd_id FROM map_courseto_student WHERE crs_id = "' . $course_id . '" AND section_id = "' . $sec_id . '" AND batch_id IS NULL');
            $reg_stud_count_section = $number_stud_section->num_rows();

            $number_stud_batch = $this->db->query('SELECT mcstd_id FROM map_courseto_student WHERE crs_id = "' . $course_id . '" AND batch_id = "' . $sec_id . '"');
            $reg_stud_count_batch = $number_stud_batch->num_rows();
            if ($reg_stud_count_section > 0 || $reg_stud_count_batch > 0) {
                $final_data = 3;
            }else{
                $final_data = 'true';
            }
        } else {
            $final_data = 'false';
        }
        return $final_data;
    }

    public function get_section_name($section_id) {
        $section_name_query = 'SELECT mt_details_name FROM master_type_details WHERE mt_details_id = "' . $section_id . '" ';
        $section_name_data = $this->db->query($section_name_query);
        $section_name_res = $section_name_data->row_array();
        $section_name = $section_name_res['mt_details_name'];
        return $section_name;
    }

    public function bloom_option_mandatory($crs_id, $clo_bl_flag_data) {
        if ($clo_bl_flag_data == 0) {
            $clo_bl_flag = 1;
        } else {
            $clo_bl_flag = 0;
        }
        $query = $this->db->query('update course set clo_bl_flag = "' . $clo_bl_flag . '", modified_by="'.$this->ion_auth->user()->row()->id.'" 
            where crs_id = "' . $crs_id . '" ');
    }

    public function stud_to_crs_reg_option_mandatory($crs_id, $stud_to_crs_reg_flag_data) {
        $query = $this->db->query('update course set edu_sys_flag = "' . $stud_to_crs_reg_flag_data . '", modified_by="'.$this->ion_auth->user()->row()->id.'" 
            where crs_id = "' . $crs_id . '" ');
           
    }

    public function check_mandatory_data_set_or_not($crs_id) {

        $query = $this->db->query("SELECT case when (cognitive_domain_flag || affective_domain_flag || psychomotor_domain_flag ) = 1 then  1 else 0
								   end as flag
								   from course where crs_id= '" . $crs_id . "'");
        return $query->result_array();
    }
    /*
      Function to check the assessment occasions defined for the section.
      @param: course id, section id.
      @return: 0:False, 1:True.
     */

     public function check_occasion($course_id, $sec_id) {
        $occasion_query = 'SELECT count(ao_id) as counter FROM assessment_occasions WHERE crs_id = ' . $course_id . ' AND section_id = ' . $sec_id . ' ';
        $occasion_data = $this->db->query($occasion_query);
        $result = $occasion_data->row_array();
        if ($result['counter'] == 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /*
     * Function to get the course owner details 
     * @param - 
     * returns
     */

    public function course_owner_login_details() {
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        $query = 'SELECT id, title, first_name, last_name, user_dept_id, base_dept_id FROM users WHERE id = ' . $loggedin_user_id;
        $result = $this->db->query($query);
        return $result->row_array();
    }

    /*
     * Function to get the section details 
     * @param   : curriculum id,term id,course id
     * returns  :
     */

    public function fetch_section_list($crclm_id, $trm_id, $crs_id) {
        $check_first_year = $this->db->query("SELECT COUNT(crclm_term_id) crclm_term_id
                                            FROM(SELECT crclm_term_id
                                            FROM crclm_terms c WHERE crclm_id=" . $crclm_id . "
                                            LIMIT 2
                                            ) A
                                            WHERE crclm_term_id=" . $trm_id . "")->result_array();
        $first_year = $check_first_year[0]['crclm_term_id'];
        $val = $this->ion_auth->user()->row();
        $firstyear_section = $val->org_name->FY_section;
        $batchwise_section = $val->org_name->BW_section;
        $already_assigned_section_list_query = 'SELECT GROUP_CONCAT(section_id) section_id FROM map_courseto_course_instructor WHERE crs_id = "' . $crs_id . '" ';
        $section_list_data = $this->db->query($already_assigned_section_list_query);
        $assigned_sections = $section_list_data->result_array();
        $sections_list = flattenArray($assigned_sections);
        $sections_list = $assigned_sections[0]['section_id'];

        if ($sections_list == NULL) {
            $sections_list = 0;
        }

        $crs_mode_result = $this->db->query("SELECT crs_mode,tutorial FROM course WHERE crs_id=$crs_id")->result_array();
        $crs_mode = $crs_mode_result[0]['crs_mode'];
        $tutorial = $crs_mode_result[0]['tutorial'];
		
        
        $first_year_flag = $this->db->query("SELECT first_year_flag FROM curriculum WHERE crclm_id=" . $crclm_id . "")->result_array();
        $first_year_flag = $first_year_flag[0]['first_year_flag'];

        if ($first_year == 1 && $first_year_flag == 1) {
            $first_year = 1;
        } else {
            $first_year = 0;
        }
        
        if (($first_year == 0 && $firstyear_section == 0 && $batchwise_section == 0) || ($first_year == 0 && $firstyear_section == 1 && $batchwise_section == 0) || ($first_year == 1 && $firstyear_section == 0 && $batchwise_section == 0)) {
            $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
        } else if (($first_year == 0 && $firstyear_section == 0 && $batchwise_section == 1) || ($first_year == 0 && $firstyear_section == 1 && $batchwise_section == 1) || ($first_year == 1 && $firstyear_section == 0 && $batchwise_section == 1)) {
            if ($crs_mode == 0) {
                if($tutorial == 0){
                    $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
                } else {
                    $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type IN('SECTION','BATCHWISE_SECTION') AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
                }
            } else if ($crs_mode == 2) {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type IN('SECTION','BATCHWISE_SECTION') AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            } else {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='BATCHWISE_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            }
        } else if ($first_year == 1 && $firstyear_section == 1 && $batchwise_section == 0) {
            if ($crs_mode == 0) {
                if($tutorial == 0){
                    $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
                } else {
                    $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
                }                
//                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            } else if ($crs_mode == 2) {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            } else {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            }
        } else if ($first_year == 1 && $firstyear_section == 1 && $batchwise_section == 1) {
            if ($crs_mode == 0) {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            } else if ($crs_mode == 2) {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type IN('FIRSTYEAR_SECTION','FIRSTYEAR_BATCHWISE_SECTION') AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            } else {
                $section_query = $this->db->query("SELECT * FROM master_type_details WHERE org_type='FIRSTYEAR_BATCHWISE_SECTION' AND mtd_status=1 AND mt_details_id NOT IN($sections_list) ORDER BY mt_details_name,org_type")->result_array();
            }
        }

        return $section_query;
    }

    //End of function course_owner_login_details
	
	/*
     * Function to check if the assessments exists for the course
     * @param   : array consisting of assessment type, course id
     * returns  :
     */

    public function check_course_assessments($params = array()) {
		$data = array();
		if(!empty($params)) {
			extract($params);
			if($crs_id) {
				$course_assessments = 'CALL checkCourseAssessments('.$crs_id.', "'.$assessment_type.'")';
				$course_assessments = $this->db->query($course_assessments);
				$data = $course_assessments->result_array();
			}
		}
		return $data;
	}
	
	/*
     * Function to fetch the assessment weightage for the course
     * @param   : array consisting of course id
     * returns  :
     */

    public function fetch_course_assessment_weightage($params = array()) {
		$data = array();
		if(!empty($params)) {
			extract($params);
			if($crs_id) {
				$course_assessments = 'SELECT total_cia_weightage, total_mte_weightage, total_tee_weightage
										FROM course
										WHERE crs_id = '.$crs_id;
				$course_assessments = $this->db->query($course_assessments);
				$data = $course_assessments->row_array();
			}
		}
		return $data;
    } 
    
    

    /*
     * Function to fetch the assessment weightage for the course
     * @param   : array consisting of course id
     * returns  :
     */

    public function check_co_po_mapping_completed($params = array()) {
		$data = array();
		if(!empty($params)) {
			extract($params);
			if($crs_id) {
				$course_assessments = 'SELECT state_id 
										FROM course
										WHERE crs_id = '.$crs_id;
				$course_assessments = $this->db->query($course_assessments);
                $data = $course_assessments->row_array();
                if($data['state_id'] === '1'){
                            $query = 'SELECT clo_po_id 
									   FROM clo_po_map
										WHERE crs_id = '.$crs_id;
				    $query_exec = $this->db->query($query);
                    if($query_exec->num_rows() > 0){
                        $state_id = 1;
                    } else {
                        $state_id = 0;
                    }
                } else if($data['state_id'] === '2') {
                    $query = 'SELECT clo_po_id 
							  FROM clo_po_map
							 WHERE crs_id = '.$crs_id;
				    $query_exec = $this->db->query($query);
                    if($query_exec->num_rows() > 0){
                        $state_id = 1;
                    } else {
                        $state_id = 0;
                    }
                } else {
                    $state_id = $data['state_id'];
                }
            }
		}
		return $state_id;
    } 

    /*
     * Function to fetch the assessment weightage for the course
     * @param   : array consisting of course id
     * returns  :
     */

    public function check_marks_imported_course($params = array()) {
		$data = array();
		if(!empty($params)) {
			extract($params);
			if($crs_id) {
                $assessment_created_query = 'SELECT mtd.mt_details_name, IF(qpd_type = 5 OR qpd_type = 4 ,"TEE is Defined",GROUP_CONCAT(distinct ao.ao_description SEPARATOR ",  ")) as occassions
                                    FROM qp_definition qpd
                                    LEFT JOIN assessment_occasions ao ON qpd.qpd_id = ao.qpd_id
                                    LEFT JOIN master_type_details mtd ON mtd.mt_details_id = ao.section_id
                                    where qpd.crs_id ='.$crs_id.'
                                    GROUP BY  ao.section_id
                                    ORDER by qpd.qpd_id';
				$assessment_created_exec = $this->db->query($assessment_created_query);
                
                if($assessment_created_exec->num_rows() > 0) {
                   
                    $assessment_query = 'SELECT sa.assessment_id,if(qpd.qpd_type = 5 ,"TEE is Defined",GROUP_CONCAT(distinct ao.ao_description SEPARATOR ", ")) as occassions, mtd.mt_details_name
                    FROM student_assessment sa
                    LEFT JOIN qp_mainquestion_definition qpmd ON sa.qp_mq_id = qpmd.qp_mq_id
                    LEFT JOIN qp_unit_definition qud ON qpmd.qp_unitd_id = qud.qpd_unitd_id
                    LEFT JOIN qp_definition qpd ON qud.qpd_id = qpd.qpd_id
                    LEFT JOIN assessment_occasions ao ON qpd.qpd_id = ao.qpd_id
                    LEFT JOIN master_type_details mtd ON mtd.mt_details_id = ao.section_id
                    where qpd.crs_id ='.$crs_id.'
                    GROUP BY  ao.section_id
                    ORDER BY qpd.qpd_id';
                    $assessment_exec = $this->db->query($assessment_query);

                    if($assessment_exec->num_rows() > 0) {
                        $data['assessment_result'] = $assessment_exec->result_array();
                        $data['assessment_created'] = 2;
                    } else {
                        $data['assessment_result'] = $assessment_created_exec->result_array();
                        $data['assessment_created'] = 1;
                    } 
                   } else {
                    $data['assessment_created'] = 0;
                    $data['assessment_result'] = 0;
                    
                } 
            }
		}
		return $data;
    } 


    public function send_mail_course_details($crs_id){
        $meta_data_query = 'SELECT concat(crs.crs_title," (",crs.crs_code,")") as crs_title, crclm.crclm_name, term.term_name, u.username
                        FROM course as crs 
                        JOIN curriculum as crclm ON crclm.crclm_id = crs.crclm_id
                        JOIN users as u ON u.id = "'.$this->ion_auth->user()->row()->id.'"
                        JOIN crclm_terms as term ON term.crclm_term_id = crs.crclm_term_id 
                        WHERE crs.crs_id = "' . $crs_id . '" ';
            $meta_data_data = $this->db->query($meta_data_query);
            $data['crs_data'] = $meta_data_data->row_array();


            $query = 'SELECT distinct course_instructor_id, crclm_id
                  FROM  map_courseto_course_instructor
                  WHERE crs_id ="' . $crs_id . '"';
            $query_data = $this->db->query($query);
            $data['crs_instr_data'] = $query_data->result_array();
      

            $query = 'SELECT crclm_id, clo_owner_id 
                    FROM  course_clo_owner
                    WHERE crs_id ="' . $crs_id . '"';
            $query_data = $this->db->query($query);
            $data['crs_owner_data'] = $query_data->row_array();


        return $data;
    }

    public function check_stud_count($crs_id){

        $query = "SELECT A.crs_id, COUNT(section_id) section_count, 
                GROUP_CONCAT(CONCAT('Section ',A.section_name,': ', A.stud_count) SEPARATOR '&#013;') AS section_wise_stud_count
                FROM
                (SELECT m.crs_id, m.section_id, mtd.mt_details_name AS section_name, COUNT(m.student_id) AS stud_count
                FROM su_student_stakeholder_details s
                JOIN map_courseto_student m ON s.ssd_id = m.student_id
                AND status IN(SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name NOT IN('Unregistered'))
                LEFT JOIN master_type_details mtd ON m.section_id = mtd.mt_details_id
                WHERE m.crs_id = {$crs_id}
                GROUP BY m.section_id) A
                GROUP BY A.crs_id";
        $query_data = $this->db->query($query);
        $data['stud_count'] = $query_data->row_array();

        return $data;
    }

    /* Function is used to fetch the section */
    public function fetch_sections($crs_id) {
        $query = "SELECT mtd2.mt_details_id, mtd2.mt_details_name
                FROM map_courseto_course_instructor mc
                LEFT JOIN master_type_details mtd1 ON (mtd1.org_type='BATCHWISE_SECTION' OR mtd1.org_type='FIRSTYEAR_BATCHWISE_SECTION') AND mc.section_id = mtd1.mt_details_id
                LEFT JOIN master_type_details mtd2 ON (mtd2.org_type='SECTION' OR mtd2.org_type='FIRSTYEAR_SECTION') AND (mc.section_id = mtd2.mt_details_id OR mtd1.parent_id = mtd2.mt_details_id)
                WHERE mc.crs_id = {$crs_id}
                GROUP BY mtd2.mt_details_id";
        $section_data = $this->db->query($query);
        return $section_data->result_array();
    }

    /*
     * Function to modiy the finalized student list for the course
     * @param - crs id , Section id
     * returns
     */
    public function check_crs_assessment_marks_uploaded($data = NULL) {
        extract($data);
        $query = 'SELECT DISTINCT ao.ao_description
                    FROM assessment_occasions ao
                    LEFT JOIN qp_definition q ON ao.qpd_id = q.qpd_id
                    LEFT JOIN qp_unit_definition qpud ON q.qpd_id = qpud.qpd_id
                    LEFT JOIN qp_mainquestion_definition qpmd ON qpud.qpd_unitd_id = qpmd.qp_unitd_id
                    LEFT JOIN student_assessment sa ON qpmd.qp_mq_id = sa.qp_mq_id
                    WHERE ao.crs_id = '.$crs_id.' AND ao.section_id = '.$section_id.'
                    AND sa.assessment_id IS NOT NULL';
        $cia_result = $this->db->query($query);
        $cia_result = $cia_result->result_array();

        $query = 'SELECT DISTINCT q.qpd_id
                    FROM qp_definition q
                    LEFT JOIN qp_unit_definition qpud ON q.qpd_id = qpud.qpd_id
                    LEFT JOIN qp_mainquestion_definition qpmd ON qpud.qpd_unitd_id = qpmd.qp_unitd_id
                    LEFT JOIN student_assessment sa ON qpmd.qp_mq_id = sa.qp_mq_id
                    WHERE q.crs_id = '.$crs_id.' AND q.qpd_type = 5
                    AND sa.assessment_id IS NOT NULL';
        $tee_result = $this->db->query($query);
        $tee_result = $tee_result->result_array();

        if(empty($cia_result) && empty($tee_result)){
            return 0;
        }else{
            return 1;
        }
    }

    /*
     * Function to finalize the student list for the course
     * @param - crclm id, crs id , crclm term id
     * returns
     */
    public function get_finalized_student_list($data = NULL) {
        extract($data);
        if($crs_id != NULL && $section_id != NULL) {
            $query = "SELECT m.mcstd_id, m.student_id, s.student_usn, CONCAT(s.title, ' ', s.first_name, ' ', COALESCE(s.last_name, '')) AS student_name, 
                        crclm_name AS student_curriculum, s.email, mtd.master_type_details_alias_name AS mt_details_name, A.batch_wise_stud_count, mtd_status.mt_details_name status, s.status_active, mtd_status2.mt_details_name AS stud_curriculum_section
                    FROM map_courseto_student m
                    LEFT JOIN su_student_stakeholder_details s ON m.student_id = s.ssd_id
                    LEFT JOIN curriculum c ON m.std_crclm_id = c.crclm_id
                    LEFT JOIN master_type_details mtd ON m.batch_id = mtd.mt_details_id
                    LEFT JOIN master_type_details mtd_status ON m.status = mtd_status.mt_details_id
                    LEFT JOIN master_type_details mtd_status2 ON s.section_id = mtd_status2.mt_details_id
                    LEFT JOIN (SELECT i.section_id, COUNT(mcstd_id) batch_wise_stud_count
                        FROM map_courseto_course_instructor i
                        LEFT JOIN map_courseto_student m ON m.crs_id = {$crs_id} AND i.section_id = m.batch_id
                        AND status IN(SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name NOT IN('Unregistered'))
                        WHERE i.crs_id = {$crs_id}
                        AND i.section_id IN (SELECT mt_details_id FROM master_type_details WHERE (org_type = 'BATCHWISE_SECTION' OR org_type = 'FIRSTYEAR_BATCHWISE_SECTION') AND parent_id = {$section_id})
                        GROUP BY i.section_id) A ON m.batch_id = A.section_id
                    WHERE m.crs_id = {$crs_id} AND m.section_id = {$section_id}
                    GROUP BY m.student_id
                    ORDER BY mtd.mt_details_name, s.student_usn";
            $result = $this->db->query($query);
            $result = $result->result_array();
            return $result;
        }
        return 0;
    }

    /*
     * Function to check batch existed under selected section
     * @param - crs id , section id
     * returns
     */
    public function check_batch_under_section($data = NULL) {
        extract($data);
        $query = "SELECT mtd.mt_details_id
                FROM map_courseto_course_instructor mc
                LEFT JOIN master_type_details mtd ON (mtd.org_type='BATCHWISE_SECTION' OR mtd.org_type='FIRSTYEAR_BATCHWISE_SECTION') AND mc.section_id = mtd.mt_details_id
                WHERE mc.crs_id = {$crs_id} AND mtd.parent_id = {$section_id}";
        $result = $this->db->query($query);
        $result = $result->result_array();

        if(empty($result)){
            return 0;
        }else{
            return 1;
        }
    }

    /* Function is used to delete the selected students 
     * @param- 
     * @return: 
     */
    function delete_students($data = array(),$role_set) {
        extract($data);
        if($role_set == 2){
            $select_query = $this->db->query("SELECT std.student_usn,mcs.crs_id,std.ssd_id 
                                                FROM su_student_stakeholder_details std 
                                                LEFT JOIN map_courseto_student mcs on mcs.student_id = std.ssd_id
                                                WHERE mcs.mcstd_id in(".$delete_values.")");
            $stu_usn_data = $select_query->result_array();

            
           
            foreach($stu_usn_data as $user_row) {
                if($user_row['student_usn'] != '') {
                    $row[] = $user_row['student_usn'];
                    $crs_data[] = $user_row['crs_id'];
                    $std_data[] = $user_row['ssd_id'];
                }
            }
            
            $student_usn = implode("','",$row);
            $crs_unique = array_unique($crs_data);
            $crs_id = implode(",",$crs_unique);
            $std_id = implode(",",$std_data);
            
            
                                            
            $select_qp = $this->db->query("SELECT group_concat(sa.qp_mq_id) as qp_data FROM qp_definition q
                                            LEFT JOIN qp_unit_definition qud ON q.qpd_id =qud.qpd_id
                                            LEFT JOIN qp_mainquestion_definition qmd ON qud.qpd_unitd_id = qmd.qp_unitd_id
                                            LEFT JOIN student_assessment sa on qmd.qp_mq_id = sa.qp_mq_id AND sa.assessment_id IS NOT NULL
                                            where sa.student_usn in ('$student_usn') and q.crs_id = ".$crs_id." ");
             $question_data = $select_qp->result_array();
                $qp_data[] = $question_data[0]['qp_data'];
                $qp_mp_id = implode(",",$qp_data);
                
            if(!empty($qp_data) && $qp_mp_id != ''){
               $delete_marks_data = $this->db->query("DELETE FROM student_assessment WHERE assessment_id IN (
                                                        SELECT B.assessment_id FROM (
                                                            SELECT student_assessment.assessment_id FROM student_assessment
                                                            JOIN (SELECT sa.assessment_id, sa.qp_mq_id, sa.student_usn, q.qp_rollout
                                                                FROM  qp_definition q
                                                                LEFT JOIN qp_unit_definition qud ON q.qpd_id = qud.qpd_id
                                                                LEFT JOIN qp_mainquestion_definition qmd ON qud.qpd_unitd_id = qmd.qp_unitd_id
                                                                LEFT JOIN student_assessment sa ON qmd.qp_mq_id = sa.qp_mq_id AND sa.assessment_id IS NOT NULL
                                                                WHERE sa.student_usn IN ('$student_usn') AND qmd.qp_mq_id IN ($qp_mp_id)
                                                            ) A ON student_assessment.assessment_id = A.assessment_id AND student_assessment.student_usn = A.student_usn
                                                        ) B
                                                    )");
              
            } 

            $survey_select = $this->db->query("SELECT GROUP_CONCAT(survey_id) as survey_id FROM su_survey WHERE crs_id = '$crs_id'");
            $survey_data = $survey_select->result_array();
            $survey_id = $survey_data[0]['survey_id'];
                
            if($survey_id != '') {
                $select_user = $this->db->query("SELECT GROUP_CONCAT(survey_user_id) AS user FROM su_survey_users WHERE stakeholder_detail_id IN ($std_id)");
                $user_data = $select_user->result_array();
                $user_id = $user_data[0]['user'];
                if($user_id != ''){
                    $select_response = $this->db->query("SELECT GROUP_CONCAT(survey_response_id) AS response FROM su_survey_response 
                                                    WHERE survey_user_id IN ($user_id) AND survey_id IN ($survey_id)");
                    $response_data = $select_response->result_array();
                    $response_id = $response_data[0]['response'];
                    if($response_id != ''){
                        $delete_option = $this->db->query("DELETE FROM su_survey_resp_options where survey_response_id IN ($response_id)");        
                        $delete_user_response = $this->db->query("DELETE FROM su_survey_response WHERE survey_response_id IN ($response_id) AND survey_id IN($survey_id)");
                    }
                    $delete_user = $this->db->query("DELETE FROM su_survey_users WHERE survey_user_id IN ($user_id) AND survey_id IN ($survey_id)"); 
                }                                                      
            }
                
            $this->db->query("DELETE FROM map_courseto_student WHERE mcstd_id IN(".$delete_values.")");

            $count_query = $this->db->query("SELECT count(mcstd_id) AS all_std_count 
                                                FROM map_courseto_student WHERE crs_id = '$crs_id' AND mcstd_id IN (".$delete_values.")");
            $count_std_data = $count_query->result_array();
            $count_std_id = $count_std_data[0]['all_std_count'];
            /* Updating the row when all student are get deleted from the particular course */
            if($count_std_id == 0 && $survey_id != ''){               
                $update_query = $this->db->query("UPDATE su_survey SET status = 0 WHERE crs_id = ".$crs_id." AND survey_id in ($survey_id)");
            }
            $query = $this->db->query("SELECT count(mcstd_id) AS all_std FROM map_courseto_student WHERE crs_id =  ".$crs_id."");
            $result = $query->result_array();

            if($result[0]['all_std'] == 0 ){
                $update_tee = $this->db->query("UPDATE qp_definition SET qp_rollout = 1 WHERE crs_id = ".$crs_id." AND qp_rollout >= 1 AND qpd_type = 5;");
            }
                                                
            return 1;
        }
        
    }

    /*
     * Function to fetch batch details
     * @param - crs id , crclm term id
     * returns
     */
    public function fetch_batch($data = NULL) {
        extract($data);
        $query = "SELECT mtd.mt_details_id, mtd.master_type_details_alias_name AS mt_details_name, mtd.parent_id
                FROM map_courseto_course_instructor mc
                LEFT JOIN master_type_details mtd ON (mtd.org_type='BATCHWISE_SECTION' OR mtd.org_type='FIRSTYEAR_BATCHWISE_SECTION')  AND mc.section_id = mtd.mt_details_id
                WHERE mc.crs_id = {$crs_id} -- AND mtd.parent_id = {$section_id}
                GROUP BY mtd.mt_details_id
                ORDER BY mtd.mt_details_id";
        // print_r($query);exit();
        $result = $this->db->query($query);
        $result = $result->result_array();
        return $result;
    }

    /* Function is used to change batch for selected students
     * @param- 
     * @return: 
     */
    function change_batch($data = array()) {
        extract($data);
        $query = "UPDATE map_courseto_student SET section_id = ".$section_id.", batch_id = ".$batch_id." WHERE mcstd_id IN(".$student_records.")";
        $result = $this->db->query($query);

        // if($lms_flag['ionlms_flag'] == 1) {
        //     $query = "UPDATE lms_manage_attendance SET section_id = ".$section_id." WHERE attendance_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_map_assignment_upload SET section_id = ".$section_id." WHERE map_assignment_upload_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_map_crs_material_upload SET section_id = ".$section_id." WHERE crs_material_map_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_lesson_schedule SET section_id = ".$section_id." WHERE lls_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

            
        //     $query = "UPDATE lms_map_share_materials_to_student SET section_id = ".$section_id." WHERE material_student_map_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_quiz_section_mapping SET section_id = ".$section_id." WHERE q_sec_map_id IN(".$student_records.")";
        //     $result = $this->db->query($query);
        // }
        return $result;
    }

    /* Function is used to change student status
     * @param- 
     * @return: 
     */
    function change_student_status($data = array()) {
        extract($data);
        $query = "UPDATE map_courseto_student SET status = (SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name = '".$status."') WHERE mcstd_id = ".$user_mapping_id."";
        $result = $this->db->query($query);
        return $result;
    }

    /*
     * Function to crate file name
     * @param - crs id , section id
     * returns
     */
    public function create_file_name($data = NULL) {
        extract($data);
        $query = "SELECT CONCAT(c.crs_code,'_',mtd.mt_details_name,'_students_to_crs_registration.xls') AS `filename`
                FROM course c
                LEFT JOIN master_type_details mtd ON mtd.mt_details_id = {$section_id}
                WHERE c.crs_id = {$crs_id}";
        $result = $this->db->query($query);
        $result = $result->row_array();
        return $result['filename'];
    }

    /*
     * Function is to store excel imported data to database table
     * @parameters: 
     * @return: flag
     */
	function load_excel_to_temp_table($params = array()) {
        /***** Start Create table Structure *****/
        $this->load->dbforge();
		$parameters = $params;
		extract($params);
        if (!empty($file_header_array) && $crs_id && $section_id) {
            $temp_table_name = "temp_student_crs_".$crs_id.'_'.$section_id;
			$parameters['temp_table_name'] = $temp_table_name;
            $this->dbforge->drop_table($temp_table_name);
            $this->dbforge->add_field('id');
            $this->dbforge->add_field(array(
                'Remarks' => array(
                    'type' => 'text',
                    'null' => TRUE,
                ),
                'duplicate' => array(
                    'type' => 'INT',
                    'constraint' => '11',
                    'default' => '0',
                ),
                'student_usn' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'student_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
				'std_crclm_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
				'batch_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                    'default' => NULL,
                ),
				'crs_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
				'section_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                )
            ));
            
            $this->dbforge->create_table($temp_table_name);

            /***** End Create table Structure **** */

            //upload data into temporary table 
            //while uploading files in Linux machine change "LOAD DATA LOCAL INFILE" TO "LOAD DATA INFILE" (if required)
            $parameters['file'] = './uploads/' . $name;
            $this->read_excel($parameters);
            $this->validate_student_course_temp_data($temp_table_name, $parameters);
            return $temp_table_name;
        }
    }
	
	/*
     * Function is to read the excel file data
     * @parameters: 
     * @return: 
     */
	function read_excel($params = array()) {
		extract($params);
        //read file from path
        $objPHPExcel = PHPExcel_IOFactory::load($file);
        //get only the Cell Collection
        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
        if(!empty($cell_collection)){
            $arr_data = array();
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
            foreach ($arr_data as $d => $val) {
                if (empty($val['A']))
                    $val['A'] = NULL;
                if (empty($val['B']))
                    $val['B'] = '';
                if (empty($val['C']))
                    $val['C'] = NULL;
            
                if ($val['A'] != '' || $val['B'] != '') { //1.Student USN 2.= Student Name 3. Batch
                    $this->store_student_data($val['A'], $val['B'], $val['C'], $params);
                }
            }
        }
    }

    /*
     * Function is to store student data from excel file to database
     * @parameters: 
     * @return: 
     */
	function store_student_data($stud_usn = NULL, $stud_name = NULL, $batch_name = NULL, $params = array()) {
        extract($params);
        $batch_wise_section_flag = $this->check_batch_under_section($params);

        if($batch_wise_section_flag == 1){
            $form_data = array(
                'student_usn' => $stud_usn,
                'student_name' => $stud_name,
                'batch_name' => $batch_name,
                'crs_id' => $crs_id,
                'section_id' => $section_id
            );
        }else{
            $form_data = array(
                'student_usn' => $stud_usn,
                'student_name' => $stud_name,
                'crs_id' => $crs_id,
                'section_id' => $section_id
            );
        }
        
        $this->db->insert($temp_table_name, $form_data);
    }

    /*
     * Function is to validate the bulk import of students registering for the course 
     * @parameters: 
     * @return: 
     */
	public function validate_student_course_temp_data($temp_table_name = '', $parameters) {
		if($temp_table_name == '') {
			echo 0;// Invalid temp table name
		} else {
            extract($parameters);
			$usn_result = $this->db->query("SELECT student_usn FROM $temp_table_name GROUP BY student_usn HAVING COUNT(`student_usn`)>1");
        
            if ($usn_result) {
				$usn_result = $usn_result->result_array();
				foreach ($usn_result as $data) {
					$usn = $data['student_usn'];
                 
					$this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),'Duplicate <b>Student {$this->lang->line('student_usn')}</b> ;') 
                    WHERE `student_usn` ='$usn'");
				}
			}

            if($this->ion_auth->user()->row()->org_name->ionlms_flag) {
                $usnresult = $this->db->query("SELECT student_usn FROM $temp_table_name");
                $usnnresult = $usnresult->result_array();
           
                $usn = array();
                for($i=0; $i<count($usnnresult); $i++) {
                    $select_std_usn_qry = $this->db->query('SELECT ssd_id FROM
                                                                map_courseto_student mcs
                                                                LEFT JOIN su_student_stakeholder_details sssd ON mcs.student_id=sssd.ssd_id
                                                                WHERE sssd.student_usn="'.$usnnresult[$i]['student_usn'].'"');
                    $get_std_usn_qry = $select_std_usn_qry->result_array();
                    if(!empty($get_std_usn_qry)) {
                        $select_reg_std_qry = $this->db->query('SELECT crs_id FROM map_courseto_student WHERE student_id = "'.$get_std_usn_qry[0]['ssd_id'].'"');
                        $get_reg_std_qry = $select_reg_std_qry->result_array();
                            for($j=0; $j<count($get_reg_std_qry); $j++) {
                                $crs [] = $get_reg_std_qry[$j]['crs_id'];
                                $crsid = implode(",", $crs);
                            }
                        
                            $select_crs_credits_qry = $this->db->query('SELECT crs_id,SUM(total_credits) as crs_credits FROM course WHERE crs_id IN ('.$crsid.')');
                            $get_crs_credits_qry = $select_crs_credits_qry->result_array();

                            $select_total_crs_credit = $this->db->query('SELECT total_crs_enroll FROM crclm_terms WHERE crclm_term_id="'.$crclm_term_id.'"');
                            $total_crs_credit = $select_total_crs_credit->row_array();
                            if(isset($get_crs_credits_qry[0]['crs_credits'])) {
                                if(isset($total_crs_credit['total_crs_enroll'])){
                                    if($get_crs_credits_qry[0]['crs_credits'] >= $total_crs_credit['total_crs_enroll']) {
                                        $usn = $usnnresult[$i]['student_usn'];
                                            $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),'<b>Student {$this->lang->line('student_usn')} $usn</b> exceeding the max credit limit for course registration') 
                                            WHERE `student_usn` ='".$usnnresult[$i]['student_usn']."'");
                                        }
                                    }
                            }  
                    } 
                }
            }

            $this->db->query("UPDATE $temp_table_name t
				LEFT JOIN su_student_stakeholder_details su ON t.student_usn = su.student_usn
				SET Remarks = 'Student has been disabled for the Curriculum ;'
				WHERE su.status_active = 0"
			);
            $this->db->query("UPDATE $temp_table_name t
				LEFT JOIN su_student_stakeholder_details su ON t.student_usn = su.student_usn
				SET Remarks = '<b>Student {$this->lang->line('student_usn')}</b> does not exist in any Curriculum ;'
				WHERE su.ssd_id IS NULL"
			);
            $this->db->query("UPDATE $temp_table_name t
				SET t.std_crclm_id = (SELECT c.crclm_id 
                                FROM su_student_stakeholder_details s
                                LEFT JOIN curriculum c ON s.crclm_id = c.crclm_id
                                WHERE s.student_usn = t.student_usn ORDER BY c.first_year_flag ASC LIMIT 1)"
			);
            $this->db->query("UPDATE $temp_table_name SET Remarks='Cannot be blank <b>Student {$this->lang->line('student_usn')}</b> ;' 
                WHERE `student_usn` IS NULL");
            $this->db->query("UPDATE $temp_table_name t
                LEFT JOIN su_student_stakeholder_details s ON t.student_usn = s.student_usn
                LEFT JOIN map_courseto_student mcs ON s.ssd_id = mcs.student_id
                LEFT JOIN master_type_details mtd ON mcs.section_id = mtd.mt_details_id
                SET Remarks = CONCAT('Student already registered in <b>Section ',mtd.mt_details_name,'</b> ;')
                WHERE mcs.crs_id = {$parameters['crs_id']} AND mcs.section_id NOT IN ({$parameters['section_id']})"
            );
			// Batch validation
            $batch_wise_section_flag = $this->check_batch_under_section($parameters);
            if($batch_wise_section_flag == 1){
                $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),'<b>Batch</b> Cannot be blank ;') 
                WHERE `batch_name` IS NULL AND `student_usn` IS NOT NULL ");

                $this->db->query("UPDATE $temp_table_name t
                    LEFT JOIN master_type_details mtd ON (org_type='BATCHWISE_SECTION' OR org_type='FIRSTYEAR_BATCHWISE_SECTION') AND t.section_id = mtd.parent_id AND mtd.master_type_details_alias_name = t.batch_name
                    LEFT JOIN map_courseto_course_instructor map ON t.crs_id = map.crs_id AND mtd.mt_details_id = map.section_id
                    SET Remarks = CONCAT(COALESCE(Remarks,' '),'<b>Batch</b> does not exist ;')
                    WHERE map.mcci_id IS NULL AND t.batch_name IS NOT NULL");
            }
		}
	}

    /*
     * Function is to fetch the data from temp table generated for bulk registration of student to course
     * @parameters: 
     * @return: array of data imported from the file
     */
	public function  get_temp_student_crs_data($params = array()) {
		extract($params);
        $query = 'SELECT t.Remarks, c.crclm_name, t.student_usn, CONCAT(ssd.title, " ", ssd.first_name, " ", COALESCE(ssd.last_name, "")) stud_name, batch_name 
            FROM '.$table_name.' t
            LEFT JOIN su_student_stakeholder_details ssd ON t.student_usn = ssd.student_usn
            LEFT JOIN curriculum c ON t.std_crclm_id = c.crclm_id
            GROUP BY t.id';
		$result = $this->db->query($query);

        return $result->result_array();
    }

    /**
     * Function is to discard temporary table
     * @parameters: course id, section id
     * @return: boolean value
     */
    public function drop_temp_table($crs_id, $section_id) {
        $this->load->dbforge();
        $temp_table_name = "temp_student_crs_".$crs_id.'_'.$section_id;

        $result = $this->db->query("DROP TABLE IF EXISTS " . $temp_table_name . "");

        return $result;
    }

    /*
     * Function is to register the student to course 
     * @parameters: 
     * @return: 
     */
	public function import_student_course_register($params = array()) {
        extract($params);
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        $batch_wise_section_flag = $this->check_batch_under_section($params);
        $temp_table_name = "temp_student_crs_".$crs_id.'_'.$section_id;

		$query = 'SELECT IF(COUNT(Remarks), 0, 1) AS "upload_status"
					FROM '.$temp_table_name.' t
					WHERE t.Remarks IS NOT NULL';
		$result = $this->db->query($query);
		$upload_valid_flag = $result->row_array();
		if($upload_valid_flag['upload_status']) {
			$this->db->trans_begin();
            if($batch_wise_section_flag == 1){
                $update_query = 'UPDATE '.$temp_table_name.' t SET  t.batch_name = 
                                    ( SELECT m.mt_details_id 
                                        FROM master_type_details m  
                                        WHERE m.master_type_id = 34 AND (m.org_type = "BATCHWISE_SECTION" OR m.org_type="FIRSTYEAR_BATCHWISE_SECTION") AND m.parent_id = '.$section_id.' 
                                        AND m.master_type_details_alias_name = t.batch_name        
                                    )';
                $this->db->query($update_query);

                $query = 'INSERT INTO map_courseto_student
						(crclm_id, crclm_term_id, crs_id, section_id, batch_id, student_id, std_crclm_id, status, created_by, created_date)
						SELECT '.$crclm_id.', '.$crclm_trm_id.', t.crs_id, t.section_id, t.batch_name, ssd.ssd_id, t.std_crclm_id, 
                        (SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name = "Registered"), 
                        '.$loggedin_user_id.', NOW()
						FROM '.$temp_table_name.' t
						LEFT JOIN su_student_stakeholder_details ssd ON t.student_usn = ssd.student_usn
                        LEFT JOIN map_courseto_student mcs ON (ssd.ssd_id = mcs.student_id AND t.crs_id = mcs.crs_id)
                       	WHERE mcs.mcstd_id IS NULL
                        GROUP BY t.id';
                $data_updated = $this->db->query($query);
                $this->db->trans_commit();
                
                $update_query = 'UPDATE map_courseto_student m
                            JOIN su_student_stakeholder_details s ON m.student_id = s.ssd_id
                            JOIN '.$temp_table_name.' t ON s.student_usn = t.student_usn 
                            SET m.batch_id = t.batch_name, 
                            m.status = (SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name = "Registered")
                            WHERE m.crs_id = "'.$crs_id.'" AND m.section_id = "'.$section_id.'"';
                $this->db->query($update_query);
            }else{
                $query = 'INSERT INTO map_courseto_student
						(crclm_id, crclm_term_id, crs_id, section_id, student_id, std_crclm_id, status, created_by, created_date)
						SELECT '.$crclm_id.', '.$crclm_trm_id.', t.crs_id, t.section_id, ssd.ssd_id, t.std_crclm_id, 
                        (SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name = "Registered"), 
                        '.$loggedin_user_id.', NOW()
						FROM '.$temp_table_name.' t
						LEFT JOIN su_student_stakeholder_details ssd ON t.student_usn = ssd.student_usn
                        LEFT JOIN map_courseto_student mcs ON (ssd.ssd_id = mcs.student_id AND t.crs_id = mcs.crs_id)
                       	WHERE mcs.mcstd_id IS NULL
                        GROUP BY t.id';
                $data_updated = $this->db->query($query);
                $this->db->trans_commit();
                
                $update_query = 'UPDATE map_courseto_student m
                            JOIN su_student_stakeholder_details s ON m.student_id = s.ssd_id
                            JOIN '.$temp_table_name.' t ON s.student_usn = t.student_usn 
                            SET m.status = (SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name = "Registered")
                            WHERE m.crs_id = "'.$crs_id.'" AND m.section_id = "'.$section_id.'"';
                $this->db->query($update_query);
            }
            
            return $data_updated;
            
		} else {
			return $upload_valid_flag['upload_status'];
		}
    }

    /**
     * Function is to check Remarks exist or not
     * @parameters: 
     * @return: 
     */
    public function check_remarks_exists($params = array()) {
        extract($params);
        $temp_table_name = "temp_student_crs_".$crs_id.'_'.$section_id;
        $temp_remarks_query = 'SELECT Remarks
                               FROM ' . $temp_table_name . '
                               WHERE Remarks IS NOT NULL';
        $temp_remarks_data = $this->db->query($temp_remarks_query);
        $temp_remarks = $temp_remarks_data->result_array();

        if (!empty($temp_remarks)) {
            return 0;
        }
        return 1;
    }

    /*
     * Function is to fetch the Student details by Curriclum ID & Section ID
     * @parameters: Curriclum ID & Section ID
     * @return: array of student data
     */
	public function  get_students_by_crclm($params = array()) {
		extract($params);
        $query = 'SELECT ssd.ssd_id, ssd.student_usn, CONCAT(ssd.title, " ", ssd.first_name, " ", COALESCE(ssd.last_name, "")) student_name, ssd.department_acronym, mcs.student_id
                    FROM su_student_stakeholder_details ssd
                    LEFT JOIN map_courseto_student mcs ON ssd.ssd_id = mcs.student_id AND mcs.crs_id= "'.$crs_id.'"
                    WHERE ssd.crclm_id = "'.$crclm_id.'" AND ssd.section_id = "'.$section_id.'" AND ssd.status_active=1';
        
		$result = $this->db->query($query);
        $data['stud_list'] = $result->result_array();

        $query = 'SELECT COUNT(ssd_id) stud_count FROM su_student_stakeholder_details WHERE crclm_id = "'.$crclm_id.'" AND section_id = "'.$section_id.'" AND status_active=1';
        $query_data = $this->db->query($query);
        $result = $query_data->row_array();
        $data['stud_count'] = $result['stud_count'];

        return $data;
    }

    /*
     * Function is to register the student to course 
     * @parameters: 
     * @return: 
     */
	public function register_selected_students($params = array()) {
        extract($params);
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        $batch_wise_section_flag = $this->check_batch_under_section($params);
        $data =array();
        $query = 'SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name = "Registered"';
        $query_data = $this->db->query($query);
        $stud_status = $query_data->row_array();
        if($batch_wise_section_flag == 1){
            $query = "SELECT mtd.mt_details_id
                FROM map_courseto_course_instructor mc
                LEFT JOIN master_type_details mtd ON (mtd.org_type='BATCHWISE_SECTION' OR mtd.org_type='FIRSTYEAR_BATCHWISE_SECTION') AND mc.section_id = mtd.mt_details_id
                WHERE mc.crs_id = {$crs_id} AND mtd.parent_id = {$section_id} LIMIT 1";
            $query_data = $this->db->query($query);
            $batch = $query_data->row_array();
            for($i=0; $i<count($selectedStudent); $i++) {
                $data[$i] = array(
                'crclm_id' => $crclm_id,
                'crclm_term_id' => $crclm_trm_id,
                'crs_id' => $crs_id,
                'section_id' => $section_id,
                'batch_id' => $batch['mt_details_id'],
                'student_id' => $selectedStudent[$i],
                'std_crclm_id' => $std_crclm_id,
                'status' => $stud_status['mt_details_id'],
                'created_by' => $loggedin_user_id,
                'created_date' => date('Y-m-d H:m:s')
                );
            }
        }else{
            for($i=0; $i<count($selectedStudent); $i++) {
                $data[$i] = array(
                'crclm_id' => $crclm_id,
                'crclm_term_id' => $crclm_trm_id,
                'crs_id' => $crs_id,
                'section_id' => $section_id,
                'student_id' => $selectedStudent[$i],
                'std_crclm_id' => $std_crclm_id,
                'status' => $stud_status['mt_details_id'],
                'created_by' => $loggedin_user_id,
                'created_date' => date('Y-m-d H:m:s')
                );
            }
        }

        $this->db->insert_batch('map_courseto_student', $data);
        
        return true;
    }

    public function check_stud_to_crs_reg_set_or_not($crs_id) {
        $query = $this->db->query("SELECT 
                                    edu_sys_flag AS flag
                                    FROM course WHERE crs_id= '" . $crs_id . "'");
        $crs_flag_data = $query->result_array();
        
    return $crs_flag_data[0]["flag"];
    }

    public function fetch_crclm_edu_sys_flag($crclm_id) {
        $query = $this->db->query('SELECT
                                    edu_sys_flag FROM curriculum
                                    WHERE crclm_id="' . $crclm_id . '"');
        $flag_data = $query->result_array();

    return $flag_data[0]["edu_sys_flag"];
    }

    public function check_marks_uploaded_or_not($crs_id) {
        $marks_query = $this->db->query('SELECT 
        qpd_id FROM qp_definition
        WHERE crs_id="' . $crs_id . '" and qp_rollout=2 and cia_model_qp=0');
        $marks_data = $marks_query->num_rows();
    
    return $marks_data;

}

    /* Function is used to fetch the course details from course table
     * @param- crs id.
     * returns - an array of edu_sys_flag main column.
     */    
    
    public function get_course_edu_sys_flag($form_data)
    {
        return $this->db->select('crs.edu_sys_flag,crs.crs_id,crs.crclm_id,crs.crclm_term_id,crs.crs_title')
                ->from('course as crs')
                ->where('crs.crs_id',$form_data->crs_id,'crs.crclm_id',$form_data->crclm_id,'crs.crclm_term_id',$form_data->term_id)
                ->join('curriculum as crclm','crs.crclm_id = crclm.crclm_id','LEFT')
                ->get()->result_array();
    }    

     /*Function to drop temp table created while bulk import of clo
     * 
     * @parameters: - crclm id,term id
     */
    public function drop_co_temp_table($crclm_id = NULL,$term_id = NULL) {
        if ($term_id != NULL) {
            $temp_table_name = "temp_clo_".$term_id; //temp table name
            $this->load->dbforge();
            $this->db->query("DROP TABLE IF EXISTS " . $temp_table_name . "");
            return '1';
        } else {
            return '0';
        }
    }

    /*Function to load the data from the bulk import template into the  temporary table
     * @parameters: -
     */
    function co_load_excel_to_temp_table($filename, $name, $file_header_array, $term_id, $crclm_id) {
        /** *** Start Create table Structure **** */
        $this->load->dbforge();
        if (!empty($file_header_array)) {
            $temp_table_name = "temp_clo_" . $term_id;
            $this->dbforge->drop_table('temp_clo_' . $term_id . '');
            $this->dbforge->add_field(array(
                'Remarks' => array(
                    'type' => 'text',
                    'null' => TRUE,
                ),
                'duplicate' => array(
                    'type' => 'INT',
                    'constraint' => '11',
                    'default' => '0',
                ),
                'lg_crs_code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'clo_code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'clo_statement' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'lg_blooms_level1' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'lg_blooms_level2' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'delivery_method' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'po_map1' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'po_map2' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'pso_map1' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                ),
                'pso_map2' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '200',
                    'null' => TRUE,
                )
            ));
            $this->dbforge->create_table('temp_clo_' . $term_id . '');

            /** *** End Create table Structure **** */

            //upload data into temporary table 
            //while uploading files in Linux machine change "LOAD DATA LOCAL INFILE" TO "LOAD DATA INFILE" (if required)
            $path = './uploads/' . $name;
            $this->co_read_excel($path, $term_id); // To read and insert the file data into temp table
            $this->co_validate_data('temp_clo_'.$term_id, $term_id,$crclm_id);
            return $temp_table_name;
        }
    }

    function co_read_excel($file, $term_id) {
        //load the excel library
        $this->load->library('excel');

        //read file from path
        $objPHPExcel = PHPExcel_IOFactory::load($file);

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
        if(isset($arr_data)) {
        foreach ($arr_data as $d => $val) {
            if (empty($val['A']))
                $val['A'] = NULL;
            if (empty($val['B']))
                $val['B'] = NULL;
            if (empty($val['C']))
                $val['C'] = NULL;
            if (empty($val['D']))
                $val['D'] = NULL;
            if (empty($val['E']))
                $val['E'] = NULL;
            if (empty($val['F']))
                $val['F'] = NULL;
            if (empty($val['G']))
                $val['G'] = NULL;
            if (empty($val['H']))
                $val['H'] = NULL;
            if (empty($val['I']))
                $val['I'] = NULL;
            if (empty($val['J']))
                $val['J'] = NULL;
                  
            if ($val['A'] != '' && $val['B'] != '' || $val['C'] != '' || $val['D'] || $val['E'] || $val['F'] || $val['G'] || $val['H'] || $val['I'] || $val['J']) { 
                $this->store_clo_data($val['A'], $val['B'], $val['C'], $val['D'] ,$val['E'],$val['F'],$val['G'],$val['H'],$val['I'],$val['J'],$term_id);
            }
        }
    }
    }

    	/*Function to store clo data in temp table
     * @parameters: - 
     */
    function store_clo_data($lg_crs_code,$clo_code = NULL, $clo_statement = NULL,$lg_blooms_level1,$lg_blooms_level2,$delivery_method,$po_map1,$po_map2,$pso_map1,$pso_map2,$term_id) {
        if($term_id != '') {

            $form_data = array(
                'lg_crs_code' => $lg_crs_code,
                'clo_code' => $clo_code,
                'clo_statement' => $clo_statement,
                'lg_blooms_level1' => $lg_blooms_level1,
                'lg_blooms_level2' => $lg_blooms_level2,
                'delivery_method' => $delivery_method,
                'po_map1' => $po_map1,
                'po_map2' => $po_map2,
                'pso_map1' => $pso_map1,
                'pso_map2' => $pso_map2,
            );
        
            $this->db->insert('temp_clo_' . $term_id . '', $form_data);
        }
    
    }

    /*Function to validate the clo data from the temporary table before inserting into database table book_det
     * @parameters: - temporary table name, department id
     */
    function co_validate_data($temp_table_name, $term_id,$crclm_id) {
		$length_clo_code = 4;
		$length_clo_statement = 2000;
        $duplicate_co = $this->db->query("SELECT  lg_crs_code,COUNT(clo_statement and lg_crs_code) as count,clo_statement,clo_code
                                            FROM $temp_table_name
                                            GROUP BY clo_statement,lg_crs_code
                                            HAVING COUNT(clo_statement and lg_crs_code) > 1");
        $duplicate_co_data = $duplicate_co->result_array();
        foreach($duplicate_co_data as $lg_crs_code) {
            $crs_code = $lg_crs_code['lg_crs_code'];
            $clo_code = $lg_crs_code['clo_code'];
            $clo_statement = $lg_crs_code['clo_statement'];
            if($duplicate_co_data["0"]['count'] >= 2) {
                $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),'CO Statement already exists. <b data-key=lg_crs_code></b>') WHERE `lg_crs_code` = '$crs_code'
                AND `clo_statement` = '$clo_statement'
                ");
            }
        }

        

        $duplicate_code = $this->db->query("SELECT  lg_crs_code,COUNT(clo_code and lg_crs_code) as count,clo_statement,clo_code
                                            FROM $temp_table_name
                                            GROUP BY clo_code,lg_crs_code
                                            HAVING COUNT(clo_code and lg_crs_code) > 1");
        $duplicate_code_data = $duplicate_code->result_array();
        foreach($duplicate_code_data as $crs_code_data) {
            $crscode = $crs_code_data['lg_crs_code'];
            $clocode = $crs_code_data['clo_code'];
            $clostatement = $crs_code_data['clo_statement'];
            if($duplicate_code_data["0"]['count'] >= 2){
                $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),'Same CO Code. <b data-key=lg_crs_code></b>') WHERE `lg_crs_code` = '$crscode' 
                AND `clo_code` = '$clocode'");
            }
        }
        
        $clo_statement_validation = $this->db->query("SELECT  distinct lg_crs_code,clo_statement,clo_code
                                                        FROM $temp_table_name
                                                        ");
        $clo_statement_validation_data = $clo_statement_validation->result_array();
       
        foreach($clo_statement_validation_data as $clo_statementvalidation_data) {
            $clo_crscode = $clo_statementvalidation_data['lg_crs_code'];
            $clo_code = $clo_statementvalidation_data['clo_code'];
            $clo_statement = $clo_statementvalidation_data['clo_statement'];
             if ((!strpos(trim($clo_statementvalidation_data['clo_statement']), ' '))  && (strpos(trim($clo_statementvalidation_data['clo_statement']), ',') == false) ) {
                $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),'CO should be more than one word. <b data-key=lg_crs_code></b><br>') WHERE `clo_statement` = '$clo_statement' AND `lg_crs_code` = '$clo_crscode'
                                AND `clo_code` = '$clo_code'");
            }

        }

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Inavlid Course Code.<br>') 
                            WHERE lg_crs_code NOT IN(SELECT crs_code 
                                                        FROM course 
                                                        WHERE crclm_term_id = ".$term_id.")");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid PO Code.<br>') 
                            WHERE po_map1 NOT IN(SELECT po_reference
                                                        FROM po
                                                        WHERE crclm_id = ".$crclm_id."
                                                        ORDER BY po_id ASC) AND '-'");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid PO Code.<br>') 
                            WHERE po_map2 NOT IN(SELECT po_reference
                                                        FROM po
                                                        WHERE crclm_id = ".$crclm_id."
                                                        ORDER BY po_id ASC)AND '-'");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid PO Code.<br>') 
                            WHERE pso_map1 NOT IN(SELECT po_reference
                                                        FROM po
                                                        WHERE crclm_id = ".$crclm_id."
                                                        ORDER BY po_id ASC)AND '-'");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid PO Code.<br>') 
                            WHERE pso_map2 NOT IN(SELECT po_reference
                                                        FROM po
                                                        WHERE crclm_id = ".$crclm_id."
                                                        ORDER BY po_id ASC)AND '-'");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid CO Code.<br>') 
                            WHERE clo_code NOT IN( SELECT mt_details_name AS co_code
					FROM master_type_details)");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid Bloom Level1.<br>')
                            WHERE temp.lg_blooms_level1 NOT IN(SELECT b.level
                            FROM bloom_level b)AND '-'");

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid Bloom Level2.<br>')
                            WHERE temp.lg_blooms_level2 NOT IN(SELECT b.level
                            FROM bloom_level b)AND '-'");
        

        $this->db->query("UPDATE $temp_table_name temp SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),'Invalid Delivery method.<br>')
                            WHERE temp.delivery_method NOT IN(SELECT  mcd.delivery_mtd_name
                            FROM map_crclm_deliverymethod mcd)");
                            
        $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),' <b data-key=lg_crs_code></b> Cannot be blank.') WHERE `lg_crs_code` IS NULL OR `lg_crs_code` = '' ");

        $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),' <b data-key=clo_code></b> Cannot be blank.') WHERE `clo_code` IS NULL OR `clo_code` = '' ");

        $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),' <b data-key=clo_statement>Course Outcome</b> Cannot be blank.') WHERE `clo_statement` IS NULL OR `clo_statement` = '' ");

        $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),' This field can only contain maximum of ".$length_clo_code." characters.') WHERE LENGTH(`clo_code`) > ".$length_clo_code);

        $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),' This field can only contain maximum of ".$length_clo_statement." characters. <b data-key=clo_statement>Course Outcome</b>') WHERE LENGTH(`clo_statement`) > ".$length_clo_statement);
        
        $this->db->query("UPDATE $temp_table_name SET Remarks=CONCAT(COALESCE(Remarks,' '),' Atleast one PO mapping should be done.') WHERE (`po_map1` IS NULL OR `po_map1` = '' OR `po_map1` = '-') AND (`po_map2` IS NULL OR `po_map2` = '' OR `po_map2` = '-') AND
                (`pso_map1` IS NULL OR `pso_map1` = '' OR `pso_map1` = '-') AND (`pso_map2` IS NULL OR `pso_map2` = '' OR `pso_map2` = '-') ");
                      
        $this->db->query("UPDATE $temp_table_name 
                            LEFT JOIN clo c ON $temp_table_name.clo_statement = c.clo_statement
                            SET $temp_table_name.remarks=CONCAT(COALESCE($temp_table_name.remarks,' '),'CO Statement already exists for this course.<br>') 
                            WHERE c.clo_id IS NOT NULL AND c.crs_id=(SELECT c.crs_id
                            FROM course c
                            WHERE $temp_table_name.lg_crs_code = c.crs_code AND c.crclm_term_id = ".$term_id.")");



        $this->db->query("UPDATE $temp_table_name 
                            LEFT JOIN clo c ON $temp_table_name.clo_code = c.clo_code
                            SET $temp_table_name.remarks=CONCAT(COALESCE($temp_table_name.remarks,' '),'CO Code already exists for this course.<br>') 
                            WHERE c.clo_id IS NOT NULL AND c.crs_id=(SELECT c.crs_id
                            FROM course c
                            WHERE $temp_table_name.lg_crs_code = c.crs_code AND c.crclm_term_id=".$term_id.")");
                            

        $this->db->query("UPDATE $temp_table_name 
                            LEFT JOIN clo c ON $temp_table_name.lg_blooms_level2 = $temp_table_name.lg_blooms_level1
                            AND $temp_table_name.lg_blooms_level1!='-' AND $temp_table_name.lg_blooms_level2!='-' 
                            SET $temp_table_name.remarks=CONCAT(COALESCE($temp_table_name.remarks,' '),'Bloom level1 and Bloom level2 are same.<br>') 
                            WHERE c.clo_id IS NOT NULL");

                            
        $this->db->query("UPDATE $temp_table_name  temp
                            LEFT JOIN course c ON c.crs_code = temp.lg_crs_code
                            SET temp.remarks=CONCAT(COALESCE(temp.remarks,' '),
                            'Blooms level cannot be blank as it is mandatory for this course.')
                            WHERE ((`lg_blooms_level1` IS NULL OR `lg_blooms_level1` = '' OR `lg_blooms_level1` = '-') AND (`lg_blooms_level2` IS NULL OR `lg_blooms_level2` = '' OR `lg_blooms_level2` = '-'))
                            AND c.crs_id=(SELECT c.crs_id 
                                            FROM course c WHERE temp.lg_crs_code = c.crs_code AND c.clo_bl_flag=1
                                            AND c.crclm_term_id=".$term_id.")");
    
    }

    /*Function to get all records from temporary table uploaded with clo deatils from bulk import
     * @parameters: - temporary table name
     */
    public function get_temp_clo_data($table_name) {
        $result = $this->db->get($table_name);
        return $result->result_array();
    }

    /*Function to get all records from temporary table uploaded with clo deatils from bulk 
     * @parameters: - temporary table name
     */
    public function co_insert_to_main_table($crclm_id,$term_id) {
        $invalid_data = '';
        $temp_table_name = "temp_clo_".$term_id; //temporary table name
        //check if temporary table exists
        $temp = $this->db->query("SHOW TABLES LIKE '$temp_table_name'");
        $temp_remarks = $temp->result_array();
        //if temporary table does not exist return
        if (empty($temp_remarks)) {
            return '2';
        }

        $temp_remarks_query = 'SELECT Remarks FROM ' . $temp_table_name . ' WHERE Remarks IS NOT NULL';
        $temp_remarks_data = $this->db->query($temp_remarks_query);
        $temp_remarks = $temp_remarks_data->result_array();
        if (!empty($temp_remarks)) {
            return '0';
        }

        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        $query = $this->db->query('SELECT
                                    oe_pi_flag 
                                    FROM curriculum
                                    WHERE crclm_id = "' . $crclm_id . '"');
        $oepi_flag_data = $query->result_array();

        if($oepi_flag_data[0]["oe_pi_flag"] == 1) {
            $oepi_clo_result1 = $this->db->query("SELECT m.msr_id,pin.pi_id,p.po_id,c.crs_id ,b.bld_id,b.bloom_id,
                                                    temp.clo_code,temp.clo_statement,mcd.crclm_dm_id
                                                    FROM course c
                                                    LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                    LEFT JOIN bloom_level b ON temp.lg_blooms_level1 = b.level
                                                    LEFT JOIN map_crclm_deliverymethod mcd ON mcd.delivery_mtd_name = temp.delivery_method
                                                    AND c.crclm_id = mcd.crclm_id
                                                    LEFT JOIN po p ON p.po_reference = temp.po_map1 AND c.crclm_id = p.crclm_id
                                                    LEFT JOIN performance_indicator pin ON pin.po_id = p.po_id
                                                    LEFT JOIN measures m ON m.pi_id = pin.pi_id
                                                    WHERE temp.duplicate = 0
                                                    AND c.crclm_id = ".$crclm_id."
                                                    AND c.crclm_term_id = ".$term_id." 
                                                    GROUP BY lg_crs_code,clo_statement;");
            $oepi_result1 = $oepi_clo_result1->result_array();

            $oepi_clo_result2 = $this->db->query("SELECT m.msr_id,pin.pi_id,p.po_id,c.crs_id ,b.bld_id,b.bloom_id,
                                                    temp.clo_code,temp.clo_statement
                                                    FROM course c
                                                    LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                    LEFT JOIN bloom_level b ON  temp.lg_blooms_level2 = b.level
                                                    LEFT JOIN po p ON p.po_reference = temp.po_map2 AND c.crclm_id = p.crclm_id
                                                    LEFT JOIN performance_indicator pin ON pin.po_id = p.po_id
                                                    LEFT JOIN measures m ON m.pi_id = pin.pi_id
                                                    WHERE temp.duplicate = 0
                                                    AND c.crclm_id=".$crclm_id."
                                                    AND c.crclm_term_id = ".$term_id."  
                                                    GROUP BY lg_crs_code,clo_statement ");
            $oepi_result2 = $oepi_clo_result2->result_array();


            $oepi_clo_result3 = $this->db->query("SELECT m.msr_id,pin.pi_id,p.po_id,c.crs_id ,b.bld_id,b.bloom_id,
                                                    temp.clo_code,temp.clo_statement
                                                    FROM course c
                                                    LEFT JOIN $temp_table_name temp ON temp.lg_crs_code=c.crs_code
                                                    LEFT JOIN bloom_level b ON  temp.lg_blooms_level2 = b.level
                                                    LEFT JOIN po p on p.po_reference = temp.pso_map1 AND c.crclm_id = p.crclm_id
                                                    LEFT JOIN performance_indicator pin ON pin.po_id = p.po_id
                                                    LEFT JOIN measures m ON m.pi_id = pin.pi_id
                                                    WHERE temp.duplicate = 0
                                                    AND c.crclm_id = ".$crclm_id."
                                                    AND c.crclm_term_id = ".$term_id."  
                                                    GROUP BY lg_crs_code,clo_statement ");
            $oepi_result3 = $oepi_clo_result3->result_array();

            $oepi_clo_result4 = $this->db->query("SELECT m.msr_id,pin.pi_id,p.po_id,c.crs_id ,b.bld_id,b.bloom_id,
                                                    temp.clo_code,temp.clo_statement
                                                    FROM course c
                                                    LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                    LEFT JOIN bloom_level b ON  temp.lg_blooms_level2 = b.level
                                                    LEFT JOIN po p on p.po_reference=temp.pso_map2 AND c.crclm_id = p.crclm_id
                                                    LEFT JOIN performance_indicator pin ON pin.po_id = p.po_id
                                                    LEFT JOIN measures m ON m.pi_id = pin.pi_id
                                                    WHERE temp.duplicate = 0
                                                    AND c.crclm_id = ".$crclm_id."
                                                    AND c.crclm_term_id = ".$term_id."  
                                                    GROUP BY lg_crs_code,clo_statement");
            $oepi_result4 = $oepi_clo_result4->result_array();

            foreach ($oepi_result1 as $oepi_clo_data1) {
                if ((strpos(trim($oepi_clo_data1['clo_statement']), ' ')) || (strpos(trim($oepi_clo_data1['clo_statement']), ',') !== false)) {
            
                    $oepi_additional_data = array(
                        'clo_code' => $oepi_clo_data1['clo_code'],
                        'clo_statement' => trim($oepi_clo_data1['clo_statement']),
                        'crclm_id' => $crclm_id,
                        'term_id' => $term_id,
                        'crs_id' => $oepi_clo_data1['crs_id'],
                        'created_by' => $loggedin_user_id,
                        'create_date' => date("Y-m-d")
                    );
                    $this->db->insert("clo", $oepi_additional_data);
                    $clo_id = $this->db->insert_id();
                    if($oepi_clo_data1['bld_id']!=null && $oepi_clo_data1['bloom_id']!=null) {
                        $oepi_clo_bloom_level_array = array(
                            'clo_id' => $clo_id,
                            'bld_id' => $oepi_clo_data1['bld_id'],
                            'bloom_id' => $oepi_clo_data1['bloom_id'],
                            'created_by' => $loggedin_user_id,
                            'created_date' => date("Y-m-d")
                        );
                        $this->db->insert('map_clo_bloom_level', $oepi_clo_bloom_level_array);
                    }
                    if($oepi_clo_data1['crclm_dm_id']!=null) {
                        $oepi_clo_delivery_method_array = array(
                            'clo_id' => $clo_id,
                            'delivery_method_id' => $oepi_clo_data1['crclm_dm_id'],
                            'created_by' => $loggedin_user_id,
                            'created_date' => date("Y-m-d")
                        );
                        $this->db->insert('map_clo_delivery_method', $oepi_clo_delivery_method_array);
                    }
                    if($oepi_clo_data1['po_id']!=null) {
                        $oepi_add_clo_po_map1 = array(
                            'clo_id' => $clo_id,
                            'po_id' => $oepi_clo_data1['po_id'],
                            'crclm_id' => $crclm_id,
                            'crs_id' => $oepi_clo_data1['crs_id'],
                            'pi_id' => $oepi_clo_data1['pi_id'],
                            'msr_id' => $oepi_clo_data1['msr_id'],
                            'map_level' => 3,
                            'created_by' => $this->ion_auth->user()->row()->id,
                            'create_date' => date('Y-m-d')
                        );
                        $this->db->insert('clo_po_map', $oepi_add_clo_po_map1);
                    }

                    foreach ($oepi_result2 as $oepi_clo_data2) {
                        if($oepi_clo_data1['crs_id'] == $oepi_clo_data2['crs_id'] ) {
                            if($oepi_clo_data2['bld_id']!=null && $oepi_clo_data2['bloom_id']!=null) {
                                if($oepi_clo_data1['clo_code'] == $oepi_clo_data2['clo_code'] && $oepi_clo_data1['clo_statement'] == $oepi_clo_data2['clo_statement']) {
                                    $clo_bloom_level_array1 = array(
                                        'clo_id' => $clo_id,
                                        'bld_id' => $oepi_clo_data2['bld_id'],
                                        'bloom_id' => $oepi_clo_data2['bloom_id'],
                                        'created_by' => $loggedin_user_id,
                                        'created_date' => date("Y-m-d")
                                    );
                                    $this->db->insert('map_clo_bloom_level', $clo_bloom_level_array1);
                                }
                            }
                            if($oepi_clo_data2['po_id']!=null) {
                                if($oepi_clo_data1['clo_code'] == $oepi_clo_data2['clo_code'] && $oepi_clo_data1['clo_statement'] == $oepi_clo_data2['clo_statement']) {
                                    $oepi_add_clo_po_map2 = array(
                                        'clo_id' => $clo_id,
                                        'po_id' => $oepi_clo_data2['po_id'],
                                        'crclm_id' => $crclm_id,
                                        'pi_id' => $oepi_clo_data2['pi_id'],
                                        'msr_id' => $oepi_clo_data2['msr_id'],
                                        'crs_id' => $oepi_clo_data2['crs_id'],
                                        'map_level' => 3,
                                        'created_by' => $this->ion_auth->user()->row()->id,
                                        'create_date' => date('Y-m-d')
                                    );
                                    $this->db->insert('clo_po_map', $oepi_add_clo_po_map2);
                                }
                            }
                        
                        }
                    }

                    foreach ($oepi_result3 as $oepi_clo_data3) {
                        if($oepi_clo_data1['crs_id'] == $oepi_clo_data3['crs_id'] ) {
                            if($oepi_clo_data3['po_id']!=null) {
                                if($oepi_clo_data1['clo_code'] == $oepi_clo_data3['clo_code'] && $oepi_clo_data1['clo_statement'] == $oepi_clo_data3['clo_statement']) {
                                    $oepi_add_clo_po_map3 = array(
                                        'clo_id' => $clo_id,
                                        'po_id' => $oepi_clo_data3['po_id'],
                                        'crclm_id' => $crclm_id,
                                        'crs_id' => $oepi_clo_data3['crs_id'],
                                        'pi_id' => $oepi_clo_data3['pi_id'],
                                        'msr_id' => $oepi_clo_data3['msr_id'],
                                        'map_level' => 3,
                                        'created_by' => $this->ion_auth->user()->row()->id,
                                        'create_date' => date('Y-m-d')
                                    );
                                    $this->db->insert('clo_po_map', $oepi_add_clo_po_map3);
                                }
                            }
                        }
                    }  

                    foreach ($oepi_result4 as $oepi_clo_data4) {
                        if($oepi_clo_data1['crs_id'] == $oepi_clo_data4['crs_id'] ) {
                            if($oepi_clo_data4['po_id']!=null) {
                                if($oepi_clo_data1['clo_code'] == $oepi_clo_data4['clo_code'] && $oepi_clo_data1['clo_statement'] == $oepi_clo_data4['clo_statement']) {
                                    $oepi_add_clo_po_map4 = array(
                                        'clo_id' => $clo_id,
                                        'po_id' => $oepi_clo_data4['po_id'],
                                        'crclm_id' => $crclm_id,
                                        'crs_id' => $oepi_clo_data4['crs_id'],
                                        'pi_id' => $oepi_clo_data4['pi_id'],
                                        'msr_id' => $oepi_clo_data4['msr_id'],
                                        'map_level' => 3,
                                        'created_by' => $this->ion_auth->user()->row()->id,
                                        'create_date' => date('Y-m-d')
                                    );
                                    $this->db->insert('clo_po_map', $oepi_add_clo_po_map4);
                                }
                            }  
                        }
                    }  

        
                } else {
                    $invalid_data = "duplicate_email";
                }   
            }

        } else {
        
            $clo_result1 = $this->db->query("SELECT p.po_id,c.crs_id ,b.bld_id,b.bloom_id,temp.clo_code,temp.clo_statement,mcd.crclm_dm_id,c.crs_code
                                                FROM course c 
                                                LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                LEFT JOIN bloom_level b ON temp.lg_blooms_level1 = b.level 
                                                LEFT JOIN map_crclm_deliverymethod mcd ON mcd.delivery_mtd_name = temp.delivery_method
                                                AND c.crclm_id = mcd.crclm_id
                                                LEFT JOIN po p ON p.po_reference = temp.po_map1
                                                AND c.crclm_id = p.crclm_id
                                                WHERE temp.duplicate = 0 
                                                AND c.crclm_id = ".$crclm_id."
                                                AND c.crclm_term_id = ".$term_id."");
            $result1 = $clo_result1->result_array();

            $clo_result2 = $this->db->query("SELECT p.po_id,c.crs_id ,b.bld_id,b.bloom_id,temp.clo_code,temp.clo_statement
                                                FROM course c
                                                LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                LEFT JOIN bloom_level b ON  temp.lg_blooms_level2 = b.level
                                                LEFT JOIN po p ON p.po_reference = temp.po_map2
                                                AND c.crclm_id = p.crclm_id
                                                LEFT JOIN clo cl ON cl.clo_code = temp.clo_code and cl.clo_statement = temp.clo_statement
                                                AND c.crclm_id = cl.crclm_id
                                                WHERE temp.duplicate = 0
                                                AND c.crclm_id = ".$crclm_id." 
                                                AND c.crclm_term_id = ".$term_id."");
            $result2 = $clo_result2->result_array();


            $clo_result3 = $this->db->query("SELECT p.po_id,c.crs_id ,temp.clo_code,temp.clo_statement
                                                FROM course c
                                                LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                LEFT JOIN bloom_level b ON  temp.lg_blooms_level2 = b.level
                                                LEFT JOIN po p ON p.po_reference = temp.pso_map1
                                                AND c.crclm_id = p.crclm_id WHERE temp.duplicate = 0
                                                AND c.crclm_id = ".$crclm_id."
                                                AND c.crclm_term_id = ".$term_id." ");
            $result3 = $clo_result3->result_array();

            $clo_result4 = $this->db->query("SELECT p.po_id,c.crs_id ,temp.clo_code,temp.clo_statement
                                                FROM course c
                                                LEFT JOIN $temp_table_name temp ON temp.lg_crs_code = c.crs_code
                                                LEFT JOIN bloom_level b ON  temp.lg_blooms_level2 = b.level
                                                LEFT JOIN po p ON p.po_reference = temp.pso_map2
                                                AND c.crclm_id = p.crclm_id WHERE temp.duplicate = 0
                                                AND c.crclm_id = ".$crclm_id." 
                                                AND c.crclm_term_id = ".$term_id."");
            $result4 = $clo_result4->result_array();
        
        
            
      
            foreach ($result1 as $clo_data1) {
                if ((strpos(trim($clo_data1['clo_statement']), ' ')) || (strpos(trim($clo_data1['clo_statement']), ',') !== false)) {
            
                    $additional_data = array(
                        'clo_code' => $clo_data1['clo_code'],
                        'clo_statement' => trim($clo_data1['clo_statement']),
                        'crclm_id' => $crclm_id,
                        'term_id' => $term_id,
                        'crs_id' => $clo_data1['crs_id'],
                        'created_by' => $loggedin_user_id,
                        'create_date' => date("Y-m-d")
                    );
                    $this->db->insert("clo", $additional_data);
                    $clo_id = $this->db->insert_id();

                    if($clo_data1['bld_id']!=null && $clo_data1['bloom_id']!=null) {
                        $clo_bloom_level_array = array(
                            'clo_id' => $clo_id,
                            'bld_id' => $clo_data1['bld_id'],
                            'bloom_id' => $clo_data1['bloom_id'],
                            'created_by' => $loggedin_user_id,
                            'created_date' => date("Y-m-d")
                        );
                        $this->db->insert('map_clo_bloom_level', $clo_bloom_level_array);
                    }
                    
                    if($clo_data1['crclm_dm_id']!=null) {
                        $clo_delivery_method_array = array(
                            'clo_id' => $clo_id,
                            'delivery_method_id' => $clo_data1['crclm_dm_id'],
                            'created_by' => $loggedin_user_id,
                            'created_date' => date("Y-m-d")
                        );
                        $this->db->insert('map_clo_delivery_method', $clo_delivery_method_array);
                    }

                    if($clo_data1['po_id']!=null) {
                        $add_clo_po_map1 = array(
                            'clo_id' => $clo_id,
                            'po_id' => $clo_data1['po_id'],
                            'crclm_id' => $crclm_id,
                            'crs_id' => $clo_data1['crs_id'],
                            'map_level' => 3,
                            'created_by' => $this->ion_auth->user()->row()->id,
                            'create_date' => date('Y-m-d')
                        );
                        $this->db->insert('clo_po_map', $add_clo_po_map1);
                    }
                  
                    foreach ($result2 as $clo_data2) {
                        if($clo_data1['crs_id'] == $clo_data2['crs_id']) {
                            if($clo_data2['bld_id']!=null  && $clo_data2['bloom_id']!=null) {
                                if($clo_data1['clo_code'] == $clo_data2['clo_code'] && $clo_data1['clo_statement'] == $clo_data2['clo_statement']) {
                                    $clo_bloom_level_array1 = array(
                                        'clo_id' => $clo_id,
                                        'bld_id' => $clo_data2['bld_id'],
                                        'bloom_id' => $clo_data2['bloom_id'],
                                        'created_by' => $loggedin_user_id,
                                        'created_date' => date("Y-m-d")
                                    );
                                    $this->db->insert('map_clo_bloom_level', $clo_bloom_level_array1);
                                }
                            }
                            if($clo_data2['po_id']!=null) {
                                if($clo_data1['clo_code'] == $clo_data2['clo_code'] && $clo_data1['clo_statement'] == $clo_data2['clo_statement']) {
                                    $add_clo_po_map2 = array(
                                        'clo_id' => $clo_id,
                                        'po_id' => $clo_data2['po_id'],
                                        'crclm_id' => $crclm_id,
                                        'crs_id' => $clo_data2['crs_id'],
                                        'map_level' => 3,
                                        'created_by' => $this->ion_auth->user()->row()->id,
                                        'create_date' => date('Y-m-d')
                                    );
                                    $this->db->insert('clo_po_map', $add_clo_po_map2);
                                }
                            }
                        }
                    }

                    foreach ($result3 as $clo_data3) {
                        if($clo_data1['crs_id'] == $clo_data3['crs_id'] ) {
                            if($clo_data3['po_id']!=null) {
                                if($clo_data1['clo_code'] == $clo_data3['clo_code'] && $clo_data1['clo_statement'] == $clo_data3['clo_statement']) {
                                    $add_clo_po_map3 = array(
                                        'clo_id' => $clo_id,
                                        'po_id' => $clo_data3['po_id'],
                                        'crclm_id' => $crclm_id,
                                        'crs_id' => $clo_data3['crs_id'],
                                        'map_level' => 3,
                                        'created_by' => $this->ion_auth->user()->row()->id,
                                        'create_date' => date('Y-m-d')
                                    );
                                    $this->db->insert('clo_po_map', $add_clo_po_map3);
                                }
                            }
                        }
                    }  

                    foreach ($result4 as $clo_data4) {
                        if($clo_data1['crs_id'] == $clo_data4['crs_id'] ) {
                            if($clo_data4['po_id']!=null) {
                                if($clo_data1['clo_code'] == $clo_data4['clo_code'] && $clo_data1['clo_statement'] == $clo_data4['clo_statement']) {
                                    $add_clo_po_map4 = array(
                                        'clo_id' => $clo_id,
                                        'po_id' => $clo_data4['po_id'],
                                        'crclm_id' => $crclm_id,
                                        'crs_id' => $clo_data4['crs_id'],
                                        'map_level' => 3,
                                        'created_by' => $this->ion_auth->user()->row()->id,
                                        'create_date' => date('Y-m-d')
                                    );
                                    $this->db->insert('clo_po_map', $add_clo_po_map4);
                                }
                            }
                        }
                    }  
    
            
                } else {
                    $invalid_data = "invalid data";
                }
            }

        }
        return 1;
       
    }

   
    function download_clo_data($crclm_id = NULL,$term_id = NULL) {
        if($term_id != NULL) {
            $clo_list = 'SELECT clo_code,clo_statement 
                            FROM clo 
                            WHERE crclm_id = '. $crclm_id . ' 
                            AND term_id = '. $term_id .' 
                            GROUP BY clo_id';
            $clo_list_result = $this->db->query($clo_list);
            return $clo_list_result->result_array();
        }
    }
   
    function download_crs_code_data($crclm_id = NULL,$term_id = NULL) {
        if($term_id != NULL) {
            for($i=0;$i<=5;$i++) {
                $crs_code_list = 'SELECT crs_code ,crs_id
                                    FROM course 
                                    WHERE crclm_id = '. $crclm_id . ' 
                                    AND crclm_term_id = '. $term_id .' 
                                    AND status = 1 
                                    GROUP BY crs_id';
                $crs_code_list_resultt = $this->db->query($crs_code_list);
                $crs_code_list_result[$i] = $crs_code_list_resultt->result_array();
                $data['crs_code_list'] = $crs_code_list_result;
            }
            return $data;
        }
    }

    
    public function get_all_bloom_level_clo() {
        $bloom_level_query = $this->db->query('SELECT b.bloom_id, b.level, b.learning, b.description, b.bloom_actionverbs
                                                FROM bloom_level b');
        $bloom_level_data = $bloom_level_query->result_array();
        return $bloom_level_data;
    }

   
    public function get_all_delivery_method($crclm_id) {
        $delivery_method_query = $this->db->query('SELECT d.crclm_dm_id, d.delivery_mtd_name 
						                            FROM map_crclm_deliverymethod d
						                            WHERE d.crclm_id = "' . $crclm_id . '"
						                            ORDER BY d.delivery_mtd_name ASC');
        $delivery_method_data = $delivery_method_query->result_array();
        return $delivery_method_data;
    }

   
    public function get_all_po($crclm_id) {
        $po_query = $this->db->query("SELECT po_reference
						                FROM po 
						                WHERE crclm_id = '" . $crclm_id . "'
                                        AND pso_flag=0
						                ORDER BY po_id ASC");
        $po_data = $po_query->result_array();
        return $po_data;
    }

    public function get_all_pso($crclm_id) {
        $po_query = $this->db->query('SELECT po_reference
						                FROM po 
						                WHERE crclm_id = "' . $crclm_id . '"
                                        AND pso_flag=1
						                ORDER BY po_id ASC');
        $po_data = $po_query->result_array();
        return $po_data;
    }


    function download_clo_crs_code_data($crclm_id = NULL,$term_id = NULL) {
        if($term_id != NULL) {
            $crs_code_list = 'SELECT crs.crs_code 
                                FROM course crs
                                JOIN clo c ON crs.crs_id = c.crs_id 
                                WHERE c.crclm_id = '. $crclm_id . ' 
                                AND c.term_id = '. $term_id .' 
                                AND status = 1';
            $crs_code_list_result = $this->db->query($crs_code_list);
            return $crs_code_list_result->result_array();
        }
    }

    function download_blooms_level($crclm_id = NULL,$term_id = NULL) {
        
        $temp_table_name = 'temp_clo_'.$term_id;
        
        if($term_id != NULL) {
         
        $bloom_lvl_list = 'SELECT GROUP_CONCAT(b.level) AS level
                            FROM map_clo_bloom_level bl
                            JOIN clo cl ON cl.clo_id=bl.clo_id
                            JOIN bloom_level b ON bl.bloom_id=b.bloom_id
                            JOIN course c ON c.crs_id=cl.crs_id
                            WHERE cl.crclm_id= '. $crclm_id . ' 
                            AND cl.term_id= '. $term_id . ' 
                            GROUP BY cl.clo_id,cl.crs_id';
        $bloom_lvl_list_result = $this->db->query($bloom_lvl_list);
        $data['level'] = $bloom_lvl_list_result->result_array();
      

        $bloom_lvl_list = 'SELECT GROUP_CONCAT(mcd.delivery_mtd_name) AS delivery_method 
                            FROM map_clo_delivery_method mcdm
                            JOIN clo cl ON cl.clo_id=mcdm.clo_id
                            JOIN map_crclm_deliverymethod mcd ON mcd.crclm_dm_id=mcdm.delivery_method_id
                            JOIN course c ON c.crs_id=cl.crs_id
                            WHERE cl.crclm_id = '. $crclm_id . ' 
                            AND cl.term_id = '. $term_id .'
                            GROUP BY cl.clo_id,cl.crs_id';
        $bloom_lvl_list_result = $this->db->query($bloom_lvl_list);
        $data['delivery_method'] = $bloom_lvl_list_result->result_array();

        $bloom_lvl_list = 'SELECT GROUP_CONCAT(po.po_reference) AS po_map
                            FROM po po
                            JOIN clo_po_map cpm ON cpm.po_id=po.po_id
                            JOIN clo cl ON cl.clo_id=cpm.clo_id
                            JOIN course c ON c.crs_id=cl.crs_id
                            WHERE cl.crclm_id= '. $crclm_id . ' 
                            AND cl.term_id= '. $term_id . ' 
                            GROUP BY cl.clo_id,cl.crs_id';

        $bloom_lvl_list_result = $this->db->query($bloom_lvl_list);
        $data['po_map'] = $bloom_lvl_list_result->result_array();
        
        return $data;
        }
    }

    public function get_common_sections($crs_id1, $crs_id2)
    {
        $sections = array();

        $where_conditions = array(
            't1.crs_id' => $crs_id1,
            't2.crs_id' => $crs_id2
        );

        $result = $this->db->select('t2.section_id')
                           ->from('map_courseto_course_instructor t1')
                           ->join('map_courseto_course_instructor t2', 't1.section_id = t2.section_id')
                           ->where($where_conditions)
                           ->get()
                           ->result();

        for ($i=0; $i<count($result); $i++)
        {
            $sections[] = $result[$i]->section_id;
        }

        return $sections;
    }

    public function get_course_list_for_import($crs_id)
    {
        $course_details = $this->db->select('crs.crs_code, crs.crs_mode, crclm.end_year')
                                    ->from('course crs')
                                    ->join('curriculum crclm', 'crs.crclm_id = crclm.crclm_id')
                                    ->where('crs.crs_id', $crs_id)
                                    ->get()
                                    ->row();

        $crs_code = $course_details->crs_code;
        $crs_mode = $course_details->crs_mode;
        $crclm_end_year = $course_details->end_year;

        $where_condition_str = "crs.crs_code=\"{$crs_code}\" AND crs.crs_mode=\"$crs_mode\" AND crs.state_id=4 AND crclm.end_year <= {$crclm_end_year}";

        return $this->db->select('crclm.crclm_id, crclm.crclm_name, term.crclm_term_id, term.term_name, crs.crs_id, crs.crs_code, crs.crs_title')
                        ->from('course crs')
                        ->join('crclm_terms term', 'crs.crclm_term_id = term.crclm_term_id')
                        ->join('curriculum crclm', 'term.crclm_id = crclm.crclm_id')
                        ->where($where_condition_str, '', FALSE)
                        ->order_by('crclm.crclm_id', 'desc')
                        ->limit(5)
                        ->get()
                        ->result();
    }

    public function is_cia_rubrics_finalized($ao_id)
    {
        $rubrics_qp_status = $this->db->select('rubrics_qp_status')
                                      ->from('assessment_occasions')
                                      ->where('ao_id', $ao_id)
                                      ->get()
                                      ->row()
                                      ->rubrics_qp_status;

        if ($rubrics_qp_status == 1)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
	
	public function check_stud_marks_exist($crs_id) {
        $mks_query = $this->db->query('SELECT crs_id FROM student_assessment_totalmarks WHERE crs_id="' . $crs_id . '"');
        $mks_data = $mks_query->num_rows();
		return $mks_data;
	}

    function fetch_course_programs()
    {
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        if ($this->ion_auth->is_admin() || $this->ion_auth->in_group('Director')) {
            $pgm_list =$this->db->query('SELECT DISTINCT p.pgm_id,p.pgm_title,p.dept_id,p.pgm_acronym
                                            FROM program as p
                                            JOIN curriculum as c
                                            ON p.pgm_id=c.pgm_id
                                            JOIN dashboard d
                                            ON d.crclm_id=c.crclm_id
                                            AND entity_id = 4
                                            AND c.status = 1 AND c.crclm_release_status = 2');
          } else  {
            $pgm_list =$this->db->query('SELECT DISTINCT p.pgm_id,p.pgm_title,p.dept_id,p.pgm_acronym
                                            FROM program as p
                                            JOIN curriculum as c
                                            ON p.pgm_id=c.pgm_id
											JOIN dashboard d
                                            ON d.crclm_id=c.crclm_id
                                            join users as u
                                            WHERE u.id =  "' . $loggedin_user_id . '" 
											AND entity_id = 4
                                            AND u.user_dept_id = p.dept_id
                                            AND c.status = 1 AND c.crclm_release_status = 2');
        } 
        $pgm_list_data = $pgm_list->result_array();
        return $pgm_list_data;	

    }

    public function fetch_crclm_list() {
       
        $loggedin_user_id = $this->ion_auth->user()->row()->id;
        if ($this->ion_auth->is_admin()) {
            $curriculum_list = $this->db->query('SELECT DISTINCT c.pgm_id,c.crclm_id, c.crclm_name 
                                                    FROM curriculum AS c, dashboard AS d 
                                                    WHERE d.crclm_id = c.crclm_id 
                                                    AND d.entity_id = 4 
                                                    AND c.status = 1 AND c.crclm_release_status = 2
                                                    ORDER BY c.crclm_name ASC');
        } else {
            $curriculum_list = $this->db->query('SELECT DISTINCT c.pgm_id,c.crclm_id, c.crclm_name 
                                                    FROM curriculum AS c, users AS u, program AS p, dashboard AS d 
                                                    WHERE u.id = "' . $loggedin_user_id . '" 
                                                    AND u.user_dept_id = p.dept_id 
                                                    AND c.pgm_id = p.pgm_id 
                                                    AND d.crclm_id = c.crclm_id 
                                                    AND d.entity_id = 4 
                                                    AND c.status = 1 AND c.crclm_release_status = 2
                                                    ORDER BY c.crclm_name ASC');
        }
    
        $crclm_list_data = $curriculum_list->result_array();
        return $crclm_list_data;
    }

    public function fetch_crclm_list_course_registration() {

        $curriculum_list = $this->db->query('SELECT DISTINCT c.pgm_id,c.crclm_id, c.crclm_name, c.first_year_flag
                            FROM curriculum AS c, dashboard AS d
                            WHERE d.entity_id = 5
                                AND c.crclm_id = d.crclm_id
                                AND d.status = 1
                                AND c.status = 1 AND c.crclm_release_status = 2
                                GROUP BY c.crclm_id
                                ORDER BY c.crclm_name ASC');
    
        $crclm_list_data = $curriculum_list->result_array();
        return $crclm_list_data;
    }

    function fetch_all_programs_course_registration()
    {
        $pgm_list =$this->db->query('SELECT DISTINCT p.pgm_id,p.pgm_title,p.dept_id,p.pgm_acronym
                                            FROM program as p
                                            JOIN curriculum as c
                                            ON p.pgm_id=c.pgm_id
                                            JOIN dashboard d
                                            ON d.crclm_id=c.crclm_id
                                            AND entity_id = 5
                                             AND c.status = 1 AND c.crclm_release_status = 2');
 
        $pgm_list_data = $pgm_list->result_array();
        return $pgm_list_data;
    }

    /**
	 * To fetch CO codes from Master type details table
	 * Return: List of CO codes
	 */
	function fetch_co_codes($curriculum_id) {
		$query = "SELECT mt_details_id AS co_code_id, mt_details_name AS co_code 
					FROM master_type_details 
					WHERE master_type_id = (SELECT master_type_id FROM master_type WHERE master_type_name = 'co_code')";
		return $this->db->query($query)->result_array();
	}

    public function loadSectionList($crclm_id)
    {
        $where_conditions = array(
            's.crclm_id' => $crclm_id,
            's.status_active' => 1
        );

        return $this->db->distinct()
                        ->select('s.section_id, m.mt_details_name')
                        ->from('su_student_stakeholder_details s')
                        ->join('master_type_details m', 's.section_id = m.mt_details_id')
                        ->where($where_conditions)
                        ->get()
                        ->result();
    }

    public function export_student_data($crs_id, $section_id){
        if($crs_id != NULL && $section_id != 0) {
            $section_cond_1 = "AND m.section_id = {$section_id}";
            $section_cond_2 = "AND parent_id = {$section_id}";
        }
        else {
            $section_cond_1 = "";
            $section_cond_2 = "";
        }
        
        $query = "SELECT m.mcstd_id, m.student_id, s.student_usn,mtd.mt_details_name as section_name, CONCAT(s.title, ' ', s.first_name, ' ', COALESCE(s.last_name, '')) AS student_name, crs.crs_code,crs.crs_title,
            crclm_name AS student_curriculum, s.email, mtd.master_type_details_alias_name AS mt_details_name, A.batch_wise_stud_count, mtd_status.mt_details_name status, s.status_active, mtd_status2.mt_details_name AS stud_curriculum_section, s.contact_number as contact_no
            FROM map_courseto_student m
            LEFT JOIN su_student_stakeholder_details s ON m.student_id = s.ssd_id
            LEFT JOIN curriculum c ON m.std_crclm_id = c.crclm_id
            LEFT JOIN course crs ON crs.crclm_id=c.crclm_id
            LEFT JOIN master_type_details mtd_m ON m.batch_id = mtd_m.mt_details_id
            LEFT JOIN master_type_details mtd ON m.section_id = mtd.mt_details_id
            LEFT JOIN master_type_details mtd_status ON m.status = mtd_status.mt_details_id
            LEFT JOIN master_type_details mtd_status2 ON s.section_id = mtd_status2.mt_details_id
            LEFT JOIN (SELECT i.section_id, COUNT(mcstd_id) batch_wise_stud_count
            FROM map_courseto_course_instructor i
            LEFT JOIN map_courseto_student m ON m.crs_id = {$crs_id} AND i.section_id = m.batch_id
            AND status IN(SELECT mt_details_id FROM master_type_details WHERE master_type_id = 58 AND mt_details_name NOT IN('Unregistered'))
            WHERE i.crs_id = {$crs_id}
            AND i.section_id IN (SELECT mt_details_id FROM master_type_details WHERE (org_type = 'BATCHWISE_SECTION' OR org_type = 'FIRSTYEAR_BATCHWISE_SECTION') ".$section_cond_2.")
            GROUP BY i.section_id) A ON m.batch_id = A.section_id
            WHERE m.crs_id = {$crs_id} ". $section_cond_1 ." AND mtd_status.mt_details_name NOT IN('Unregistered')
            GROUP BY m.student_id
            ORDER BY mtd.mt_details_name, student_curriculum, s.student_usn";
        $result = $this->db->query($query);
        $result = $result->result_array();
        return $result;
    }

    public function fetch_department($pgm_id)
    {
        $query = "SELECT dept_name FROM program p INNER JOIN department d ON d.dept_id=p.dept_id WHERE pgm_id = {$pgm_id}";
        $result = $this->db->query($query);
        $result = $result->result_array();
        return $result;
    }

    /* function is to check course is exist in the LMS time table */
    public function check_lms_crs_data($crs_id){
        $select_crs_query = $this->db->query('SELECT time_table_id FROM lms_tt_time_table WHERE crs_id = "'. $crs_id .'" ');
        $select_crs_data = $select_crs_query->result_array();

        if(!empty($select_crs_data)){
            return "1";
        } else {
            return "0";
        }
    }
    
    /* function is used to check if all students selected are not associated with manage student attendance (LMS) */
    public function check_student_data($data = array()){
        extract($data);
        $result = '';

        $select_student_query = $this->db->query('SELECT DISTINCT m.mcstd_id FROM lms_map_student_attendance l INNER JOIN map_courseto_student m ON l.ssd_id = m.student_id WHERE mcstd_id IN ('.$student_data.')');
        $student_data_lms = $select_student_query->result_array();

        if(!empty($student_data_lms)){
            $select_student = array_column($student_data_lms,'mcstd_id');
            $student_data_lms = array();
            $student_data_lms = explode(",",$student_data);
            if($student_data_lms == $select_student){
                return "1";
            } else {
                $result = array_diff($student_data_lms,$select_student);
            }
        }
        if(!empty($result)){
            $student_lms_data = implode(",",$select_student);
            $select_student_query = $this->db->query('SELECT student_usn FROM su_student_stakeholder_details su INNER JOIN map_courseto_student mc ON mc.student_id = su.ssd_id WHERE mcstd_id IN('.$student_lms_data.')');
            $lms_student_data = $select_student_query->result_array();   
            return $lms_student_data;
        } else {
            return "0";
        }
    }

    public function fetch_section($crs_id){
        $query = $this->db->query('select * from (select distinct section_id, md.mt_details_name from map_courseto_student m inner join master_type_details md on md.mt_details_id = m.section_id where crs_id= '.$crs_id.' ) as a');
        $studunt_section = $query->result_array();
        return $studunt_section;
    }

    function fetch_change_section_list($data = NULL) {
        extract($data);
        // Section list except the current selected section
        $query = "SELECT mtd.mt_details_id, mtd.mt_details_name AS mt_details_name
                FROM map_courseto_course_instructor mc
                LEFT JOIN master_type_details mtd ON (mtd.org_type='SECTION' OR mtd.org_type='FIRSTYEAR_SECTION') 
                AND mc.section_id = mtd.mt_details_id
                WHERE mc.crs_id = {$crs_id} AND mtd.mt_details_id != {$section_id}
                GROUP BY mtd.mt_details_id
                ORDER BY mtd.mt_details_id";
        $result = $this->db->query($query)->result_array();

        return $result;
    }

    function change_reg_stud_section($data = array()) {
        extract($data);
        $query = "UPDATE map_courseto_student SET section_id = ".$section_id." WHERE mcstd_id IN(".$student_records.")";
        $result = $this->db->query($query);

        /* fetching the lms_flag */
        $select_lms_flag = $this->db->query('SELECT ionlms_flag FROM organisation');
        $lms_flag = $select_lms_flag->row_array();
        // if($lms_flag['ionlms_flag'] == 1) {
        //     $query = "UPDATE lms_manage_attendance SET section_id = ".$section_id." WHERE attendance_id IN(".$student_records.")";
        //     $result = $this->db->query($query);
 
        //     $query = "UPDATE lms_map_assignment_upload SET section_id = ".$section_id." WHERE map_assignment_upload_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_map_crs_material_upload SET section_id = ".$section_id." WHERE crs_material_map_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_lesson_schedule SET section_id = ".$section_id." WHERE lls_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

            
        //     $query = "UPDATE lms_map_share_materials_to_student SET section_id = ".$section_id." WHERE material_student_map_id IN(".$student_records.")";
        //     $result = $this->db->query($query);

        //     $query = "UPDATE lms_quiz_section_mapping SET section_id = ".$section_id." WHERE q_sec_map_id IN(".$student_records.")";
        //     $result = $this->db->query($query);
        // }
        return $result;
    }

    public function check_section_marks_upld($data = NULL) {
        extract($data);
        $students_list = implode(",",$students);
        $select_query = $this->db->query('SELECT s.student_usn FROM map_courseto_student mc JOIN
                                            su_student_stakeholder_details s ON mc.student_id = s.ssd_id WHERE mcstd_id IN('.$students_list.')');
        $stud_usn_result = $select_query->result_array();

        foreach($stud_usn_result as $user_row) {
            if($user_row['student_usn'] != '') {
                $rows[] = $user_row['student_usn'];
            }
        }
        $student_usns = '"'.implode('","',$rows).'"';
        $student_usns = trim($student_usns, "'");

        $query = "SELECT assessment_id
                    FROM student_assessment sa
                    LEFT JOIN qp_mainquestion_definition qpmd ON sa.qp_mq_id = qpmd.qp_mq_id
                    LEFT JOIN qp_unit_definition qud ON qpmd.qp_unitd_id = qud.qpd_unitd_id
                    LEFT JOIN qp_definition qpd ON qud.qpd_id = qpd.qpd_id
                    LEFT JOIN assessment_occasions ao ON qpd.crs_id = ao.crs_id
                    WHERE ao.crs_id = ".$crs_id." AND sa.section_id = ".$section_id." AND sa.student_usn in(".$student_usns.");";
        $upld_cnt = $this->db->query($query)->num_rows();
       
        return $upld_cnt;
        
    }

    public function fetch_clo_bl_flag($crclm_id){
        $query = $this->db->query('SELECT clo_bl_flag FROM course WHERE crclm_id = '.$crclm_id.'');
        $select_clo_bl_flag = $query->result_array();
        
            if(!empty($select_clo_bl_flag) && $select_clo_bl_flag[0]['clo_bl_flag'] == 1) {
                return '3';
            }
    }

   public function fetch_first_year_flag($crclm_id) {
        $query = $this->db->query('SELECT first_year_flag FROM curriculum WHERE crclm_id = '.$crclm_id.'');
        $first_year_flag = $query->result_array();
        if($first_year_flag[0]['first_year_flag'] == 1){
            return "1";
        }
   }

   public function fetch_course_domain($crs_id) {
        $select_query = $this->db->query('SELECT crs_domain_id FROM course WHERE crs_id = '.$crs_id.'');
        $domain_result = $select_query->result_array();
        if($domain_result[0]['crs_domain_id'] != null && $domain_result[0]['crs_domain_id'] != "") {
            return "1";
        } else {
            return "2";
        }
   }

   /* this function is used for the fetching the lms_flag and student attendence */
   public function fetch_lms_attendance($course_id ,$sec_id) {
        /* fetching the lms_flag */
        $select_lms_flag = $this->db->query('SELECT ionlms_flag FROM organisation');
        $lms_flag = $select_lms_flag->row_array();

        /* fetching the Time Table(LMS) */
        $select_tt_query = $this->db->query('SELECT ltt.crs_id,lttd.section_id FROM lms_tt_time_table_details AS lttd
                                             JOIN lms_tt_time_table ltt ON lttd.tt_detail_id = ltt.tt_detail_id
                                             WHERE ltt.crs_id = '.$course_id.' AND lttd.section_id = '.$sec_id.'');
        $lms_tt = $select_tt_query->result_array();
                                  
        /* fetching the Attendence(LMS) */
        $select_attendance = $this->db->query('SELECT status FROM lms_manage_attendance WHERE crs_id = '.$course_id.' AND section_id = '.$sec_id.'');
        $lms_attendence = $select_attendance->result_array();

        if($lms_flag['ionlms_flag'] == 1 && ((!empty($lms_tt)) || !empty($lms_attendence))) {
            return 4;
        }else {
            return 0;
        }
   }
   
   /*this function used to check the marks uploded for the particulr section */
   public function chk_marks_uploded($course_id,$sec_id) {
        $select_marks_query = $this->db->query('SELECT  ao.section_id,ao.crs_id, COUNT(ao.ao_id) AS "occasion_count", SUM(A.crs_occasion_upload_status) AS "crs_occasion_upload_status"
                                                FROM assessment_occasions ao
                                                  LEFT JOIN (SELECT ao.crs_id, qpd.cia_model_qp, ao.ao_id, IF(sa.student_name IS NULL, 0, 1) AS "crs_occasion_upload_status",ao.section_id
                                                  FROM assessment_occasions ao
                                                  LEFT JOIN qp_definition qpd ON (ao.qpd_id = qpd.qpd_id OR ao.qpd_id= 0)
                                                  LEFT JOIN qp_unit_definition qpud ON qpd.qpd_id = qpud.qpd_id
                                                  LEFT JOIN qp_mainquestion_definition qpmd ON qpud.qpd_unitd_id = qpmd.qp_unitd_id
                                                  LEFT JOIN student_assessment sa ON qpmd.qp_mq_id = sa.qp_mq_id
                                                  WHERE ao.crs_id = '.$course_id.' AND ao.section_id = '.$sec_id.' AND qpd.cia_model_qp = 0 AND ao.mte_flag = 0
                                                  GROUP BY ao.crs_id,ao.section_id, ao.ao_id)
                                                  A ON A.ao_id = ao.ao_id
                                                  WHERE ao.crs_id = '.$course_id.' AND ao.section_id = '.$sec_id.' AND ao.mte_flag = 0 AND A.cia_model_qp = 0
                                                  GROUP BY ao.crs_id,ao.section_id');
        $marks_upld_check = $select_marks_query->result_array();
        return $marks_upld_check;       
    }
    
    /*function is used for checking co_creation status */
    public function check_co_creation($crs_id){
        $select_query = $this->db->query('SELECT status from course WHERE crs_id = '.$crs_id.'');
        $result_query = $select_query->result_array();
        $result = $result_query[0]['status'];
        return $result;  
    }
    /* this function is for fetch lms_flag */
    function fetch_lms_flag() {
        $query = $this->db->query('SELECT ionlms_flag FROM organisation');
        $lms_flag_result = $query->row_array();
        $result = $lms_flag_result['ionlms_flag'];
        return $result;
    }
    /* this function is to check student is register for course */
    function check_student_register($crs_id){
        $select_query = $this->db->query('SELECT COUNT(crs_id)AS register_course FROM map_courseto_student WHERE crs_id ='.$crs_id.'');
        $query = $select_query->row_array();
        $result = $query['register_course'];
        return $result;

    }
    

    /* this function is used for the fetching the lms_flag and student attendence */
    public function check_lms_attendance($course_id ,$from_section_id,$to_section_id,$student) {
        /* fetching the lms_flag */
        $select_lms_flag = $this->db->query('SELECT ionlms_flag FROM organisation');
        $lms_flag = $select_lms_flag->row_array();
        $student_ids = implode(",",$student);
        $params = array();
    
        $params = array(
            'crs_id' => $course_id,
            'section_id' => $from_section_id
        );
        $batch_wise_section_flag = $this->check_batch_under_section($params);
        if($batch_wise_section_flag == 1){
            $query_batch_wise = $this->db->query("SELECT GROUP_CONCAT(batch_id) as batch_sec from map_courseto_student where mcstd_id in ($student_ids)");
            $result_batch_sec_id = $query_batch_wise->row_array();
        } else {
            $query_section_id = $this->db->query("SELECT GROUP_CONCAT(section_id)as batch_sec from map_courseto_student where mcstd_id in ($student_ids)");
            $result_batch_sec_id = $query_section_id->row_array();
        }

        $student_list = $this->db->query("SELECT s.student_usn FROM map_courseto_student mc JOIN
                                            su_student_stakeholder_details s ON mc.student_id = s.ssd_id WHERE mcstd_id IN($student_ids)");
        $result_stud_usn = $student_list->result_array();

        foreach($result_stud_usn as $user_row) {
            if($user_row['student_usn'] != '') {
                $rows[] = $user_row['student_usn'];
            }
        }
        $student_usns = '"'.implode('","',$rows).'"';
        $student_usns = trim($student_usns, "'");
        /* fetching the Attendence(LMS) */
        $from_select_attendance = $this->db->query('SELECT status FROM lms_manage_attendance l JOIN lms_map_student_attendance lmsa on lmsa.attendance_id = l.attendance_id 
                                                    WHERE l.crs_id = '.$course_id.' AND l.section_id in ('.$result_batch_sec_id['batch_sec'].' AND lmsa.student_usn in ('.$student_usns.'))');
        $from_lms_attendence = $from_select_attendance->result_array();

        $to_select_attendance = $this->db->query('SELECT status FROM lms_manage_attendance l JOIN lms_map_student_attendance lmsa on lmsa.attendance_id = l.attendance_id 
                                                    WHERE l.crs_id = '.$course_id.' AND l.section_id = '.$to_section_id.' AND lmsa.student_usn in ('.$student_usns.')');
        $to_lms_attendence = $to_select_attendance->result_array();

        if($lms_flag['ionlms_flag'] == 1 && !empty($from_lms_attendence || $to_lms_attendence )) {
            return 1;
        }else {
            return 0;
        }
    }

    /* this function is used for the fetching the lms_flag and student attendence */
    public function check_stud_crs_red_credits($params = array()) {
        extract($params);
        /* fetching the lms_flag */
        $student_name = array();
        $student_usn = array();
        $stud_usn = array();
        for($i=0; $i<count($selectedStudent); $i++) {
            $select_reg_std_qry = $this->db->query('SELECT crs_id FROM map_courseto_student WHERE student_id = "'.$selectedStudent[$i].'" AND crclm_term_id="'.$crclm_trm_id.'"');
            $get_reg_std_qry = $select_reg_std_qry->result_array();
            if(empty($get_reg_std_qry)) { // if student is not registered in any of the courses
                $stud_usn[] =$selectedStudent[$i];
                $data['usn'] = $stud_usn;
            } else {
                for($j=0; $j<count($get_reg_std_qry); $j++) {
                    $crs [] = $get_reg_std_qry[$j]['crs_id'];
                    $crsid = implode(",", $crs);
                }
                // $select_crs_credits_qry = $this->db->query('SELECT crs_id,SUM(total_credits) as crs_credits FROM course WHERE crs_id IN ('.$crsid.')');
                $select_crs_credits_qry = $this->db->query('SELECT c.crs_id,SUM(total_credits) as crs_credits FROM course c
                                                                LEFT JOIN map_courseto_student mcs ON c.crs_id=mcs.crs_id
                                                                AND c.crclm_term_id=mcs.crclm_term_id
                                                                WHERE c.crclm_term_id ="'.$crclm_trm_id.'" AND c.crs_id IN ('.$crsid.')
                                                                AND student_id IN ('.$selectedStudent[$i].')');
               
                $get_crs_credits_qry = $select_crs_credits_qry->result_array();

                $select_total_crs_credit = $this->db->query('SELECT total_crs_enroll FROM crclm_terms WHERE crclm_term_id="'.$crclm_trm_id.'"');
                $total_crs_credit = $select_total_crs_credit->row_array();

                if(isset($get_crs_credits_qry)) {
                    if(isset($total_crs_credit['total_crs_enroll'])){
                        if(($crs_credits+$get_crs_credits_qry[0]['crs_credits']) > $total_crs_credit['total_crs_enroll']) {
                            $select_std_details = $this->db->query('SELECT student_usn,first_name
                                                                        FROM map_courseto_student mcs
                                                                        JOIN course c ON mcs.crs_id=c.crs_id
                                                                        JOIN su_student_stakeholder_details sssd ON mcs.student_id=sssd.ssd_id
                                                                        WHERE  student_id IN ('.$selectedStudent[$i].') AND mcs.crclm_term_id="'.$crclm_trm_id.'"
                                                                        GROUP BY sssd.ssd_id');
                            $fetch_std_details = $select_std_details->result_array();
                            $student_name[] = $fetch_std_details[0]['first_name'];
                            $data['std_details'] = $student_name;
                            $student_usn[] = $fetch_std_details[0]['student_usn'];
                            $data['student_usn'] = $student_usn;
                        } else {
                            $stud_usn[] =$selectedStudent[$i];
                            $data['usn'] = $stud_usn;
                        }
                    } else {
                        $stud_usn[] =$selectedStudent[$i];
                        $data['usn'] = $stud_usn;
                    }
                    
                } 
            }
        }
        return $data;
    }

    public function fetch_ci($crs_id,$section_id) {
        $select_course_instructor = 'SELECT usr.id, usr.title, usr.first_name,usr.last_name,ci.mcci_id, ci.crclm_id, ci.crclm_term_id, ci.crs_id, ci.course_instructor_id, ci.section_id, (CONCAT(usr.title, usr.first_name," ", usr.last_name)) as user_name, sec.mt_details_name as section   FROM map_courseto_course_instructor as ci'
                                        . ' JOIN users as usr ON id = ci.course_instructor_id'
                                        . ' JOIN master_type_details as sec ON sec.mt_details_id = ci.section_id and master_type_id = 34'
                                        . ' WHERE ci.crs_id="' . $crs_id . '" 
                                        and ci.section_id ="'.$section_id.'"
                                        GROUP BY ci.mcci_id
                                        ORDER BY sec.mt_details_id';
        $instructor_data = $this->db->query($select_course_instructor);
        $instructor_details = $instructor_data->result_array();
        return $instructor_details;
   }

   public function fetch_crs_finalize_flag($crs_id){
        $query = $this->db->query('SELECT crs_attainment_finalize_flag FROM course WHERE crs_id = '.$crs_id.'');
        $query_data = $query->result_array();
        return $query_data[0]['crs_attainment_finalize_flag'];
   }
}
?>