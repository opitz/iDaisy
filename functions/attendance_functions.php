<?php

//==================================================================================================
//
//	module attendance functions
//	Last changes: Matthias Opitz --- 2012-11-13
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function module_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$module_title_q = $_POST['module_title_q'];										// get module_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='module'>";

//	write out all $_POST attributes as hidden input
	print write_post_vars_html();

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();
	print end_row();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Course Title:" . new_column(0) . "<input type='text' name = 'module_title_q' value='$module_title_q' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

/*
//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "Show Unit/Module Titles:";
	print new_column(60);
		if ($_POST['show_module_title']) print "<input type='checkbox' name='show_module_title' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_module_title' value='TRUE'>";
	print end_row();

	print "</TABLE>";

	print "<HR>";
*/
//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function show_module_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	$given_ay_id = $_POST['ay_id'];							// get selected academic_year_id
	$year = $_POST['year'];										// get year
	$department_code = $_POST['department_code'];			// get department_code

	$module_title_q = $_POST['module_title_q'];					// get module_title_q

//	define column width in output table
	$table_width = array('Department' => 250, 'Term' => 60, 'Code' => 100, 'Assessment Unit / Module' => 350, 'Component' => 350, 'Lecturer' => 150, 'Bkbl' => 60, 'Closing' => 100, 'Capacity' => 40);

//	get list of Academic Years
	$query = "
		SELECT
		*
		FROM AcademicYear ay
		WHERE 1=1
		";
	if($given_ay_id > 0) $query = $query."AND id = $given_ay_id ";
	$query = $query."ORDER BY ay.startdate";
	$ac_years = get_data($query);
		
	$some_result = FALSE;
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
		$query = "
			SELECT 
#			au.id AS 'AU_ID',
		";
		if(strlen($department_code) < 4) $query = $query."d.department_name AS 'Department', ";
		$query = $query . "
au.assessment_unit_code AS 'Code', 
au.title AS 'Assessment Unit', 
";
if($_POST['show_module_title']) $query = $query."au.title AS 'Assessment Unit / Module', ";
$query = $query."
tc.subject AS 'Component',
#e.fullname AS 'Lecturer',
t.term_code AS 'Term',
CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Lecturer',
IF(ti.bookable=1, 'Yes','') AS 'Bkbl',
#ti.close_date AS 'Closing Date',
CONCAT(day(ti.close_date),'/',month(ti.close_date),'/',year(ti.close_date)) AS 'Closing Date',
ti.teaching_capacity AS 'Cap.'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id AND tc.bookable = 1
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
INNER JOIN Employee e ON e.id = ti.employee_id

WHERE 1=1 
AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%')
#AND au.import_into_SES = 1
			";

		if($ay_id) $query = $query."AND tcau.academic_year_id = $ay_id ";
		if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
		if($module_title_q) $query = $query."AND tc.subject LIKE '%$module_title_q%' ";

		$query = $query."ORDER BY d.department_name, au.title, tc.subject, t.startdate ";

if($_POST['debug'] == 'query') d_print($query);

		$table = get_data($query);

//	amend the table with the number of enrolled students per course
/*
		$new_table = array();
		if($table) foreach($table AS $row)
		{
//			$au_id = $row['AU_ID'];
			$au_id = array_shift($row);
			
			$query = "
			";
			
			$row['Students'] = count_au_students($au_id, $ay_id);
			$new_table[] = $row;
		}
		$table = $new_table;	
*/		

		if(!$excel_export AND !$title_printed)
		{
			print_header("PGR Module Attendance Report");
			course_query_form(); 
			print "<HR>";
			$title_printed = TRUE;
		}
		if($table) 
		{
			$some_result = TRUE;
			if ($excel_export) export2csv($table, "PGR_Module_Attendance_Report_");
			else 
			{
				print"<H4>".$ac_year['label']."<H4>";
				print_table($table, $table_width, TRUE);
			}
		}
	}
	if(!$some_result) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function show_module_attendance()
{
	dprint("show_module_attendance()");
}

//--------------------------------------------------------------------------------------------------------------
function show_module_title($module_data)
//	print title of a module (assessment unit)
{
	print "<H3>".$module_data['title']."</H3>";
}

//--------------------------------------------------------------------------------------------------------------
function module_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";

	print "<TD WIDTH=200 ALIGN=LEFT></TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}


?>