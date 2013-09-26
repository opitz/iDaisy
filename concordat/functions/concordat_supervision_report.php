<?php

//==================================================================================================
//
//	Separate file with concordat supervision report function
//
//	Last changes: 2013-07-29
//==================================================================================================

function concordat_supervision_report_version()
//	returns the version number
{	
//	return "130320.1";
//	return "130404.1";		//	added 'Appointed Stint', removed 'Gross Stint'
	return "130507.1";		//	added 'OSS ID'
}

//--------------------------------------------------------------------------------------------------------------
function concordat_supervision_calc_report()
{
	$ay_id = $_POST['ay_id'];														// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	here comes the query
//	get the list of supervisions for the selected department(s)
$query = "
SELECT DISTINCT
e.id AS 'E_ID',
t.id AS 'T_ID',
p_d.id AS 'P_D_ID',
dp_d.id AS 'ST_D_ID',
CONCAT(p_d.department_code,'-',p_d.department_name) AS 'Supervisor Dept',
e.fullname AS 'Supervisor',
'' AS 'Stint Opbligation Split',
e.opendoor_employee_code AS 'Employee Number',
p.person_status AS 'Status',

IF(p.manual = 1, 'Yes', '') AS 'Manual',
CONCAT(dp_d.department_code,'-',dp_d.department_name) AS 'Student Dept',
CONCAT(st.surname,', ',st.forename) AS 'Student',
st.oss_student_code AS 'OSS ID',
dp.degree_programme_code AS 'Programme Code',
dp.title AS 'Title',
svt.supervision_type_code AS 'Type',
sdp.status AS 'Prog Status',
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
AND p.startdate < t.enddate
AND p.post_number NOT LIKE 'C%' 
AND IF(YEAR(p.enddate) > 1980, p.enddate > t.startdate, 1=1)
AND t.academic_year_id = $ay_id
		";

	$query = $query . "
		AND (p_d.department_code LIKE '$department_code%' OR dp_d.department_code LIKE '$department_code%') 
		";

	$query = $query . "
		ORDER BY p_d.department_name, e.fullname, st.surname,st.forename, t.startdate
		#LIMIT 100
		";
//d_print($query);
//	return get_data($query);
	return amend_supervision_calc_table(get_data($query));
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
p_d.id AS 'P_D_ID',
p_d.department_name AS 'Supervisor Dept',
e.fullname AS 'Supervisor',
'' AS 'Stint Obligation Split',
e.opendoor_employee_code AS 'Employee Number',
p.person_status AS 'Status',

IF(p.manual = 1, 'Yes', '') AS 'Manual',
dp_d.department_name AS 'Student Dept',
CONCAT(st.surname,', ',st.forename) AS 'Student',
st.oss_student_code AS 'OSS ID',
dp.degree_programme_code AS 'Programme Code',
dp.title AS 'Title',
svt.supervision_type_code AS 'Type',
sdp.status AS 'Prog Status',
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
AND p.post_number NOT LIKE 'C%' 
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
	return amend_supervision_table(get_data($query));
}

//--------------------------------------------------------------------------------------------------------------
function amend_supervision_calc_table($table)
//	calculate the Stint Obligation Split for a given supervisor and academic year and use this to compute the Actual Stint
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	
	$new_table = array();
	foreach ($table AS $row)
	{
		$e_id = array_shift($row);		// get the employee id
		$t_id = array_shift($row);		// get the term id
		$e_d_id = array_shift($row);	// get the employee dept id
		$st_d_id = array_shift($row);	// get the student dept id

		$post_dept_ids = get_post_dept_ids($e_id, $t_id);

		$ogy = $row['OGY'];
		$app_split = get_appointment_split($e_id, $e_d_id, $t_id);
		$row['Stint Obligation Split'] = number_format($app_split * 100, 2);
		
		$factor = 1;
		if ($ogy == 4) $factor = 0.5;										// if the Oxford Garduate Year = 4 50% of the stint will be given
		if ($ogy > 4) $factor = 0;										// if the Oxford Garduate Year > 4 no stint will be given
		
//		$row['Gross Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $factor, 2);
		$row['Appointed Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $app_split, 2);

		if(in_array($st_d_id , $post_dept_ids) AND $row['Type'] != 'DPhil')	//	if the student department is one of the post departments and the sv type is not 'DPhil' account 100% of the stint to the  post department
		{
			if($e_d_id == $st_d_id)
			{
				$row['Actual Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $factor, 2);
			}
			else	
			{
				$row['Actual Stint'] = number_format(0,2);
			}
		}
		else 															// otherwise split the stint according to the appointment split
			$row['Actual Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $app_split * $factor, 2);
		$new_table[] = $row;
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_supervision_table($table)
//	calculate the Stint Obligation Split for a given supervisor and academic year and use this to compute the Actual Stint
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	
	$new_table = array();
	foreach ($table AS $row)
	{
		$e_id = array_shift($row);	// get the employee id
		$t_id = array_shift($row);	// get the term id
		$d_id = array_shift($row);	// get the dept id

		$ogy = $row['OGY'];
		$app_split = get_appointment_split($e_id, $d_id, $t_id);
		$row['Stint Obligation Split'] = number_format($app_split * 100, 2);
		
		$factor = 1;
		if ($ogy > 4) $factor = 0;
		if ($ogy == 4) $factor = 0.5;
		
//		$row['Gross Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $factor, 2);
		$row['Appointed Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $app_split, 2);
		$row['Actual Stint'] = number_format($row['Supervision Stint'] * $row['Supervision Percent'] / 300 * $app_split * $factor, 2);
		$new_table[] = $row;
	}
	return $new_table;
}

?>