<?php

//==================================================================================================
//
//	Separate file with Graduate Training report functions
//
//	13-04-25	8th version
//==================================================================================================

//-----------------------------------------------------------------------------------------
function show_graduate_query()
{
	if(!$_POST['excel_export'])
	{	
		graduate_query_form();
		if(!$_POST['query_type'])
		{
			print_graduate_intro();
			print_graduate_options();
		}
	}
}

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

	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(140);		// 1st row
		print  "Show own Students:";
	print new_column(60);
		print html_checkbox('show_own_students');

	print new_column(100);
		 print "Show Terms:";
	print new_column(60);
		print html_checkbox('show_terms');

	print new_column(140);
		 print "Show Line Numbers:";
	print new_column(60);
		print html_checkbox('show_line_numbers');

/*
	print new_column(140);
		 print "Show line numbers:";
	print new_column(60);
		if ($_POST['show_line_numbers'])  print "<input type='checkbox' name='show_line_numbers' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_line_numbers' value='TRUE'>";

/	print new_column(170);
		 print "Show Student Details:";
	print new_column(0);
		if ($_POST['show_students'])  print "<input type='checkbox' name='show_students' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_students' value='TRUE'>";
*/
	print end_row();
/*
	print start_row(0);		// 2nd row
		print "Include staff without stint:";
	print new_column(0);
		if ($_POST['include_zero_stint']) print "<input type='checkbox' name='include_zero_stint' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='include_zero_stint' value='TRUE'>";
	print new_column(0);
	print new_column(0);
	print end_row();
*/
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//----------------------------------------------------------------------------------------
function graduate_report_options()
//	shows the options for a dtc report
{
	$query_type = $_POST['query_type'];					// get query_type
	
	$options = array();
//	$options[] = array('Select a report type','');
	$options[] = array('Graduate Training Courses','dtc');
	$options[] = array('Graduate Training Courses with Students by Department','dtc_students_depts');
	$options[] = array('Graduate Training Courses with Students by Degree Programme','dtc_students_progs');
	$options[] = array('Student Graduate Enrolment by Department','dtc_student_graduate_enrolment_by_dept');
	$options[] = array('Student Graduate Enrolment by Course','dtc_student_graduate_enrolment_by_course');
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

//-----------------------------------------------------------------------------------------
function print_graduate_intro()
{
	$text = "<B>This report shows information on DTC Assessment Units and PGR Modules.</B>
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<P>";
	print "Available Report Types:<p>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Graduate Training Courses</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Graduate Training Courses for the selected division or department.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Graduate Training Courses with Students by Department</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Graduate Training Courses for the selected division or department and the number of enrolled students by department.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Graduate Training Courses with Students by Degree Programme</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Graduate Training Courses for the selected division or department and the number of enrolled students by degree programme.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Student Graduate Enrolment by Department</FONT></B>:";
			print "</TD><TD>";
				print "This will list the enrolment of students of the selected department into graduate training courses by department providing the courses.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Student Graduate Enrolment by  Course</FONT></B>:";
			print "</TD><TD>";
				print "This will list the enrolment of students of the selected department by graduate training course.<BR />
				<B>Please note</B> that this report can run for more than 60 seconds - please be patient, do not reload the page";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
}

//-----------------------------------------------------------------------------------------
function print_graduate_options()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Options</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Own Students</FONT></B>:";
			print "</TD><TD>";
				print "This will additionally show number of students of the providing department that are enrolled into any graduate course";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Terms</FONT></B>:";
			print "</TD><TD>";
				print "This will amend the list bythe terms in which each course is/was given.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Line Numbers</FONT></B>:";
			print "</TD><TD>";
				print "This will show line numbers for the resulting list.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
//	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: These options are ignored for the Student SES Enrolment Reports</I>";
}

//==================< Graduate Training Courses >======================
//--------------------------------------------------------------------------------------------------------------
function graduate_au_report()
//	show assessment units available for doctoral training
{
	$table = graduate_au_by_owner();						// get all assessment units owned by the given department(s) that are marked as doctoral training
	$table = stint_per_au($table);
	$table = amend_students_per_au($table);
	if($_POST['show_own_students']) $table = amend_own_students_per_au($table);

	if ($_POST['show_terms'])  $table = amend_term($table);
	if($_POST['query_type'] == 'dtc_students_depts')   $table = amend_d_students_per_au($table);		// show students by department
	if($_POST['query_type'] == 'dtc_students_progs')   $table = amend_dp_students_per_au($table);	// show students by degree programme
	
	$table = add_au_link($table);
	
//	$table = amend_au_by_owner_data($table);
	$table = cleanup($table,2);								//removing all internally used values from the table
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function graduate_au_by_owner()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$query = "
		SELECT 
		au.id AS 'AU_ID',
		d.id AS 'D_ID',
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
			au.assessment_unit_code AS 'Code',
			au.title AS 'Assessment Unit / PGR Module'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id


		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
		INNER JOIN Term t on t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
		WHERE 1=1 
		AND au.id  != 99999
		AND tcau.academic_year_id = $ay_id
		AND au.doctoral_training = 1
		AND d.department_code LIKE '$department_code%'
		
		GROUP BY d.department_code, au.assessment_unit_code
		ORDER BY d.department_code, au.assessment_unit_code
#		LIMIT 10
	";

	deb_print($query);
	return get_data($query);	
}

//=====================< Graduate Enrolment >========================
//--------------------------------------------------------------------------------------------------------------
function student_graduate_enrolment_report_by_course()
//	shows the Doctoral Training Courses (AU) into which students of a given department or division are enrolled
{
	$table = get_own_students_per_graduate_course();			// get all students of a selected department or division that are enrolled into any graduate course
	if ($_POST['show_terms'])  $table = amend_term($table);
	
//	$table = cleanup($table,2);									//removing all internally used values from the table
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function student_graduate_enrolment_report_by_dept()
//	shows the Doctoral Training Courses (AU) into which students of a given department or division are enrolled
{
	$table = get_own_students_per_graduate_course_dept();	// get all students of a selected department or division that are enrolled into any graduate course

//	$table = cleanup($table,2);									//removing all internally used values from the table
	return $table;
}

//-----------------------------------------------------------------------------------------
function get_own_students_per_graduate_course()
{
	$ay_id = $_POST['ay_id'];									// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code

	$query ="
SELECT
#au.id AS 'AU_ID',
au_d.department_name AS 'Provided by',
au.assessment_unit_code AS 'Code',
au.title AS 'Assessment Unit / PGR Module',
#COUNT(sdp.student_id) AS 'Whole Students',
FORMAT(SUM(sdp.student_id/sdp.student_id * dpd.percentage / 100),1) AS 'Students'

FROM StudentDegreeProgramme sdp
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = $ay_id
INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.year_of_programme = 1 AND dpd.academic_year_id = $ay_id
INNER JOIN Department d ON d.id = dpd.department_id

WHERE 1 = 1
AND sdp.academic_year_id = $ay_id
AND sdp.status = 'ENROLLED'
AND d.department_code LIKE '$department_code%'
AND au.doctoral_training = 1

GROUP BY au.title
ORDER BY au_d.department_name, au.title
	";

	deb_print($query);
	return get_data($query);
}

//-----------------------------------------------------------------------------------------
function get_own_students_per_graduate_course_dept()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$query ="
SELECT
au_d.department_name AS 'Graduate Training provided by',
#au.assessment_unit_code AS 'Code',
#au.title AS 'Assessment Unit / PGR Module',
#COUNT(sdp.student_id) AS 'Whole Students',
FORMAT(SUM(sdp.student_id/sdp.student_id * dpd.percentage / 100),1) AS 'Own Students enrolled'

FROM StudentDegreeProgramme sdp
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = $ay_id
INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.year_of_programme = 1 AND dpd.academic_year_id = $ay_id
INNER JOIN Department d ON d.id = dpd.department_id

WHERE 1 = 1
AND sdp.academic_year_id = $ay_id
AND sdp.status = 'ENROLLED'
AND d.department_code LIKE '$department_code%'
AND au.doctoral_training = 1

GROUP BY au_d.department_name
ORDER BY au_d.department_name
	";

	deb_print($query);
	return get_data($query);
}

?>