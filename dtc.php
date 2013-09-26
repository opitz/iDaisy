<?php

//==================================================================================================
//
//	DTC reports main index page
//	Last changes: Matthias Opitz --- 2013-01-22
//
//==================================================================================================
$version = "130212.1";					// starting

include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

if(!$excel_export AND $_POST['debug'])
//if (1==1)
{
	dget();
	dpost();
}

$conn = open_daisydb();										// open DAISY database

//	start a timer
	$starttime = start_timer(); 

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

if($query_type == 'dtc') 
{
	$header = "Graduate Training Report - Doctoral Training Courses";
	$table = dtc_report();
}

elseif($query_type == 'prov_by_stud') 
{
	$header = "Graduate Training Report - Provision by Student Dept";
	$table = provision_by_student_dept_report();
} 
elseif($query_type == 'stud_by_prov') 
{
	$header = "Graduate Training Report - Student by Provision Dept";
	$table = student_by_provision_dept_report();
} 
	
if($_POST['excel_export'])
{
	export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";
	
	if($au_id) show_unit_details($au_id);	//	If an AU title was clicked on show AU details
	elseif(current_user_is_in_DAISY_user_group("Editor"))
	{
		print_header($header);
		dtc_query($query_type);
	}
	else show_no_mercy();

//	define column width in table
	$table_width = array('Department' => 300, 'Code' => 150, 'Assessment Unit / PGR Module' => 450, 'Students' => 50, 'Term' => 50, 'MT (%)' => 60, 'HT (%)' => 60, 'TT (%)' => 60, 'Stint' => 60);

	if($table) print_table($table, $table_width, $_POST['show_line_numbers']);
	print "</FONT>";

//	stop the timer
	$totaltime = stop_timer($starttime); 
	print "<HR><FONT SIZE=2 COLOR=GREY>v.$version  | executed in $totaltime seconds</FONT>";
}
mysql_close($conn);												// close database connection $conn

	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function dtc_query($query_type)
{
	dtc_query_form();
//	if(!query_type)
	if(!$_POST['query_type'])
	{
		print_dtc_intro();
		print_dtc_help();
	}
}

//-----------------------------------------------------------------------------------------
function print_dtc_intro()
{
	$text = "<B>This report shows information on DTC Assessment Units and PGR Modules.</B>
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;

}

//-----------------------------------------------------------------------------------------
function print_dtc_help()
{
}

?>
