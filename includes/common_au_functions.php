<?php

//==================================================================================================
//
//	Functions on Assessment Units that are used by other scripts
//
//	13-04-08	2nd version
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function au_students($au_id)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	
	if($au_id) 
	{
		$query = "
			SELECT COUNT(sau.student_id) AS 'Students' 
			FROM StudentAssessmentUnit sau
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
			WHERE sau.academic_year_id = $ay_id 
			AND sau.assessment_unit_id = $au_id
		";
		$result = get_data($query);
		return  $result[0]['Students'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function own_au_students($au_id, $dept_id)
{
	$ay_id = $_POST['ay_id'];												// get academic_year_id
//	$department_code = $_POST['department_code'];						// get department_code
//	$dept_id = get_dept_id($department_code);
	
	if($au_id)
	{
		$query = "
			SELECT COUNT(DISTINCT sau.student_id) AS 'Students'
			FROM StudentAssessmentUnit sau 
			INNER JOIN StudentDegreeProgramme sdp ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id AND sdp.status = 'ENROLLED'
			INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.academic_year_id = sdp.academic_year_id AND year_of_programme = 1 
			WHERE sau.assessment_unit_id = $au_id 
			AND dpd.department_id = $dept_id
			AND sau.academic_year_id = $ay_id
		";
		$result = get_data($query);
		return  $result[0]['Students'];		
	} else return FALSE;
}

//==================< Graduate Training Courses >======================
//--------------------------------------------------------------------------------------------------------------
function amend_students_per_au($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$new_table = array();
	
	if($table) foreach($table as $row)
	{
		$au_id = $row['AU_ID'];
		$query = "
			SELECT COUNT(sau.student_id) AS 'Students' 
			FROM StudentAssessmentUnit sau
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
			WHERE sau.academic_year_id = $ay_id 
			AND sau.assessment_unit_id = $au_id
		";
		$result = get_data($query);
		$row['Students'] =  $result[0]['Students'];
		
		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_own_students_per_au($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$new_table = array();
	
	if($table) foreach($table as $row)
	{
		$au_id = $row['AU_ID'];
		$d_id = $row['D_ID'];
		$query = "
			SELECT COUNT(DISTINCT sau.student_id) AS 'Students'
			FROM StudentAssessmentUnit sau 
			INNER JOIN StudentDegreeProgramme sdp ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id AND sdp.status = 'ENROLLED'
			INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.academic_year_id = sdp.academic_year_id AND year_of_programme = 1 
			WHERE sau.assessment_unit_id = $au_id 
			AND dpd.department_id = $d_id
			AND sau.academic_year_id = $ay_id
		";
		$result = get_data($query);
		$row['Own Students'] =  $result[0]['Students'];
		
		$new_table[] = $row;		
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function add_au_link($table)
//	adds a link to the AU title linking to more details
{
	$ay_id = $_POST['ay_id'];														// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	
	$new_table = array();
	if($table) foreach ($table AS $row)
	{
		$au_id = $row['AU_ID'];	//			get the AU ID
		if(!$_POST['excel_export'])
		{
			$row['Assessment Unit / PGR Module'] = "<A HREF=../teaching.php?au.id=$au_id&ay_id=$ay_id&department_code=$department_code>".$row['Assessment Unit / PGR Module']."</A>";
		}
		$new_table[] = $row;
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function dp_per_au($au_id)
// returns all degree programmes that have students in a given assessment unit
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
SELECT DISTINCT
dp.id AS 'DP_ID',
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

//---------------------------< Amend Students per Degree Programme >---------------------------
//--------------------------------------------------------------------------------------------------------------
function amend_dp_students_per_au($table)
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id

	$new_table = array();
	
	if($table) 
	{
//		$table =  dp_by_au_dept($table);					// get all possible degree programmes and amend each $row of the $table accordingly
		$progs = all_programmes_involved($table);			// get all possible degree programmes and amend each $row of the $table accordingly
		foreach($table as $row)
		{		
			$au_id = $row['AU_ID'];
			$row = array_merge($row, $progs);				// amend the row with the fields for degree programmes


//	get the  number of students enrolled into each assessment unit by degree programme
			$dp_students = dp_students_per_au($au_id);
			if ($dp_students) foreach($dp_students AS $dp_student)
			{
				$row[$dp_student['Degree Programme']] = number_format($dp_student['DP Students'],1);
			}

			$new_table[] = $row;		
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function all_programmes_involved($table)
//	returns all degree programmes that have students in any assessment unit provided in $table
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$progs = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];

		$au_dp = dp_per_au($au_id);
		if($au_dp) foreach($au_dp AS $dp)
		{
			$progs[$dp['Degree Programme']] = '';
		}		
	}

	return $progs;
}

//--------------------------------------------------------------------------------------------------------------
function dp_students_per_au($au_id)
//	get the number of students enrolled into each assessment unit by degree programme
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT
		dp.id AS 'DP_ID',
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
function amend_term($table)
//	this will show the term(s) in which the assessment unit was taught
{
	$ay_id = $_POST['ay_id'];										// get academic_year_id
	$department_code = $_POST['department_code'];				// get department code
	
	$new_table = array();
	if($table) foreach ($table AS $row)
	{
		$au_id = $row['AU_ID'];		//	get the AU ID
		if(!$_POST['excel_export'])
		$query = "
			SELECT DISTINCT
			t.term_code AS 'Term'

			FROM AssessmentUnit au
			INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
			INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
			INNER JOIN Term t on t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

			WHERE 1=1
			AND au.id = $au_id
			AND tcau.academic_year_id = $ay_id
		
			ORDER BY t.startdate
		";
		$result = get_data($query);
		$row['Term'] = '';
		if($result) foreach($result AS $rrow)
		{
			if($row['Term'] != '') $row['Term'] = $row['Term'] . ' / ';
			$row['Term'] = $row['Term'] . $rrow['Term'];
		}

		$new_table[] = $row;
	}
	return $new_table;
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

SUM(IF(ti.stint_override > 0, ti.stint_override,IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) * ti.sessions * ti.percentage / 100) AS 'Stint'

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
function hours_per_au($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];
//	get the total number of students enrolled into each assessment unit
		$query = "
SELECT

SUM(tctt.hours * ti.sessions * ti.percentage / 100) AS 'Hours'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id		";
//d_print($query);

		$result = get_data($query);
		$row['Hours'] = number_format($result[0]['Hours'],1);


		$new_table[] = $row;		
	}
	return $new_table;
}

//-------------------< Amend Students per Degree Programme Department >-------------------
//--------------------------------------------------------------------------------------------------------------
function amend_d_students_per_au($table)
//	split each student by the percentage of owning departments of any given degree programme that has sent students into an assessment unit
{
	$ay_id = $_POST['ay_id'];									// get academic_year_id

	$new_table = array();
	
	if($table) 
	{
		$deptlist =  all_departments_involved($table);			// get all possible departments and amend each $row of the $table accordingly
		foreach($table as $row)
		{		
			$au_id = $row['AU_ID'];

			$row = array_merge($row, $deptlist);				// amend the row with the fields for departments

//	get the number of students enrolled in the assessment unit by degree programme
			$dp_students = dp_students_per_au($au_id);

			if ($dp_students) foreach($dp_students AS $dp_student)
			{
				$dp_id = $dp_student['DP_ID'];
				$dp_students = $dp_student['DP Students'];
			
				$depts = get_departments($dp_id);
			
				if ($depts) foreach($depts AS $dept)
				{
					$row[$dept['Department']] = $row[$dept['Department']] + number_format($dept['Percentage'] / 100 *  $dp_student['DP Students'],1);
				}			
			}
			$new_table[] = $row;		
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function all_departments_involved($table)
//	amends owning departments of degree programmes that have students in any assessment unit owned by a given division or department
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$deptlist = array();
	
	if($table) foreach($table as $row)
	{		
		$au_id = $row['AU_ID'];

//	1st get the IDs of the degree programme enrolled into the given assessment unit
		$au_dp = dp_per_au($au_id);
		if($au_dp) foreach($au_dp AS $dp)
		{
			$dp_id = $dp['DP_ID'];
			
			$depts = get_departments($dp_id);
			if($depts) foreach($depts AS $dept)
			{
				$deptlist[$dept['Department']] = '';		
			}
		}
	}

	ksort($deptlist);
	return $deptlist;
}

//--------------------------------------------------------------------------------------------------------------
function get_departments($dp_id)
//	get departments that are owning a given degree programme (in year 1 of the programme)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$query = "
		SELECT
		d.department_name AS 'Department',
		dpd.percentage AS 'Percentage'

		FROM Department d
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.department_id = d.id

		WHERE 1=1
		AND dpd.year_of_programme = 1
		AND dpd.academic_year_id = $ay_id
		AND dpd.degree_programme_id = $dp_id
	";
	
	return get_data($query);	
}



//--------------------------------------------------------------------------------------------------------------
function get_all_tc_students00($ay_id, $tc_id)
//	get all students enrolled to any assessment unit using a giving teaching component and a given academic year
{
	$query = "
		SELECT
		COUNT(DISTINCT sau.student_id) AS 'TC Students'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id
		
		WHERE tc.id = $tc_id
		";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
		$row = mysql_fetch_assoc($result);
		return $row['TC Students'];
}
















?>