<?php

//==================================================================================================
//
//	Separate file with Graduate Training report functions
//
//	13-02-15	3rd version
//==================================================================================================

//----------------------------------------------------------------------------------------
function graduate_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	$year = $_POST['year'];															// get year
	
	print "<form action='$actionpage' method=POST>";

	print "<input type='hidden' name='query_type' value='dtc'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Academic Year:";
	print new_column(400);
	print academic_year_options();

	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print start_row(0) . "Report Type:" . new_column(0) . dtc_report_options() . end_row();		
	print start_row(0) . "Report Type:" . new_column(0) . graduate_report_options() . end_row();		

	print start_row(0);
	print "Show line numbers:";
	print new_column(400);
	if ($_POST['show_line_numbers'])  print "<input type='checkbox' name='show_line_numbers' value='TRUE' checked='checked'>";
	else print "<input type='checkbox' name='show_line_numbers' value='TRUE'>";
	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//----------------------------------------------------------------------------------------
function graduate_report_options()
// shows the options for a dtc report
{
	$query_type = $_POST['query_type'];					// get query_type
	
	$options = array();
	$options[] = array('Select a report type','');
	$options[] = array('Doctoral Training Courses','dtc');
	$options[] = array('Doctoral Training Courses with Students','dtc1');
	$options[] = array('Doctoral Training Courses with Students and Terms','dtc2');
//	$options[] = array('Provision by Student Dept','prov_by_stud');
//	$options[] = array('Student by Provision Dept','stud_by_prov');

	$html = "<select name='query_type'>";
	
	foreach($options AS $option)
	{
		if($option[1]==$query_type) $html = $html."<option SELECTED='selected' value=".$option[1].">".$option[0]."</option>";
		else $html = $html."<option value=".$option[1].">".$option[0]."</option>";
	}
	$html = $html."</select>";

	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function graduate_report()
//	show assessment units available for doctoral training
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	here comes the query

$query = "
		SELECT DISTINCT
		au.id AS 'AU_ID',
		d.department_name AS 'Department',
		au.assessment_unit_code AS 'Code',
		au.title AS 'Assessment Unit / PGR Module'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id

		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
		INNER JOIN Term t on t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND au.doctoral_training = 1
		AND d.department_code LIKE '$department_code%' 

		GROUP BY d.department_name, au.assessment_unit_code
		ORDER BY d.department_name, au.assessment_unit_code
		#LIMIT 100
		";
//d_print($query);
	$table = get_data($query);
	$table = stint_per_au($table);
	$table = add_au_link($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function graduate_report1()
//	show assessment units available for doctoral training and the student numbers
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	here comes the query

$query = "
SELECT DISTINCT
au.id AS 'AU_ID',
d.department_name AS 'Department',
au.assessment_unit_code AS 'Code',
au.title AS 'Assessment Unit / PGR Module',
(SELECT COUNT(DISTINCT student_id) FROM StudentAssessmentUnit WHERE assessment_unit_id = au.id AND academic_year_id = $ay_id) AS 'Students',
#COUNT(DISTINCT sau2.student_id) AS 'Students1', 
(
	SELECT COUNT(DISTINCT sau.student_id) 
	FROM StudentAssessmentUnit sau 
	INNER JOIN StudentDegreeProgramme sdp ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
	INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.academic_year_id = sdp.academic_year_id AND year_of_programme = 1 
	WHERE sau.assessment_unit_id = au.id 
	AND dpd.department_id = d.id
	AND sau.academic_year_id = $ay_id
) AS 'Own Students'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id

INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
INNER JOIN Term t on t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND au.doctoral_training = 1
		AND d.department_code LIKE '$department_code%' 

		GROUP BY d.department_name, au.assessment_unit_code
		ORDER BY d.department_name, au.assessment_unit_code
		#LIMIT 100
		";
//d_print($query);
	$table = get_data($query);
	$table = stint_per_au($table);
	$table = add_au_link($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function graduate_report2()
//	show assessment units available for doctoral training, the student numbers and the term(s) they are taught in
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	here comes the query

$query = "
SELECT DISTINCT
au.id AS 'AU_ID',
d.department_name AS 'Department',
au.assessment_unit_code AS 'Code',
au.title AS 'Assessment Unit / PGR Module',
(SELECT COUNT(DISTINCT student_id) FROM StudentAssessmentUnit WHERE assessment_unit_id = au.id AND academic_year_id = $ay_id) AS 'Students',
#COUNT(DISTINCT sau2.student_id) AS 'Students1', 
(
	SELECT COUNT(DISTINCT sau.student_id) 
	FROM StudentAssessmentUnit sau 
	INNER JOIN StudentDegreeProgramme sdp ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
	INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.academic_year_id = sdp.academic_year_id AND year_of_programme = 1 
	WHERE sau.assessment_unit_id = au.id 
	AND dpd.department_id = d.id
	AND sau.academic_year_id = $ay_id
) AS 'Own Students'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id

INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
INNER JOIN Term t on t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND au.doctoral_training = 1
		AND d.department_code LIKE '$department_code%' 

		GROUP BY d.department_name, au.assessment_unit_code
		ORDER BY d.department_name, au.assessment_unit_code
		#LIMIT 100
		";
//d_print($query);

	$table = get_data($query);
	$table = amend_term($table);
	$table = stint_per_au($table);
	$table = add_au_link($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function check_shared_use($table)
//	amends the row by a field that indicates if an assessment unit is used by students on degree programmes owned by departments other than the department that owns the assessment unit
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	
	$new_table = array();
	if($table) foreach ($table AS $row)
	{
//		$au_id = array_shift($row);	//	get the AU ID
		$au_dept = array_shift($row);	//	get the Dept ID
		$au_id = $row['AU_ID'];		//	get the AU ID

		$query = "
			SELECT
			COUNT(*) AS 'Others'
			FROM StudentAssessmentUnit sau
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sau.student_id AND sdp.academic_year_id = sau.academic_year_id AND sdp.status = 'ENROLLED'
			INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.academic_year_id = sdp.academic_year_id
			INNER JOIN Department d ON d.id = dpd.department_id
			
			WHERE 1=1
			AND sau.academic_year_id = $ay_id
			AND sau.assessment_unit_id = $au_id
			AND dpd.department_id != $au_dept
		";
d_print($query);
//		$result = get_data($query);
		
//		if($result[0]['Others'] > 1) $row['Shared'] = 'Yes';
//		else $row['Shared'] = '';

		$new_table[] = $row;
	}
	return $new_table;
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
function add_au_link($table)
//	adds a link to the AU title linking to more details
{
	$ay_id = $_POST['ay_id'];														// get academic_year_id

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	
	$new_table = array();
	if($table) foreach ($table AS $row)
	{
		$au_id = array_shift($row);	//	get the AU ID
		if(!$_POST['excel_export'])
		{
			$row['Assessment Unit / PGR Module'] = "<A HREF=".this_page()."?au.id=$au_id&ay_id=$ay_id&department_code=$department_code>".$row['Assessment Unit / PGR Module']."</A>";
		}
		$new_table[] = $row;
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function provision_by_student_dept_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	here comes the query

$query = "
SELECT 
*

FROM Assessment_Unit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_id
INNER JOIN 


		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND au.doctoral_training = 1
		AND d.department_code LIKE '$department_code%' 

		GROUP BY d.department_name, au.assessment_unit_code
		ORDER BY d.department_name, au.assessment_unit_code
		#LIMIT 100
		";
//d_print($query);
	return amend_table(get_data($query));
//	return get_data($query);
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


?>