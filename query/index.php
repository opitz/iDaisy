<?php

//==================================================================================================
//
//	iDAISY  Query Report index page
//	Last changes: Matthias Opitz --- 2013-06-13
//
//==================================================================================================
$version = "130226.3";
$version = "130404.1";		// fixed export
$version = "130411.1";		// added table selection
$version = "130613.1";		// added WHERE 1=1

include 'query_include_list.php';
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

$header = "Query";

//if(!$_POST['query_type'] == 'query')

if($_POST['table'])
{
	if(isset($_POST['show_fields']))
		$_POST['query'] = "DESCRIBE ".$_POST['table'];
	else 
		$_POST['query'] = "
SELECT * \n
FROM ".$_POST['table']."\n
WHERE 1=1\n
LIMIT 10 
	";
}

if($_POST['query']) $table = query_report();

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Overseer")) show_the_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv($table, $header."  ");
	else print "ach...!";
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
