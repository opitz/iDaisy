<?php
// huhu!
//==================================================================================================
//
//	iDAISY Develop index page
//	Last changes: Matthias Opitz --- 2013-09-24
//
//==================================================================================================
$version = "D3-130620.1";			// new design!
$version = "D3-130703.1";			// moved Students
$version = "D3-130722.1";			// moved Forms (Basics)
$version = "D3-130924.1";			// briefly renamed 'Forms (Basics)'to 'Basics (Forms)'


include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

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

	print_header('Central Services - Development');

	print "<TABLE>";
	print "<TR>";
	print "<TD WIDTH=$left_indent>";
	print "</TD>";
	print "<TD>";
	print "<TABLE>";

//	===================================< Line 1 >===================================
	print "<TR>";						// start a new row
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
			print "<A HREF=../balance/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Balance Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "...";
			print "</FONT><P><P>";
		print "</TD>"; 					// end a column
		print "<TD WIDTH=$middle_space>"; 		// start a new column
		print "</TD>"; 					// end a column
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
			print "<A HREF=../special/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Special Reports</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "All kind of stuff that fits nowhere else (yet).";
			print "</FONT><P><P>";
		print "</TD>"; 					// end a column
	print "</TR>";						// end a row


//	===================================< Line 2 >===================================
	print "<TR>";						// start a new row
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
			print "<A HREF=../basic/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Basics</B> (Forms)</FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Some reports on basic tables (<I>in progress</I>)";
			print "</FONT><P><P>";
		print "</TD>"; 					// end a column
		print "<TD WIDTH=$middle_space>"; 		// start a new blank column
		print "</TD>"; 					// end a column
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column

		print "</TD>"; 					// end a column
	print "</TR>";						// end a row

	
//	================================< Close Table >=================================
	print "</TABLE>";
	print "</TD>";
	print "</TR>";
	print "</TABLE>";

	print "<HR>";
	
//	=================================< NAVIGATION >=================================
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
