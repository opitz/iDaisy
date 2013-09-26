<?php

//==================================================================================================
//
//	iDAISY Office index page
//	Last changes: Matthias Opitz --- 2013-03-04
//
//==================================================================================================
$version = "130304.1";

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

$webauth_code = current_user_webauth();


//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
if(current_user_is_in_DAISY_user_group("Editor"))
{
	show_office_query();
	if($query_type == 'office') show_office_list();
	else
	{
		print_office_intro();
		print_office_help();
	}
}
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	
//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_office_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Academic Office-Holding Report";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('Academic Office-Holding Report');
	office_query_form();
}

//-----------------------------------------------------------------------------------------
function print_office_intro()
{
	$text = "<B>This report shows the academic offices of a department and their holders.</B><BR />
	Just click on 'Go!' to get a list of all offices of the department.<BR />
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_office_help()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Year</FONT></B>:";
			print "</TD><TD>";
				print "Select a year of the office holding to narrow down the list.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: You can combine selection criteria</I>";
}

?>
