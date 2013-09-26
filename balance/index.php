<?php

//==================================================================================================
//
//	iDAISY Balance Report index page
//	Last changes: Matthias Opitz --- 2013-03-06
//
//==================================================================================================
$version = "130306.1";

include 'balance_include_list.php';
include '../includes/the_usual_suspects.php';

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

$header = "Balance Report";

if($_POST['query_type'] == 'balance') 
{
	$header = "Balance Report";
	$table = balance_report();
}

if($_POST['query_type'] == 'other_au') 
{
	$header = "Balance Report - Student Stint Share used from other Departments";
	$table = other_au_report();
}

if($_POST['query_type'] == 'own_au') 
{
	$header = "Balance Report - Student Stint Share delivered to other Departments";
	$table = own_au_report();
}

if($_POST['query_type'] == 'lent') 
{
	$header = "Balance Report - Stint from Lent Teaching";
	$table = lent_report();
}

if($_POST['query_type'] == 'borrow') 
{
	$header = "Balance Report - Stint for Borrowed Teaching";
	$table = borrow_report();
}

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Editor")) show_balance_query();
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
