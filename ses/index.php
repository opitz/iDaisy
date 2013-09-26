<?php

//==================================================================================================
//
//	iDAISY SES Course index page
//	Last changes: Matthias Opitz --- 2013-04-08
//
//==================================================================================================
$version = "130305.1";
$version = "130408.1";		//	added 'Own Students" in table

include 'ses_include_list.php';
include '../includes/the_usual_suspects.php';

$conn = open_daisydb();													// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";
if(!$excel_export AND $_POST['debug'])
//if (1==1)
{
	dget();
	dpost();
}

//	start a timer
	$starttime = start_timer(); 

//	if no academic year is selected in the first place propose the current academic year as a selection
//if(!$ay_id) $ay_id = get_current_academic_year_id();
//$_POST['ay_id'] = $ay_id;
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();


$header = "SES Course Report";

if($_POST['query_type'] == 'ses_all' OR $_POST['query_type'] == 'non_ses' OR $_POST['query_type'] == 'pgr_only' OR $_POST['query_type'] == 'au_only') 
{
	$header = "SES Course Report";
	$table = ses_report();
}

elseif($_POST['query_type'] == 'dtc_student_ses_enrolment_by_course') 
{
	$header = "SES Course Report - Student Enrolment by Course";
	$table = student_ses_enrolment_report_by_course();
}

elseif($_POST['query_type'] == 'dtc_student_ses_enrolment_by_dept') 
{
	$header = "SES Course Report - Student Enrolment by Department";
	$table = student_ses_enrolment_report_by_dept();
}

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Editor")) show_ses_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";

//	define column width in output table
	$table_width = array('Department' => 250, 'Term' => 60, 'Code' => 100, 'Assessment Unit / Module' => 350, 'Component' => 350, 'Lecturer' => 150, 'Bkbl' => 60, 'Closing' => 100, 'Capacity' => 40);

	if($table) print_table($table, $table_width, $_POST['show_line_numbers']);
	print "</FONT>";

}

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($table) show_footer($version, $totaltime);
else show_footer($version, 0);
mysql_close($conn);												// close database connection $conn

?>
