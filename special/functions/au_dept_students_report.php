<?php

//==================================================================================================
//
//	Special Report - List Students by Degree Programme Departments enrolled into Assessment Units
//
//	Last changes: 2013-02-15
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function au_dept_students_report()
{
	$ay_id = $_POST['ay_id'];							// get academic_year_id
	$department_code = $_POST['department_code'];					// get department code
	$dept_id = get_dept_id($department_code);					// get department id

	$table = au_by_owner($department_code);	
//	$table = stint_per_au($table);
//	$table = students_per_au($table);
	$table =  dept_by_au_dept($table);						// get all possible departments and amend each $row of the $table accordingly
//	$table = dp_students_per_au($table);


//	$table = amend_au_by_owner_data($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function dept_by_au_dept($table)
// amende the $table by - empty - columns for each department owning any degree programme that have students in any of the assessment units given in $table
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$depts = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];

		$au_depts = depts_per_au($au_id);
		if($au_depts) foreach($au_depts AS $dept)
		{
			$depts[$dept['Department']] = '';
		}		
	}

	$new_table = array();
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
		$row = array_merge($row, $depts);
		
		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function depts_per_au($au_id)
// returns all departments owning a degree programmes that have students in a given assessment unit
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
SELECT DISTINCT
d.department_name AS 'Department'

FROM AssessmentUnit au
INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id AND dpd.academic_year_id = sau.academic_year_id AND dpd.year_of_programme = 1
INNER JOIN Department d ON d.id = dpd.department_id

WHERE au.id = $au_id
ORDER BY d.department_name		
";
//d_print($query);

//	$result = get_data($query);
	return $result;
}






?>