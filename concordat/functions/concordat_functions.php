<?php

//==================================================================================================
//
//	Separate file with concordat report functions
//
//	13-07-09	8th version
//==================================================================================================

//----------------------------------------------------------------------------------------
function concordat_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	$year = $_POST['year'];															// get year
	
	print "<form action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<input type='hidden' name='query_type' value='concordat'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Academic Year:";
	print new_column(400);
	print academic_year_options();
//		print " <FONT COLOR=GREY>(no effect on REF reports)</FONT>";
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print start_row(0) . "Title:" . new_column(0) . "<input type='text' name = 'title_q' value='".$_POST['title_q']."' size=50>" . end_row();		
	print start_row(0) . "Report Type:" . new_column(0) . concordat_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";
	print concordat_report_checkboxes();
	print "<HR>";
	
//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function concordat_report_checkboxes()
// shows the checkboxes for the report query
{
//	display the option checkboxes
	$html = $html . "<TABLE BORDER=0>";

	$html = $html . start_row(400);		// 1st row
		 $html = $html . "Exclude Assessment Units without enrolled Students:";
	$html = $html . new_column(60);
		$html = $html . html_checkbox('exclude_no_students');
	$html = $html . end_row();


	$html = $html . "</TABLE>";

	return $html;
}

//----------------------------------------------------------------------------------------
function concordat_report_options()
// shows the options for a publication report
{
	$query_type = $_POST['query_type'];					// get query_type
	
//	print "<input type='hidden' name='query_type' value='concordat'>";
//	$options = array("General report: summary", "General report: standard", "General report: detailed", "REF report", "REF report compact");
	$options = array();
	$html = "<select name='query_type'>";
	
	$options[] = array('Select a report type','');
//	$options[] = array('Teaching Report','teaching');
//	$options[] = array('Teaching Report (no HUSC)','teaching_nohusc');
	$options[] = array('AU Student Sums per DP Report','student');
	$options[] = array('AU Single Student per DP Report','single_student');
//	$options[] = array('Supervision Report','supervision');
	$options[] = array('Supervision Report','supervision_calc');
	$options[] = array('Teaching Report *NEW*','teaching_calc');
//	$options[] = array('Teaching Report (no HUSC) *NEW*','teaching_nohusc_calc');
	$options[] = array('Student Load','student_load');
	
	foreach($options AS $option)
	{
		if($option[1]==$query_type) $html = $html."<option SELECTED='selected' value=".$option[1].">".$option[0]."</option>";
		else $html = $html."<option value=".$option[1].">".$option[0]."</option>";
	}
	$html = $html."</select>";

	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function get_appointment_split($e_id, $d_id, $t_id)
//	return the percentage of ownership for a given employee, department and term
{
	$stint_dept = get_term_dept_stint($e_id, $t_id, $d_id);
	$stint_total = get_term_stint_total($e_id, $t_id);
	$post_count = count_posts($e_id, $t_id);

	if($post_count) $app_split = 1/$post_count;
	else $app_split = 1;
	if($stint_total > 0)
	{
		if($stint_dept > 0) $app_split = $stint_dept / $stint_total;
		else $app_split = 0;
	}
	
	return $app_split;
}

//--------------------------------------------------------------------------------------------------------------
function get_term_dept_stint($e_id, $t_id, $d_id)
//	returns the stint obligation for a given employee and department
{
	$query = "
		SELECT
			SUM(p.dept_stint_obligation) AS 'Stint'
		FROM 
			Post p, 
			Term t
		WHERE 1=1
		AND p.employee_id = $e_id
		AND p.startdate < t.startdate
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1)
		AND t.id = $t_id	
		AND p.department_id = $d_id
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Stint'];
}

//--------------------------------------------------------------------------------------------------------------
function get_term_stint_total($e_id, $t_id)
//	returns the stint obligation total for a given employee and the departments he had a post with at the given term
{
	$query = "
		SELECT
			SUM(p.dept_stint_obligation) AS 'Stint'
		FROM 
			Post p INNER JOIN Department d ON d.id = p.department_id, 
			Term t
		WHERE 1=1
		AND p.employee_id = $e_id
		AND p.startdate < t.startdate
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1)
		AND t.id = $t_id	
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Stint'];
}

//--------------------------------------------------------------------------------------------------------------
function get_post_dept_ids($e_id, $t_id)
//	returns as an array the ids of the department that an academic had a post at the given term 
{
	$query = "
		SELECT
			p.department_id AS 'P_D_ID'
		FROM 
			Post p INNER JOIN Department d ON d.id = p.department_id, 
			Term t
		WHERE 1=1
		AND p.employee_id = $e_id
		AND p.startdate < t.startdate
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1)
		AND t.id = $t_id	
	";
//d_print($query);
	$table = get_data($query);
	
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$new_table [] = $row['P_D_ID'];
	}
	
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function count_posts($e_id, $t_id)
//	returns the number of posts that an academic had at the given term
{
	$query = "
		SELECT
			COUNT(p.id) AS 'Posts'
		FROM 
			Post p INNER JOIN Department d ON d.id = p.department_id, 
			Term t
		WHERE 1=1
		AND p.employee_id = $e_id
		AND p.startdate < t.enddate
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1)
		AND t.id = $t_id	
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Posts'];
}

//--------------------------------------------------------------------------------------------------------------
function get_enrolment_split($e_id, $dept_code, $t_id)
//	a TC can be related to more than one AU
//	if there are students enrolled into the AU calculate the amount of stint that belongs to students enrolled in this AU
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$tc_students = get_tc_students($e_id, $t_id, $dept_code);
	$au_students = get_au_students($e_id, $t_id, $dept_code);
	
	$app_split = 1;
	if($stint_dept > 0 AND $stint_total > 0) $app_split = $stint_dept / $stint_total;
	
	return $app_split;
}

//--------------------------------------------------------------------------------------------------------------
function get_au_student_number($au_id)
//	returns the number of distinct enrolled students for a given Assessment Unit
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(DISTINCT sau.student_id) AS 'Students'
		FROM StudentAssessmentUnit sau
			INNER JOIN AssessmentUnit au 
				ON au.id = sau.assessment_unit_id
			INNER JOIN Department d 
				ON d.id = au.department_id
		WHERE 1=1
		AND sau.assessment_unit_id = $au_id
		AND sau.academic_year_id = $ay_id
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function get_tcau_student_number($tc_id, $au_id)
//	returns the number of distinct enrolled students for a given Assessment Unit and Teaching Component
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(DISTINCT sau.student_id) AS 'Students'
		FROM StudentAssessmentUnit sau
			INNER JOIN AssessmentUnit au 
				ON au.id = sau.assessment_unit_id
			INNER JOIN Department d 
				ON d.id = au.department_id
			INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = sau.assessment_unit_id AND tcau.academic_year_id = sau.academic_year_id
		WHERE 1=1
		AND sau.assessment_unit_id = $au_id
		AND sau.academic_year_id = $ay_id
		AND tcau.teaching_component_id = $tc_id
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function get_au_students($au_id)
//	returns the number of distinct enrolled students for a given Assessment Unit
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(DISTINCT sau.student_id) AS 'Students'
		FROM StudentAssessmentUnit sau
			INNER JOIN AssessmentUnit au 
				ON au.id = sau.assessment_unit_id
			INNER JOIN Department d 
				ON d.id = au.department_id
		WHERE 1=1
		AND sau.assessment_unit_id = $au_id
		AND sau.academic_year_id = $ay_id
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Students'];
}


//--------------------------------------------------------------------------------------------------------------
function get_tc_student_number($tc_id)
//	returns the number of distinct Students that are enrolled into any Assessment Unit that is related to the given Teaching Component
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(DISTINCT sau.student_id) AS 'Students' 
		FROM StudentAssessmentUnit sau
			INNER JOIN AssessmentUnit au 
				ON au.id = sau.assessment_unit_id
			INNER JOIN Department d 
				ON d.id = au.department_id
			INNER JOIN TeachingComponentAssessmentUnit tcau 
				ON tcau.assessment_unit_id = sau.assessment_unit_id 
				AND tcau.academic_year_id = sau.academic_year_id
		WHERE 1=1
		AND tcau.teaching_component_id = $tc_id
		AND sau.academic_year_id = $ay_id
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function get_tc_students($tc_id)
//	returns the distinct Students that are enrolled into any Assessment Unit that is related to the given Teaching Component
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT DISTINCT s.* 
		FROM StudentAssessmentUnit sau
			INNER JOIN AssessmentUnit au 
				ON au.id = sau.assessment_unit_id
			INNER JOIN Department d 
				ON d.id = au.department_id
			INNER JOIN TeachingComponentAssessmentUnit tcau 
				ON tcau.assessment_unit_id = sau.assessment_unit_id 
				AND tcau.academic_year_id = sau.academic_year_id
			INNER JOIN Student s
				ON s.id = sau.student_id
		WHERE 1=1
		AND tcau.teaching_component_id = $tc_id
		AND sau.academic_year_id = $ay_id
	";
//d_print($query);
	return get_data($query);
	
}

//--------------------------------------------------------------------------------------------------------------
function get_tcau_students($tc_id, $au_id)
//	returns the distinct Students that are enrolled into the given Assessment Unit that is related to the given Teaching Component
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT DISTINCT s.* 
		FROM StudentAssessmentUnit sau
			INNER JOIN AssessmentUnit au 
				ON au.id = sau.assessment_unit_id
			INNER JOIN Department d 
				ON d.id = au.department_id
			INNER JOIN TeachingComponentAssessmentUnit tcau 
				ON tcau.assessment_unit_id = sau.assessment_unit_id 
				AND tcau.academic_year_id = sau.academic_year_id
			INNER JOIN Student s
				ON s.id = sau.student_id
		WHERE 1=1
		AND tcau.teaching_component_id = $tc_id
		AND sau.academic_year_id = $ay_id
		AND sau.assessment_unit_id = $au_id
	";
//d_print($query);
	return get_data($query);
	
}



?>