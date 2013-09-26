<?php

//==================================================================================================
//
//	Separate file with committee functions
//	Last changes: Matthias Opitz --- 2013-06-13
//
//==================================================================================================

//===========================< Query >============================
//--------------------------------------------------------------------------------------------------------------
function show_the_query()
{
	if(!$_POST['excel_export'])
	{	
		the_query_form();
		if(!$_POST['query_type'])
		{
//			print_ses_intro();
//			print_ses_options();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function the_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$rows = $_POST['rows'];														// get number of rows for query window
	if (!$rows) $rows = 25;															// default value for rows
	$cols = 220;																	// default value of colums for query window

	$query = $_POST['query'];														// get query
	$query = stripslashes($query);

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='query'>";

	print"Select Table: " . table_options() . "&nbsp; &nbsp;  Show fields: " . html_checkbox('show_fields');		
	print"&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;";		
	print "Rows query window: <input type='text' size = 2 name='rows' value = '$rows' />";
	print"<BR />";		

	print "<textarea name = 'query' rows='$rows' cols='$cols'>$query</textarea><BR />";

//	display the buttons
//	print "<HR>";
	print_query_buttons();
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function query_report_options()
// shows the options for a ses report
{
	$query_type = $_POST['query_type'];					// get course_report_type
	
	$options = array();

	$options[] = array( 'All Courses on SES', 'ses_all');
	$options[] = array('Assessment Units on SES', 'au_only');
	$options[] = array('PGR Modules on SES', 'pgr_only');
	$options[] = array('PGR Modules NOT on SES', 'non_ses');

	$options[] = array('Student SES Enrolment by Dept','dtc_student_ses_enrolment_by_dept');
	$options[] = array('Student SES Enrolment by Course','dtc_student_ses_enrolment_by_course');
	
	$html = "<select name='query_type'>";
	foreach($options AS $option)
	{
		$option_label = $option[0];
		$option_value = $option[1];
		if($option_value==$query_type) $html = $html."<option VALUE='$option_value' SELECTED='selected'>$option_label</option>";
		else $html = $html."<option VALUE='$option_value'>$option_label</option>";
	}
	$html = $html."</select>";

	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function database_tables_options()
//returns the html code for a drop down menu of the tables of the database connected to
{
	$params = parse_ini_file('idaisy.ini');
	$db_name  = $params['dbname'];
	$db_host = $params['dbhost'];

	$query = "
		SHOW TABLES
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$counter = 0;
	$tables = array();
	$html = "<select name='default_table'>";
	$html = $html . "<option value=''><I>Select table</I></option>";
	while( $table = mysql_fetch_assoc($result)) 
	{
		$tables[] = $table;
		$html = $html . "<option value = ".$table["Tables_in_$db_name"].">".$table["Tables_in_$db_name"]."</option>";
	}
	$html = $html . "</select>";

	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function print_query_intro()
{
	$text = "<B>The report shows the graduate training or options of your department that are displayed in the Student Enrolment System (SES).</B><BR />
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;
	print "<P>";
	print "Available Report Types:<p>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>All courses on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Assessment Units and PGR Modules available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Assessment Unts on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all Assessment Units that are  available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>PGR Modules on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all PGR Modules that are  available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>PGR Modules NOT on SES</FONT></B>:";
			print "</TD><TD>";
				print "This will list all PGR Modules that are <I>not</I> available through the SES.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Student SES Enrolment by Dept</FONT></B>:";
			print "</TD><TD>";
				print "This will list the enrolment into courses offered through the SES of students of the selected department by the department providing the courses.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Student SES Enrolment by  Course</FONT></B>:";
			print "</TD><TD>";
				print "This will list the enrolment of students of the selected department by course offered through the SES.<BR />
				<B>Please note</B> that this report can run for more than 60 seconds - please be patient and do not reload the page.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
}

//--------------------------------------------------------------------------------------------------------------
function print_query_options()
{
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Options</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Component Titles</FONT></B>:";
			print "</TD><TD>";
				print "This will additionally show the title of the related Teaching Components in case they are different.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Instances</FONT></B>:";
			print "</TD><TD>";
				print "This will amend the list by all Teaching Instances related to each listed course.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Dates</FONT></B>:";
			print "</TD><TD>";
				print "This will amend the list by the start and end dated for enrolment and for the courses themselves.";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT COLOR=DARKBLUE>Show Student Details</FONT></B>:";
			print "</TD><TD>";
				print "This will show the numbers of students enrolled into a course per department owning the degree programmes they are enrolled into.";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT COLOR=DARKBLUE>Please note</FONT>: These options are ignored for the Student SES Enrolment Reports</I>";
}

//--------------------------------------------------------------------------------------------------------------
function query_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

//	$query = stripslashes($_POST['query']);
	$query = $_POST['query'];
//d_print($query);
	$table = get_data($query);

//	if ($_POST['show_students'])  $table = amend_dp_students_per_au($table);

//	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//----------------------------------------------------------------------------------------
function table_options()
// shows a drop down selector for all tables in the selected database
{
	$db_name = get_database_name();

	$html = "<select name='table'>";
	$html = $html."<option value=''>No table selected</option>";

//	get the list of tables
	$query = "SHOW TABLES";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($table = mysql_fetch_assoc($result))	
	{
//		$html = $html."<option value='".$table['Tables_in_daisy2_opitz']."'>".$table['Tables_in_daisy2_opitz']."</option>";
//		$html = $html."<option value='".$table[0]."'>".$table[0]."</option>";
		$html = $html . "<option value = ".$table["Tables_in_$db_name"].">".$table["Tables_in_$db_name"]."</option>";
//		if($_POST['table'] == $table["Tables_in_$db_name"]) $html = $html . "<option value = ".$table["Tables_in_$db_name"]." selected='selected'>".$table["Tables_in_$db_name"]."</option>";

//		$html = $html."<option value='".$table[0]."'";
//		if($_POST['table'] == $table[0]) $html = $html." selected='selected'";
//		$html = $html.">".$table[0]."</option>";
	}
	$html = $html."</select>";
	return $html;
}



?>