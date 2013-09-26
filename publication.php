<?php

//==================================================================================================
//
//	iDAISY Publication index page
//	Last changes: Matthias Opitz --- 2012-11-06
//
//==================================================================================================
$version = "121106.1";

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

	print_header('Publications Report');
	publication_query_form();
	print_publication_intro();
	print_publication_help();
}

//-----------------------------------------------------------------------------------------
function print_publication_intro()
{
	$text = "<B>This report shows information on publications related to the department in various levels of detail,  <BR />
	according to the search criteria specified (please see help below for details).</B>
	<P>
	Just click on 'Go!' to get a summary list of all Publications by staff members of the department.
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;

}

//-----------------------------------------------------------------------------------------
function print_publication_help()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Year of Publication</FONT></B>:";
			print "</TD><TD>";
				print "You may narrow down the list by selecting a time span in which the publication was published. (This will have no effects on REF reports)";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Author</FONT></B>:";
			print "</TD><TD>";
				print "You can enter (a part of) an Author's name to narrow the search.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT COLOR=DARKBLUE>Title</FONT></B>:";
			print "</TD><TD>";
				print "You can enter (a part of) a publication title to narrow the search.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD VALIGN=TOP>";
				print "<B><FONT COLOR=DARKBLUE>Report Type</FONT></B>:";
			print "</TD><TD>";
				print "<B>General report: summary</B> - a short list of publications.<BR>";
				print "<B>General report: standard</B> - a standard list of publications.<BR>";
				print "<B>General report: detailed</B> - a detailed list of publications.<BR>";
				print "<B>REF report</B> - standard list for REF data.<BR>";
				print "<B>REF report compact</B> - compact list for REF data.<BR>";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: You can combine selection criteria</I>";
}

?>
