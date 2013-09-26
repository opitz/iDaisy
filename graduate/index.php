<?php

//==================================================================================================
//
//	Graduate Training Reports main index page
//	Last changes: Matthias Opitz --- 2013-04-24
//
//==================================================================================================
$version = "130424.3";	

//ini_set("memory_limit","1024M");

include 'graduate_include_list.php';
//include '../includes/the_usual_suspects.php';

if(!$_POST['excel_export'] AND $_POST['debug'])
//if (1==1)
{
	dget();
	dpost();
}

$conn = open_daisydb();					// open DAISY database
$starttime = start_timer(); 				// start a timer

//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];
if(isset($_POST['deb']))
{
	dget();
	dpost();
}

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();

$header = "Graduate Training Report";

if($_POST['query_type'] == 'dtc') 
{
	$header = "Graduate Training Report - Doctoral Training Courses";
	$table = graduate_au_report();
}

elseif($_POST['query_type'] == 'dtc_students_depts') 
{
	$header = "Graduate Training Report - Doctoral Training Courses / Students by Department";
	$table = graduate_au_report();
}

elseif($_POST['query_type'] == 'dtc_students_progs') 
{
	$header = "Graduate Training Report - Doctoral Training Courses / Students by Degree Programme";
	$table = graduate_au_report();
}

elseif($_POST['query_type'] == 'dtc_student_graduate_enrolment_by_course') 
{
	$header = "Graduate Training Report - Student Enrolment";
	$table = student_graduate_enrolment_report_by_course();
}

elseif($_POST['query_type'] == 'dtc_student_graduate_enrolment_by_dept') 
{
	$header = "Graduate Training Report - Student Enrolment";
	$table = student_graduate_enrolment_report_by_dept();
}

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Editor")) show_graduate_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";

//	define column width in output table
	$table_width = array('Department' => 300, 'Code' => 150, 'Assessment Unit / PGR Module' => 450, 'Students' => 50, 'Term' => 50, 'MT (%)' => 60, 'HT (%)' => 60, 'TT (%)' => 60, 'Stint' => 60);

	if($table) print_table($table, $table_width, $_POST['show_line_numbers']);
	print "</FONT>";

}

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($table) show_footer($version, $totaltime);
else show_footer($version, 0);
mysql_close($conn);												// close database connection $conn

?>
