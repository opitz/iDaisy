<?php

//==================================================================================================
//
//	special reports main index page
//	Last changes: Matthias Opitz --- 2013-02-15
//
//==================================================================================================
$version = "130215.1";					// starting

include 'special_include_list.php';

//	start a timer
$starttime = start_timer(); 

//if(!$_POST['excel_export'] AND $_POST['debug'])
if (1==2)
{
	dget();
	dpost();
}

$conn = open_daisydb();										// open DAISY database

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();
if(isset($_POST['query_type'])) $query_type = $_POST['query_type'];
else $query_type = '';

if($query_type == 'dup_names') 
{
	$header = "Duplicate Name Report";
	$table = duplicate_names_report();
} 
elseif($query_type == 'dup_en') 
{
	$header = "Duplicate Employee Number Report";
	$table = duplicate_employee_numbers_report();
} 
elseif($query_type == 'au_by_owner') 
{
	$header = "Assessment Units by Owner Report";
	$table = au_by_owner_report();
} 
elseif($query_type == 'au_dp_students') 
{
	$header = "Student Programmes per Assessment Unit Report";
	$table = au_dp_students_report();
} 
elseif($query_type == 'au_dept_students') 
{
	$header = "Student Departments per Assessment Unit Report";
	$table = au_dept_students_report();
} 
elseif($query_type == 'stint_balance') 
{
	$header = "Stint Balance Report";
	$table = stint_balance_report();
} 
elseif($query_type == 'joint_postholders') 
{
	$header = "Joint Postholders Report";
	$table = joint_postholder_report();
}

elseif($query_type == 'TC_AU') 
{
	$header = "TC AU Report";
	$table = TC_AU_report();
}

elseif($query_type == 'TI_TC') 
{
	$header = "TI TCReport";
	$table = TI_TC_report();
}

else 	$header = "Special Report";

	
if(isset($_POST['excel_export']))
{
	export2csv($table, $header."  ");
} else
{
	print "<FONT FACE = 'Arial'>";
	print_header($header);
	if(current_user_is_in_DAISY_user_group("Administrator")) show_special_query($query_type);
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
function show_special_query($query_type)
{
	special_query_form();
//	if(!query_type)
	if(!$_POST['query_type'])
	{
		print_special_intro();
		print_special_help();
	}
}

//-----------------------------------------------------------------------------------------
function print_special_intro()
{
	$text = "<B>These are the Special Reports.</B>
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;

}

//-----------------------------------------------------------------------------------------
function print_special_help()
{
}

?>
