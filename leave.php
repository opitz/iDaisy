<?php

//==================================================================================================
//
//	iDAISY Leave index page
//	Last changes: Matthias Opitz --- 2012-11-07
//
//==================================================================================================
$version = "121107.1";

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

if($e_id) show_single_staff_details($e_id);
elseif($au_id) show_unit_details();
elseif($tc_id) show_component_details();
elseif($query_type == 'staff') show_staff_list();
elseif($query_type == 'unit') show_unit_list();
elseif($query_type == 'comp') show_component_list();
elseif($query_type == 'pub') show_publication_list();
elseif($query_type == 'leave') show_leave_list();
//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
elseif(current_user_is_in_DAISY_user_group("Editor"))
	show_leave_query();
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

if(!$_POST['excel_export']) print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_leave_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Academic Leave Report";
		excel_header($excel_title);
	} else
	{
		print_header('Academic Leave Report');
		leave_query_form();
		print_leave_intro();
	}
}

//-----------------------------------------------------------------------------------------
function print_leave_intro()
{
	$text = "<B>This report shows leave and buyouts by term of each academic staff member of the department for a selected academic year.</B><BR />";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
//	print "<HR>";

}

?>
