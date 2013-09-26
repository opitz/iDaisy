<?php
// huhu!
//==================================================================================================
//
//	iDAISY Maintenance index page
//	Last changes: Matthias Opitz --- 2013-09-03
//
//==================================================================================================
$version = "D3-130903.1";			// new design!


include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';
include '../includes/common_export_functions.php';

$conn = open_daisydb();										// open DAISY database
$webauth_code = current_user_webauth();

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];
if(isset($_POST['deb']))
{
	dget();
	dpost();
}

//if(user_is_in_DAISY_user_group($webauth_code, "Super-Administrator")) show_options();
if(user_is_in_DAISY_user_group($webauth_code, "Super-Administrator")) show_options();
//else show_no_mercy();
else
{
	$e_id = get_employee_id_from_webauth($webauth_code);
	show_no_mercy($e_id);
}

mysql_close($conn);												// close database connection $conn
show_footer($version,0);
	

//========================================================================================
//				Functions
//========================================================================================

//-----------------------------------------------------------------------------------------
function show_options()
{
	$webauth_code = current_user_webauth();

	$left_indent = 50;
	$column_width = 600;
	$middle_space = 20;
	$link_size = 5;
	$text_size = 2;
	$link_colour = 'DARKBLUE';
	$special_colour = '#FF6600';
	$nav_colour = 'GREY';
	
	print "<FONT FACE = 'Arial'>";
	print "<body link=$link_colour vlink=$link_colour alink=#FF6600> ";

	print_header('Central Services - Maintenance');

	print "<TABLE>";
	print "<TR>";
	print "<TD WIDTH=$left_indent>";
	print "</TD>";
	print "<TD>";
	print "<TABLE>";

//	===================================< Line 1 >===================================
	print "<TR>";					// start a new row
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
			print "<A HREF=import_hris.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Import HRIS data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Import a CSV file with Staff and Post data previously exported from HRIS";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD WIDTH=$middle_space>"; 	// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
			print "<A HREF=replace_staff.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Replace Employees</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "In case there are duplicate records in DAISY for the same person the data can be unified here.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 2 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=parse_ses_data.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Parse SES Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "...";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=parse_xcri_data.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Parse XCRI Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "...";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 3 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=get_audp.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Get Assessment Unit - Degree Programme Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Update the table for relations between Degree Programmes and Assessment Units.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=repair_audp.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Repair Assessment Unit - Degree Programme Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "...";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 4 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=import_supervision.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Import Supervision</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Update the Supervision Data one term at a time. To be executed in 9th week each term.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=import_teaching_data.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Import Teaching Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Please know what you are doing...!";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 5 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=import_ref_publications.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$special_colour><B>Import REF Publication Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Under development";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column


		print "</TD>"; 				// end a column
	print "</TR>";					// end a row
	
	print "</TABLE>";
	print "</TD>";
	print "</TR>";
	print "</TABLE>";

	print "<HR>";
	
//	===================================< LAST LINE >===================================
	if(user_is_in_DAISY_user_group($webauth_code, "Overseer"))
	{
		print "<TABLE>";
		print "<TR>";
		print "<TD WIDTH=$left_indent>";
			print "<A HREF=../index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$nav_colour><B><<<</B></FONT></A>";
		print "</TD>";

			print "<TD WIDTH=$column_width>"; 	// start a new column
				print "<A HREF=../index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$nav_colour><B>Back</B></FONT></A>";
			print "</TD>"; 				// end a column
			print "<TD WIDTH=$middle_space>"; 	// start a new blank column
			print "</TD>"; 				// end a column
			print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
			print "</TD>"; 				// end a column

		print "</TR>";

	print "</TABLE>";
	}
}


?>
