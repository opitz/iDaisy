<?php

//==================================================================================================
//
//	iDAISY Shared Courses Pricing Report index page
//	Last changes: Matthias Opitz --- 2012-11-01
//
//==================================================================================================
$version = "121101.1";

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

include 'pricing_functions.php';
$conn = open_daisydb();													// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

if($e_id) show_staff_details();
elseif($au_id) show_unit_details();
elseif($tc_id) show_component_details();
elseif($cte_id) show_committee_details();
elseif($ti_id) show_pricing_details();

elseif($query_type == 'staff') show_staff_list();
elseif($query_type == 'unit') show_unit_list();
elseif($query_type == 'comp') show_component_list();
elseif($query_type == 'pub') show_publication_list();
elseif($query_type == 'leave') show_leave_list();
elseif($query_type == 'cttee') show_committee_list();
elseif($query_type == 'course') show_course_list();
elseif($query_type == 'pricing') show_pricing_list();

//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
elseif(current_user_is_in_DAISY_user_group("Administrator"))
	show_pricing_query();
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	
//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_pricing_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Shared Courses Pricing Report";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('Shared Courses Pricing Report');
	pricing_query_form();
}

?>
