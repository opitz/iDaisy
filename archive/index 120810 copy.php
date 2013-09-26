<?php

//==================================================================================================
//
//	iDAISY main index page
//	Last changes: Matthias Opitz --- 2012-08-07
//
//==================================================================================================
//$version = "120419.1";			// branched off from query report
//$version = "120420.1";			// new user check
//$version = "berlin_120420.2";		// berlin branch
//$version = "120423.2";			// back to SSD 
//$version = "120424.1";			// added multi-query 
//$version = "120425.1";			// added tables for better formatting purposes 
//$version = "120426.1";			// added Programme and Unit query 
//$version = "120427.1";			// added Department query 
//$version = "120524.1";			// added Teaching Component query | using query_boxes.php
//$version = "120614.1";			// added academic year
$version = "120621.1";				// use new location for commonly used files to include
$version = "120716.1";				// unify all possible user roles
$version = "120717.1";				// put all academic into index.php
$version = "120718.1";				// put all units into index.php
$version = "120727.1";				// renamed to iDaisy
$version = "120801.1";				// added component functions
$version = "120807.1";				// all in one iDAISY folder again
$version = "120808.1";				// using relative actionpages, print_header now without webauth code

include 'opendb.php';
include 'common_functions.php';
include 'common_DAISY_functions.php';
include 'common_export_functions.php';
include 'common_staff_functions.php';
include 'common_unit_functions.php';
include 'common_component_functions.php';
include 'common_pub_functions.php';

include 'query_boxes.php';

include 'staff_buttons.php';
include 'unit_buttons.php';
include 'component_buttons.php';
include 'staff_attribs.php';
include 'unit_attribs.php';
include 'component_attribs.php';

$conn = open_daisydb();										// open DAISY database

$query_type = $_POST['query_type'];							// get query type

$e_id = $_GET["e_id"];										// get employee ID e_id
if(!$e_id) $e_id = $_POST['e_id'];

$au_id = $_GET["au_id"];									// get assessment unit ID au_id
if(!$au_id) $au_id = $_POST['au_id'];

$tc_id = $_GET["tc_id"];									// get teaching component ID au_id
if(!$tc_id) $tc_id = $_POST['tc_id'];



$user_group = current_user_group();

if($_SERVER['HTTP_HOST'] == 'daisy-dev.socsci.ox.ac.uk') $webauth_code = 'admn2055';	// on the dev server its always m.opitz
if($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') $webauth_code = $_SERVER['REMOTE_USER'];

if ($webauth_code)
{
	if($e_id) show_staff_details($webauth_code);
	elseif($au_id) show_unit_details($webauth_code);
	elseif($tc_id) show_component_details($webauth_code);
	elseif($query_type == 'staff') show_staff_list($webauth_code);
	elseif($query_type == 'unit') show_unit_list($webauth_code);
	elseif($query_type == 'comp') show_component_list($webauth_code);
	elseif($query_type == 'pub') show_publication_list($webauth_code);
	elseif(user_is_in_DAISY_user_group($webauth_code, "Administrator") OR user_is_in_DAISY_user_group($webauth_code, "Super-Administrator") OR user_is_in_DAISY_user_group($webauth_code, "Overseer"))
		show_general_query($webauth_code);
	else show_staff_details($webauth_code);
}
else
	show_no_mercy();

mysql_close($conn);												// close database connection $conn

print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_general_query($webauth_code)
{
//dprint($webauth_code);
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself

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

	print_header('Central');

	print "<TABLE BORDER = 0>";
	print "<TR><TD VALIGN = TOP BGCOLOR = LIGHTGREY>";
	print "<H2>Staff Query</H2>";
	staff_query_form($webauth_code, $department_code,'', $actionpage);
//	print "<HR COLOR=WHITE><H2>Student Query</H2>";
//	student_query_form($webauth_code, $department_code, '', 'student_index.php');

	print "</TD><TD VALIGN = TOP BGCOLOR = LIGHTGREY>";
//	print "<H2>Programme Query</H2>";
//	programme_query_form($webauth_code, $department_code, '', 'programme_index.php');
	print "<H2>Unit Query</H2>";
	unit_query_form($webauth_code, $department_code, '', $actionpage);
	print "<HR COLOR=WHITE><H2>Teaching Component Query</H2>";
	component_query_form($webauth_code, $department_code, '', $actionpage);
	print "<HR COLOR=WHITE><H2>Publication Query</H2>";
//	publication_query_form($webauth_code, $department_code, '', 'index.php');
	publication_query_form($webauth_code, $department_code, '', $actionpage);
	print "</TD></TR>";
	print "</TABLE>";
}

?>
