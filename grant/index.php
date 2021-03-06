<?php

//==================================================================================================
//
//	iDAISY Grant Report index page
//	Last changes: Matthias Opitz --- 2013-04-24
//
//==================================================================================================
$version = "130423.3";

include 'include_list.php';
//include '../includes/the_usual_suspects.php';

$conn = open_daisydb();													// open DAISY database
if(!isset($_POST['excel_export'])) print "<FONT FACE = 'Arial'>";

//------------- <uncomment this for debugging  >----------------
//dget();
//dpost();
//--------------------------------------------------------------------------

//	start a timer
	$starttime = start_timer(); 

//	if no academic year is selected in the first place propose the current academic year as a selection
//if(!$ay_id) $ay_id = get_current_academic_year_id();
//$_POST['ay_id'] = $ay_id;

if(!isset($_POST['ay_id'])) 
	if(isset ($_GET['ay_id'])) $_POST['ay_id'] = $_GET['ay_id'];
	else $_POST['ay_id'] = get_current_academic_year_id();

//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];
if(isset($_POST['deb']))
{
	dget();
	dpost();
}


//if(!isset($_POST['grant_id']) AND isset ($_GET['grant_id'])) $_POST['grant_id'] = $_GET['grant_id'];

if(!isset($_POST['department_code']) AND isset ($_GET['department_code'])) $_POST['department_code'] = $_GET['department_code'];

$header = "Research Grant Report";

if($_POST['query_type'] == 'grant') 
{
	$header = "Research Grant Report";
	$table = grant_report();
}

if($_POST['grant_id']) 
{
	$header = "Single Grant Report";
	$table = grant_report();
}


//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Editor")) show_grant_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv_header($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";

//	define column width in output table
	$table_width = array('Department' => 250, 'Committee' => 200, 'Type' => 100, 'Member' => 250, 'Component' => 350, 'Role' => 300, 'Bkbl' => 60, 'Start Term' => 100, 'End Term' => 100);

	if($table) print_table($table, $table_width, $_POST['show_line_numbers']);
	print "</FONT>";

}

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($table) show_footer($version, $totaltime);
else show_footer($version, 0);
mysql_close($conn);												// close database connection $conn

?>
