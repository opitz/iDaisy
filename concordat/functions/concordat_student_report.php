<?php

//==================================================================================================
//
//	Separate file with concordat student report function
//
//	Last changes: 2013-07-29
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function concordat_student_report()
{

//	This is a 2-step performance
//	1st we select all assessment units (of a given division or department) and their related degree programmes for a given academic year
//	$table = get_assessment_units($dept_id, $ay_id);
	$table = get_assessment_units();

//	2nd we amend the data in the table with the nr.of students and if the degree programme is joint owned
	$table = amend_data($table);

	$table =cleanup($table,1);
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_assessment_units()
{
	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department code

	$query = "
		SELECT 
			au.id AS 'AU_ID',
			au.assessment_unit_code AS 'Unit Code',
			au.title AS 'Unit Title',
			CONCAT(d.department_code,'-',d.department_name) AS 'Unit Dept'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id

		WHERE 1=1 
		AND d.department_code LIKE '$department_code%' 

		ORDER BY d.department_name, au.assessment_unit_code
#		LIMIT 4
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
//		$au_id = array_shift($row);
//		$dp_id = array_shift($row);
		$au_id = $row['AU_ID'];

// get all students that are enrolled into the AU by degree programme
		$query = "
			SELECT
#			sdp.degree_programme_id AS 'DP_ID',
			dp.id AS 'DP_ID',
			dp.title AS 'Programme Title',
			dp.degree_programme_code AS 'Programme Code',
			
			COUNT(DISTINCT sau.student_id) AS 'Students'
			
			FROM StudentAssessmentUnit sau
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id
			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
			
			WHERE 1=1
			AND sau.academic_year_id = $ay_id
			AND sau.assessment_unit_id = $au_id
			AND sdp.status = 'ENROLLED'

			GROUP BY sdp.degree_programme_id
			";

		$students = get_data($query);
		
		if($students) foreach($students AS $student_row)
		{
//			$dp_id = $student_row['DP_ID'];
			$dp_id = array_shift($student_row);
			$row = array_merge($row, $student_row);
//			$row['Students'] = $result[0]['Students'];
		
//	check if the degree programme is joint owned
			if($dp_id > 0) 
			{
				if(is_joint_owned($dp_id)) $row['Joint Owned'] = 'YES';
				else $row['Joint Owned'] = 'NO';
			}
			else $row['Joint Owned'] = '-';

			$new_table[] = $row;
		}
		else if(!$_POST['exclude_no_students'])
		{
			$student_row = array('Programme Title' => '', 'Programme Code' => '', 'Students' => 0, 'Joint Owned' => '');
			$row = array_merge($row, $student_row);
			$new_table[] = $row;
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function concordat_single_student_report()
{
//	This is a 2-step performance
//	1st we select all assessment units (of a given division or department) and their related degree programmes for a given academic year
	$table = get_assessment_units();

//	2nd we amend the data in the table with the nr.of students and if the degree programme is joint owned
	$table = amend_single_data($table);
	
	$table =cleanup($table,1);

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_single_data($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{
//		$au_id = array_shift($row);
//		$dp_id = array_shift($row);
		$au_id = $row['AU_ID'];
		$dp_id = $row['DP_ID'];

		
//	get the list of students
		$query = "
			SELECT
			dp.id AS 'DP_ID',
			dp.title AS 'Programme Title',
			dp.degree_programme_code AS 'Programme Code',

			s.oss_student_code AS 'Student Number',
			CONCAT(s.surname,', ',s.forename) AS 'Student Name',
			sdp.year_of_student AS 'YOS'
	
			FROM StudentAssessmentUnit sau
			INNER JOIN Student s ON s.id = sau.student_id

			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id
			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
	
			WHERE 1=1
			AND sau.academic_year_id = $ay_id
			AND sau.assessment_unit_id = $au_id
			AND sdp.status = 'ENROLLED'

			ORDER BY s.surname, s.forename
		";
//d_print($query);

		$students = get_data($query);
//p_table($students);
		
		if($students) foreach($students AS $student)
		{
//	get student attributes

/*
//	check if the degree programme is joint owned
			$dp_id = $student['DP_ID'];
			if($dp_id > 0) 
			{
				if(is_joint_owned($dp_id)) $joint_owned = 'YES';
				else $joint_owned = 'NO';
			}
			else $joint_owned = '-';
*/
			$dp_id = $student['DP_ID'];
			
			$row['Programme Title'] = $student['Programme Title'];
			$row['Programme Code'] = $student['Programme Code'];
			$row['Programme Dept'] = '';
			$row['Student Number'] = $student['Student Number'];
			$row['Student Name'] = $student['Student Name'];
			$row['Student Year'] = $student['YOS'];
			$row['% DP-Own'] = '';
			
//	get the departments that own a degree programme (in year 1) and their percentage
			$dp_depts = get_dp_depts($dp_id);
			
			if($dp_depts) foreach( $dp_depts AS $dp_dept)
			{
				$row['Programme Dept'] = $dp_dept['department_code'] . "-". $dp_dept['department_name'];
				$row['% DP-Own'] = number_format($dp_dept['percentage'] / 100,2);		
				$new_table[] = $row;
			}
			else $new_table[] = $row;

//			$row['Joint Owned'] = $joint_owned;

		}
		else		// enter blank values
		{
			if(!$_POST['exclude_no_students'])
			{
				$row['Programme Title'] = '';
				$row['Programme Code'] = '';
				$row['Programme Dept'] = '';
				$row['Student Number'] = '';
				$row['Student Name'] = '';
				$row['Student Year'] = '';
				$row['% DP-Own'] = '';
			
//				$row['Joint Owned'] = '';

				$new_table[] = $row;
			}
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function is_joint_owned($dp_id)
//	returns TRUE if there is more than one owning department in 1st year of a given degree programme
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT COUNT(*) AS 'Depts'
		FROM DegreeProgrammeDepartment dpd
		WHERE year_of_programme = 1
		AND degree_programme_id = $dp_id
		AND academic_year_id = $ay_id
	";
	
	$result = get_data($query);
	if($result[0]['Depts'] > 1) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function get_dp_depts($dp_id)
//	returns the departments owning a given degree programme in year 1 and their owning percentage
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$query = "
		SELECT 
		d.*,
		dpd.percentage
		
		FROM DegreeProgrammeDepartment dpd
		INNER JOIN Department d ON d.id = dpd.department_id
		
		WHERE 1=1
		AND year_of_programme = 1
		AND degree_programme_id = $dp_id
		AND academic_year_id = $ay_id
	";
	
	$depts = get_data($query);
	return $depts;
}

?>