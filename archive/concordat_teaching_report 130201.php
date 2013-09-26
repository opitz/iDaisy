<?php

//==================================================================================================
//
//	Separate file with concordat teaching report function
//
//	Last changes: 2013-01-31
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function concordat_teaching_report()
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	here comes the query

$query = "
SELECT
e.id AS 'E_ID',
t.id AS 'T_ID',
au.id AS 'AU_ID',
tc.id AS 'TC_ID',
au.assessment_unit_code AS 'Unit Code',
au.title AS 'Unit Title',
CONCAT(au_d.department_code,'-',au_d.department_name) AS 'Unit Dept',
tc.subject AS 'Subject',
tct.title AS 'Type',
tctt.stint AS 'Tariff',
tc.sessions_planned AS 'Norm Sessions',
e.fullname AS 'Lecturer',
e.opendoor_employee_code AS 'Employee Number',
p.person_status AS 'Status',
p.manual AS 'Manual',
p.dept_stint_obligation AS 'Stint Obl.',
'<FONT COLOR=£FF6600>tbc</FONT>' AS 'Appointment Split',
CONCAT(p_d.department_code,'-',p_d.department_name) AS 'Lecturer Dept',
ti.sessions AS 'Sessions',
ti.percentage AS 'Percentage',
t.term_code AS 'Term'

FROM AssessmentUnit au
INNER JOIN Department au_d ON au_d.id = au.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Post p ON p.employee_id = e.id
INNER JOIN Department p_d ON p_d.id = p.department_id


WHERE 1=1
AND tcau.academic_year_id = $ay_id
AND au_d.department_code LIKE '$department_code%'
AND au.assessment_unit_code != 'ZOM99999' 
";
if($_POST['query_type'] == 'teaching_nohusc') $query = $query . "AND au.assessment_unit_code NOT LIKE 'HUSC%' ";
$query = $query . "
ORDER BY au.assessment_unit_code

#LIMIT 10
";

//d_print($query);
//	return get_data($query);
	return amend_teaching_table(get_data($query), $department_code);
}

//--------------------------------------------------------------------------------------------------------------
function concordat_teaching_report_calc()
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	here comes the query

	$query = "
		SELECT
		e.id AS 'E_ID',
		t.id AS 'T_ID',
		au.id AS 'AU_ID',
		tc.id AS 'TC_ID',
		au_d.id AS 'AU_D_ID',
		p_d.id AS 'P_D_ID',
		au.assessment_unit_code AS 'Unit Code',
		au.title AS 'Unit Title',
		CONCAT(au_d.department_code,'-',au_d.department_name) AS 'Unit Dept',
		tc.subject AS 'Subject',
		tct.title AS 'Type',
		tctt.stint AS 'Tariff',
		tc.sessions_planned AS 'Norm Sessions',
		e.fullname AS 'Lecturer',
		e.opendoor_employee_code AS 'Employee Number',
		p.person_status AS 'Status',
		p.manual AS 'Manual',
		p.dept_stint_obligation AS 'Stint Obl.',
		'<FONT COLOR=£FF6600>tbc</FONT>' AS 'Appointment Split',
		CONCAT(p_d.department_code,'-',p_d.department_name) AS 'Lecturer Dept',
		ti.sessions AS 'Sessions',
		ti.percentage AS 'Percentage',
		t.term_code AS 'Term'

		FROM AssessmentUnit au
		INNER JOIN Department au_d ON au_d.id = au.department_id
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
		INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
		INNER JOIN Employee e ON e.id = ti.employee_id
		INNER JOIN Post p ON p.employee_id = e.id
		INNER JOIN Department p_d ON p_d.id = p.department_id

		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND au_d.department_code LIKE '$department_code%'
		AND p.startdate < t.startdate
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.enddate, 1=1)
		AND au.assessment_unit_code != 'ZOM99999' 
	";
	if($_POST['query_type'] == 'teaching_nohusc') $query = $query . "AND au.assessment_unit_code NOT LIKE 'HUSC%' ";
	$query = $query . "
		ORDER BY au.assessment_unit_code
		#LIMIT 10
	";

//d_print($query);
//	return get_data($query);
	return amend_teaching_table_calc(get_data($query), $department_code);
}

//--------------------------------------------------------------------------------------------------------------
function amend_teaching_table($table, $dept_code)
//	calculate the Appointment Split for a given supervisor and academic year and use this to compute the Actual Stint
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$e_nr = $_POST['Employee Number'];								// get Employee Number
	
	$new_table = array();
	if($table) foreach($table AS $row)
	{
//		$e_id = $row['E_ID'];
//		$t_id = $row['T_ID'];
//		$au_id = $row['AU_ID'];
//		$tc_id = $row['TC_ID'];
		
		$e_id = array_shift($row);
		$t_id = array_shift($row);
		$au_id = array_shift($row);
		$tc_id = array_shift($row);
		
		$app_split = get_appointment_split($e_id, $dept_code, $t_id);	

		$au_students = get_au_students($au_id);
		$tc_students = get_tc_students($tc_id);
		$enrolment_split = 1;
		if($tc_students > 0) $enrolment_split = $au_students / $tc_students;
		$stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;

		$row['Appointment Split'] = number_format($app_split,2);
		$row['AU / TC Students'] = "$au_students / $tc_students";
		$row['Stint Earned'] = number_format($stint_earned,2);
		
		if($row['Manual'] == 1) $row['Manual'] = 'Yes';
		else $row['Manual'] = '';

//		$row['AU Students'] = $au_students;
//		$row['TC Students'] = $tc_students;


		$new_table[] = $row;
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_teaching_table_calc($table, $dept_code)
//	calculate the Appointment Split for a given supervisor and academic year and use this to compute the Actual Stint
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$e_nr = $_POST['Employee Number'];								// get Employee Number
	
	$new_table = array();
	if($table) foreach($table AS $row)
	{
//		$e_id = $row['E_ID'];
//		$t_id = $row['T_ID'];
//		$au_id = $row['AU_ID'];
//		$tc_id = $row['TC_ID'];
		
		$e_id = array_shift($row);
		$t_id = array_shift($row);
		$au_id = array_shift($row);
		$tc_id = array_shift($row);
		$au_d_id = array_shift($row);
		$p_d_id = array_shift($row);
		
//		$app_split = get_appointment_split($e_id, $dept_code, $t_id);	
		$app_split = get_appointment_split($e_id, $p_d_id, $t_id);	
		
		$post_dept_ids = get_post_dept_ids($e_id, $t_id);
		
		$au_students = get_au_students($au_id);
		$tc_students = get_tc_students($tc_id);
		$enrolment_split = 1;
		if($tc_students > 0) $enrolment_split = $au_students / $tc_students;
		if(in_array($au_d_id , $post_dept_ids)) 
		{
			if ($au_d_id == $p_d_id) $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $enrolment_split;
			else $stint_earned = 0;
//			$row['Appointment Split'] = '('.number_format($app_split,2).')';
		}
		else 
		{
			$stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
		}

		$row['Appointment Split'] = number_format($app_split,2);
		$row['AU / TC Students'] = "$au_students / $tc_students";
		$row['Stint Earned'] = number_format($stint_earned,2);
		
		if($row['Manual'] == 1) $row['Manual'] = 'Yes';
		else $row['Manual'] = '';

//		$row['AU Students'] = $au_students;
//		$row['TC Students'] = $tc_students;


		$new_table[] = $row;
	}
	return $new_table;
}

?>