<?php
//==================================< The Buttons >=======================================
//
// common unit button functions to be used where needed 
// to be included in other scripts
// 2012-07-26 - 1. Version    cloned from staff buttons
//
//========================================================================================

//--------------------------------------------------------------------------------------------------------------
function unit_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_unit_ay_button()."</TD>";

	if (unit_has_teaching($au_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".unit_teaching_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_unit_teaching_button()."</TD>";
	if (unit_has_enrollment($au_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".unit_enrollment_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_unit_enrollment_button()."</TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_unit_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function unit_teaching_button()
//	display a button to display/hide teaching information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_unit_teaching)
	{
		$html = $html."<input type='hidden' name='show_unit_teaching' value=0>";
		$html = $html."<input type='submit' value='Hide Teaching Details'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_unit_teaching' value=1>";
		$html = $html."<input type='submit' value='Show Teaching Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_unit_teaching_button()
//	display a (dummy) button when there is NO teaching
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	
	$html = $html."<input type='hidden' name='show_unit_teaching' value=0>";
	$html = $html."<input type='submit' value='NO Teaching Details'></FORM>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function unit_enrollment_button()
//	display a button to display/hide enrollment information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_enrollment)
	{
		$html = $html."<input type='hidden' name='show_enrollment' value=0>";
		$html = $html."<input type='submit' value='Hide Enrolment Details'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_enrollment' value=1>";
		$html = $html."<input type='submit' value='Show Enrolment Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_unit_enrollment_button()
//	display a (dummy) button when there is NO enrollment
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	$html = $html."<input type='hidden' name='show_enrollment' value=0>";
	$html = $html."<input type='submit' value='NO Enrolment Details'></FORM>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function export_unit_button()
//	display a button to export a result into an Excel file
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
//	$html = $html."<input type='hidden' name='query' value='$query'>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_unit_teaching' value='$show_unit_teaching'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	$html = $html."<input type='hidden' name='excel_export' value=1>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$html = $html."<input type='submit' value='Export to Excel'></FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function reload_unit_ay_button()
//	display a button to export a result into an Excel file
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<TABLE BORDER=0>";
	$html = $html."<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD>";		
	
//	$html = $html."<input type='hidden' name='query' value='$query'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_unit_teaching' value='$show_unit_teaching'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$html = $html."<TD><input type='submit' value='Reload'></TD></TR></TABLE></FORM>";
	return $html;
}

//===================================< Attributes >=======================================

//--------------------------------------------------------------------------------------------------
function unit_has_teaching($au_id, $ay_id)
//	checks if  a given Assessment Unit ID has some teaching at all (for a given academic year)
{
	if($ay_id > 0) $query = "
		SELECT * 
		FROM TeachingInstance ti 
		INNER JOIN Term t ON t.id = ti.term_id 
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		INNER JOIN TeachingComponentAssessmentUnit tcau 
			ON tcau.teaching_component_id = tc.id 
			AND tcau.academic_year_id = t.academic_year_id
		
		WHERE tcau.assessment_unit_id = $au_id 
		AND t.academic_year_id = $ay_id
		";
	else $query = "
		SELECT * 
		FROM TeachingInstance ti 
		INNER JOIN Term t ON t.id = ti.term_id 
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		INNER JOIN TeachingComponentAssessmentUnit tcau 
			ON tcau.teaching_component_id = tc.id 
			AND tcau.academic_year_id = t.academic_year_id
		
		WHERE tcau.assessment_unit_id = $au_id 
		";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function unit_has_enrollment($au_id, $ay_id)
//	checks if  a given Assessment Unit ID has some enrollment at all (for a given academic year)
{
	if($ay_id > 0) $query = "
		SELECT * 
		FROM StudentAssessmentUnit sau 
		
		WHERE sau.assessment_unit_id = $au_id 
		AND sau.academic_year_id = $ay_id
		";
	else $query = "
		SELECT * 
		FROM StudentAssessmentUnit sau 
		
		WHERE sau.assessment_unit_id = $au_id 
		";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}


?>