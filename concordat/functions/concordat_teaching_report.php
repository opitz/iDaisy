<?php

//==================================================================================================
//
//	Separate file with concordat teaching report function
//
//	Last changes: 2013-07-29
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function concordat_teaching_report()
//	list all teaching instances related to assessment units owned by a given department for a given academci year
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
		IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) AS 'Tariff',
		tc.sessions_planned AS 'Norm Sessions',
		e.fullname AS 'Lecturer',
		e.opendoor_employee_code AS 'Employee Number',
		p.person_status AS 'Status',
		p.manual AS 'Manual',
		p.dept_stint_obligation AS 'Stint Obl.',
		'<FONT COLOR=£FF6600>tbc</FONT>' AS 'Stint Obligation Split',
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

#AND e.id = 1344
				
		AND tcau.academic_year_id = $ay_id
		AND p.post_number NOT LIKE 'C%' 
		AND au_d.department_code LIKE '$department_code%'
		AND p.startdate < t.startdate
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.enddate, 1=1)
		AND au.assessment_unit_code != 'ZOM99999' 
	";
	if($_POST['query_type'] == 'teaching_nohusc' OR $_POST['query_type'] == 'teaching_nohusc_calc') $query = $query . "AND au.assessment_unit_code NOT LIKE 'HUSC%' ";
	$query = $query . "
		ORDER BY au.assessment_unit_code

		#LIMIT 10
	";

//d_print($query);
//	return get_data($query);
	return amend_teaching_table(get_data($query), $department_code);
}

//--------------------------------------------------------------------------------------------------------------
function concordat_teaching_report_calcxxx()
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
		IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) AS 'Tariff',
		tc.sessions_planned AS 'Norm Sessions',
		e.fullname AS 'Lecturer',
		e.opendoor_employee_code AS 'Employee Number',
		p.person_status AS 'Status',
		p.manual AS 'Manual',
		p.dept_stint_obligation AS 'Stint Obl.',
		'<FONT COLOR=£FF6600>tbc</FONT>' AS 'Stint Obligation Split',
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
	return get_data($query);
//	return amend_teaching_table_calc(get_data($query), $department_code);
}




//--------------------------------------------------------------------------------------------------------------
function get_tc_enrollments($s_id, $tc_id)
//	get the number of AU enrollments of a given student that are related to the given TC
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
SELECT
COUNT(*) AS 'Enrollments'

FROM
TeachingComponentAssessmentUnit tcau
INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id 

WHERE 1=1
AND tcau.academic_year_id = $ay_id 
AND tcau.assessment_unit_id != 99999
AND tcau.teaching_component_id = $tc_id
AND sau.student_id = $s_id

	";
	$result = get_data($query);
	return $result[0]['Enrollments'];
}

//--------------------------------------------------------------------------------------------------------------
function amend_teaching_table($table, $dept_code)
//	calculate the Stint Obligation Split for a given supervisor and academic year and use this to compute the Actual Stint
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
		$app_split = get_appointment_split($e_id, $p_d_id, $t_id);				//	get the appointment split
		
		$post_dept_ids = get_post_dept_ids($e_id, $t_id);					//	get the IDs of all posts that a given emplyee has in a given term
		
		$au_student_number = get_au_student_number($au_id);					//	get the number of students enrolled into a given AU 
		$tc_student_number = get_tc_student_number($tc_id);					//	get thenumber of students enrolled into ALL AU related to a given TC
		$enrolment_split = 1;
		if($tc_student_number > 0) $enrolment_split = $au_student_number / $tc_student_number;

//		$tc_students = get_tc_students($tc_id);						//	get thenumber of students enrolled into ALL AU related to a given TC
		$tc_students = get_tcau_students($tc_id, $au_id);						//	get thenumber of students enrolled into ALL AU related to a given TC

		$appointed_stint = 0;
		$stint_earned = 0;
		
		if($tc_students > 0) foreach($tc_students AS $tc_student)
		{
//dprint($row['Unit Title'] . ' : ' .$tc_student['surname'] . ' - ' . $stint_earned);			
			
			if($_POST['query_type'] == 'teaching_calc')
			{
				if(in_array($au_d_id , $post_dept_ids))
				{
					if ($au_d_id == $p_d_id)
					{
						$tc_enrolments = get_tc_enrollments($tc_student['id'], $tc_id);
						$stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 / $tc_student_number / $tc_enrolments);
//dprint("AU Code = ".$row['Unit Code']." | stint = $stint || app_split = $app_split | enrolment_split = $enrolment_split | tc_student_number = $tc_student_number |  tcau_student_number = $tcau_student_number |tc_enrolments = $tc_enrolments");
						$stint_earned = $stint_earned + $stint;
					}
				}
				else
				{
					$tc_enrolments = get_tc_enrollments($tc_student['id'], $tc_id);
					$stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split / $tc_student_number / $tc_enrolments);
					$stint_earned = $stint_earned + $stint;
				}
			}
			else
			{
				$tc_enrolments = get_tc_enrollments($tc_student['id'], $tc_id);
				$stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split / $tc_student_number / $tc_enrolments);
				$stint_earned = $stint_earned + $stint;
			}
			$app_stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split / $tc_student_number / $tc_enrolments);
			$appointed_stint = $appointed_stint + $app_stint;
		}
		else
		{
			if($_POST['query_type'] == 'teaching_calc' OR $_POST['query_type'] == 'teaching_nohusc_calc')
			{
				if(in_array($au_d_id , $post_dept_ids)) 
				{
					if ($au_d_id == $p_d_id) $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $enrolment_split;
					else $stint_earned = 0;
				}
				else 
				{
					$stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
				}
			}
			else $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;

			$app_stint = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
			$appointed_stint = $appointed_stint + $app_stint;
		}

		$row['Stint Obligation Split'] = number_format($app_split,2);
		$row['AU | TC Students'] = "$au_student_number | $tc_student_number";
//		$row['Gross Stint '] = number_format($stint_earned/$app_split,2);
//		$row['Appointed Stint '] = number_format($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split,2);
		$row['Appointed Stint '] = number_format($appointed_stint,2);
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
function amend_teaching_table0($table, $dept_code)
//	calculate the Stint Obligation Split for a given supervisor and academic year and use this to compute the Actual Stint
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
		$app_split = get_appointment_split($e_id, $p_d_id, $t_id);				//	get the appointment split
		
		$post_dept_ids = get_post_dept_ids($e_id, $t_id);					//	get the IDs of all posts that a given emplyee has in a given term
		
		$au_student_number = get_au_student_number($au_id);					//	get the number of students enrolled into a given AU 
		$tc_student_number = get_tc_student_number($tc_id);					//	get thenumber of students enrolled into ALL AU related to a given TC
		$enrolment_split = 1;
		if($tc_student_number > 0) $enrolment_split = $au_student_number / $tc_student_number;

//		$tc_students = get_tc_students($tc_id);						//	get thenumber of students enrolled into ALL AU related to a given TC
		$tc_students = get_tcau_students($tc_id, $au_id);						//	get thenumber of students enrolled into ALL AU related to a given TC

		$stint_earned = 0;
		
		if($tc_students > 0) foreach($tc_students AS $tc_student)
		{
//dprint($row['Unit Title'] . ' : ' .$tc_student['surname'] . ' - ' . $stint_earned);			
			
			if($_POST['query_type'] == 'teaching_calc')
			{
				if(in_array($au_d_id , $post_dept_ids))
				{
					if ($au_d_id == $p_d_id)
					{
						$tc_enrolments = get_tc_enrollments($tc_student['id'], $tc_id);
						$stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 / $tc_student_number / $tc_enrolments);
//dprint("AU Code = ".$row['Unit Code']." | stint = $stint || app_split = $app_split | enrolment_split = $enrolment_split | tc_student_number = $tc_student_number |  tcau_student_number = $tcau_student_number |tc_enrolments = $tc_enrolments");
						$stint_earned = $stint_earned + $stint;
					}
				}
				else
				{
					$tc_enrolments = get_tc_enrollments($tc_student['id'], $tc_id);
					$stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split / $tc_student_number / $tc_enrolments);
					$stint_earned = $stint_earned + $stint;
				}
				
			}
			else
			{
				$tc_enrolments = get_tc_enrollments($tc_student['id'], $tc_id);
				$stint = ($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split / $tc_student_number / $tc_enrolments);
				$stint_earned = $stint_earned + $stint;
			}
		}
		else
		{
			if($_POST['query_type'] == 'teaching_calc' OR $_POST['query_type'] == 'teaching_nohusc_calc')
			{
				if(in_array($au_d_id , $post_dept_ids)) 
				{
					if ($au_d_id == $p_d_id) $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $enrolment_split;
					else $stint_earned = 0;
				}
				else 
				{
					$stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
				}
			}
			else $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
		}

		$row['Stint Obligation Split'] = number_format($app_split,2);
		$row['AU | TC Students'] = "$au_student_number | $tc_student_number";
//		$row['Gross Stint '] = number_format($stint_earned/$app_split,2);
		$row['Appointed Stint '] = number_format($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split,2);
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
function amend_teaching_table00($table, $dept_code)
//	calculate the Stint Obligation Split for a given supervisor and academic year and use this to compute the Actual Stint
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
		$app_split = get_appointment_split($e_id, $p_d_id, $t_id);		//	get the appointment split
		
		$post_dept_ids = get_post_dept_ids($e_id, $t_id);				//	get the IDs of all posts that a given emplyee has in a given term
		
		$au_students = get_au_student_number($au_id);						//	get the number of students enrolled into a given AU 
		$tc_students = get_tc_student_number($tc_id);						//	get thenumber of students enrolled into ALL AU related to a given TC
		$enrolment_split = 1;
		if($tc_students > 0) $enrolment_split = $au_students / $tc_students;
		
//	if one of the new report options is used do this
		if($_POST['query_type'] == 'teaching_calc' OR $_POST['query_type'] == 'teaching_nohusc_calc')
		{
			if(in_array($au_d_id , $post_dept_ids)) 
			{
				if ($au_d_id == $p_d_id) $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $enrolment_split;
				else $stint_earned = 0;
			}
			else 
			{
				$stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
			}
		}
		else $stint_earned = $row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split * $enrolment_split;
		
		$row['Stint Obligation Split'] = number_format($app_split,2);
		$row['AU | TC Students'] = "$au_students | $tc_students";
//		$row['Gross Stint '] = number_format($stint_earned/$app_split,2);
		$row['Appointed Stint '] = number_format($row['Tariff'] * $row['Sessions'] * $row['Percentage'] / 100 * $app_split,2);
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