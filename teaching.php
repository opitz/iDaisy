<?php

//==================================================================================================
//
//	iDAISY Teaching index page
//	Last changes: Matthias Opitz --- 2013-02-28
//
//==================================================================================================
$version = "130228.1";			

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

if($e_id) show_single_staff_details($e_id);
elseif($tc_id) show_component_details();
elseif($au_id) show_unit_details($au_id);
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
	show_unit_query();
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	

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
		$excel_title = "Teaching Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('Department Teaching Report');
	unit_query_form();
	print_teaching_intro();
	print_teaching_help();
}

//-----------------------------------------------------------------------------------------
function print_teaching_intro()
{
	$text = "<B>This report shows teaching provision for each Assessment Unit (AU) owned by your department in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.</B><BR />
	<P>	
	Just click on 'Go!' to get a list of all Assessment Units owned by the department.
	<P>
	The report presents the information in two stages:
	<P>
	1.	Summary information<BR />
	A list of all AUs owned by the department according to the search criteria specified (please see help below for details).

	<P>	
	2.	Further details<BR />
	To select an AU, click on its title for details.
	<P>
	To further see the details for an assessment unit listed please click on its title.<BR />
	The next screen will allow to show teaching details and / or enrollment details for each assessment unit.
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_teaching_help()
{
	print "<HR>";
	print "<H4><FONT FACE='Arial' COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Academic Year</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>The current Academic Year is pre-selected; you can select another by clicking on it.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Assessment Unit Code</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>You can enter a (part of a) Assessment Unit code to narrow the search.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Assessment Unit Title</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>You can enter a (part of a) Assessment Unit title to narrow the search.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Exclude PGR Modules</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>Check this to exclude PGR Modules from the report.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT FACE='Arial' COLOR=DARKBLUE>Please note</FONT><FONT FACE='Arial'>: You can combine selection criteria</I></FONT>";
}

?>
