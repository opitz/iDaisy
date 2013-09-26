<?php

//==================================================================================================
//
//	DINFO query boxes to be used in other scripts
//	Last changes: Matthias Opitz --- 2012-08-15
//
//==================================================================================================
$version_qb = "120524.1";			// 1st version
$version_qb = "120528.1";			// bugfix: added title to unit query
$version_qb = "120614.1";			// supporting academic year in staff query
$version_qb = "120618.1";			// added academic query form
$version_qb = "120626.1";			// returned to staff query form
$version_qb = "120712.1";			// added print_reset_button , changed textarea to text input
$version_qb = "120808.1";			// allowing any actionpage as target for reset
$version_qb = "120815.2";			// added year range in publication query form, removed all parameters
$version_qb = "120821.1";			// renovated button menu for publications

//----------------------------------------------------------------------------------------
function department_query()
//	print the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
	print "<H2>Department Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Department:</TD><TD>".department_options("")."</TD><TR>";		
	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}

//----------------------------------------------------------------------------------------
function publication_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$year = $_POST['year'];															// get year
	
	$author_q = $_POST['author_q'];												// get author_q
	$title_q = $_POST['title_q'];														// get title_q


	$parameter['department_code'] = $_POST['department_code'];					// get department_code
	$parameter['from_year'] = $_POST['from_year'];								// get from_year
	$parameter['to_year'] = $_POST['to_year'];										// get to_year
	$parameter['author_q'] = $_POST['author_q'];									// get author_q
	$parameter['title_q'] = $_POST['title_q'];										// get title_q
	$parameter['query_type'] = $_POST['query_type'];								// get query_type

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='pub'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Year of Publication:";
	print new_column(300);
		print from_year_options()." to ".to_year_options();
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Author:" . new_column(0) . "<input type='text' name = 'author_q' value='$author_q' size=50>" . end_row();		
	print start_row(0) . "Title:" . new_column(0) . "<input type='text' name = 'title_q' value='$title_q' size=50>" . end_row();		
	print start_row(0) . "Report Type:" . new_column(0) . publication_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print "<TABLE BORDER=0>";
	print start_row(250);
		print "<TABLE BORDER=0>";
		print_reset_button();
		print "</TABLE>";
	print new_column(190);
		print "<input type='submit' value='Go!'>";
		print "</FORM>";
	print new_column(0);
		print "<TABLE BORDER=0>";
		if($parameter['query_type']) print_export_button($parameter);
		print "</TABLE>";
	print end_row();
	print "</TABLE>";

}

//----------------------------------------------------------------------------------------
function staff_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
	$fullname_q = $_POST['fullname_q'];											// get fullname_q
	$forename_q = $_POST['forename_q'];											// get forename_q
	$surname_q = $_POST['surname_q'];											// get surname_q
	$webauth_q = $_POST['webauth_q'];											// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];									// get employee_nr_q

	$academic_only = $_POST['academic_only'];										// get academic_only
	$non_academic = $_POST['non_academic'];										// get non_academic
	$non_actv = $_POST['non_actv'];												// get non_actv
	$manual_only = $_POST['manual_only'];											// get manual_only
	$show_sums = $_POST['show_sums'];											// get show_sums
	$include_borrowed_staff = $_POST['include_borrowed_staff'];					// get include_borrowed_staff
	$include_zero_stint = $_POST['include_zero_stint'];								// get include_zero_stint

	$parameter['e_id'] = $_POST['e_id'];											// get employee id
	$parameter['ay_id'] = $_POST['ay_id'];											// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];					// get department_code
	$parameter['fullname_q'] = $_POST['fullname_q'];								// get fullname_q
	$parameter['forename_q'] = $_POST['forename_q'];								// get forename_q
	$parameter['surname_q'] = $_POST['surname_q'];								// get surname_q
	$parameter['webauth_q'] = $_POST['webauth_q'];								// get webauth_q
	$parameter['employee_nr_q'] = $_POST['employee_nr_q'];						// get employee_nr_q

	$parameter['academic_only'] = $_POST['academic_only'];						// get academic_only
	$parameter['non_academic'] = $_POST['non_academic'];							// get non_academic

	$parameter['non_actv'] = $_POST['non_actv'];									// get non_actv
	$parameter['manual_only'] = $_POST['manual_only'];							// get manual_only
	$parameter['show_sums'] = $_POST['show_sums'];								// get show_sums
	$parameter['include_borrowed_staff'] = $_POST['include_borrowed_staff'];		// get include_borrowed_staff
	$parameter['include_zero_stint'] = $_POST['include_zero_stint'];				// get include_zero_stint

	$parameter['query'] = $_POST['query'];											// get query
	$parameter['query_type'] = $_POST['query_type'];								// get query_type
//	$start_row = "<TR><TD>";
//	$new_column = "</TD><TD>";
//	$end_row = "</TD></TR>";
	
	print "<FORM ACTION='$actionpage' method=POST>";

	print "<input type='hidden' name='webauth_code' value='$webauth_code'>";
	print "<input type='hidden' name='query_type' value='staff'>";

//	print the query input fields
	print "<TABLE BORDER=0>";
	
	print start_row(250);
		print "Academic Year:" ;
	print new_column(300);
		print academic_year_options();
		if(!$_POST['ay_id']) print " <FONT COLOR =GREY>Select a year for stint values</FONT>";		
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Full Name:" . new_column(0) . "<input type='text' name = 'fullname_q' value='$fullname_q' size=50>" . end_row();		
	print start_row(0) . "Forename:" . new_column(0) . "<input type='text' name = 'forename_q' value='$forename_q' size=50>" . end_row();		
	print start_row(0) . "WebAuth Code:" . new_column(0) . "<input type='text' name = 'webauth_q' value='$webauth_q' size=50>" . end_row();		
	print start_row(0) . "Employee Number:" . new_column(0) . "<input type='text' name = 'employee_nr_q' value='$employee_nr_q' size=50>" . end_row();		

	print "</TABLE>";

	print "<HR>";
	
//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "Include non-academic staff:";
	print new_column(60);
		if ($non_academic) print "<input type='checkbox' name='non_academic' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='non_academic' value='TRUE'>";
	print new_column(190);
		 print "Include borrowed staff:";
	print new_column(0);
		if ($include_borrowed_staff)  print "<input type='checkbox' name='include_borrowed_staff' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='include_borrowed_staff' value='TRUE'>";
	print end_row();

	print start_row(0);		// 2nd row
		print "Include staff without stint:";
	print new_column(0);
		if ($include_zero_stint) print "<input type='checkbox' name='include_zero_stint' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='include_zero_stint' value='TRUE'>";
	print new_column(0);
		print "Include inactive staff:";
	print new_column(0);
		if ($non_actv)  print "<input type='checkbox' name='non_actv' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='non_actv' value='TRUE'>";
	print end_row();

	print start_row(0);		// 3rd row
		print "Show manually addedd staff only:";
	print new_column(0);
		if ($manual_only) print "<input type='checkbox' name='manual_only' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='manual_only' value='TRUE'>";
	print new_column(0);
		print "Show department sums:";
	print new_column(0);
		if ($show_sums) print "<input type='checkbox' name='show_sums' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_sums' value='TRUE'>";
	print end_row();
	print "</TABLE>";

	print "<HR>";
	
//	display the buttons
	print "<TABLE BORDER=0>";
	print start_row(250);
		print "<TABLE BORDER=0>";
		print_reset_button();
		print "</TABLE>";
	print new_column(190);
		print "<input type='submit' value='Go!'>";
		print "</FORM>";
	print new_column(0);
		print "<TABLE BORDER=0>";
		if($parameter['query_type']) print_export_button($parameter);
		print "</TABLE>";
	print end_row();
	print "</TABLE>";
}

//----------------------------------------------------------------------------------------
function student_query_form()
//	print the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
	$fullname_q = $_POST['fullname_q'];						// get fullname_q
	$forename_q = $_POST['forename_q'];						// get forename_q
	$surname_q = $_POST['surname_q'];						// get surname_q
	$webauth_q = $_POST['webauth_q'];						// get webauth_q
	$student_code_q = $_POST['student_code_q'];				// get student_code_q

//	print "<H2> Student Query</H2>";
	print "<form action='$actionpage' method=POST>";
			
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options()."</TD><TR>";		

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("")."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print "<TR><TD>Surname:</TD><TD><textarea name = 'surname_q' rows=1 cols=42>$surname_q</textarea></TD><TR>";		
	print "<TR><TD>Surname:</TD><TD><input type='text' name = 'surname_q' value='$surname_q' size=50></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><input type='text' name = 'forename_q' value='$forename_q' size=50></TD><TR>";		
	print "<TR><TD>WebAuth Code:</TD><TD><input type='text' name = 'webauth_q' value='$webauth_q' size=50></TD><TR>";		
	print "<TR><TD>Student Code:</TD><TD><input type='text' name = 'student_code_q' value='$student_code_q' size=50></TD><TR>";		
//	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
//	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
//	print "<TR><TD>Student Code:</TD><TD><textarea name = 'student_code_q' rows=1 cols=42>$student_code_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	print "<TR><TD></TD><TD>";
	
		print "<TABLE BORDER=0>";
		print "<TR WIDTH=350>";
		print "<TD WIDTH=207 VALIGN=TOP>";
		print "<form action='$actionpage' method=POST>";
//		print "<input type='cancel' value='Start over'>";
		print "<input type='button' name='Cancel' value='Reset' onclick=window.location='$actionpage'  />";
		print "</form>";
		print "</TD>";
		print "<TD WIDTH=100 ALIGN=RIGHT>";
		print "<input type='submit' value='Go!'>";
		print "</form>";
		print "</TD>";
		print "</TR>";
		print "</TABLE>";

	print "</TD></TR>";
	print "</TABLE>";

//	print "<P>";
//	print "<input type='submit' value='Go!'>";
//	print "</form>";
}

//----------------------------------------------------------------------------------------
function programme_query_form()
//	print the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
	$dp_code_q = $_POST['dp_code_q'];						// get dp_code_q
	$dp_title_q = $_POST['dp_title_q'];						// get dp_title_q

//	print "<H2> Programme Query</H2>";
	print "<form action='$actionpage' method=POST>";
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options()."</TD><TR>";		

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("")."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print "<TR><TD>Code:</TD><TD><input type='text' name = 'dp_code_q' value='$dp_code_q' size=50></TD><TR>";		
	print "<TR><TD>Title:</TD><TD><input type='text' name = 'dp_title_q' value='$dp_title_q' size=50></TD><TR>";		

	print "<TR><TD></TD><TD>";
	
		print "<TABLE BORDER=0>";
		print "<TR WIDTH=350>";
		print "<TD WIDTH=200 VALIGN=TOP>";
		print "<form action='$actionpage' method=POST>";
//		print "<input type='cancel' value='Start over'>";
		print "<input type='button' name='Cancel' value='Reset' onclick=window.location='$actionpage'  />";
		print "</form>";
		print "</TD>";
		print "<TD WIDTH=100 ALIGN=RIGHT>";
		print "<input type='submit' value='Go!'>";
		print "</form>";
		print "</TD>";
		print "</TR>";
		print "</TABLE>";

	print "</TD></TR>";
	print "</TABLE>";

//	print "<P>";
//	print "<input type='submit' value='Go!'>";
//	print "</form>";
}

//----------------------------------------------------------------------------------------
function unit_query_form()
//	print the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
	$au_code_q = $_POST['au_code_q'];									// get au_code_q
	$au_title_q = $_POST['au_title_q'];										// get au_title_q

	$parameter['ay_id'] = $_POST['ay_id'];									// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];			// get department_code
	$parameter['au_code_q'] = $_POST['au_code_q'];						// get au_code_q
	$parameter['au_title_q'] = $_POST['au_title_q'];						// get au_title_q
	$parameter['actv_only'] = $_POST['actv_only'];							// get actv_only
	$parameter['query_type'] = $_POST['query_type'];								// get query_type

//	print "<H2>Assessment Unit Query</H2>";
	print "<FORM action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='unit'>";

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";		
	print new_column(400);
		print academic_year_options();		
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Code:" . new_column(0) . "<input type='text' name = 'au_code_q' value='$au_code_q' size=50>" . end_row();		
	print start_row(0) . "Title:" . new_column(0) . "<input type='text' name = 'au_title_q' value='$au_title_q' size=50>" . end_row();		

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print "<TABLE BORDER=0>";
	print start_row(250);
		print "<TABLE BORDER=0>";
		print_reset_button();
		print "</TABLE>";
	print new_column(190);
		print "<input type='submit' value='Go!'>";
		print "</FORM>";
	print new_column(0);
		print "<TABLE BORDER=0>";
		if($parameter['query_type']) print_export_button($parameter);
		print "</TABLE>";
	print end_row();
	print "</TABLE>";
}

//----------------------------------------------------------------------------------------
function component_query_form()
//	print the query
{
	$parameter['ay_id'] = $_POST['ay_id'];											// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];					// get department_code
	$parameter['query_type'] = $_POST['query_type'];								// get query_type
	$parameter['tc_subject_q'] = $_POST['tc_subject_q'];							// get tc_subject_q

	$actionpage = $_SERVER["PHP_SELF"];
	
	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='comp'>";
			
	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";		
	print new_column(400);
		print academic_year_options();		
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";

	print start_row(0) . "Subject:" . new_column(0) . "<input type='text' name = 'tc_subject_q' value='$tc_subject_q' size=50>" . end_row();		

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print "<TABLE BORDER=0>";
	print start_row(250);
		print "<TABLE BORDER=0>";
		print_reset_button();
		print "</TABLE>";
	print new_column(190);
		print "<input type='submit' value='Go!'>";
		print "</FORM>";
	print new_column(0);
		print "<TABLE BORDER=0>";
		if($parameter['query_type']) print_export_button($parameter);
		print "</TABLE>";
	print end_row();
	print "</TABLE>";
}

//----------------------------------------------------------------------------------------
function publication_report_options()
// shows the options for a publication report
{
	$report_type = $_POST['report_type'];					// get report_type
	
	$options = array("short", "medium", "large");
	
	$html = "<select name='report_type'>";
	foreach($options AS $option)
	{
		if($option==$report_type) $html = $html."<option SELECTED='selected'>$option</option>";
		else $html = $html."<option>$option</option>";
	}
	$html = $html."</select>";

	return $html;
}

//******************************************************************
//----------------------------------------------------------------------------------------
function start_row($width)
//	return the HTML code for starting a row of a table
{
	if($width > 0) return "<TR><TD WIDTH=$width>";
	else return "<TR><TD>";
}

//----------------------------------------------------------------------------------------
function new_column($width)
//	return the HTML code for a new column of a table
{
	if($width > 0) return "</TD><TD WIDTH=$width>";
	else return "</TD><TD>";
}

//----------------------------------------------------------------------------------------
function end_row()
//	return the HTML code for ending a row of a table
{
	return "</TD></TR>";
}



?>