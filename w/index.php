<?php

//==================================================================================================
//
//	iDAISY  Workbench Report index page
//	Last changes: Matthias Opitz --- 2013-03-26
//
//==================================================================================================
$version = "130326.1";

include 'workbench_include_list.php';
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

$header = "Workbench";

if($_POST['query_type'] == 'query') $table = query_report();

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Overseer")) show_the_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";

//	if($table) print_table($table, array(), $_POST['show_line_numbers']);
	if($table) p_table($table);
	print "</FONT>";

}

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($table) show_footer($version, $totaltime);
else show_footer($version, 0);
mysql_close($conn);												// close database connection $conn

?>
