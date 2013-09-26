<?php

//==================================================================================================
//
//	Special Report - List Assessment Units by Owner
//
//	Last changes: 2013-01-31
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function au_by_owner_report()
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//$table = au_by_owner($dept_id, $ay_id);
//$table = au_by_owner($department_code, $ay_id);
$table = au_by_owner($department_code);
$table = stint_per_au($table);

//$table = amend_au_by_owner_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function au_by_owner($department_code)
{
	$query = "
		SELECT 
		au.id AS 'AU_ID',
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
			au.assessment_unit_code AS 'Code',
			au.title AS 'Assessment Unit / PGR Module'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id

		WHERE 1=1 
		AND au.id  != 99999
		AND d.department_code LIKE '$department_code%'
		ORDER BY d.department_code, au.assessment_unit_code
#		LIMIT 10
	";

//	d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function au_by_owner0($department_code, $ay_id)
{
	$query = "
		SELECT 
		au.id AS 'AU_ID',
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
			au.assessment_unit_code AS 'Code',
			au.title AS 'Assessment Unit / PGR Module'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id

		WHERE 1=1 
		AND au.id  != 99999
		AND d.department_code LIKE '$department_code%'
		ORDER BY d.department_code, au.assessment_unit_code
#		LIMIT 10
	";

//	d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function au_by_owner00($dept_id, $ay_id)
{
	$query = "
		SELECT 
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
			au.assessment_unit_code AS 'Code',
			au.title AS 'Assessment Unit / PGR Module'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id

		WHERE 1=1 
	";
	if ($dept_id) $query = $query . "
		AND d.id = $dept_id 
	";
	$query = $query . "
#		ORDER BY d.department_code, au.title
		ORDER BY d.department_code, au.assessment_unit_code
#		LIMIT 10
	";

//	d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function amend_au_by_owner_data($table)
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