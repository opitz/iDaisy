<?php

//==================================================================================================
//
//	concordat reports main index page
//	Last changes: Matthias Opitz --- 2013-07-29
//
//==================================================================================================
$version = "130311.1";		// starting
$version = "130320.1";		// adding 'Gross Stint' to supervision and teaching reports, removed 'no HUSC' filter
$version = "130404.1";		// adding 'Appointed Stint' to supervision and teaching reports, removed 'Gross Stint'
$version = "130415.1";		// changed 'Appointment Split' to 'Stint Obligation Split'
$version = "130709.3";		// corrected a bug in the Teaching Report where the same student is enrolled into more than one assessment unit that is related to the same TC
$version = "130729.1";		// excluded academics on casual payroll (post number begins with 'C')

//ini_set("memory_limit","256M");
//ini_set("memory_limit","512M");
ini_set("memory_limit","1024M");

include 'concordat_include_list.php';

//	start a timer
$starttime = start_timer();

if(!$excel_export AND $_POST['debug'])
//if (1==1)
{
	dget();
	dpost();
}

$conn = open_daisydb();										// open DAISY database

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

if($query_type == 'teaching' OR $query_type == 'teaching_calc') 
{
	$header = "Concordat Teaching Report";
	$table = concordat_teaching_report();
} 
elseif($query_type == 'teaching_nohusc' OR $query_type == 'teaching_nohusc_calc') 
{
	$header = "Concordat Teaching Report (no HUSC)";
	$table = concordat_teaching_report();
} 
elseif($query_type == 'supervision') 
{
	$header = "Concordat Supervision Report";
//	$version = concordat_supervision_report_version();
	$table = concordat_supervision_report();
} 
elseif($query_type == 'supervision_calc') 
{
	$header = "Concordat Supervision Report";
//	$version = concordat_supervision_report_version();
	$table = concordat_supervision_calc_report();
} 
elseif($query_type == 'student') 
{
	$header = "Concordat AU Student Sums Report";
	$table = concordat_student_report();
} 
elseif($query_type == 'single_student') 
{
	$header = "Concordat AU Single Student Report";
	$table = concordat_single_student_report();
} 
elseif($query_type == 'student_load') 
{
	$header = "Concordat Student Load Report";
	$table = student_load_report();
} 
else 	$header = "Concordat Report";

	
if($_POST['excel_export'])
{
	if($table) export2csv_header($table, $header."  ");
//	export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";
	print_header($header);
	if(current_user_is_in_DAISY_user_group("Super-Administrator")) show_concordat_query($query_type);
	else show_no_mercy();

	if($table) print_table($table, array(), 1);
	print "</FONT>";

// 	stop the timer
	$totaltime = stop_timer($starttime); 

//	print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	print "<HR><FONT SIZE=2 COLOR=GREY>v.$version  | executed in $totaltime seconds</FONT>";
}
mysql_close($conn);												// close database connection $conn

	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_concordat_query($query_type)
{
	concordat_query_form();
//	if(!query_type)
	if(!$_POST['query_type'])
	{
		print_concordat_intro();
		print_concordat_help();
	}
}

//-----------------------------------------------------------------------------------------
function print_concordat_intro()
{
	$text = "<B>These reports show information on Concordat data.</B>
	<P>
	The <B>AU Student per DP Report</B> will show for each Assessment Unit of a given division or department the number of students enrolled to each degree programme related to that assessment unit.<BR />
	(Please not that in most cases a relation between an assessment unit and a degree programme cannot  be determined if there are no enrolled students.)<P>
	<FONT COLOR=#FF6600><B>Please note:</B></FONT> This report typically runs for up to 60 seconds for a single department, up to 10 minutes for all of the Social Sciences Division and up to <FONT COLOR=#FF6600><B>2 hours</B></FONT>(<FONT COLOR=#FF6600><B>!</B></FONT>) for the whole university!
	<HR>
	<P>
	The <B>Supervision Report</B> will show all supervision of which either the supervisor or the student (or both) are coming from the selected division or department.<P>
	This <FONT COLOR=#FF6600>new</FONT> report will now calculate the stint for joint post holders as follows: <BR />
	If the owning department of the student supervised by a joint post holder is one of the departments of the joint post 100% of the stint is accounted against the student department. <BR />If not the stint will be split according to the appointment split.<BR />
	This rule does <I>not</I> apply for supervision of DPhil students where the stint is always split according to the appointment split.
	<HR>
	<P>
	The <B>Teaching Report</B> will show for all assessment units (excluding those having a code beginning with 'HUSC') of a selected division or department each teaching instance given, the lecturer and - needed for joint posts - the appointment split that will split the earned stint according to the relation of the departmental stint obligation.<P>
	This <FONT COLOR=#FF6600>new</FONT> report will now calculate the stint for joint post holders as follows: <BR />
	If some teaching given by a joint post holder is related to an Assessment Unit that is owned by one of his post departments 100% of the stint will be accounted against this department's stint obligation.<BR /> If not the stint will be split according to the appointment split.
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
}

//-----------------------------------------------------------------------------------------
function print_concordat_help()
{
}

?>
