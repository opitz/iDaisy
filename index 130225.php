<?php

//==================================================================================================
//
//	iDAISY main index page
//	Last changes: Matthias Opitz --- 2013-02-08
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
$version = "121101.2";					// sans serif
$version = "121105.1";					// added grant
$version = "121108.2";					// added parameter to show_unit_details
$version = "12112.12";					// ...
$version = "130208.1";					// using modern timer


include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

//	start a timer
$starttime = start_timer(); 

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

if(!$_POST['debug'] AND $_GET['debug']) $_POST['debug'] = $_GET['debug'];
$debug = $_POST['debug'];

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

//if(!$excel_export AND current_user_is_in_DAISY_user_group("Overseer"))
//if(!$excel_export AND $_POST['debug'])
if(1==1)
//if(!$excel_export)
{
	dget();
	dpost();
}

$user_group = current_user_group();
$webauth_code = current_user_webauth();

if ($webauth_code)
{
	if($e_id) show_single_staff_details($e_id);
	elseif($tc_id) show_component_details();
	elseif($au_id) show_unit_details($au_id);
	elseif($dp_id) show_programme_details();
	elseif($cte_id) show_committee_details();
	elseif($off_id) show_office_details();
	elseif($rg_id) show_grant_details();
	
	elseif($query_type == 'staff') show_staff_list();
	elseif($query_type == 'unit') show_unit_list();
	elseif($query_type == 'comp') show_component_list();
	elseif($query_type == 'pub') show_publication_list();
	elseif($query_type == 'leave') show_leave_list();
	elseif($query_type == 'prog') show_programme_list();
	elseif($query_type == 'cttee') show_committee_list();
	elseif($query_type == 'office') show_office_list();
	elseif($query_type == 'course') show_course_list();
	elseif($query_type == 'grant') show_grant_list();

	elseif(current_user_is_in_DAISY_user_group("Editor"))
		show_general_query();
	else 
	{
		$e_id = get_employee_id_from_webauth($webauth_code);
		show_single_staff_details($e_id);
	}
}
else
	show_no_mercy();
if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

// stop the timer
$totaltime = stop_timer($starttime); 

if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=GREY>v.$version  | executed in $totaltime seconds</FONT>";
	

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
	} else
	{
		print "<body link=DARKBLUE vlink=DARKBLUE alink=#FF6600> ";
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$no_ay_id = FALSE;

	print_header('Central Services');

	print "<TABLE BORDER = 0>";

//	==================================< 1st column >===========================
	print "<TR><TD VALIGN = TOP BGCOLOR = LIGHTGREY>";

	print "<H2><A HREF=academic.php  STYLE=TEXT-DECORATION:NONE>Academic Stint Report</A></FONT></H2>";
	staff_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=publication.php  STYLE=TEXT-DECORATION:NONE>Publications Report</A></FONT></H2>";
	publication_query_form('', '');

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=leave.php  STYLE=TEXT-DECORATION:NONE>Academic Leave Report</A></FONT></H2>";
	leave_query_form('', '');

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=office.php  STYLE=TEXT-DECORATION:NONE>Academic Office-Holding Report</A></FONT></H2>";
	office_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=grant.php  STYLE=TEXT-DECORATION:NONE>Research Grant Report</A></FONT></H2>";
	grant_query_form();

//	print "<H2>Student Query</H2>";
//	student_query_form();

//	==================================< 2nd column >===========================
	print "</TD><TD VALIGN = TOP BGCOLOR = LIGHTGREY>";

	print "<H2><A HREF=programme.php  STYLE=TEXT-DECORATION:NONE>Degree Programme Teaching Report</A></FONT></H2>";
	programme_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=teaching.php  STYLE=TEXT-DECORATION:NONE>Department Teaching Report</A></FONT></H2>";
	unit_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=ses/index.php  STYLE=TEXT-DECORATION:NONE>SES Course Report</A></FONT></H2>";
	 ses_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=graduate/index.php  STYLE=TEXT-DECORATION:NONE>Graduate Training Report</A></FONT></H2>";
	graduate_query_form();

	print "<HR COLOR=WHITE>";
	print "<H2><A HREF=committee.php  STYLE=TEXT-DECORATION:NONE>Committee and Membership Report</A></FONT></H2>";
	committee_query_form();

//	print "<H2>Teaching Component Report</H2>";
//	component_query_form();
//	print "</TD></TR>";

	print "</TD></TR>";

	print "</TABLE>";
}

?>
