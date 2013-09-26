<?php

//==================================================================================================
//
//	iDAISY Publication index page
//	Last changes: Matthias Opitz --- 2012-08-14
//
//==================================================================================================
$version = "120808.1";				// separate publications launch page
$version = "120810.3";				// bugfix user
$version = "120814.1";				//no webauth_code in arguments anymore

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database

if($e_id) show_staff_details();
elseif($au_id) show_unit_details();
elseif($tc_id) show_component_details();
elseif($query_type == 'staff') show_staff_list();
elseif($query_type == 'unit') show_unit_list();
elseif($query_type == 'comp') show_component_list();
elseif($query_type == 'pub') show_publication_list();
//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
elseif(current_user_is_in_DAISY_user_group("Administrator"))
	show_unit_query();
else
	show_no_mercy();

mysql_close($conn);												// close database connection $conn

print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_unit_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('Teaching by Unit Report');
	unit_query_form();
}

?>
