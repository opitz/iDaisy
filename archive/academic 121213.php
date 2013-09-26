<?php

//==================================================================================================
//
//	iDAISY Staff index page
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

$webauth_code = current_user_webauth();

if($e_id) show_single_staff_details($e_id);
elseif($au_id) show_unit_details();
elseif($tc_id) show_component_details();
elseif($query_type == 'staff') show_staff_list();
elseif($query_type == 'unit') show_unit_list();
elseif($query_type == 'comp') show_component_list();
elseif($query_type == 'pub') show_publication_list();
//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
elseif(current_user_is_in_DAISY_user_group("Editor"))
	show_publication_query();
else
	show_no_mercy();

if(!$excel_export) print "</FONT>";
mysql_close($conn);												// close database connection $conn

if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_publication_query()
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

	print_header('Academic Stint Report');
	staff_query_form();
	print_academic_intro();
	print_academic_help();
}

//-----------------------------------------------------------------------------------------
function print_academic_intro()
{
	$text = "<B>This report shows information about the stint obligation and stint delivered by each academic employed by your department in a chosen academic year in various levels of detail.</B><BR />
	This report shows for each department the stint earned by an academic / lecturer - or all of them - during an academic year. 
	<P>	
	The Academic Stint Report comes in two stages:
	<P>
	1.	Summary information <BR />
	A list of academics according to the search criteria specified (see help below for details) with the stint obligation and the stint delivered by each.
	<P>
	Staff members with a joint post is shown once for each employing department; the information relating to the 'other' employing department(s) is shown in grey font to help distinguish it. 
	<P>
	2.	Further details 
	For details of an individual staff member, click on the name.
	The next screen allows selection of details by using the buttons at the top, for teaching, supervision, leave etc, for a staff member, export to Excel, or reset of the report to the start screen. 

	<P>
	To further see the details for a staff member listed please click on its name.<BR />
	On the next screen you will be able to select details for the selected staff member , export the output to Excel or reset the report to the start screen.";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_academic_help()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Academic Year</FONT></B>:";
			print "</TD><TD>";
				print "The current academic year is pre-selected; you can select another year by clicking on it.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Include other staff</FONT></B>:";
			print "</TD><TD>";
				print "Select this to include staff members who do not have an academic-related staff classification.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Include staff without stint</FONT></B>:";
			print "</TD><TD>";
				print "Select this to include staff members who have not earned any stint in the selected academic year(s).";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Show manually addedd staff only</FONT></B>:";
			print "</TD><TD>";
				print "Select this to get a list of the manually added staff members in your department.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Include borrowed staff</FONT></B>:";
			print "</TD><TD>";
				print "Select this to include staff ‘borrowed’ from other departments, i.e. providing teaching or supervision for your department but not employed by it.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Include inactive staff</FONT></B>:";
			print "</TD><TD>";
				print "Select this to include staff members that are not longer marked as ACTV (in the HR system).";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Show Stint Details for all academics’</FONT></B>:";
			print "</TD><TD>";
				print "Selecting this will give you a detailed listing of teaching, supervising and leave for each member of staff.";
			print "</TD>";
		print "</TR><TR>";
/*
		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Show department sums</FONT></B>:";
			print "</TD><TD>";
				print "Select this to get the sums of the shown data.";
			print "</TD>";
		print "</TR><TR>";
*/

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: You can combine selection criteria</I>";
}


?>
