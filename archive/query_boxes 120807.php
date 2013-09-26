<?php

//==================================================================================================
//
//	DINFO query boxes to be used in other scripts
//	Last changes: Matthias Opitz --- 2012-07-12
//
//==================================================================================================
$version_qb = "120524.1";			// 1st version
$version_qb = "120528.1";			// bugfix: added title to unit query
$version_qb = "120614.1";			// supporting academic year in staff query
$version_qb = "120618.1";			// added academic query form
$version_qb = "120626.1";			// returned to staff query form
$version_qb = "120712.1";			// added print_reset_button , changed textarea to text input

//--------------------------------------------------------------------------------------------------------------
function department_query($department_code, $actionpage)
//	print the query
{
	print "<H2>Department Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Department:</TD><TD>".department_options($conn, "", $department_code)."</TD><TR>";		
	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}

//----------------------------------------------------------------------------------------
function publication_query_form($webauth_code, $department_code, $ay_id, $actionpage)
//	print the form to support the query
{
	$author_q = $_POST['author_q'];							// get author_q
	$title_q = $_POST['title_q'];							// get title_q

	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=300></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
	print "<TR><TD>Author:</TD><TD><input type='text' name = 'author_q' value='$author_q' size=50></TD><TR>";		
	print "<TR><TD>Title:</TD><TD><input type='text' name = 'title_q' value='$title_q' size=50></TD><TR>";		

	if ($report2)
		print "<TR><TD>Show medium report:</TD><TD><input type='checkbox' name='report2' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show medium report:</TD><TD><input type='checkbox' name='report2' value='TRUE'></TD><TR>";

	print "<TR><TD></TD><TD>";
	
	print "<TABLE BORDER=0>";
	print "<input type='hidden' name='webauth_code' value='$webauth_code'>";
	print "<input type='hidden' name='query_type' value='pub'>";

print "<TR WIDTH=250>";
print "<TD WIDTH=200 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
	print "<input type='button' name='Cancel' value='Reset' onclick=window.location='index.php'  />";
//	print "<input type='submit' value='Start over'>";
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

	

}

//----------------------------------------------------------------------------------------
function staff_query_form($webauth_code, $department_code, $ay_id, $actionpage)
//	print the form to support the query
{
	$fullname_q = $_POST['fullname_q'];							// get fullname_q
	$forename_q = $_POST['forename_q'];							// get forename_q
	$surname_q = $_POST['surname_q'];							// get surname_q
	$webauth_q = $_POST['webauth_q'];							// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];					// get employee_nr_q

	$academic_only = $_POST['academic_only'];					// get academic_only
	$non_academic = $_POST['non_academic'];						// get non_academic
	$non_actv = $_POST['non_actv'];								// get non_actv
	$manual_only = $_POST['manual_only'];						// get manual_only
	$include_borrowed_staff = $_POST['include_borrowed_staff'];	// get include_borrowed_staff

//	print "<H2>Academic Report</H2>";
	print "<form action='$actionpage' method=POST>";
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=300></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

//	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
//	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
	print "<TR><TD>Full Name:</TD><TD><input type='text' name = 'fullname_q' value='$fullname_q' size=50></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><input type='text' name = 'forename_q' value='$forename_q' size=50></TD><TR>";		
	print "<TR><TD>WebAuth Code:</TD><TD><input type='text' name = 'webauth_q' value='$webauth_q' size=50></TD><TR>";		
	print "<TR><TD>Employee Number:</TD><TD><input type='text' name = 'employee_nr_q' value='$employee_nr_q' size=50></TD><TR>";		

//	print "<TR><TD>Full Name:</TD><TD><textarea name = 'fullname_q' rows=1 cols=42>$fullname_q</textarea></TD><TR>";		
//	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
//	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
//	print "<TR><TD>Employee Number:</TD><TD><textarea name = 'employee_nr_q' rows=1 cols=42>$employee_nr_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	if ($non_academic)
		print "<TR><TD>Include non-academic staff:</TD><TD><input type='checkbox' name='non_academic' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Include non-academic staff:</TD><TD><input type='checkbox' name='non_academic' value='TRUE'></TD><TR>";

	if ($include_borrowed_staff)
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE'></TD><TR>";

	if ($non_actv)
		print "<TR><TD>Include inactive staff:</TD><TD><input type='checkbox' name='non_actv' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Include inactive staff:</TD><TD><input type='checkbox' name='non_actv' value='TRUE'></TD><TR>";

	if ($manual_only)
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE'></TD><TR>";
	print "<TR><TD></TD><TD>";
	
	print "<TABLE BORDER=0>";
	print "<input type='hidden' name='webauth_code' value='$webauth_code'>";
	print "<input type='hidden' name='query_type' value='staff'>";

print "<TR WIDTH=250>";
print "<TD WIDTH=200 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
	print "<input type='button' name='Cancel' value='Reset' onclick=window.location='index.php'  />";
//	print "<input type='submit' value='Start over'>";
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

	

}

//----------------------------------------------------------------------------------------
function academic_query_form00($department_code, $ay_id, $actionpage)
//	print the form to support the query
{
	$fullname_q = $_POST['fullname_q'];							// get fullname_q
	$forename_q = $_POST['forename_q'];							// get forename_q
	$surname_q = $_POST['surname_q'];							// get surname_q
	$webauth_q = $_POST['webauth_q'];							// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];					// get employee_nr_q

	$academic_only = $_POST['academic_only'];					// get academic_only
	$actv_only = $_POST['actv_only'];							// get actv_only
	$manual_only = $_POST['manual_only'];						// get manual_only
	$include_borrowed_staff = $_POST['include_borrowed_staff'];	// get include_borrowed_staff

//	print "<H2>Academic Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		
	print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";		
	print "<TR><TD>Full Name:</TD><TD><textarea name = 'fullname_q' rows=1 cols=42>$fullname_q</textarea></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
	print "<TR><TD>Employee Number:</TD><TD><textarea name = 'employee_nr_q' rows=1 cols=42>$employee_nr_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	if ($academic_only)
		print "<TR><TD>Show only academic and research staff:</TD><TD><input type='checkbox' name='academic_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show only academic and research staff:</TD><TD><input type='checkbox' name='academic_only' value='TRUE'></TD><TR>";

	if ($include_borrowed_staff)
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE'></TD><TR>";

	if ($actv_only)
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE'></TD><TR>";

	if ($manual_only)
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE'></TD><TR>";
	print "<TR><TD></TD><TD>";
	
	print "<TABLE BORDER=0>";
print "<TR WIDTH=350>";
print "<TD WIDTH=250 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
//	print "<input type='submit' value='Start over'>";
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

	

}

//----------------------------------------------------------------------------------------
function academic_query_form0($department_code, $ay_id, $actionpage)
//	print the form to support the query
{
	$fullname_q = $_POST['fullname_q'];							// get fullname_q
	$forename_q = $_POST['forename_q'];							// get forename_q
	$surname_q = $_POST['surname_q'];							// get surname_q
	$webauth_q = $_POST['webauth_q'];							// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];					// get employee_nr_q

	$academic_only = $_POST['academic_only'];					// get academic_only
	$actv_only = $_POST['actv_only'];							// get actv_only
	$manual_only = $_POST['manual_only'];						// get manual_only
	$include_borrowed_staff = $_POST['include_borrowed_staff'];	// get include_borrowed_staff

	print "<H2>Academic Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		
	print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";		
	print "<TR><TD>Full Name:</TD><TD><textarea name = 'fullname_q' rows=1 cols=42>$fullname_q</textarea></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
	print "<TR><TD>Employee Number:</TD><TD><textarea name = 'employee_nr_q' rows=1 cols=42>$employee_nr_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	if ($academic_only)
		print "<TR><TD>Show only academic and research staff:</TD><TD><input type='checkbox' name='academic_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show only academic and research staff:</TD><TD><input type='checkbox' name='academic_only' value='TRUE'></TD><TR>";

	if ($include_borrowed_staff)
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE'></TD><TR>";

	if ($actv_only)
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE'></TD><TR>";

	if ($manual_only)
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE'></TD><TR>";
	print "<TR><TD></TD><TD>";
	
	print "<TABLE BORDER=0>";
print "<TR WIDTH=350>";
print "<TD WIDTH=250 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
//	print "<input type='submit' value='Start over'>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
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

	

}

//----------------------------------------------------------------------------------------
function staff_query_formz($webauth_code, $department_code, $ay_id, $actionpage)
//	print the form to support the query
{
	$fullname_q = $_POST['fullname_q'];							// get fullname_q
	$forename_q = $_POST['forename_q'];							// get forename_q
	$surname_q = $_POST['surname_q'];							// get surname_q
	$webauth_q = $_POST['webauth_q'];							// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];					// get employee_nr_q

	$actv_only = $_POST['actv_only'];							// get actv_only
	$manual_only = $_POST['manual_only'];						// get manual_only
	$include_borrowed_staff = $_POST['include_borrowed_staff'];	// get include_borrowed_staff

//	print "<H2> Staff Query</H2>";
	print "<form action='$actionpage' method=POST>";
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
	print "<TR><TD>Surname:</TD><TD><input type='text' name = 'surname_q' value='$surname_q' size=50></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><input type='text' name = 'forename_q' value='$forename_q' size=50></TD><TR>";		
	print "<TR><TD>WebAuth Code:</TD><TD><input type='text' name = 'webauth_q' value='$webauth_q' size=50></TD><TR>";		
	print "<TR><TD>Employee Number:</TD><TD><input type='text' name = 'employee_nr_q' value='$employee_nr_q' size=50></TD><TR>";		

//	print "<TR><TD>Full Name:</TD><TD><textarea name = 'fullname_q' rows=1 cols=42>$fullname_q</textarea></TD><TR>";		
//	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
//	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
//	print "<TR><TD>Employee Number:</TD><TD><textarea name = 'employee_nr_q' rows=1 cols=42>$employee_nr_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	if ($include_borrowed_staff)
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Include borrowed staff:</TD><TD><input type='checkbox' name='include_borrowed_staff' value='TRUE'></TD><TR>";
	if ($actv_only)
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE'></TD><TR>";

	if ($manual_only)
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE'></TD><TR>";
	print "<TR><TD></TD><TD>";
	
	print "<TABLE BORDER=0>";
print "<TR WIDTH=350>";
print "<TD WIDTH=200 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
//	print "<input type='cancel' value='Start over'>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
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
}

//--------------------------------------------------------------------------------------------------------------
function student_query_form($webauth_code, $department_code, $au_id, $actionpage)
//	print the query
{
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
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
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
//	print "<input type='cancel' value='Start over'>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
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

//--------------------------------------------------------------------------------------------------------------
function programme_query_form($webauth_code, $department_code, $ay_id, $actionpage)
//	print the query
{
	$dp_code_q = $_POST['dp_code_q'];						// get dp_code_q
	$dp_title_q = $_POST['dp_title_q'];						// get dp_title_q

//	print "<H2> Programme Query</H2>";
	print "<form action='$actionpage' method=POST>";
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
	print "<TR><TD>Code:</TD><TD><input type='text' name = 'dp_code_q' value='$dp_code_q' size=50></TD><TR>";		
	print "<TR><TD>Title:</TD><TD><input type='text' name = 'dp_title_q' value='$dp_title_q' size=50></TD><TR>";		

	print "<TR><TD></TD><TD>";
	
	print "<TABLE BORDER=0>";
print "<TR WIDTH=350>";
print "<TD WIDTH=200 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
//	print "<input type='cancel' value='Start over'>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
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

//--------------------------------------------------------------------------------------------------------------
function unit_query_form($webauth_code, $department_code, $ay_id, $actionpage)
//	print the query
{
	$au_code_q = $_POST['au_code_q'];						// get au_code_q
	$au_title_q = $_POST['au_title_q'];						// get au_title_q

//	print "<H2>Assessment Unit Query</H2>";
	print "<form action='$actionpage' method=POST>";
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
	print "<TR><TD>Code:</TD><TD><input type='text' name = 'au_code_q' value='$au_code_q' size=50></TD><TR>";		
	print "<TR><TD>Title:</TD><TD><input type='text' name = 'au_title_q' value='$au_title_q' size=50></TD><TR>";		

	print "<TR><TD></TD><TD>";

	print "<input type='hidden' name='webauth_code' value='$webauth_code'>";
	print "<input type='hidden' name='query_type' value='unit'>";

	print "<TABLE BORDER=0>";
print "<TR WIDTH=350>";
print "<TD WIDTH=200 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
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

//--------------------------------------------------------------------------------------------------------------
function component_query_form($webauth_code, $department_code, $ay_id, $actionpage)
//	print the query
{
//	print "<H2>Teaching Component Query</H2>";
	print "<form action='$actionpage' method=POST>";
			
//	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TABLE BORDER=0>";
	print "<TR><TD WIDTH=250></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD><TR>";		

	if (user_is_in_DAISY_user_group($webauth_code, 'Super-Administrator')) print "<TR><TD>Department:</TD><TD>".department_options("", $department_code)."</TD><TR>";
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_user($webauth_code)."'>";
	
	print "<TR><TD>Subject:</TD><TD><input type='text' name = 'tc_subject_q' value='$tc_subject_q' size=50></TD><TR>";		

	print "<TR><TD></TD><TD>";

	print "<input type='hidden' name='webauth_code' value='$webauth_code'>";
	print "<input type='hidden' name='query_type' value='comp'>";

	print "<TABLE BORDER=0>";
print "<TR WIDTH=350>";
print "<TD WIDTH=200 VALIGN=TOP>";
	print "<form action='$actionpage' method=POST>";
	print "<input type='button' name='Cancel' value='Cancel' onclick=window.location='index.php'  />";
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





//--------------------------------------------------------------------------------------------------------------
function staff_query($department_code, $actionpage)
//	print the query
{
	$fullname_q = $_POST['fullname_q'];						// get fullname_q
	$forename_q = $_POST['forename_q'];						// get forename_q
	$surname_q = $_POST['surname_q'];						// get surname_q
	$webauth_q = $_POST['webauth_q'];						// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];				// get employee_nr_q

	$actv_only = $_POST['actv_only'];						// get actv_only
	$manual_only = $_POST['manual_only'];					// get manual_only

//	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	print "<H2> Staff Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Department:</TD><TD>".department_options($conn, "", $department_code)."</TD><TR>";		
	print "<TR><TD>Full Name:</TD><TD><textarea name = 'fullname_q' rows=1 cols=42>$fullname_q</textarea></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
	print "<TR><TD>Employee Number:</TD><TD><textarea name = 'employee_nr_q' rows=1 cols=42>$employee_nr_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	if ($actv_only)
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show ACTV staff only:</TD><TD><input type='checkbox' name='actv_only' value='TRUE'></TD><TR>";

	if ($manual_only)
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE' checked='checked'></TD><TR>";
	else
		print "<TR><TD>Show manually addedd staff only:</TD><TD><input type='checkbox' name='manual_only' value='TRUE'></TD><TR>";

	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}

//--------------------------------------------------------------------------------------------------------------
function student_query($department_code, $actionpage)
//	print the query
{
	$fullname_q = $_POST['fullname_q'];						// get fullname_q
	$forename_q = $_POST['forename_q'];						// get forename_q
	$surname_q = $_POST['surname_q'];						// get surname_q
	$webauth_q = $_POST['webauth_q'];						// get webauth_q
	$student_code_q = $_POST['student_code_q'];				// get student_code_q

	print "<H2> Student Query</H2>";
	print "<form action='$actionpage' method=POST>";
			
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
//	print "<TR><TD>Department:</TD><TD>".department_options($conn, "", $department_code)."</TD><TR>";		
	print "<TR><TD>Surname:</TD><TD><textarea name = 'surname_q' rows=1 cols=42>$surname_q</textarea></TD><TR>";		
	print "<TR><TD>Forename:</TD><TD><textarea name = 'forename_q' rows=1 cols=42>$forename_q</textarea></TD><TR>";
	print "<TR><TD>WebAuth Code:</TD><TD><textarea name = 'webauth_q' rows=1 cols=42>$webauth_q</textarea></TD><TR>";
	print "<TR><TD>Student Code:</TD><TD><textarea name = 'student_code_q' rows=1 cols=42>$student_code_q</textarea></TD><TR>";
//	print "<TR><TD></TD><TD></TD><TR>";

	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}

//--------------------------------------------------------------------------------------------------------------
function programme_query($department_code, $actionpage)
//	print the query
{
	$dp_code_q = $_POST['dp_code_q'];						// get dp_code_q
	$dp_title_q = $_POST['dp_title_q'];						// get dp_title_q

	print "<H2> Programme Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Department:</TD><TD>".department_options($conn, "", $department_code)."</TD><TR>";		
	print "<TR><TD>Code:</TD><TD><textarea name = 'dp_code_q' rows=1 cols=42>$dp_code_q</textarea></TD><TR>";		
	print "<TR><TD>Title:</TD><TD><textarea name = 'dp_title_q' rows=1 cols=42>$dp_title_q</textarea></TD><TR>";

	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}

//--------------------------------------------------------------------------------------------------------------
function unit_query($department_code, $actionpage)
//	print the query
{
	$au_code_q = $_POST['au_code_q'];						// get au_code_q
	$au_title_q = $_POST['au_title_q'];						// get au_title_q

	print "<H2>Assessment Unit Query</H2>";
	print "<form action='$actionpage' method=POST>";
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Department:</TD><TD>".department_options($conn, "", $department_code)."</TD><TR>";		
	print "<TR><TD>Code:</TD><TD><textarea name = 'au_code_q' rows=1 cols=42>$au_code_q</textarea></TD><TR>";		
	print "<TR><TD>Title:</TD><TD><textarea name = 'au_title_q' rows=1 cols=42>$au_title_q</textarea></TD><TR>";
	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}

//--------------------------------------------------------------------------------------------------------------
function component_query($department_code, $actionpage)
//	print the query
{
	print "<H2>Teaching Component Query</H2>";
	print "<form action='$actionpage' method=POST>";
			
	print "<TABLE BORDER=0 BGCOLOR=LIGHTGREY>";
	print "<TR><TD WIDTH=300></TD><TD WIDTH=400></TD></TR>";
	print "<TR><TD>Department:</TD><TD>".department_options($conn, "", $department_code)."</TD><TR>";		
	print "<TR><TD>Subject:</TD><TD><textarea name = 'tc_subject_q' rows=1 cols=42>$tc_subject_q</textarea></TD><TR>";
	print "</TABLE>";

	print "<P>";
	print "<input type='submit' value='Go!'>";
	print "</form>";
}


?>