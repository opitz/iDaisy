<?php

//==================================================================================================
//
//	Separate file with concordat supervision report function
//
//	Last changes: 2013-01-22
//==================================================================================================

function concordat_supervision_report_version()
//	returns the version number
{	
	return "130122.1";
}

//--------------------------------------------------------------------------------------------------------------
function concordat_supervision_report0()
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	here comes the query

$query = "
SELECT
p_d.department_name AS 'Supervisor Dept',
e.fullname AS 'Supervisor',
p.dept_stint_obligation,
#(SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id AND p.person_status = 'ACTV') AS 'TotalStint',
(SELECT SUM(dept_stint_obligation) FROM Post p2 WHERE p2.employee_id = e.id AND p2.startdate < t.startdate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t.enddate, 1=1)) AS 'TotalStint ta',

IF((SELECT COUNT(*) FROM Post p2 WHERE p2.employee_id = e.id AND p2.person_status = 'ACTV') > 0, IF((SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id AND p.person_status = 'ACTV') > 0, p.dept_stint_obligation / (SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id AND p.person_status = 'ACTV'), 1), IF((SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id) > 0, p.dept_stint_obligation / (SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id), 1)) AS 'Appointment Split',

e.opendoor_employee_code AS 'Employee Number',
p.person_status AS 'Status',

(SELECT COUNT(*) FROM Post WHERE employee_id = e.id) AS 'Post #',
IF((SELECT COUNT(*) FROM Post WHERE employee_id = e.id AND p.person_status = 'ACTV') > 0, 'ACTV', 'LEFT') AS 'Post-Status',
p.enddate,
t.term_code AS 'Term',
CONCAT(st.surname,', ',st.forename) AS 'Student'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id
INNER JOIN Post p ON p.employee_id = e.id
INNER JOIN Department p_d ON p_d.id = p.department_id

INNER JOIN Student st ON st.id = sv.student_id
#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id

WHERE 1 = 1
#AND e.fullname LIKE 'Robinson MA%'
AND t.academic_year_id = $ay_id
AND IF((SELECT COUNT(*) FROM Post p2 WHERE p2.employee_id = e.id AND p2.person_status = 'ACTV') > 0, p.person_status = 'ACTV', 1=1)

#AND IF((SELECT COUNT(*) FROM Post p2 WHERE p2.employee_id = e.id AND p2.person_status = 'ACTV') > 0, IF((SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id AND p.person_status = 'ACTV') > 0, p.dept_stint_obligation / (SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id AND p.person_status = 'ACTV'), 1), IF((SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id) > 0, p.dept_stint_obligation / (SELECT SUM(dept_stint_obligation) FROM Post WHERE employee_id = e.id), 1)) = 1
#AND (SELECT COUNT(*) FROM Post WHERE employee_id = e.id) > 1
#AND p.person_status = 'LEFT'

		";
if ($dept_id) $query = $query . "
AND p_d.id = $dept_id 
";
else $query = $query . "
AND p_d.department_code LIKE '$department_code%'
";

$query = $query . "
ORDER BY p_d.department_name, e.fullname, st.surname,st.forename, t.startdate
#LIMIT 100
";
if(isset($_POST['show_query'])) d_print($query);
return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function concordat_supervision_report()
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	here comes the query

$query = "
SELECT DISTINCT
e.id AS 'E_ID',
t.id AS 'T_ID',
p_d.department_name AS 'Supervisor Dept',
e.fullname AS 'Supervisor',

e.opendoor_employee_code AS 'Employee Number',
p.person_status AS 'Status',

IF(p.manual = 1, 'Yes', '') AS 'Manual',
dp_d.department_name AS 'Student Dept',
CONCAT(st.surname,', ',st.forename) AS 'Student',
dp.degree_programme_code AS 'Programme Code',
dp.title AS 'Title',
sdp.status AS 'Prog Status',
svt.supervision_type_code AS 'Type',
svtt.stint AS 'Supervision Stint',
sv.percentage AS 'Supervision Percent',
t.term_code AS 'Term',
sdp.oxford_graduate_year AS 'OGY'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id
INNER JOIN Post p ON p.employee_id = e.id
INNER JOIN Department p_d ON p_d.id = p.department_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

INNER JOIN Student st ON st.id = sv.student_id
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = t.academic_year_id AND sdp.status = 'ENROLLED'
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id AND dp.degree_programme_type != 'UGRAD'
INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id AND dpd.year_of_programme = 1 AND dpd.academic_year_id = t.academic_year_id AND dpd.percentage = 100
LEFT JOIN Department dp_d ON dp_d.id = dpd.department_id


WHERE 1 = 1
AND t.academic_year_id = $ay_id
#AND IF((SELECT COUNT(*) FROM Post p2 WHERE p2.employee_id = e.id AND p2.person_status = 'ACTV') > 0, p.person_status = 'ACTV', 1=1)
AND (

SELECT COUNT(*) FROM Post p2, Term t2 WHERE t2.id = t.id AND p2.employee_id = e.id AND p2.startdate < t2.startdate AND IF(YEAR(p2.enddate) > 1980, p2.enddate > t2.enddate, 1=1)

) > 0


		";
/*
	if ($dept_id) $query = $query . "
		AND (p_d.id = $dept_id OR dp_d.id = $dept_id) 
		";
	else $query = $query . "
		AND (p_d.department_code LIKE '$department_code%' OR dp_d.department_code LIKE '$department_code%') 
		";
*/
	$query = $query . "
		AND (p_d.department_code LIKE '$department_code%' OR dp_d.department_code LIKE '$department_code%') 
		";

	$query = $query . "
		ORDER BY p_d.department_name, e.fullname, st.surname,st.forename, t.startdate
		#LIMIT 100
		";
//d_print($query);
	return amend_table(get_data($query), $department_code);
}

//--------------------------------------------------------------------------------------------------------------
function amend_table($table, $dept_code)
//	calculate the Appointment Split for a given supervisor and academic year and use this to compute the Actual Stint
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	
	$new_table = array();
	foreach ($table AS $row)
	{
		$e_id = array_shift($row);	// get the employee id
		$t_id = array_shift($row);	// get the term id

		$ogy = $row['OGY'];
		$app_split = get_appointment_split($e_id, $dept_code, $t_id);
		$row['Appointment Split'] = number_format($app_split * 100, 2);
		
		$factor = 1;
		if ($ogy > 4) $factor = 0;
		if ($ogy == 4) $factor = 0.5;
		
		$row['Actual Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $factor, 2);
		$new_table[] = $row;
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function get_appointment_split($e_id, $dept_code, $t_id)
//	return the percentage of ownership for a given employee, department and term
{
	$stint_dept = get_dept_stint($e_id, $dept_code);
	$stint_total = get_term_stint_total($e_id, $t_id);
	
	$app_split = 1;
	if($stint_dept > 0 AND $stint_total > 0) $app_split = $stint_dept / $stint_total;
	
	return $app_split;
}

//--------------------------------------------------------------------------------------------------------------
function get_appointment_split0($e_id, $dept_id, $t_id)
//	return the percentage of ownership for a given employee, department and term
{
	$stint_dept = get_dept_stint($e_id, $dept_id);
	$stint_total = get_term_stint_total($e_id, $t_id);
	
	$app_split = 1;
	if($stint_dept > 0 AND $stint_total > 0) $app_split = $stint_dept / $stint_total;
	
	return $app_split;
}

//--------------------------------------------------------------------------------------------------------------
function get_appointment_split00($e_id, $dept_id)
//	return the percentage of ownership for a given department and postholder
{
	$query = "
		SELECT
		SUM(p.dept_stint_obligation) AS 'P_STINT'
		FROM Post p
		WHERE 1=1
		AND p.person_status = 'ACTV'
		AND p.department_id = $dept_id
		AND p.employee_id = $e_id
	";
	$result = get_data($query);
	$p_stint = $result[0]['P_STINT'];
	
	$query = "
		SELECT
		SUM(p.dept_stint_obligation) AS 'ALL_STINT'
		FROM Post p
		WHERE 1=1
		AND p.person_status = 'ACTV'
		AND p.employee_id = $e_id
	";
	$result = get_data($query);
	$all_stint = $result[0]['ALL_STINT'];
	
	return $p_stint / $all_stint;
}

//--------------------------------------------------------------------------------------------------------------
function get_dept_stint($e_id, $dept_code)
//	returns the stint obligation for a given employee and department
{
	$query = "
		SELECT
			p.dept_stint_obligation AS 'Stint'
		FROM 
			Post p INNER JOIN Department d ON d.id = p.department_id
		WHERE 1=1
		AND p.employee_id = $e_id
		AND d.department_code LIKE '$dept_code%'
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Stint'];
}

//--------------------------------------------------------------------------------------------------------------
function get_dept_stint0($e_id, $dept_id)
//	returns the stint obligation for a given employee and department
{
	$query = "
		SELECT
			p.dept_stint_obligation AS 'Stint'
		FROM 
			Post p
		WHERE 1=1
		AND p.employee_id = $e_id
		AND p.department_id = $dept_id
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
		AND IF(YEAR(p.enddate) > 1980, p.enddate > t.enddate, 1=1)
		AND t.id = $t_id	
	";
//d_print($query);
	$resulttable = get_data($query);
	
	$result = $resulttable[0];
	return $result['Stint'];
}

?>