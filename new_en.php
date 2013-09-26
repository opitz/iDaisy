<?php

//==================================================================================================
//
//	iDAISY List Employees with a changed Employee Number
//	Last changes: Matthias Opitz --- 2013-01-10
//
//==================================================================================================
$version = "130110.1";

include 'includes/include_list.php';
//include 'includes/opendb.php';
//include 'includes/common_functions.php';
//include 'includes/common_DAISY_functions.php';
//include 'includes/common_export_functions.php';

include 'functions/new_en_functions.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

$webauth_code = current_user_webauth();

if($e_id) show_single_staff_details($e_id);
elseif($query_type == 'new_en') show_new_en_list();
elseif(current_user_is_in_DAISY_user_group("Editor"))
{
	show_new_en_query();
	print_new_en_intro();
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
function show_new_en_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Staff with new Employee Numbers";
		excel_header($excel_title);
	}

	print_header('Staff with new Employee Numbers');
	new_en_query_form();
}

//-----------------------------------------------------------------------------------------
function print_new_en_intro()
{
	$text = "<B>This report shows all staff members of of your department that have been issued a new Employee Number.</B><BR />
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_new_en_help()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Default</FONT></B>:";
			print "</TD><TD>";
				print "Just click on 'Go!' to get a list of all offices of the department.";
			print "</TD>";
		print "</TR><TR>";

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
