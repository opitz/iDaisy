<?php

//==================================================================================================
//
//	iDAISY main index page
//	Last changes: Matthias Opitz --- 2012-11-01
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
//$version = "120621.1";				// use new location for commonly used files to include
//$version = "120716.1";				// unify all possible user roles
//$version = "120717.1";				// put all academic into index.php
//$version = "120718.1";				// put all units into index.php
//$version = "120727.1";				// renamed to iDaisy
//$version = "120801.1";				// added component functions
//$version = "120807.1";				// all in one iDAISY folder again
//$version = "120808.1";				// using relative actionpages, print_header now without webauth code
//$version = "120814.1";				// hmmm...
//$version = "120815.1";				// cleaning up
//$version = "120820.1";				// fixed export to Excel
//$version = "120823.1";				// cosmetics
//$version = "120927.1";				// added committees
//$version = "121001.1";				// cleanup
//$version = "121004.1";				// testing GIT
$version = "121101.1";				// sans serif


include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

if(!$_POST['debug'] AND $_GET['debug']) $_POST['debug'] = $_GET['debug'];
$debug = $_POST['debug'];

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

//if(!$excel_export AND current_user_is_in_DAISY_user_group("Overseer"))
if(!$excel_export AND $_POST['debug'])
{
	dget();
	dpost();
}

$user_group = current_user_group();
$webauth_code = current_user_webauth();

if ($webauth_code)
{
	if($e_id) show_staff_details();
	elseif($tc_id) show_component_details();
	elseif($au_id) show_unit_details();
	elseif($dp_id) show_programme_details();
	elseif($cte_id) show_committee_details();
	elseif($off_id) show_office_details();
	
	elseif($query_type == 'staff') show_staff_list();
	elseif($query_type == 'unit') show_unit_list();
	elseif($query_type == 'comp') show_component_list();
	elseif($query_type == 'pub') show_publication_list();
	elseif($query_type == 'leave') show_leave_list();
	elseif($query_type == 'prog') show_programme_list();
	elseif($query_type == 'cttee') show_committee_list();
	elseif($query_type == 'office') show_office_list();
	elseif($query_type == 'course') show_course_list();

	elseif(current_user_is_in_DAISY_user_group("Editor"))
		show_general_query();
	else show_staff_details();
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
function show_general_query()
{
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

	$no_ay_id = FALSE;

	print_header('Central Services');

	print "<TABLE BORDER = 0>";

//	1st column
	print "<TR><TD VALIGN = TOP BGCOLOR = LIGHTGREY>";

	print "<H2>Academic Stint Report <FONT SIZE=2><A HREF=academic.php>>></A></FONT></H2>";
	staff_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2>Publications Report <FONT SIZE=2><A HREF=publication.php>>></A></FONT></H2>";
	publication_query_form('', '');

	print "<HR COLOR=WHITE>";
	print "<H2>Academic Leave Report <FONT SIZE=2><A HREF=leave.php>>></A></FONT></H2>";
	leave_query_form('', '');

	print "<HR COLOR=WHITE>";
	print "<H2>Academic Office-Holding Report <FONT SIZE=2><A HREF=office.php>>></A></FONT></H2>";
	office_query_form();

//	print "<H2>Student Query</H2>";
//	student_query_form();

//	2nd column
	print "</TD><TD VALIGN = TOP BGCOLOR = LIGHTGREY>";

	print "<H2>Degree Programme Teaching Report <FONT SIZE=2><A HREF=programme.php>>></A></FONT></H2>";
	programme_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2>Department Teaching Report <FONT SIZE=2><A HREF=teaching.php>>></A></FONT></H2>";
	unit_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2>SES Export Report <FONT SIZE=2><A HREF=course.php>>></A></FONT></H2>";
	course_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2>Committee and Membership Report <FONT SIZE=2><A HREF=committee.php>>></A></FONT></H2>";
	committee_query_form();

//	print "<H2>Teaching Component Report</H2>";
//	component_query_form();
//	print "</TD></TR>";

	print "</TD></TR>";

	print "</TABLE>";
}

?>
