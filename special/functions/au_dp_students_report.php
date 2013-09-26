<?php

//==================================================================================================
//
//	Special Report - List Assessment Units by Owner
//
//	Last changes: 2013-02-14
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function au_dp_students_reportx()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = au_by_owner2($department_code, $ay_id);
	$table =  dp_by_au_dept1($table);

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function au_dp_students_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

//	$table = au_by_owner($department_code, $ay_id);	
	$table = au_by_owner($department_code);	
	$table = stint_per_au($table);
	$table = amend_students_per_au($table);
	$table =  dp_by_au_dept($table);						// get all possible degree programmes and amend each $row of the $table accordingly
	$table = amend_dp_students_per_au($table);

	$table = add_au_link($table);


//	$table = amend_au_by_owner_data($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function dp_by_au_dept($table)
//	returns all degree programmes that have students in any assessment unit provided in $table
//	returns all owning departments of degree programmes that have students in any assessment unit owned by a given division or department
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$depts = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];

		$au_dp = dp_per_au($au_id);
		if($au_dp) foreach($au_dp AS $dp)
		{
			$depts[$dp['Degree Programme']] = '';
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
function amend_students_per_au0($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
//	get the total number of students enrolled into each assessment unit
		$query = "
			SELECT COUNT(sau.student_id) AS 'Students' 
			FROM StudentAssessmentUnit sau
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
			WHERE sau.academic_year_id = $ay_id 
			AND sau.assessment_unit_id = $au_id";
//d_print($query);

		$result = get_data($query);
		$row['Students'] = $result[0]['Students'];


		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_students_per_au($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$row['Students'] = students_per_au($row['AU_ID']);

		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function students_per_au($au_id)
//	get the total number of students enrolled into each assessment unit
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(sau.student_id) AS 'Students' 
		FROM StudentAssessmentUnit sau
		INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
		WHERE sau.academic_year_id = $ay_id 
		AND sau.assessment_unit_id = $au_id
	";
//d_print($query);

	$result = get_data($query);
	return $result[0]['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function amend_dp_students_per_au($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
//	get the total number of students enrolled into each assessment unit

		$result = dp_students_per_au($au_id);
		if ($result) foreach($result AS $rrow)
		{
			$row[$rrow['Degree Programme']] = $rrow['DP Students'];
		}

		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_dp_students_per_au0($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
//	get the total number of students enrolled into each assessment unit
		$query = "
SELECT
dp.title AS 'Degree Programme',
COUNT(DISTINCT sau.student_id) AS 'DP Students'

FROM AssessmentUnit au
INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id

WHERE au.id = $au_id
GROUP BY dp.id
ORDER BY dp.title		";
//d_print($query);

		$result = get_data($query);
		if ($result) foreach($result AS $rrow)
		{
			$row[$rrow['Degree Programme']] = $rrow['DP Students'];
		}


		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function dp_students_per_au($au_id)
//	get the number of students enrolled into each assessment unit by degree programme
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT
		dp.title AS 'Degree Programme',
		COUNT(DISTINCT sau.student_id) AS 'DP Students'

		FROM AssessmentUnit au
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
		INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
		INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id

		WHERE au.id = $au_id
		GROUP BY dp.id
		ORDER BY dp.title
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function dp_per_au($au_id)
// returns all degree programmes that have students in a given assessment unit
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

//	get the total number of students enrolled into each assessment unit
	$query = "
SELECT DISTINCT
dp.title AS 'Degree Programme'

FROM AssessmentUnit au
INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id

WHERE au.id = $au_id
ORDER BY dp.title		
";
//d_print($query);

	$result = get_data($query);
	return $result;
}

//--------------------------------------------------------------------------------------------------------------
function stint_per_au($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
//	get the total number of students enrolled into each assessment unit
		$query = "
SELECT

SUM(IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) * ti.sessions * ti.percentage / 100) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id		";
//d_print($query);

		$result = get_data($query);
		$row['Stint'] = number_format($result[0]['Stint'],2);


		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function stint_per_au0($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
//	get the total number of students enrolled into each assessment unit
		$query = "
SELECT

SUM(tctt.stint * ti.sessions * ti.percentage / 100) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id		";
//d_print($query);

		$result = get_data($query);
		$row['Stint'] = number_format($result[0]['Stint'],2);


		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function add_au_link($table)
//	adds a link to the AU title linking to more details
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	
	$new_table = array();
	if($table) foreach ($table AS $row)
	{
		$au_id = array_shift($row);	//	get the AU ID
		if(!$_POST['excel_export'])
		{
			$row['Assessment Unit / PGR Module'] = "<A HREF="."../index.php"."?au.id=$au_id&ay_id=$ay_id&department_code=$department_code>".$row['Assessment Unit / PGR Module']."</A>";
		}
		$new_table[] = $row;
	}
	return $new_table;
}


?>