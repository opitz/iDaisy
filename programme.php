<?php

//==================================================================================================
//
//	iDAISY Degree Programme index page
//	Last changes: Matthias Opitz --- 2013-03-04
//
//==================================================================================================
$version = "130304.1";

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	start a timer
	$starttime = start_timer(); 

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
	show_programme_query();
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($query_type) show_footer($version, $totaltime);
else show_footer($version, 0);
	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_programme_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Programme Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('Degree Programme Teaching Report');
	programme_query_form();
	print_programme_intro();
	print_programme_help();
}

//-----------------------------------------------------------------------------------------
function print_programme_intro()
{
	$text = "<B>This report shows for a selected degree programme in your department the teaching provision for each Assessment Unit (AU).</B><BR />
	<P>	
	The Degree Programme Teaching Report comes in two stages:
	<P>
	1.	Summary information<BR />
	From the list of degree programmes 'owned' in whole or part by your department, select a programme according to the search criteria specified (please see help below for details).<BR />
	<FONT COLOR=#FF6600><B>Please note</B></FONT> that computing the summary information <B>for a whole division</B> will run for up to 120 seconds - please be patient and do not reload the page.
	<P>
	2.	Further details <BR />
	To see details for a degree programme, click on its title.<BR />
	The next screen will show a list of AUs that are related to the degree programme and the number of students entered for each AU from the selected degree programme and all degree programmes using the AU, and the share of the total stint for each AU that is used by this degree programme. 
	<P>
	To further see the details for a degree programme listed please click on its title.<BR />
	The next screen will show a list of all assessment units that are related to the degree programme and the number of students of the selected degree programme compared to the total number of students from all possible degree programmes enrolled into each assessment unit.
	It will further show the share related to the selected degree programme of the total stint used for each assessment unit.";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_programme_help()
{
//	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

/*
		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Default</FONT></B>:";
			print "</TD><TD>";
				print "Just click on 'Go!' to get a list of all Degree Programmes the department has a share in.";
			print "</TD>";
		print "</TR><TR>";
*/
		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Academic Year</FONT></B>:";
			print "</TD><TD>";
				print "The current Academic Year is pre-selected; to select another, click on it.";
			print "</TD>";
		print "</TR><TR>";

/*
		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Code</FONT></B>:";
			print "</TD><TD>";
				print "You can enter a (part of a) Degree Programme code to narrow the search.";
			print "</TD>";
		print "</TR><TR>";
*/

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Title</FONT></B>:";
			print "</TD><TD>";
				print "You can enter a (part of a) Degree Programme title to narrow the search.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: You can combine selection criteria</I>";
}


?>
