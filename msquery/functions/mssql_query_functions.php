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

	print"Select Table: " . mssql_table_options() . "&nbsp; &nbsp;  Show fields: " . html_checkbox('show_fields');		
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

///--------------------------------------------------------------------------------------------------------------
function dw_query_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

//	$query = stripslashes($_POST['query']);
	$query = $_POST['query'];
//d_print($query);
	$table = get_mssql_data($query);

//	if ($_POST['show_students'])  $table = amend_dp_students_per_au($table);

//	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//----------------------------------------------------------------------------------------
function mssql_table_options()
// shows a drop down selector for all tables in the selected MSSQL database
{
	$html = "<select name='table'>";
	$html = $html."<option value=''>No table selected</option>";

//	get the list of tables
	$query = "SELECT * FROM  INFORMATION_SCHEMA.TABLES";

	$result = mssql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($table = mssql_fetch_assoc($result))	
	{
		$html = $html . "<option value = ".$table["TABLE_NAME"].">".$table["TABLE_NAME"]."</option>";
	}
	$html = $html."</select>";
	return $html;
}



?>