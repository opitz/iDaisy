<?php

//==================================================================================================
//
//	iDAISY Committee Membership index page
//	Last changes: Matthias Opitz --- 2013-03-11
//
//==================================================================================================
$version = "130311.1";

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

$conn = open_daisydb();										// open DAISY database
if(!$excel_export) print "<FONT FACE = 'Arial'>";

//	start a timer
	$starttime = start_timer(); 

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

//------------- <uncomment this for debugging  >----------------
dget();
dpost();
//--------------------------------------------------------------------------

$header = "Committee Report";

if($_POST['query_type'] == 'cttee') 
{
	$header = "Committee Report Report";
	$table = committee_report();
}

//	show the results
show_header($header);

if(current_user_is_in_DAISY_user_group("Editor")) show_programme_query();
else show_no_mercy();

if($_POST['excel_export'])
{
	if($table) export2csv_header($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";

//	define column width in output table
	$table_width = array('Department' => 250, 'Term' => 60, 'Code' => 100, 'Assessment Unit / Module' => 350, 'Component' => 350, 'Lecturer' => 150, 'Bkbl' => 60, 'Closing' => 100, 'Capacity' => 40);

	if($table) print_table($table, $table_width, $_POST['show_line_numbers']);
	print "</FONT>";

}

//	stop the timer
$totaltime = stop_timer($starttime); 
if ($table) show_footer($version, $totaltime);
else show_footer($version, 0);
mysql_close($conn);												// close database connection $conn




























$webauth_code = current_user_webauth();

//if($cte_id) show_committee_details();
//elseif($_POST['cttee_id']) show_committee_details();

elseif($query_type == 'staff') show_staff_list();
elseif($query_type == 'unit') show_unit_list();
elseif($query_type == 'comp') show_component_list();
elseif($query_type == 'pub') show_publication_list();
elseif($query_type == 'leave') show_leave_list();
elseif($query_type == 'cttee') show_committee_list();
//elseif(current_user_is_in_DAISY_user_group("Administrator") OR current_user_is_in_DAISY_user_group("Super-Administrator") OR current_user_is_in_DAISY_user_group("Overseer"))
elseif(current_user_is_in_DAISY_user_group("Editor"))
{
	show_cttee_query();
	print_cttee_intro();
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
function show_cttee_query()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Committee Membership Report";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$department_code = $_POST['department_code'];			// get department_code

	print_header('Committee Membership Report');
	committee_query_form();
}

//-----------------------------------------------------------------------------------------
function print_cttee_intro()
{
	$text = "<B>This report shows the committees of your department and their members.</B><BR />
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<HR>";

}

//-----------------------------------------------------------------------------------------
function print_cttee_help()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Default</FONT></B>:";
			print "</TD><TD>";
				print "Just click on 'Go!' to get a list of all offices of the department.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Year</FONT></B>:";
			print "</TD><TD>";
				print "Select a year of the office holding to narrow down the list.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: You can combine selection criteria</I>";
}

?>
