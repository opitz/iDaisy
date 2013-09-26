<?php

//==================================================================================================
//
//	Separate file with concordat student report function
//
//	Last changes: 2013-03-08
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

	$table =cleanup($table,2);
	
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
			dp.id AS 'DP_ID',
			au.assessment_unit_code AS 'Unit Code',
			au.title AS 'Unit Title',
			CONCAT(d.department_code,'-',d.department_name) AS 'Unit Dept',
			dp.title AS 'Programme Title',
			dp.degree_programme_code AS 'Programme Code'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id
		LEFT JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id AND audp.academic_year_id = $ay_id
		LEFT JOIN DegreeProgramme dp ON dp.id = audp.degree_programme_id

		WHERE 1=1 
		AND d.department_code LIKE '$department_code%' 

		ORDER BY d.department_name, au.assessment_unit_code, dp.degree_programme_code
#		LIMIT 10
	";

//	d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function get_assessment_units0()
{
	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department code

	$query = "
		SELECT 
			au.id AS 'AU_ID',
			dp.id AS 'DP_ID',
			d.department_code AS 'Dept Code',
			d.department_name AS 'Department',
			au.assessment_unit_code AS 'AU Code',
			au.title AS 'Assessment Unit',
			dp.degree_programme_code AS 'Programme Code',
			dp.title AS 'Degree Programme'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id
		LEFT JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id AND audp.academic_year_id = $ay_id
		LEFT JOIN DegreeProgramme dp ON dp.id = audp.degree_programme_id

		WHERE 1=1 
		AND d.department_code LIKE '$department_code%' 

		ORDER BY d.department_name, au.assessment_unit_code, dp.degree_programme_code
#		LIMIT 10
	";

//	d_print($query);

	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function get_assessment_units00($dept_id, $ay_id)
{
	$department_code = $_POST['department_code'];
	$query = "
		SELECT 
			au.id AS 'AU_ID',
			dp.id AS 'DP_ID',
			d.department_code AS 'Dept Code',
			d.department_name AS 'Department',
			au.assessment_unit_code AS 'AU Code',
			au.title AS 'Assessment Unit',
			dp.degree_programme_code AS 'Programme Code',
			dp.title AS 'Degree Programme'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id
		LEFT JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id AND audp.academic_year_id = $ay_id
		LEFT JOIN DegreeProgramme dp ON dp.id = audp.degree_programme_id

		WHERE 1=1 
	";
	if ($dept_id) $query = $query . "
		AND d.id = $dept_id 
	";
	else $query = $query . "
		AND d.department_code LIKE '$department_code%' 
	";
	$query = $query . "
		ORDER BY d.department_name, au.assessment_unit_code, dp.degree_programme_code
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
//		$au_id = array_shift($row);
//		$dp_id = array_shift($row);
		$au_id = $row['AU_ID'];
		$dp_id = $row['DP_ID'];

//	get the number of students
		if($dp_id > 0)	// if there is a degree programme related select only students of that degree programme that are enrolled into the AU
		{
			$query = "
				SELECT
				COUNT(DISTINCT sau.student_id) AS 'Students'
				
				FROM StudentAssessmentUnit sau
				INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id
				
				WHERE 1=1
				AND sau.academic_year_id = $ay_id
				AND sau.assessment_unit_id = $au_id
				AND sdp.degree_programme_id = $dp_id
			";
		} else		// get all students that are enrolled into the AU
		{
			$query = "
				SELECT
				COUNT(DISTINCT sau.student_id) AS 'Students'
				
				FROM StudentAssessmentUnit sau
				
				WHERE 1=1
				AND sau.academic_year_id = $ay_id
				AND sau.assessment_unit_id = $au_id
			";
		}
		$result = get_data($query);
		$row['Students'] = $result[0]['Students'];

		if(!$_POST['exclude_no_students'] OR $row['Students'] > 0)
		{
//	check if the degree programme is joint owned

			if($dp_id > 0) 
			{
				if(is_joint_owned($dp_id)) $row['Joint Owned'] = 'YES';
				else $row['Joint Owned'] = 'NO';
			}
			else $row['Joint Owned'] = '-';
		
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
	
	$table =cleanup($table,2);

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

//	check if the degree programme is joint owned

		if($dp_id > 0) 
		{
			if(is_joint_owned($dp_id)) $joint_owned = 'YES';
			else $joint_owned = 'NO';
		}
		else $joint_owned = '-';
		
//	get the number of students
		if($dp_id > 0)	// if there is a degree programme related select only students of that degree programme that are enrolled into the AU
		{
			$query = "
				SELECT
				CONCAT(d.department_code,'-',d.department_name) AS 'Programme Dept',
				s.oss_student_code AS 'Student Number',
				CONCAT(s.surname,', ',s.forename) AS 'Student Name',
				dpd.percentage AS 'dp_percentage'
				
				FROM StudentAssessmentUnit sau
				INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id
				
				INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id 
					AND dpd.academic_year_id = sdp.academic_year_id 
					AND dpd.year_of_programme = 1
				INNER JOIN Department d ON d.id = dpd.department_id
				INNER JOIN Student s ON s.id = sau.student_id
				
				WHERE 1=1
				AND sau.academic_year_id = $ay_id
				AND sau.assessment_unit_id = $au_id
				AND sdp.degree_programme_id = $dp_id
				
				ORDER BY s.surname, s.forename
			";
		} else	// get all students that are enrolled into the AU
		{
			$query = "
				SELECT
				s.oss_student_code AS 'Student Number',
				CONCAT(s.surname,', ',s.forename) AS 'Student Name'
				
				FROM StudentAssessmentUnit sau
				INNER JOIN Student s ON s.id = sau.student_id
				
				WHERE 1=1
				AND sau.academic_year_id = $ay_id
				AND sau.assessment_unit_id = $au_id

				ORDER BY s.surname, s.forename
			";
		}
		$students = get_data($query);
//p_table($students);
		
		if($students) foreach($students AS $student)
		{
//	get student attributes
			$row['Programme Dept'] = $student['Programme Dept'];
			$row['Student Number'] = $student['Student Number'];
			$row['Student Name'] = $student['Student Name'];
			$row['% DP-Own'] = number_format($student['dp_percentage'] / 100,2);

//			$row['Joint Owned'] = $joint_owned;

			$new_table[] = $row;
		}
		else		// enter blank values
		{
			if(!$_POST['exclude_no_students'])
			{
				$row['Programme Dept'] = '';
				$row['Student Number'] = '';
				$row['Student Name'] = '';
				$row['% DP-Own'] = '';
			
//				$row['Joint Owned'] = '';

				$new_table[] = $row;
			}
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_single_data0($table)
{
	$ay_id = $_POST['ay_id'];											// get academic_year_id

	$new_table = array();
	
	if($table) foreach($table as $row)
	{
//		$au_id = array_shift($row);
//		$dp_id = array_shift($row);
		$au_id = $row['AU_ID'];
		$dp_id = $row['DP_ID'];

//	check if the degree programme is joint owned

		if($dp_id > 0) 
		{
			if(is_joint_owned($dp_id)) $joint_owned = 'YES';
			else $joint_owned = 'NO';
		}
		else $joint_owned = '-';
		
//	get the number of students
		if($dp_id > 0)	// if there is a degree programme related select only students of that degree programme that are enrolled into the AU
		{
			$query = "
				SELECT
				s.oss_student_code AS 'Student Number',
				CONCAT(s.surname,', ',s.forename) AS 'Student Name'
				
				FROM StudentAssessmentUnit sau
				INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id
				INNER JOIN Student s ON s.id = sau.student_id
				
				WHERE 1=1
				AND sau.academic_year_id = $ay_id
				AND sau.assessment_unit_id = $au_id
				AND sdp.degree_programme_id = $dp_id
				
				ORDER BY s.surname, s.forename
			";
		} else	// get all students that are enrolled into the AU
		{
			$query = "
				SELECT
				s.oss_student_code AS 'Student Number',
				CONCAT(s.surname,', ',s.forename) AS 'Student Name'
				
				FROM StudentAssessmentUnit sau
				INNER JOIN Student s ON s.id = sau.student_id
				
				WHERE 1=1
				AND sau.academic_year_id = $ay_id
				AND sau.assessment_unit_id = $au_id

				ORDER BY s.surname, s.forename
			";
		}
		$students = get_data($query);
//p_table($students);
		
		if($students) foreach($students AS $student)
		{
			$row['Student Number'] = $student['Student Number'];
			$row['Student Name'] = $student['Student Name'];

			$row['Joint Owned'] = $joint_owned;

			$new_table[] = $row;
		}
		else		// enter blank values
		{
			$row['Student Number'] = '';
			$row['Student Name'] = '';
			
			$row['Joint Owned'] = $joint_owned;

			$new_table[] = $row;
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

?>