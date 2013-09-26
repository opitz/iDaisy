<?php

//==================================================================================================
//
//	iDAISY Teaching Report index page
//	Last changes: Matthias Opitz --- 2013-08-14
//
//==================================================================================================
$version = "130814.1";

include 'teaching_include_list.php';
//include '../includes/the_usual_suspects.php';

$conn = open_daisydb();													// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//------------- <uncomment this for debugging  >----------------
//dget();
//dpost();
//--------------------------------------------------------------------------

//	start a timer
	$starttime = start_timer(); 

//	if no academic year is selected in the first place propose the current academic year as a selection
//if(!$ay_id) $ay_id = get_current_academic_year_id();
//$_POST['ay_id'] = $ay_id;
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();

$header = "Department Teaching Report";

if($_POST['query_type'] == 'teaching') 
{
	$header = "Department Teaching Report";
	$table = teaching_report();
}

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Editor")) show_teaching_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv_header($table, $header."  ");
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
