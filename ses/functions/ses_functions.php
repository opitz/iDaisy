<?php

//==================================================================================================
//
//	Separate file with committee functions
//	Last changes: Matthias Opitz --- 2013-03-05
//
//==================================================================================================

//=========================< SES Query >===========================
//--------------------------------------------------------------------------------------------------------------
function show_ses_query()
{
	if(!$_POST['excel_export'])
	{	
		ses_query_form();
		if(!$_POST['query_type'])
		{
			print_ses_intro();
			print_ses_options();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function ses_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='ses'>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Course Title:" . new_column(0) . "<input type='text' name = 'course_title_q' value='$course_title_q' size=50>" . end_row();		
	print start_row(0) . "Report Type:" . new_column(0) . ses_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(180);		// 1st row
		print  "Show Component Titles:";
	print new_column(60);
		print html_checkbox('show_component_title');

	print new_column(130);
		 print "Show instances:";
	print new_column(60);
		print html_checkbox('show_instances');

	print new_column(100);
		 print "Show dates:";
	print new_column(60);
		print html_checkbox('show_dates');

	print new_column(170);
		 print "Show Student Details:";
	print new_column(0);
		print html_checkbox('show_students');

	print end_row();
/*
	print start_row(0);		// 2nd row
		print "Include staff without stint:";
	print new_column(0);
		print html_checkbox('include_zero_stint');
	print new_column(0);
	print new_column(0);
	print end_row();
*/
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function ses_report_options()
// shows the options for a ses report
{
	$query_type = $_POST['query_type'];					// get course_report_type
	
	$options = array();

	$options[] = array( 'All Courses on SES', 'ses_all');
	$options[] = array('Assessment Units on SES', 'au_only');
	$options[] = array('PGR Modules on SES', 'pgr_only');
	$options[] = array('PGR Modules NOT on SES', 'non_ses');

	$options[] = array('Student SES Enrolment by Dept','dtc_student_ses_enrolment_by_dept');
	$options[] = array('Student SES Enrolment by Course','dtc_student_ses_enrolment_by_course');
	
	$html = "<select name='query_type'>";
	foreach($options AS $option)
	{
		$option_label = $option[0];
		$option_value = $option[1];
		if($option_value==$query_type) $html = $html."<option VALUE='$option_value' SELECTED='selected'>$option_label</option>";
		else $html = $html."<option VALUE='$option_value'>$option_label</option>";
	}
	$html = $html."</select>";

	return $html;
}

///--------------------------------------------------------------------------------------------------------------
function print_ses_intro()
{
	$text = "<B>The report shows the graduate training or options of your department that are displayed in the Student Enrolment System (SES).</B><BR />
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<P>";
	print "Available Report Types:<p>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>All courses on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Assessment Units and PGR Modules available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Assessment Unts on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Assessment Units that are  available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>PGR Modules on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all PGR Modules that are  available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>PGR Modules NOT on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all PGR Modules that are <I>not</I> available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Student SES Enrolment by Dept</FONT></B>:";
			print "</TD><TD>";
				print "This will list the enrolment into courses offered through the SES of students of the selected department by the department providing the courses.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Student SES Enrolment by  Course</FONT></B>:";
			print "</TD><TD>";
				print "This will list the enrolment of students of the selected department by course offered through the SES.<BR />
				<B>Please note</B> that this report can run for more than 60 seconds - please be patient and do not reload the page.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
}

//--------------------------------------------------------------------------------------------------------------
function print_ses_options()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Options</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Component Titles</FONT></B>:";
			print "</TD><TD>";
				print "This will additionally show the title of the related Teaching Components in case they are different.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Instances</FONT></B>:";
			print "</TD><TD>";
				print "This will amend the list by all Teaching Instances related to each listed course.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Dates</FONT></B>:";
			print "</TD><TD>";
				print "This will amend the list by the start and end dated for enrolment and for the courses themselves.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Student Details</FONT></B>:";
			print "</TD><TD>";
				print "This will show the numbers of students enrolled into a course per department owning the degree programmes they are enrolled into.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: These options are ignored for the Student SES Enrolment Reports</I>";
}

//========================< SES Courses >==========================
//--------------------------------------------------------------------------------------------------------------
function ses_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = ses_course_list();
//	$table = amend_students_per_au($table);
//	$table = amend_own_students_per_au($table);
	
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];
		$dept_id = $row['D_ID'];
		$au_students = au_students($au_id);
		$own_au_students = own_au_students($au_id,$dept_id);
		$other_au_students = $au_students - $own_au_students;
		$row['Total Students'] = $au_students;
		if (!$_POST['show_students']) $row['Own Students'] = $own_au_students;
		if (!$_POST['show_students']) $row['Other Students'] = $other_au_students;
		$new_table[] = $row;
	}
	
//	if ($_POST['show_students'])  $table = amend_dp_students_per_au($table);
	if ($_POST['show_students'])  $new_table = amend_d_students_per_au($new_table);

	$new_table = cleanup($new_table,2);								//removing all internally used values from the table
	
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function ses_course_list()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$course_title_q = $_POST['course_title_q'];					// get course_title_q

	
		
	$query = "
		SELECT 
		au.id AS 'AU_ID',
		d.id AS 'D_ID',
	";
// show a department column only when used on divisional level or above
	if(strlen($department_code) < 4) $query = $query."d.department_name AS 'Department', ";

	$query = $query . "
au.assessment_unit_code AS 'Code', ";

//if($_POST['show_module_title']) $query = $query."au.title AS 'Assessment Unit / Module', ";
if($_POST['excel_export']) $query = $query."au.title AS 'Assessment Unit / Module', ";
else $query = $query."CONCAT('<A HREF=../teaching.php?au_id=', au.id, '&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit / Module', ";

if($_POST['show_component_title']) $query = $query."
#tc.subject AS 'Component', 
CONCAT('<A HREF=../index.php?tc_id=', tc.id, '&ay_id=$ay_id&department_code=$department_code>',tc.subject,'</A>') AS 'Component', 
";
//if ($_POST['show_instances'])  $query = $query . "e.fullname AS 'Lecturer', ";
if ($_POST['show_instances'])  $query = $query . "
t.term_code AS 'Term',
CONCAT('<A HREF=../index.php?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Lecturer', 
";
if($_POST['show_dates']) $query = $query . "
CONCAT(day(ti.open_date),'/',month(ti.open_date),'/',year(ti.open_date)) AS 'Opening Date',
CONCAT(day(ti.close_date),'/',month(ti.close_date),'/',year(ti.close_date)) AS 'Closing Date',
CONCAT(day(ti.start_date),'/',month(ti.start_date),'/',year(ti.start_date)) AS 'Start Date',
CONCAT(day(ti.end_date),'/',month(ti.end_date),'/',year(ti.end_date)) AS 'End Date', 
CONCAT(day(ti.expiry_date),'/',month(ti.expiry_date),'/',year(ti.expiry_date)) AS 'Exp. Date',
";
$query = $query . "
IF(ti.bookable=1, 'Yes','') AS 'Bkbl', 
ti.teaching_capacity AS 'Cap.'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id AND tc.bookable = 1 
#INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id  
";
$query = $query . "INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id AND ti.start_date > 0 ";
$query = $query . "
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
INNER JOIN Employee e ON e.id = ti.employee_id

WHERE 1=1 
			";
		if($_POST['query_type'] == 'non_ses') $query = $query."AND au.import_into_SES != 1 AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		else $query = $query."AND au.import_into_SES = 1 ";
		if($_POST['query_type'] == 'pgr_only') $query = $query."AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		if($_POST['query_type'] == 'au_only') $query = $query."AND (au.assessment_unit_code NOT LIKE '2B%' AND au.assessment_unit_code NOT LIKE '3C%' AND au.assessment_unit_code NOT LIKE '4D%' AND au.assessment_unit_code NOT LIKE '5E%') ";

		if($ay_id) $query = $query."AND tcau.academic_year_id = $ay_id ";
		if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
		if($course_title_q) $query = $query."AND tc.subject LIKE '%$course_title_q%' ";

//		$query = $query."ORDER BY d.department_name, au.title, tc.subject, t.startdate ";
		$query = $query."ORDER BY d.department_name, au.assessment_unit_code, tc.subject, t.startdate ";

//d_print($query);

		return get_data($query);
}

//========================< SES Enrolment >=========================
//--------------------------------------------------------------------------------------------------------------
function student_ses_enrolment_report_by_course()
//	shows the Doctoral Training Courses (AU) into which students of a given department or division are enrolled
{
	$table = get_own_students_per_ses_course();			// get all students of a selected department or division that are enrolled into any graduate course
	if ($_POST['show_terms'])  $table = amend_term($table);
	
	$table = cleanup($table,1);									//removing all internally used values from the table
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function student_ses_enrolment_report_by_dept()
//	shows the Doctoral Training Courses (AU) into which students of a given department or division are enrolled
{
	$table = get_own_students_per_ses_course_dept();	// get all students of a selected department or division that are enrolled into any graduate course

//	$table = cleanup($table,2);									//removing all internally used values from the table
	return $table;
}

//-----------------------------------------------------------------------------------------
function get_own_students_per_ses_course()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$query ="
SELECT
au.id AS 'AU_ID',
au.assessment_unit_code AS 'Code',
au.title AS 'Assessment Unit / PGR Module',
au_d.department_name AS 'Provided by',
#COUNT(sdp.student_id) AS 'Whole Students',
FORMAT(SUM(sdp.student_id/sdp.student_id * dpd.percentage / 100),1) AS 'Students'

FROM StudentDegreeProgramme sdp
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = $ay_id
INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.year_of_programme = 1 AND dpd.academic_year_id = $ay_id
INNER JOIN Department d ON d.id = dpd.department_id

WHERE 1 = 1 ";
		if($_POST['query_type'] == 'non_ses') $query = $query."AND au.import_into_SES != 1 AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		else $query = $query."AND au.import_into_SES = 1 ";
		if($_POST['query_type'] == 'pgr_only') $query = $query."AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		if($_POST['query_type'] == 'au_only') $query = $query."AND (au.assessment_unit_code NOT LIKE '2B%' AND au.assessment_unit_code NOT LIKE '3C%' AND au.assessment_unit_code NOT LIKE '4D%' AND au.assessment_unit_code NOT LIKE '5E%') ";

		if($ay_id) $query = $query."AND sau.academic_year_id = $ay_id ";

$query = $query . "
AND sdp.status = 'ENROLLED'
AND d.department_code LIKE '$department_code%'

GROUP BY au.title
ORDER BY au_d.department_name, au.title
	";
	return get_data($query);
}

//-----------------------------------------------------------------------------------------
function get_own_students_per_ses_course_dept()
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

WHERE 1 = 1 ";
		if($_POST['query_type'] == 'non_ses') $query = $query."AND au.import_into_SES != 1 AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		else $query = $query."AND au.import_into_SES = 1 ";
		if($_POST['query_type'] == 'pgr_only') $query = $query."AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		if($_POST['query_type'] == 'au_only') $query = $query."AND (au.assessment_unit_code NOT LIKE '2B%' AND au.assessment_unit_code NOT LIKE '3C%' AND au.assessment_unit_code NOT LIKE '4D%' AND au.assessment_unit_code NOT LIKE '5E%') ";

		if($ay_id) $query = $query."AND sau.academic_year_id = $ay_id ";

$query = $query . "
AND sdp.status = 'ENROLLED'
AND d.department_code LIKE '$department_code%'

GROUP BY au_d.department_name
ORDER BY au_d.department_name
	";
//d_print($query);
	return get_data($query);
}





?>