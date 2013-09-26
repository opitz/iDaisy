<?php

//==================================================================================================
//
//	basic reports main index page
//	Last changes: Matthias Opitz --- 2013-05-07
//
//==================================================================================================
$version = "130507.1";					// starting

include 'include_list.php';

//	open the database and start a timer
$conn = open_daisydb();					// open DAISY database
$starttime = start_timer(); 

//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];
if(isset($_POST['deb']))
{
	dget();
	dpost();
}

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();


if(isset($_POST['query_type'])) $query_type = $_POST['query_type'];
else $query_type = '';

if($query_type == 'au') 
{
	$header = "Assessment Units";
	$table = assessment_units();
} 
elseif($query_type == 'tc') 
{
	$header = "Teaching Components";
	$table = teaching_components();
} 
elseif($query_type == 'ti') 
{
	$header = "Teaching Instances";
	$table = teaching_instances();
} 
elseif($query_type == 'st') 
{
	$header = "Students";
	$table = students();
} 
elseif($query_type == 'tst') 
{
	$header = "Teaching Stint Tariff";
	$table = teaching_stint_tariff();
} 
elseif($query_type == 'svst') 
{
	$header = "Supervision Stint Tariff";
	$table = supervision_stint_tariff();
} 

//	see if there is the request for a single record and if so display it
elseif (isset($_GET['table']) AND isset($_GET['id']))
{
	$header = "Edit Record";
	$table_name = $_GET['table'];
	$id = $_GET['id'];
//	$record = read_record($table_name, $id);
	edit_record($_GET['table'], $_GET['id']);
}

//	save a record
elseif ($_POST['action_type'] == 'save')
{
	$header = "Save Record";
	$table_name = $_POST['table_name'];
	$id = $_POST['id'];
	$record = save_record($_POST['table_name'], $_POST['id']);
}

else 	$header = "Basics";


if(isset($_POST['excel_export']))
{
	export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";
	print_header($header);
	if(current_user_is_in_DAISY_user_group("Administrator")) show_basic_query($query_type);
	else show_no_mercy();

	if($table) print_table($table, array(), 1);


//	if (isset($_GET['table']) AND isset($_GET['id'])) $record = edit_record($_GET['table'], $_GET['id']);


//	if($record) edit_record($table_name, $record, array(), 1);
	print "</FONT>";

// 	stop the timer
	$totaltime = stop_timer($starttime); 

//	print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	print "<HR><FONT SIZE=2 COLOR=GREY>v.$version  | executed in $totaltime seconds</FONT>";
}
mysql_close($conn);												// close database connection $conn

	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_basic_query($query_type)
{
	basic_query_form();
//	if(!query_type)
	if(!$_POST['query_type'] AND !$_GET['table'])
	{
		print_basic_intro();
		print_basic_help();
	}
}

//-----------------------------------------------------------------------------------------
function print_basic_intro()
{
	$text = "<B>These are the Basic Reports.</B>
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;

}

//-----------------------------------------------------------------------------------------
function print_basic_help()
{
}

?>
