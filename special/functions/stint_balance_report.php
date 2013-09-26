<?php

//==================================================================================================
//
//	Special Report - List all Students from other departments supervised by staff from the selected department
//
//	Last changes: 2013-02-06
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function stint_balance_report()
{

//	1. Teaching
//	1.1	get teaching by post dept for all units of a given department

	$table = teaching_by_post_dept();
	
//	1.2 get teaching by unit dept for a given post dept

//$table = amend_external_supervision_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function teaching_by_post_dept()
//	1.1	get teaching by post dept for all units of a given department
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];									// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);				// get department id

	$query = "
SELECT
CONCAT(d.department_name,' (',d.department_code,')') AS 'Dept.',

FORMAT(SUM(ti.sessions * ti.percentage / 100 * IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) * IF(d.id = au_d.id, 1,  IF((SELECT COUNT(*) FROM Post p3 WHERE p3.employee_id = e.id AND p3.department_id = au_d.id AND p3.startdate < t.enddate AND IF(YEAR(p3.enddate) > 1980, p3.enddate > t.startdate, 1=1)) >0,0,IF((SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) > 0, p.dept_stint_obligation / (SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))), 1)))),2) AS 'Stint'


FROM AssessmentUnit au
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id

INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Post p ON p.employee_id = e.id AND (p.startdate < t.enddate AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1))
INNER JOIN Department d ON d.id = p.department_id

WHERE 1=1
AND au.assessment_unit_code != 'ZOM99999'
AND au_d.department_code LIKE '$department_code%' 
AND tcau.academic_year_id = $ay_id

GROUP BY d.department_name
ORDER BY d.department_name

	";
d_print($query);
	$table =get_data($query);

//$table = amend_external_supervision_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function teaching_by_post_dept0()
//	1.1	get teaching by post dept for all units of a given department
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];									// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);				// get department id

	$query = "
SELECT
CONCAT(d.department_name,' (',d.department_code,')') AS 'Dept.',

FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint * IF(d.id = au_d.id, 1,  IF((SELECT COUNT(*) FROM Post p3 WHERE p3.employee_id = e.id AND p3.department_id = au_d.id AND p3.startdate < t.enddate AND IF(YEAR(p3.enddate) > 1980, p3.enddate > t.startdate, 1=1)) >0,0,IF((SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) > 0, p.dept_stint_obligation / (SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))), 1)))),2) AS 'Stint'


FROM AssessmentUnit au
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id

INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Post p ON p.employee_id = e.id AND (p.startdate < t.enddate AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1))
INNER JOIN Department d ON d.id = p.department_id

WHERE 1=1
AND au.assessment_unit_code != 'ZOM99999'
AND au_d.department_code LIKE '$department_code%' 
AND tcau.academic_year_id = $ay_id

GROUP BY d.department_name
ORDER BY d.department_name

	";
d_print($query);
	$table =get_data($query);

//$table = amend_external_supervision_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function teaching_by_post_dept_raw()
//	1.1	get teaching by post dept for all units of a given department
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];									// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);				// get department id

	$query = "
SELECT
au.assessment_unit_code AS 'AU Code',
au.title AS 'Assessment Unit',
tc.subject AS 'Subject',
tc.sessions_planned AS 'Norm',
tct.title AS 'Type',
IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) AS ' Tariff',
t.term_code AS 'Term',
ti.sessions AS 'Sessions',
ti.percentage AS 'Perc.',
e.fullname AS 'Lecturer',
d.department_name AS 'Dept.',
p.dept_stint_obligation AS 'Obl.',
(SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) AS OB_Sum,

ti.sessions * ti.percentage / 100 *IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) AS 'Stint raw',


IF(d.id = au_d.id, 1,  IF((SELECT COUNT(*) FROM Post p3 WHERE p3.employee_id = e.id AND p3.department_id = au_d.id AND p3.startdate < t.enddate AND IF(YEAR(p3.enddate) > 1980, p3.enddate > t.startdate, 1=1)) >0,0,IF((SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) > 0, p.dept_stint_obligation / (SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))), 1))) AS 'AppSplit',






ti.sessions * ti.percentage / 100 * IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) * IF(d.id = au_d.id, 1,  IF((SELECT COUNT(*) FROM Post p3 WHERE p3.employee_id = e.id AND p3.department_id = au_d.id AND p3.startdate < t.enddate AND IF(YEAR(p3.enddate) > 1980, p3.enddate > t.startdate, 1=1)) >0,0,IF((SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) > 0, p.dept_stint_obligation / (SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))), 1))) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id

INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Post p ON p.employee_id = e.id AND (p.startdate < t.enddate AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1))
INNER JOIN Department d ON d.id = p.department_id

WHERE 1=1
AND au_d.department_code LIKE '$department_code%' 
AND tcau.academic_year_id = $ay_id

ORDER BY au.assessment_unit_code, tc.subject, t.startdate
LIMIT 1000

	";
d_print($query);
	$table =get_data($query);

//$table = amend_external_supervision_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function teaching_by_post_dept_raw0()
//	1.1	get teaching by post dept for all units of a given department
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];									// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);				// get department id

	$query = "
SELECT
au.assessment_unit_code AS 'AU Code',
au.title AS 'Assessment Unit',
tc.subject AS 'Subject',
tc.sessions_planned AS 'Norm',
tct.title AS 'Type',
tctt.stint AS ' Tariff',
t.term_code AS 'Term',
ti.sessions AS 'Sessions',
ti.percentage AS 'Perc.',
e.fullname AS 'Lecturer',
d.department_name AS 'Dept.',
p.dept_stint_obligation AS 'Obl.',
(SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) AS OB_Sum,

ti.sessions * ti.percentage / 100 * tctt.stint AS 'Stint raw',


IF(d.id = au_d.id, 1,  IF((SELECT COUNT(*) FROM Post p3 WHERE p3.employee_id = e.id AND p3.department_id = au_d.id AND p3.startdate < t.enddate AND IF(YEAR(p3.enddate) > 1980, p3.enddate > t.startdate, 1=1)) >0,0,IF((SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) > 0, p.dept_stint_obligation / (SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))), 1))) AS 'AppSplit',






ti.sessions * ti.percentage / 100 * tctt.stint * IF(d.id = au_d.id, 1,  IF((SELECT COUNT(*) FROM Post p3 WHERE p3.employee_id = e.id AND p3.department_id = au_d.id AND p3.startdate < t.enddate AND IF(YEAR(p3.enddate) > 1980, p3.enddate > t.startdate, 1=1)) >0,0,IF((SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))) > 0, p.dept_stint_obligation / (SELECT SUM(p2.dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND (p2.startdate < t.enddate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.startdate, 1=1))), 1))) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id

INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Post p ON p.employee_id = e.id AND (p.startdate < t.enddate AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1))
INNER JOIN Department d ON d.id = p.department_id

WHERE 1=1
AND au_d.department_code LIKE '$department_code%' 
AND tcau.academic_year_id = $ay_id

ORDER BY au.assessment_unit_code, tc.subject, t.startdate
LIMIT 1000

	";
d_print($query);
	$table =get_data($query);

//$table = amend_external_supervision_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function x0000()
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

	$query = "
SELECT
e_d.department_name,
e.fullname,
t.term_code AS 'Term',
CONCAT(st.surname,', ',st.forename) AS 'Student',
dp.title AS 'Degree Programme',
sdp.status AS 'Status'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id
INNER JOIN Post p ON p.employee_id = e.id AND (p.startdate < t.startdate AND IF(YEAR(p.enddate) > '1980', p.enddate >= t.startdate, 1=1))
INNER JOIN Department e_d ON e_d.id = p.department_id

INNER JOIN Student st ON st.id = student_id
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = t.academic_year_id
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id

INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id 

WHERE 1=1
AND t.academic_year_id = $ay_id
AND (
          SELECT COUNT(*) 
          FROM DegreeProgrammeDepartment 
          WHERE 1=1 
          AND academic_year_id = t.academic_year_id
          AND degree_programme_id = dp.id
          AND department_id = p.department_id
        ) = 0
	";
	if ($dept_id) $query = $query . "AND e_d.id = $dept_id ";
	else $query = $query . "AND e_d.department_code LIKE '$department_code' ";
	$query = $query . "
		ORDER BY e.fullname, st.surname, st.forename, t.startdate 
#		LIMIT 10
	";

	$table =get_data($query);

//$table = amend_external_supervision_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_external_supervision_data($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
//		$e_id = $row['id'];
		$e_fullname = addslashes($row['fullname']);
		$employee_number = $row['opendoor_employee_code'];

		if(duplicate_name($e_fullname, $employee_number)) $new_table[] = $row;		
	}
	return $new_table;
}

?>