<?php

//==================================================================================================
//
//	Separate file with basic functions
//
//	13-09-24	all in one place?!
//==================================================================================================

//-----------------------------------------------------------------------------------------
function show_basic_query()
{
	basic_query_form();
//	if(!query_type)
	if(!$_POST['query_type'] AND !$_GET['table'])
	{
		print "<FONT FACE = 'Arial'>";
		print_basic_intro();
		print_basic_help();
		print "</FONT>";
	}
}

//-----------------------------------------------------------------------------------------
function print_basic_intro()
{
	$text = "<B>Teaching Data</B>
	<P>
	Currently you may chose from four different report types: <I>Academics(Staff), Assessment Units, Teaching Components</I> and <I>Teaching Instances</I>.<P>
	
	<B>Teaching Data:</B> This report will list all Teaching Instance records of the selected academic year. For each instance it will show the term, the related employee (aka lecturer, including her/his HR Employee Number),
	the Teaching Component given (including the *new* Teaching Component code and type) as well as the number of sessions and the stint value given for that instance.<P>
	
	The report may be exported to a CSV file that then can be edited using a spread sheet application (e.g. Excel).<BR>
	The edited file may then be uploaded again to update the data in DAISY (please note that column names may not be changed for this to work!).
	<P>
	<B>Academics:</B> This will list all academics including their Employee Numbers. THis might be helpful when editing or adding data in the Teaching Data.<P>
	
	<B>Assessment Units:</B> A list of all Assessment Units of a department.<P>
	
	<B>Teaching Components:</B> This report lists all Teaching Components with their *new* Teaching Component Code. This unique code is needed to add or edit Teaching Data in a spread sheet as it will identify the component when updating DAISY again.<BR>
	So this report will come in handy when you need to add or change Teaching Data this way.
	
	<B>SES Courses:</B> This report will show all Teaching Instances that are destined to appear in the Student Enrolment System.
	
	
	";
	
	$text = "<B>Teaching Data</B>
	<P>
	Currently you may chose from four different report types: <I>Teaching Data, Academics, Assessment Units</I> and <I>Teaching Components</I>.<P>
	
	<B>Teaching Data:</B> This report will list all Teaching Instance records of the selected academic year. For each instance it will show the term, the related employee (aka lecturer, including her/his HR Employee Number),
	the Teaching Component given (including the *new* Teaching Component code and type) as well as the number of sessions and the stint value given for that instance.<P>
	
	The report may be exported to a CSV file that then can be edited using a spread sheet application (e.g. Excel).<BR>
	The edited file may then be uploaded again to update the data in DAISY (please note that column names may not be changed for this to work!).
	<P>
	<B>Academics:</B> This will list all academics including their Employee Numbers. THis might be helpful when editing or adding data in the Teaching Data.<P>
	
	<B>Assessment Units:</B> A list of all Assessment Units of a department.<P>
	
	<B>Teaching Components:</B> This report lists all Teaching Components with their *new* Teaching Component Code. This unique code is needed to add or edit Teaching Data in a spread sheet as it will identify the component when updating DAISY again.<BR>
	So this report will come in handy when you need to add or change Teaching Data this way.
	
	";
	
	print "<HR>";
	print "<H4><FONT COLOR=DARKBLUE>Introduction</FONT></H4>";
	print $text;

}

//-----------------------------------------------------------------------------------------
function print_basic_help()
{
}
//----------------------------------------------------------------------------------------
function basic_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	if(isset($_POST['year'])) $year = $_POST['year'];	// get year
	else $year = FALSE;
	
	print "<FONT FACE = 'Arial'>";

	print "<form action='".$_SERVER["PHP_SELF"]."' method=POST>";

	print "<input type='hidden' name='query_type' value='special'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Academic Year:";
	print new_column(400);
	print academic_year_options();
//		print " <FONT COLOR=GREY>(no effect on REF reports)</FONT>";

	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Report Type:" . new_column(0) . basic_report_options() . end_row();		
//	if (current_user_is_in_DAISY_user_group('Overseer')) print start_row(0) . "Show Query:" . new_column(0) . html_checkbox('show_query') . end_row();		
	print "</TABLE>";
	print "<HR></FONT>";

//	display the buttons
	query_buttons();
}

//----------------------------------------------------------------------------------------
function basic_report_options()
// shows the options for a publication report
{
	$query_type = $_POST['query_type'];				// get query_type
	
//	print "<input type='hidden' name='query_type' value='concordat'>";
//	$options = array("General report: summary", "General report: standard", "General report: detailed", "REF report", "REF report compact");
	$options = array();
	$options[] = array('Please select a report type','');
	$options[] = array('Teaching Data','teaching');
	$options[] = array('-------------','-');
	$options[] = array('Academics','acad');
	$options[] = array('Employees','emp');
//	$options[] = array('Posts','post');
	$options[] = array('Assessment Units','au');
	$options[] = array('Teaching Components','tc');
	$options[] = array('Teaching Instances','ti');
//	$options[] = array('SES Data','ses');
//	$options[] = array('Teaching Instances','ti');
//	$options[] = array('Students','st');
//	$options[] = array('Teaching Stint Tariff','tst');
//	$options[] = array('Supervising Stint Tariff','svst');
	
//	$options = array(array('Select a report type',''), array('Duplicate Names','dup_names'), array('Duplicate Employee Numbers','dup_en'), array('AU by Owner','au_by_owner'), array('Stint Balance','stint_balance'));
	$html = "<select name='query_type'>";
	
	foreach($options AS $option)
	{
		if($option[1]==$query_type) $html = $html."<option SELECTED='selected' value=".$option[1].">".$option[0]."</option>";
		else $html = $html."<option value=".$option[1].">".$option[0]."</option>";
	}
	$html = $html."</select>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function query_buttons()
//	display the standard buttons below each query : go, reset and  export to excel
{
	$actionpage = $_SERVER["PHP_SELF"];

//	if $_POST['deb'] is set preserve this
	if(isset($_POST['deb'])) print "<input type='hidden' name='deb' value='" . $_POST['deb'] . "'>";
	
	print "<TABLE BORDER = 0>";
	print "<TR>";
	print "<TD BGCOLOR=LIGHTGREEN>";
		print "<input type='submit' value='         Go!         '>";
	print "</TD>";
	print "<TD BGCOLOR=PINK>";
		print "<input type='button' name='Cancel' value='    - Reset -    ' onclick=window.location='$actionpage'  />";
	print "</TD>";

//	Show a button leading to Central Services but for Overseers only
	if(current_user_is_in_DAISY_user_group("Super-Administrator"))
	{
		$bgcolor = '#FF6600';
		if(this_page() == 'index.php')
		{
			print "<TD BGCOLOR=$bgcolor>";
				print "<input type='button' name='Index' value='    - Central -    ' onclick=window.location='../index.php'  />";
			print "</TD>";
		}
		else 
		{
			print "<TD BGCOLOR=$bgcolor>";
				print "<input type='button' name='Index' value='    - Central -    ' onclick=window.location='index.php'  />";
			print "</TD>";
		}
	}

	print "</FORM>";	// this closes the form that each query page has opened 
	
//	now print the 'Export to Excel' button in its own FORM
	print "<FORM action='$actionpage' method=POST>";
//	write all parameters for the separate FORM needed here
	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		print "<input type='hidden' name='$param_key' value='$param'>";
	}
//	print "<TD BGCOLOR=#AAAAAA>";
	print "<TD>";
		if($_POST['query_type'])
//		if(TRUE)
		{
			print "<input type='hidden' name='excel_export' value=1>";	
			print "<input type='submit' value='Export to Excel'>";
		}
	print "</TD>";
	
	
	if (current_user_is_in_DAISY_user_group('Overseer'))
	{
		print "<TD WIDTH = 100>";
//		print "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ";
		print "</TD>";
		print "<TD>";
		print new_form_html();
		print "</TD>";
	}
	
	
	
	print "</FORM>";
	
	print "</TR>";
	print "</TABLE>";
 }


//--------------------------------------------------------------------------------------------------------------
function get_table_fields($table_name)
//	get all field names of a given table
{
	$query = "DESCRIBE $table_name";
	$raw_fields = get_data($query);
	
	$fields = array();
	
	if($raw_fields) foreach($raw_fields AS $raw_field)
	{
		$fields[] = $raw_field['Field'];
	}
	
	return $fields;
}

?>