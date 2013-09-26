<?php

//==================================================================================================
//
//	Separate file with concordat student load function
//
//	Last changes: 2013-04-04
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function student_load_report()
//	list degree programmes and their ownmership by departments
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

//	here comes the query

	$query = "
SELECT 
dp.degree_programme_code AS 'DP Code', 
dp.title AS 'Degree Programme', 
d.department_code AS 'Dept Code', 
d.department_name AS 'Department', 
dpd.percentage AS 'Percentage' 

FROM DegreeProgrammeDepartment dpd 
INNER JOIN DegreeProgramme dp ON dp.id = dpd.degree_programme_id 
INNER JOIN Department d ON d.id = dpd.department_id 

WHERE 1=1 
AND dpd.academic_year_id = $ay_id 
AND dpd.year_of_programme = 1 
AND d.department_code LIKE '$department_code%'
ORDER BY dp.degree_programme_code, d.department_code 
";

//d_print($query);
	return get_data($query);
}


?>