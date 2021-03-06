<?php

//==================================================================================================
//
//	iDAISY Student Report index page
//	Last changes: Matthias Opitz --- 2013-07-02
//
//==================================================================================================
$version = "130702.1";	// a start...

include 'include_list.php';
//include '../includes/the_usual_suspects.php';

$conn = open_daisydb();													// open DAISY database
//if(!isset($_POST['excel_export'])) print "<FONT FACE = 'Arial'>";

//------------- <uncomment this for debugging  >----------------
//dget();
//dpost();
//--------------------------------------------------------------------------

//	start a timer
	$starttime = start_timer(); 

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!isset($_POST['ay_id'])) 
	if(isset ($_GET['ay_id'])) $_POST['ay_id'] = $_GET['ay_id'];
	else $_POST['ay_id'] = get_current_academic_year_id();

if(!isset($_POST['department_code']) AND isset ($_GET['department_code'])) $_POST['department_code'] = $_GET['department_code'];

//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];
if(isset($_POST['deb']))
{
	dget();
	dpost();
}

//show_header($header);

//if(current_user_is_in_DAISY_user_group("Editor")) show_student_query();
//else show_no_mercy();
if(!current_user_is_in_DAISY_user_group("Editor")) show_no_mercy();



if($_GET['s_id'] > 0) 
{
	show_header("Single Student Report");

	$student = read_record('Student', $_GET['s_id']);
	$_POST['surname_q'] = $student['surname'];
	$_POST['forename_q'] = $student['forename'];
	

//	show_student_query();
	print_query_buttons();

	$table = student_report();
}
elseif($_POST['query_type'] == 'stud') 
{
	show_header("Student Report");
	show_student_query();
	$table = student_list();
}
else
{
	show_header("Student Report");
	show_student_query();
}


//	show the results
if($table)
{
	if($_POST['excel_export']) export2csv_header($table, $header."  ");
	else
	{
		print "<FONT FACE = 'Arial'>";
//		$table_width = array('Department' => 250, 'Committee' => 200, 'Type' => 100, 'Member' => 250, 'Component' => 350, 'Role' => 300, 'Bkbl' => 60, 'Start Term' => 100, 'End Term' => 100);

		print_table($table, $table_width, TRUE);
		print "</FONT>";
	}
}

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($table) show_footer($version, $totaltime);
else show_footer($version, 0);
mysql_close($conn);												// close database connection $conn

?>
