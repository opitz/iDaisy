<?php
// huhu!
//==================================================================================================
//
//	iDAISY main index page
//	Last changes: Matthias Opitz --- 2013-08-08
//
//==================================================================================================
//$version = "120419.1";			// branched off from query report
//$version = "120420.1";			// new user check
//$version = "berlin_120420.2";			// berlin branch
//$version = "120423.2";			// back to SSD 
//$version = "120424.1";			// added multi-query 
//$version = "120425.1";			// added tables for better formatting purposes 
//$version = "120426.1";			// added Programme and Unit query 
//$version = "120427.1";			// added Department query 
//$version = "120524.1";			// added Teaching Component query | using query_boxes.php
//$version = "120614.1";			// added academic year
//$version = "120621.1";			// use new location for commonly used files to include
//$version = "120716.1";			// unify all possible user roles
//$version = "120717.1";			// put all academic into index.php
//$version = "120718.1";			// put all units into index.php
//$version = "120727.1";			// renamed to iDaisy
//$version = "120801.1";			// added component functions
//$version = "120807.1";			// all in one iDAISY folder again
//$version = "120808.1";			// using relative actionpages, print_header now without webauth code
//$version = "120814.1";			// hmmm...
//$version = "120815.1";			// cleaning up
//$version = "120820.1";			// fixed export to Excel
//$version = "120823.1";			// cosmetics
//$version = "120927.1";			// added committees
//$version = "121001.1";			// cleanup
//$version = "121004.1";			// testing GIT
//$version = "121101.2";			// sans serif
//$version = "121105.1";			// added grant
//$version = "121108.2";			// added parameter to show_unit_details
//$version = "12112.12";			// ...
//$version = "130208.1";			// using modern timer
//$version = "130225.1";			// radically trimmed down
//$version = "130412.1";			// successful migration to mubuntu
//$version = "130419.1";			// added basic functions
//$version = "130422.2";				// using the new leave
//$version = "130425.1";				// using the new grant
//$version = "130507.1";				// now with debug
//$version = "130515.1";				// added shape & size
$version = "D3-130605.1";			// DAISY3 compatible
$version = "D3-130614.1";			// cosmetics
$version = "D3-130620.1";			// cosmetics
$version = "D3-130703.1";			// added Students, cosmetics
$version = "D3-130722.1";			// moved Forms(Basics) to develop
$version = "D3-130808.1";			// with edit forms (overseer only) in the basic section


include 'includes/include_list.php';
include 'includes/the_usual_suspects.php';

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
if($_GET['e_id'] > 0) show_single_staff_details($_GET['e_id']);

//if(user_is_in_DAISY_user_group($webauth_code, "Super-Administrator")) show_options();
elseif(user_is_in_DAISY_user_group($webauth_code, "Super-Administrator")) show_options();
//else show_no_mercy();
else
{
	$_POST['show_post'] = 1;
	$_POST['show_teaching'] = 1;
	$_POST['show_supervision'] = 1;
	$_POST['show_leave'] = 1;

	$e_id = get_employee_id_from_webauth($webauth_code);
	show_single_staff_details($e_id);
}

mysql_close($conn);												// close database connection $conn
show_footer($version,0);
//if(!$excel_export) print "<HR><FONT SIZE=2 COLOR=GREY>v.$version </FONT>";
	

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

	print_header('Central Services');

	print "<TABLE>";
	print "<TR>";
	print "<TD WIDTH=$left_indent>";
	print "</TD>";
	print "<TD>";
	print "<TABLE>";

//	===================================< Line 1 >===================================
	print "<TR>";					// start a new row
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
//			print "<H2><A HREF=academic.php  STYLE=TEXT-DECORATION:NONE>Academic Stint Report</A></H2>";
			print "<A HREF=academic.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Academic Stint Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows information about the stint obligation and stint delivered by each academic employed by your department in a chosen academic year in various levels of detail.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD WIDTH=$middle_space>"; 	// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD WIDTH=$column_width VALIGN=TOP>"; 	// start a new column
//			print "<H2><A HREF=programme/index.php STYLE=TEXT-DECORATION:NONE>Degree Programme Teaching Report</A></FONT></H2>";
			print "<A HREF=programme/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Degree Programme Teaching Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows teaching provision for each Assessment Unit (AU) related to a Degree Programme owned by your department in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 2 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=publication.php  STYLE=TEXT-DECORATION:NONE>Publications Report</A></H2>";
			print "<A HREF=publication.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Publications Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows information on publications related to the department in various levels of detail.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=teaching/index.php  STYLE=TEXT-DECORATION:NONE>Department Teaching Report</A></H2>";
			print "<A HREF=teaching/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Department Teaching Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows teaching provision for each Assessment Unit (AU) owned by your department in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 3 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=leave/index.php  STYLE=TEXT-DECORATION:NONE>Academic Leave Report</A></H2>";
			print "<A HREF=leave/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Academic Leave Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows leave and buyouts by term of each academic staff member of the department for a selected academic year.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=ses/index.php  STYLE=TEXT-DECORATION:NONE>SES Course Report</A></H2>";
			print "<A HREF=ses/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>SES Course Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "The report shows the graduate training or options of your department that are displayed in the Student Enrolment System (SES).";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 4 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=office/index.php  STYLE=TEXT-DECORATION:NONE>Academic Office-Holding Report</A></H2>";
			print "<A HREF=office/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Academic Office-Holding Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows the offices and office holdings of the department.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=graduate/index.php  STYLE=TEXT-DECORATION:NONE>Graduate Training Report</A></H2>";
			print "<A HREF=graduate/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Graduate Training Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows information on DTC Assessment Units and PGR Modules.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 5 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=grant/index.php  STYLE=TEXT-DECORATION:NONE>Research Grant Report</A></FONT></H2>";
			print "<A HREF=grant/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Research Grant Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows the grants related to staff of your department.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=committee/index.php  STYLE=TEXT-DECORATION:NONE>Committee and Membership Report</A></FONT></H2>";
			print "<A HREF=committee/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Committee and Membership Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows the committees of your department and their members.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 6 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
//			print "<H2><A HREF=sizeshape/index.php  STYLE=TEXT-DECORATION:NONE>Size & Shape Report</A></H2>";
			print "<A HREF=sizeshape/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Size & Shape Report</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "This report shows student norm hours for each Assessment Unit (AU) related to a Degree Programme owned by your department in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
			if(user_is_in_DAISY_user_group($webauth_code, "Super-Administrator"))
			{
//				print "<H2><A HREF=concordat/index.php  STYLE=TEXT-DECORATION:NONE>Concordat</A></H2>";
				print "<A HREF=concordat/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Concordat</B></FONT></A>";
				print "<BR><FONT SIZE = $text_size>";
				print "These reports show information on Concordat data.";
				print "</FONT><P><P>";
			}
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row

//	===================================< Line 7 >===================================
	print "<TR>";					// start a new row
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=student/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Students</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Student Data(<I>in progress</I>)";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
		print "<TD>"; 				// start a new blank column
		print "</TD>"; 				// end a column
		print "<TD VALIGN=TOP>"; 		// start a new column
			print "<A HREF=teaching_data/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$link_colour><B>Teaching Data</B></FONT></A>";
			print "<BR><FONT SIZE = $text_size>";
			print "Some basic reports on teaching data (<I>in progress</I>)";
			print "</FONT><P><P>";
		print "</TD>"; 				// end a column
	print "</TR>";					// end a row
		
//	=================================< Overseer Line >=================================
	if(user_is_in_DAISY_user_group($webauth_code, "Overseer"))
	{
		print "<TR>";					// start a new row
			print "<TD VALIGN=TOP>"; 		// start a new column
				print "<A HREF=query/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$special_colour><B>Query</B></FONT></A>";
				print "<BR><FONT SIZE = $text_size>";
				print "Submit direct queries to the DAISY database (Overseers only!)";
				print "</FONT><P><P>";
			print "</TD>"; 				// end a column
			print "<TD>"; 				// start a new blank column
			print "</TD>"; 				// end a column
			print "<TD VALIGN=TOP>"; 		// start a new column
				print "<A HREF=msquery/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$special_colour><B>DW Query</B></FONT></A>";
				print "<BR><FONT SIZE = $text_size>";
				print "Submit direct queries to the Data Warehouse database (Overseers only!)";
				print "</FONT><P><P>";
			print "</TD>"; 				// end a column
		print "</TR>";					// end a row
	}
	
//	===================================< Close Table >==================================
	print "</TABLE>";
	print "</TD>";
	print "</TR>";
	print "</TABLE>";

	print "<HR>";
	

//	===================================< Navigation >===================================
	if(user_is_in_DAISY_user_group($webauth_code, "Overseer"))
	{
		print "<TABLE>";

		print "<TR>";
		print "<TD WIDTH=$left_indent>";
			print "<A HREF=maintenance/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$nav_colour><B>>>></B></FONT></A>";
		print "</TD>";
		print "<TD WIDTH=$column_width>"; 	// start a new column
				print "<A HREF=maintenance/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$nav_colour><B>Maintenance</B></FONT></A>";
				print "<FONT SIZE=$link_size COLOR=$nav_colour><B> | </B></FONT>";
				print "<A HREF=develop/index.php STYLE=TEXT-DECORATION:NONE><FONT SIZE=$link_size COLOR=$nav_colour><B>Development</B></FONT></A>";
		print "</TD>"; 				// end a column
		print "</TR>";

		print "</TABLE>";
	}
}

?>
