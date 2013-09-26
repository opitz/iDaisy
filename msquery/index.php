<?php

//==================================================================================================
//
//	iDAISY  MSSQL (Data Warehouse) Query Report index page
//	Last changes: Matthias Opitz --- 2013-06-28
//
//==================================================================================================
$version = "130628.1";		// here we go...

include 'query_include_list.php';
//include '../includes/the_usual_suspects.php';

$conn = open_daisydb();

$msconn = mssql_connect_data_warehousedb();						// open MSSQL database
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

$header = "DW Query";

//if(!$_POST['query_type'] == 'query')

if($_POST['table'])
{
	if(isset($_POST['show_fields']))
//		$_POST['query'] = "DESCRIBE ".$_POST['table'];
		$_POST['query'] = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$_POST['table']."'";
	else 
		$_POST['query'] = "
SELECT TOP 10 * \n
FROM ".$_POST['table']."\n
WHERE 1=1\n
	";
}

if($_POST['query']) $table = dw_query_report();

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
