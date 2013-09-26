<?php

//==================================================================================================
//
//	iDAISY SES Course index page
//	Last changes: Matthias Opitz --- 2012-11-06
//
//==================================================================================================
$version = "121106.1";

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();													// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

if(!$_POST['debug'] AND $_GET['debug']) $_POST['debug'] = $_GET['debug'];

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

if(!$excel_export AND $_POST['debug'])
{
	dget();
	dpost();
}

if($e_id) show_single_staff_details($e_id);
elseif($au_id) show_unit_details();
elseif($tc_id) show_component_details();
elseif($cte_id) show_committee_details();
elseif($ti_id) show_course_details();

elseif($query_type == 'staff') show_staff_list();
elseif($query_type == 'unit') show_unit_list();
elseif($query_type == 'comp') show_component_list();
elseif($query_type == 'pub') show_publication_list();
elseif($query_type == 'leave') show_leave_list();
elseif($query_type == 'cttee') show_committee_list();
elseif($query_type == 'course') show_course_list();

//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
elseif(current_user_is_in_DAISY_user_group("Editor"))
	show_course_query();
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	
//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_course_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "SES Export Report";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('SES Export Report');
	course_query_form();
	print_course_intro();
}

//-----------------------------------------------------------------------------------------
function print_course_intro()
{
	$text = "<B>The report shows the graduate training or options of your department that are displayed in the Student Enrolment System (SES).</B><BR />
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_course_help()
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
