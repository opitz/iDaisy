<?php

//==================================================================================================
//
//	Special Report - Find Duplicate Employee Names 
//
//	Last changes: 2013-01-31
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function duplicate_names_report()
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	This is a 2-step performance
//	1st we select all assessment units (of a given department) and their related degree programmes for a given academic year
//$table = get_duplicate_fullnames($dept_id, $ay_id);
$table = get_duplicate_fullnames();

//	2nd we amend the data in the table with the nr.of students and if the degree programme is joint owned
//$table = amend_data($table);
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_duplicate_fullnames()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT 
e.fullname, 
e.surname AS 'Surname',
e.forename AS 'Forename',
d.department_name AS 'Department',
e.email AS 'Email',
e.status AS 'Status',
e.opendoor_employee_code AS 'Employee Number',
e.old_opendoor_employee_code AS 'Old Employee Number'

FROM Employee e
INNER JOIN Department d ON d.id = e.department_id 
INNER JOIN 
(
	SELECT e_dup.surname, e_dup.forename 
	FROM Employee e_dup
	INNER JOIN Department d_dup ON d_dup.id = e_dup.department_id
	WHERE d_dup.department_code LIKE '$department_code%'
	GROUP BY e_dup.surname, e_dup.forename HAVING COUNT(e_dup.id) > 1
) dup ON e.surname = dup.surname AND e.forename = dup.forename

WHERE 1=1 
#AND d.department_code LIKE '$department_code%'

ORDER BY e.fullname
	";
	if(isset($_POST['show_query'])) d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function get_duplicate_fullnames0($dept_id, $ay_id)
{
	$dept_code = $_POST['department_code'];
	$query = "
SELECT 
e.fullname, 
e.surname AS 'Surname',
e.forename AS 'Forename',
d.department_name AS 'Department',
e.email AS 'Email',
e.status AS 'Status',
e.opendoor_employee_code AS 'Employee Number',
e.old_opendoor_employee_code AS 'Old Employee Number'

FROM Employee e
INNER JOIN Department d ON d.id = e.department_id 
INNER JOIN 
(
	SELECT surname, forename 
	FROM Employee
	GROUP BY surname, forename HAVING COUNT(id) > 1
) dup ON e.surname = dup.surname AND e.forename = dup.forename

WHERE 1=1 
#AND e.email !='' ";
if($dept_id) $query = $query . "AND d.id = $dept_id";
else $query = $query . "AND d.department_code LIKE '$dept_code%' ";
$query = $query . "
ORDER BY e.fullname
	";
	if(isset($_POST['show_query'])) d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function get_employees($dept_id, $ay_id)
{
	$query = "
		SELECT 
			e.fullname,
			e.surname,
			e.forename,
			e.opendoor_employee_code,
			e.status,
			d.department_name

		FROM Employee e
		INNER JOIN Department d ON d.id = e.department_id

		WHERE 1=1 
	";
	if ($dept_id) $query = $query . "
		AND e.department_id = $dept_id 
	";
	$query = $query . "
		ORDER BY e.fullname
#		LIMIT 10
	";

//	d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function amend_data($table)
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

//--------------------------------------------------------------------------------------------------------------
function duplicate_name($e_fullname, $employee_number)
//	returns TRUE if there is an employee with the same name but a different Employee Number
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(*) AS 'Count'
		FROM Employee e
		WHERE 1=1
		AND e.fullname = '$e_fullname'
		AND e.opendoor_employee_code != '$employee_number'
	";
	
	$result = get_data($query);
	if($result[0]['Count'] > 1) return TRUE;
	else return FALSE;
}

?>