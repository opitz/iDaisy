<?php

//==================================================================================================
//
//	Separate file with DTC report functions
//
//	13-01-22	1st version
//==================================================================================================

//----------------------------------------------------------------------------------------
function dtc_query_form()
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
	
	
	print start_row(0);
	print "Show line numbers:";
	print new_column(400);
	if ($_POST['show_line_numbers'])  print "<input type='checkbox' name='show_line_numbers' value='TRUE' checked='checked'>";
	else print "<input type='checkbox' name='show_line_numbers' value='TRUE'>";
	print end_row();

//	print start_row(0) . "Report Type:" . new_column(0) . dtc_report_options() . end_row();		
	print start_row(0) . "Report Type:" . new_column(0) . dtc_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//----------------------------------------------------------------------------------------
function dtc_report_options()
// shows the options for a dtc report
{
	$query_type = $_POST['query_type'];					// get query_type
	
	$options = array(
					array('Select a report type',''), 
					array('Doctoral Training Courses','dtc'), 
					array('Provision by Student Dept','prov_by_stud'), 
					array('Student by Provision Dept','stud_by_prov')
					);
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
function dtc_report()
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
) AS 'Own Students',
''

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
	return amend_table(get_data($query));
//	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function amend_table($table)
//	http://localhost/iDaisy/teaching.php?au_id=96723&ay_id=5&department_code=3C08
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

?>