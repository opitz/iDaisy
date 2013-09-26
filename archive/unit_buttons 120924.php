<?php
//==================================< The Buttons >=======================================
//
// common unit button functions to be used where needed 
// to be included in other scripts
// 2012-07-26 - 1. Version    cloned from staff buttons
//
//========================================================================================

//--------------------------------------------------------------------------------------------------------------
function unit_switchboard($ay_id, $au_id, $show_unit_teaching, $show_enrollment)
{
		print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
		print "<TR>";

		print "<TD WIDTH=400 ALIGN=LEFT>".reload_unit_ay_button($query, $ay_id, $au_id, $show_unit_teaching, $show_enrollment)."</TD>";

		if (unit_has_teaching($au_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".unit_teaching_button($ay_id, $au_id, $show_teaching, $show_enrollment)."</TD>";
		else print "<TD WIDTH=200 ALIGN=LEFT>".no_unit_teaching_button($ay_id, $au_id, $show_teaching, $show_enrollment)."</TD>";
		if (unit_has_enrollment($au_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".unit_enrollment_button($ay_id, $au_id, $show_teaching, $show_enrollment)."</TD>";
		else print "<TD WIDTH=200 ALIGN=LEFT>".no_unit_enrollment_button($ay_id, $au_id, $show_teaching, $show_enrollment)."</TD>";
//		if (has_leave($e_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".leave_button($ay_id, $e_id, $show_teaching, $show_supervising, $show_leave, $show_publication)."</TD>";
//		else print "<TD WIDTH=200 ALIGN=LEFT>".no_leave_button($ay_id, $e_id, $show_teaching, $show_supervising, $show_leave, $show_publication)."</TD>";
//		if (has_published($e_id)) print "<TD WIDTH=200 ALIGN=LEFT>".publication_button($ay_id, $e_id, $show_teaching, $show_supervising, $show_leave, $show_publication)."</TD>";
//		else print "<TD WIDTH=200 ALIGN=LEFT>".no_publication_button($ay_id, $e_id, $show_teaching, $show_supervising, $show_leave, $show_publication)."</TD>";

		print "<TD WIDTH=250 ALIGN=LEFT></TD>";
		print "<TD WIDTH=200 ALIGN=LEFT>".export_unit_button($query, $ay_id, $au_id, $show_unit_teaching, $show_enrollment)."</TD>";
		print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
		print "<TR>";
		print "</TABLE>";
		print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function unit_teaching_button($ay_id, $au_id)
//	display a button to display/hide teaching information
{
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
function no_unit_teaching_button($ay_id, $au_id)
//	display a (dummy) button when there is NO teaching
{
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
function unit_enrollment_button($ay_id, $au_id)
//	display a button to display/hide enrollment information
{
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
function no_unit_enrollment_button($ay_id, $au_id)
//	display a (dummy) button when there is NO enrollment
{
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
function export_unit_button($query, $ay_id, $au_id)
//	display a button to export a result into an Excel file
{
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='query' value='$query'>";
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
function reload_unit_ay_button($query, $ay_id, $au_id)
//	display a button to export a result into an Excel file
{
	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<TABLE BORDER=0>";
	$html = $html."<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD>";		
	
	$html = $html."<input type='hidden' name='query' value='$query'>";
	$html = $html."<input type='hidden' name='au_id' value='$au_id'>";
	$html = $html."<input type='hidden' name='show_unit_teaching' value='$show_unit_teaching'>";
	$html = $html."<input type='hidden' name='show_enrollment' value='$show_enrollment'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$html = $html."<TD><input type='submit' value='Reload'></TD></TR></TABLE></FORM>";
	return $html;
}

?>