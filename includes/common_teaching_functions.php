<?php

//==================================================================================================
//
//	Functions on Assessment Units that are used by other scripts
//
//	13-03-14	2nd version
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function au_record($au_id)
// returns the complete DAISY record of a given assessment unit ID
{
	$query = "
		SELECT *
		FROM AssessmentUnit
		WHERE id = $au_id
	";
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function au_title($au_id)
// returns title of the given assessment unit
{
	$query = "
		SELECT *
		FROM AssessmentUnit
		WHERE id = $au_id
	";
	$result = get_data($query);
	return $result[0]['title'];
}

//--------------------------------------------------------------------------------------------------------------
function au_code($au_id)
// returns code of the given assessment unit
{
	$query = "
		SELECT *
		FROM AssessmentUnit
		WHERE id = $au_id
	";
	$result = get_data($query);
	return $result[0]['assessment_unit_code'];
}


//==================< Graduate Training Courses >======================
//--------------------------------------------------------------------------------------------------------------
function students_per_au($ay_id, $au_id)
// returns the total number of students that are enrolled into a given assessment unit in a given year
{
	$query = "
		SELECT COUNT(sau.student_id) AS 'Students' 
		FROM StudentAssessmentUnit sau
		INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
		WHERE sau.academic_year_id = $ay_id 
		AND sau.assessment_unit_id = $au_id
	";
	$result = get_data($query);
	return $result[0]['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function own_students_per_au($ay_id, $au_id, $d_id)
// returns the number of students 'owned' by the given department that are enrolled into a given assessment unit in a given year
{
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
	return $result[0]['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function au_link($au_id, $title)
//	adds a link to the given assessment unit title
{
	$department_code = $_POST['department_code'];
	
	if(!$_POST['excel_export']) return "<A HREF=../teaching.php?au.id=$au_id&ay_id=$ay_id&department_code=$department_code>".$title."</A>";
	else return $title;
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
function get_term($ay_id, $au_id)
//	this will return the term(s) in which the assessment unit was taught
{
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
	$terms = '';
	if($result) foreach($result AS $row)
	{
		if($terms != '') $terms = $terms . ' / ';
		$terms= $terms . $row['Term'];
	}

	return $terms;
}

//--------------------------------------------------------------------------------------------------------------
function au_norm_stint($ay_id, $au_id)
//	get the norm stint for an assessment unit and a given academic year
{
	$query = "
SELECT

SUM(IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint) * tc.sessions_planned) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id
		";
//d_print($query);

	$result = get_data($query);
	return $result[0]['Stint'];
}

//--------------------------------------------------------------------------------------------------------------
function au_norm_stint_share($ay_id, $au_id)
//	get the norm stint for an assessment unit and a given academic year corrected by the factor of AU students aginst the total number of student for every Component involved
{
//	get the total number of students enroled into the given assessment unit
			$au_students  = students_per_au($ay_id, $au_id);
			
	$query = "
SELECT
tc.id AS 'TC_ID',
tc.capacity,
SUM(IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint) * tc.sessions_planned) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id
GROUP BY tc.id
		";
//d_print($query);

	$components = get_data($query);
	$total_norm_stint_share = 0;
	if($components) foreach($components AS $component)
	{
		$tc_id = $component['TC_ID'];
		$capacity = $component['capacity'];
		$tc_students = count_all_tc_students($ay_id, $tc_id);
//dprint($au_students . " | " .$tc_students);
	
		if($tc_students > 0) $tc_share_factor = $au_students/$tc_students;
		else $tc_share_factor = 0;
		$tc_stint_share = $component['Stint'] * $tc_share_factor;
		
		if($capacity > 0) $factor = ceil($tc_students / $capacity);
		else $factor = 1;
		$total_norm_stint_share = $total_norm_stint_share + $tc_stint_share * $factor;
	}
	return $total_norm_stint_share;
}

//--------------------------------------------------------------------------------------------------------------
function au_stint($ay_id, $au_id)
//	get the actual stint for an assessment unit and a given academic year
{
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

WHERE au.id = $au_id
		";
//d_print($query);

	$result = get_data($query);
	return $result[0]['Stint'];
}

//--------------------------------------------------------------------------------------------------------------
function au_stint_share($ay_id, $au_id)
//	get the actual stint for an assessment unit and a given academic year corrected by the factor of AU students aginst the total number of student for every Component involved
{
//	get the total number of students enroled into the given assessment unit
			$au_students  = students_per_au($ay_id, $au_id);
			
	$query = "
SELECT
tc.id AS 'TC_ID',
SUM(IF(ti.stint_override > 0, ti.stint_override,IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint)) * ti.sessions * ti.percentage / 100) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id
GROUP BY tc.id
		";
//d_print($query);

	$components = get_data($query);
	$total_stint_share = 0;
	if($components) foreach($components AS $component)
	{
		$tc_id = $component['TC_ID'];
		$tc_students = count_all_tc_students($ay_id, $tc_id);
//dprint($au_students . " | " .$tc_students);
	
		if($tc_students > 0) $tc_share_factor = $au_students/$tc_students;
		else $tc_share_factor = 0;
		$tc_stint_share = $component['Stint'] * $tc_share_factor;
		$total_stint_share = $total_stint_share + $tc_stint_share;
		
	}
	return $total_stint_share;
}

//--------------------------------------------------------------------------------------------------------------
function au_norm_hours($ay_id, $au_id)
//	get the norm hours for an assessment unit and a given academic year
{
	$query = "
SELECT

SUM(tctt.hours * tc.sessions_planned) / COUNT(DISTINCT tcau.id) AS 'Hours'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id

WHERE au.id = $au_id		";
//d_print($query);

	$result = get_data($query);
	return $result[0]['Hours'];
}

//--------------------------------------------------------------------------------------------------------------
function au_norm_hours_share($ay_id, $au_id)
//	get the norm hours for an assessment unit and a given academic year corrected by the factor of AU students aginst the total number of student for every Component involved
{
//	get the total number of students enroled into the given assessment unit
			$au_students  = students_per_au($ay_id, $au_id);
			
	$query = "
SELECT
tc.id AS 'TC_ID',
SUM(tctt.hours * tc.sessions_planned) / COUNT(DISTINCT tcau.id) AS 'Hours'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id

WHERE au.id = $au_id
GROUP BY tc.id
		";
//d_print($query);

	$components = get_data($query);
	$total_norm_hours_share = 0;
	if($components) foreach($components AS $component)
	{
		$tc_id = $component['TC_ID'];
		$tc_students = get_all_tc_students($ay_id, $tc_id);
		if($tc_students > 0) $tc_share_factor = $au_students/$tc_students;
		else $tc_share_factor = 0;
		$tc_hours_share = $component['Hours'] * $tc_share_factor;
		$total_norm_hours_share = $total_norm_hours_share + $tc_hours_share;
		
	}
	return $total_norm_hours_share;
}

//--------------------------------------------------------------------------------------------------------------
function au_hours($ay_id, $au_id)
//	get the actual hours for an assessment unit and a given academic year
{
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
	return $result[0]['Hours'];
}

//--------------------------------------------------------------------------------------------------------------
function au_student_hours0($ay_id, $au_id)
//	return the average number of hours a student enrolled into a given Assessment Unit is getting
{
//	get the distinct students enrolled into the AU

	$query = "
SELECT DISTINCT
*

FROM Students s
INNER JOIN StudentAssessmentUnit sau ON sau.student_id = s.id AND sau.academic_year_id = $ay_id
		";
//d_print($query);

	$components = get_data($query);
	$total_hours_share = 0;
	if($components) foreach($components AS $component)
	{
		$tc_id = $component['TC_ID'];
		$tc_students = get_all_tc_students($ay_id, $tc_id);
		if($tc_students > 0) $tc_share_factor = $au_students/$tc_students;
		else $tc_share_factor = 0;
		$tc_hours_share = $component['Hours'] * $tc_share_factor;
		$total_hours_share = $total_hours_share + $tc_hours_share;
		
	}
	return $total_hours_share;
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

//-------------------< Amend Stint per Degree Programme Department >-------------------
//--------------------------------------------------------------------------------------------------------------
function amend_d_stint_per_au($table)
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
			
//	get the total amount of stint used for the assessment unit
			$au_stint = au_stint($ay_id, $au_id);
//	get the total number of students enroled into a given assessment unit
			$au_students  = students_per_au($ay_id, $au_id);
//	get the number of students enrolled in the assessment unit by degree programme
			$dp_students = dp_students_per_au($au_id);

			if ($dp_students) foreach($dp_students AS $dp_student)
			{
				$dp_id = $dp_student['DP_ID'];
				$dp_students = $dp_student['DP Students'];
			
				$depts = get_departments($dp_id);				// get the departments that own the degree programmes (in year 1)
			
				if ($depts) foreach($depts AS $dept)
				{
					$row[$dept['Department']] = $row[$dept['Department']] + number_format($dept['Percentage'] / 100 *  $dp_student['DP Students'] / $au_students * $au_stint,2);
				}			
			}
			$new_table[] = $row;		
		}
	}
	return $new_table;
}

//-------------------< Amend Stint per Degree Programme Department >-------------------
//--------------------------------------------------------------------------------------------------------------
function get_d_stint_per_au($table)
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
			
			$new_row = $deptlist;				
			
//	get the total amount of stint used for the assessment unit
			$au_stint = au_stint($ay_id, $au_id);
//	get the total number of students enroled into a given assessment unit
			$au_students  = students_per_au($ay_id, $au_id);
//	get the number of students enrolled in the assessment unit by degree programme
			$dp_students = dp_students_per_au($au_id);

			if ($dp_students) foreach($dp_students AS $dp_student)
			{
				$dp_id = $dp_student['DP_ID'];
				$dp_students = $dp_student['DP Students'];
			
				$depts = get_departments($dp_id);				// get the departments 
			
				if ($depts) foreach($depts AS $dept)
				{
					$new_row[$dept['Department']] = $new_row[$dept['Department']] + number_format($dept['Percentage'] / 100 *  $dp_student['DP Students'] / $au_students * $au_stint,2);
				}			
			}
			$new_table[] = $new_row;		
		}
	}
	return $new_table;
}



//--------------------------------------------------------------------------------------------------------------
function count_all_tc_students($ay_id, $tc_id)
//	count all students enrolled to any assessment unit using a giving teaching component and a given academic year
{
	$query = "
		SELECT
		COUNT(sau.student_id) AS 'TC Students'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id
		
		WHERE tc.id = $tc_id
		";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
		$row = mysql_fetch_assoc($result);
		return $row['TC Students'];
}


//--------------------------------------------------------------------------------------------------------------
function count_distinct_tc_students($ay_id, $tc_id)
//	count all distinct students enrolled to any assessment unit using a giving teaching component and a given academic year
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


//--------------------------------------------------------------------------------------------------------------
function get_au_hours($ay_id, $dp_id, $au_id)
//	get the average norm and actual hours for a given degree programme, assessment unit and academic year
{
	$hours = array();
//	$au_students = get_distinct_au_students($ay_id, $au_id);
	$au_students = get_distinct_dp_au_students($ay_id, $dp_id, $au_id);
	$au_components = get_au_tc($ay_id, $au_id);
	$au_tc_norm_hours = 0;
	$au_tc_stud_norm_hours = 0;
	$au_tc_actual_hours = 0;
	
	$student_counter = 0;
	
	if($au_students) foreach($au_students AS $au_student)
	{
		$s_id = $au_student['id'];
		if($au_components) foreach($au_components AS $au_component)
		{
			$tc_id = $au_component['id'];
			$number_of_student_au_relations = count_tc_s_au($ay_id, $tc_id, $s_id);
//			$number_of_student_au_relations = 1;
			if($number_of_student_au_relations > 0)
			{
				$norm_hours = count_tc_norm_hours($ay_id, $tc_id);
				$au_tc_norm_hours = $au_tc_norm_hours + $norm_hours['dept'] / $number_of_student_au_relations;
				$au_tc_stud_norm_hours = $au_tc_stud_norm_hours + $norm_hours['stud'] / $number_of_student_au_relations;
				$au_tc_actual_hours = $au_tc_actual_hours + count_tc_actual_hours($ay_id, $tc_id) / $number_of_student_au_relations;
//				$au_tc_actual_hours = $au_tc_actual_hours + 1;
			}
		}
		
		
		$student_counter++;
	}

	$au_dp_students = get_prog_au_students($ay_id, $au_id, $dp_id);	// get the number of students enrolled into a given AU and DP


	if($au_dp_students > 0)
	{
		$hours['dept_norm'] = $au_tc_norm_hours / $au_dp_students;
		$hours['stud_norm'] = $au_tc_stud_norm_hours / $au_dp_students;
		$hours['actual'] = $au_tc_actual_hours / $au_dp_students;
	} else
	{
		$hours['dept_norm'] = $au_tc_norm_hours;
		$hours['stud_norm'] = $au_tc_stud_norm_hours;
		$hours['actual'] = $au_tc_actual_hours;	
	}
	
	return $hours;
}

//--------------------------------------------------------------------------------------------------------------
function get_distinct_dp_au_students($ay_id, $dp_id, $au_id)
//	get all distinct students from a given degree_programme enrolled into an assessment unit in a given academic year
{
	$query = "
		SELECT DISTINCT
		s.*
		
		FROM AssessmentUnit au
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
		INNER JOIN Student s ON s.id = sau.student_id
		INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id  = sau.student_id AND sdp.academic_year_id  = sau.academic_year_id AND sdp.status = 'ENROLLED' 
		
		WHERE 1=1
		AND au.id = $au_id
		AND sdp.degree_programme_id = $dp_id
	";
//d_print($query);
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_distinct_au_students($ay_id, $au_id)
//	get all distinct students enrolled to an assessment unit in a given academic year
{
	$query = "
		SELECT DISTINCT
		s.*
		
		FROM AssessmentUnit au
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
		INNER JOIN Student s ON s.id = sau.student_id
		
		WHERE au.id = $au_id
	";
//d_print($query);
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_au_tc($ay_id, $au_id)
//	get all teaching components related to a given assessment unit in a given academic year
{
	$query = "
		SELECT 
		tc.*
		
		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		
		WHERE au.id = $au_id
	";
//d_print($query);
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function count_tc_s_au($ay_id, $tc_id, $s_id)
//	count all assessment units enrolled by a given student related to a given teaching component in a given academic year
{
	$query = "
		SELECT 
		COUNT(DISTINCT au.id) AS 'TC_S_AU_COUNT'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
		INNER JOIN AssessmentUnit au ON au.id = tcau.assessment_unit_id
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tc.id = $tc_id
		AND sau.student_id = $s_id
	";
//d_print($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['TC_S_AU_COUNT'];
//	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function count_tc_norm_hours($ay_id, $tc_id)
//	return the student and department norm hours for a given teaching component
{
	$norm_hours = array();
	
	$query = "
		SELECT 
		tc.capacity,
#		tctt.hours * tc.sessions_planned / COUNT(DISTINCT tcau.id) AS 'TC_Norm_Hours'
		SUM(tctt.hours * tc.sessions_planned) / COUNT(DISTINCT tcau.id) AS 'TC_Norm_Hours'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id AND tcau.assessment_unit_id != 99999
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tc.id = $tc_id
	";
//d_print($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	$capacity = $row['capacity'];
	
	$basic_norm = $row['TC_Norm_Hours'];
	
	$tc_students = count_distinct_tc_students($ay_id, $tc_id);
		
	if($capacity > 0) $factor = ceil($tc_students / $capacity);
	else $factor = 1;
//dprint("tc_students = $tc_students | capacity = $capacity | factor (st/cap) = $factor");
	$norm_hours['stud'] = $basic_norm;
	$norm_hours['dept'] = $basic_norm * $factor;

	return $norm_hours;
}

//--------------------------------------------------------------------------------------------------------------
function count_tc_norm_hours0($ay_id, $tc_id)
//	return the norm hours for a given teaching component
{
	$query = "
		SELECT 
		tc.capacity,
#		tctt.hours * tc.sessions_planned / COUNT(DISTINCT tcau.id) AS 'TC_Norm_Hours'
		SUM(tctt.hours * tc.sessions_planned) / COUNT(DISTINCT tcau.id) AS 'TC_Norm_Hours'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id AND tcau.assessment_unit_id != 99999
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tc.id = $tc_id
	";
//d_print($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	$capacity = $row['capacity'];
	
	$basic_norm = $row['TC_Norm_Hours'];
	
	$tc_students = count_distinct_tc_students($ay_id, $tc_id);
		
	if($capacity > 0) $factor = ceil($tc_students / $capacity);
	else $factor = 1;
//dprint("tc_students = $tc_students | capacity = $capacity | factor (st/cap) = $factor");
	
	return $basic_norm * $factor;
}

//--------------------------------------------------------------------------------------------------------------
function count_tc_actual_hours($ay_id, $tc_id)
//	return the actual hours for a given teaching component
{
	$query = "
		SELECT
		SUM(tctt.hours * ti.sessions * ti.percentage / 100) / COUNT(DISTINCT tcau.id) AS 'TC_Actual_Hours'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = 5 AND tcau.assessment_unit_id != 99999
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
		INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = 5
		
		WHERE 1=1
		AND tc.id = $tc_id
		GROUP BY tc.id
	";
//d_print($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);

	return $row['TC_Actual_Hours'];
	
}









?>