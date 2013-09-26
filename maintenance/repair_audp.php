<?php

//==================================================================================================
//
//	iDAISY maintenance: Repair  AssessmentUnitDegreeProgramme relations from student data

//	Last changes: Matthias Opitz --- 2013-05-16
//
//==================================================================================================
$version = "130516.1";

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

$ay_id = 5;	// the academic year ID

$conn = open_daisydb();													// open DAISY database

//	start a timer
	$starttime = start_timer(); 

//	read a list of all degree programmes

$dp_list = get_degree_programmes();
if($dp_list) foreach($dp_list AS $dprog)
{
//	echo $dprog['title'] . "<BR />";
	echo $dprog['title'] . "\n";

//	get all ENROLLED students of that degree programme
	$dp_students = get_dp_students($ay_id, $dprog['id']);

//	loop it!
	if($dp_students) foreach($dp_students AS $student)
	{
//		echo '--- ' .$student['surname'] . ', ' . $student['forename']  . "<BR />";
	
//	get all enrolled Assessment Units of that student
		$st_units = get_student_units($ay_id, $student['id']);
	
//	loop it!
		if($st_units) foreach($st_units AS $unit)
		{
//			echo '------ ' .$unit['title'] . "<BR />";

			if(!au_is_related($ay_id, $dprog['id'], $unit['id']) )
			{
				relate_au2dp($ay_id, $dprog['id'], $unit['id'], $dprog['degree_programme_type_name']);
				echo "+";
			}
		}
	}
//	echo "<P>";
	echo "\n\n";
}

//	stop the timer
$totaltime = stop_timer($starttime); 
show_footer($version, $totaltime);

mysql_close($conn);												// close database connection $conn

//--------------------------------------------------------------------------------------------------------------
function get_degree_programmes()
//	get all PGRAD_T degree programmes 
{
	$query = "
		SELECT DISTINCT
		dpt.degree_programme_type_name,
		dp.*
		FROM DegreeProgramme dp 
		INNER JOIN DegreeProgrammeType dpt ON dpt.id = dp.degree_programme_type_id
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
		WHERE 1=1
		AND dpt.degree_programme_type_name = 'PGRAD_T'
		ORDER BY dp.title	
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_dp_students($ay_id, $dp_id)
//	get all degree programmes
{
	$query = "
		SELECT DISTINCT
		s.*
		FROM StudentDegreeProgramme sdp
		INNER JOIN Student s ON s.id = sdp.student_id
		
		WHERE 1=1
		AND sdp.academic_year_id = $ay_id
		AND sdp.degree_programme_id = $dp_id
		AND sdp.status = 'ENROLLED'
		
		ORDER BY s.surname, s.forename
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_student_units($ay_id, $s_id)
//	get all assessment units a student is enrolled into
{
	$query = "
		SELECT DISTINCT
		au.*
		FROM StudentAssessmentUnit sau
		INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
		
		WHERE 1=1
		AND sau.academic_year_id = $ay_id
		AND sau.student_id = $s_id
		
		ORDER BY au.title
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function au_is_related($ay_id, $dp_id, $au_id)
//	return TRUE if the assessment unit is aready related to the given degree programme in the given academic year
{
	$query = "
		SELECT 
		COUNT(*) as 'count'
		
		FROM AssessmentUnitDegreeProgramme audp
		
		WHERE 1=1
		AND audp.academic_year_id = $ay_id
		AND audp.degree_programme_id = $dp_id
		AND audp.assessment_unit_id = $au_id
	";
//d_print($query);

	$result = get_data($query);
	if($result[0]['count'] > 0) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function relate_au2dp($ay_id, $dp_id, $au_id, $unit_type)
//	relates aan assessment unit to a degree programme fro the given academic year
{
	$query = "
		INSERT INTO AssessmentUnitDegreeProgramme 
		( 
			created_at,
			degree_programme_id, 
			assessment_unit_id, 
			academic_year_id,
			unit_type,
			core_option
		)
		VALUES 
		(
			CURDATE(),
			$dp_id,
			$au_id,
			$ay_id,
			'$unit_type',
			'Optional'
		)
		";

//d_print($query);

	$result = get_data($query);
}

?>
