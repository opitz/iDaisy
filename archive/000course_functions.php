<?php

//==================================================================================================
//
//	Separate file with committee functions
//	Last changes: Matthias Opitz --- 2012-11-22
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function course_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='course'>";

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
	print start_row(0) . "Report Type:" . new_column(0) . course_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "Show Unit/Module Titles:";
	print new_column(60);
		if ($_POST['show_module_title']) print "<input type='checkbox' name='show_module_title' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_module_title' value='TRUE'>";

	print new_column(190);
		 print "Show all instances:";
	print new_column(0);
		if ($_POST['show_all_instances'])  print "<input type='checkbox' name='show_all_instances' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_all_instances' value='TRUE'>";
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

//--------------------------------------------------------------------------------------------------------------
function show_course_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	$given_ay_id = $_POST['ay_id'];							// get selected academic_year_id
	$year = $_POST['year'];										// get year
	$department_code = $_POST['department_code'];			// get department_code

	$course_title_q = $_POST['course_title_q'];					// get course_title_q

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
t.term_code AS 'Term',
au.assessment_unit_code AS 'Code', ";
if($_POST['show_module_title']) $query = $query."au.title AS 'Assessment Unit / Module', ";
$query = $query."
tc.subject AS 'Component',
#e.fullname AS 'Lecturer',
CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Lecturer',
IF(ti.bookable=1, 'Yes','') AS 'Bkbl',
#ti.close_date AS 'Closing Date',
CONCAT(day(ti.open_date),'/',month(ti.open_date),'/',year(ti.open_date)) AS 'Opening Date',
CONCAT(day(ti.close_date),'/',month(ti.close_date),'/',year(ti.close_date)) AS 'Closing Date',
CONCAT(day(ti.start_date),'/',month(ti.start_date),'/',year(ti.start_date)) AS 'Start Date',
CONCAT(day(ti.end_date),'/',month(ti.end_date),'/',year(ti.end_date)) AS 'End Date', 
CONCAT(day(ti.expiry_date),'/',month(ti.expiry_date),'/',year(ti.expiry_date)) AS 'Exp. Date',
ti.teaching_capacity AS 'Cap.'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id AND tc.bookable = 1 
#INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id  
";
if ($_POST['show_all_instances'])  $query = $query . "INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id ";
else $query = $query . "INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id AND ti.start_date > 0 ";
$query = $query . "
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
INNER JOIN Employee e ON e.id = ti.employee_id

WHERE 1=1 
			";
		if($_POST['course_report_type'] == 'non_ses') $query = $query."AND au.import_into_SES != 1 AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		else $query = $query."AND au.import_into_SES = 1 ";
		if($_POST['course_report_type'] == 'pgr_only') $query = $query."AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		if($_POST['course_report_type'] == 'au_only') $query = $query."AND (au.assessment_unit_code NOT LIKE '2B%' AND au.assessment_unit_code NOT LIKE '3C%' AND au.assessment_unit_code NOT LIKE '4D%' AND au.assessment_unit_code NOT LIKE '5E%') ";

		if($ay_id) $query = $query."AND tcau.academic_year_id = $ay_id ";
		if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
		if($course_title_q) $query = $query."AND tc.subject LIKE '%$course_title_q%' ";

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
			print_header("SES Export Report");
			course_query_form(); 
			print "<HR>";
			$title_printed = TRUE;
		}
		if($table) 
		{
			$some_result = TRUE;
			if ($excel_export) export2csv($table, "iDAISY_SES_Export_Report_");
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
function show_course_details()
{
	dprint("show_course_details()");
}

//--------------------------------------------------------------------------------------------------------------
function show_course_title($course_data)
//	print details for a given committee record
{
	print "<H3>".$course_data['subject']."</H3>";
}

//--------------------------------------------------------------------------------------------------------------
function show_course_members($cte_id)
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_POST['department_code'];								// get department_code

	$query = "
		SELECT
#		e.fullname AS 'Member',
		CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Member',
		cms.role AS 'Role',
		cms.startdate AS 'Start Date',
		cms.enddate AS 'End Date',
		cms.re_electable AS 'Re-electable',
		cms.notes AS 'Notes'
		
		
		FROM CommitteeMembership cms
		INNER JOIN Employee e ON e.id = cms.employee_id
		
		WHERE cms.committee_id = $cte_id
		
		ORDER BY cms.role, e.fullname
	";
	
	$members = get_data($query);
	
	$column_width = array('Member' => 300, 'Role' =>300, 'Start Date' => 100, 'End Date' => 100, 'Notes' =>300);
	if($members) 
	{
		print "<H4>Members:</H4>";
		print_table($members, $column_width,0);
	}
}

//--------------------------------------------------------------------------------------------------------------
function course_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];

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

//----------------------------------------------------------------------------------------
function course_report_options()
// shows the options for a course report
{
	$report_type = $_POST['course_report_type'];					// get course_report_type
	
//	$options = array("summary", "standard", "detailed", "REF report", "REF report compact");
	$options = array();
	$options[] = array('ses_all', 'All Courses ON SES');
	$options[] = array('non_ses', 'PGR Modules NOT on SES');
	$options[] = array('pgr_only', 'PGR Modules on SES');
	$options[] = array('au_only', 'Assessment Units on SES');
	
	$html = "<select name='course_report_type'>";
	foreach($options AS $option)
	{
		$option_value = $option[0];
		$option_label = $option[1];
		if($option_value==$report_type) $html = $html."<option VALUE='$option_value' SELECTED='selected'>$option_label</option>";
		else $html = $html."<option VALUE='$option_value'>$option_label</option>";
	}
	$html = $html."</select>";

	return $html;
}

//----------------------------------------------------------------------------------------
function is_dtc_module($au_code)
// returns TRUE if the given AU code belongs to a DTC module
{
	if(strstr($au_code, '2B') OR strstr($au_code, '3C') OR strstr($au_code, '4D') OR strstr($au_code, '5E')) return TRUE;
	else return FALSE;
}



?>