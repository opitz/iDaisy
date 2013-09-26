<?php
//==================================< The Buttons >=======================================
//
// common degree programme button functions to be used where needed 
// to be included in other scripts
// 2012-09-19 - 1. Version    cloned from unit buttons
// 2012-09-24 - included attributes, streamlines parameters
//
//========================================================================================

//--------------------------------------------------------------------------------------------------------------
function programme_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_programme_ay_button()."</TD>";

	if (programme_has_unit($dp_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".programme_units_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_programme_units_button()."</TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_programme_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function programme_units_button()
//	display a button to display/hide teaching information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	
	$html = "<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='dp_id' value='$dp_id'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($_POST['show_programme_units'])
	{
		$html = $html."<input type='hidden' name='show_programme_units' value=0>";
		$html = $html."<input type='submit' value='Hide Assessment Units'>";
	} else
	{
		$html = $html."<input type='hidden' name='show_programme_units' value=1>";
		$html = $html."<input type='submit' value='Show Assessment Units'>";
	}
	$html = $html."</FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_programme_units_button()
//	display a (dummy) button when there is NO teaching
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	
	$html = "<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='dp_id' value='$dp_id'>";
	
	$html = $html."<input type='hidden' name='show_programme_units' value=0>";
	$html = $html."<input type='submit' value='NO Assessment Units'></FORM>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function export_programme_button()
//	display a button to export a result into an Excel file
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];

	$show_programme_teaching = $_POST['show_programme_teaching'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
//	$html = $html."<input type='hidden' name='query' value='$query'>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='dp_id' value='$dp_id'>";
	$html = $html."<input type='hidden' name='show_programme_teaching' value='$show_programme_teaching'>";
	$html = $html."<input type='hidden' name='excel_export' value=1>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$html = $html."<input type='submit' value='Export to Excel'></FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function reload_programme_ay_button()
//	display a button to export a result into an Excel file
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];

	$show_programme_teaching = $_POST['show_programme_teaching'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<TABLE BORDER=0>";
	$html = $html."<TR><TD>Academic Year:</TD><TD>".academic_year_options($ay_id)."</TD>";		
	
	$html = $html."<input type='hidden' name='query' value='$query'>";
	$html = $html."<input type='hidden' name='dp_id' value='$dp_id'>";
	$html = $html."<input type='hidden' name='show_programme_teaching' value='$show_programme_teaching'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$html = $html."<TD><input type='submit' value='Reload'></TD></TR></TABLE></FORM>";
	return $html;
}

//===================================< Attributes >=======================================

//--------------------------------------------------------------------------------------------------
function programme_has_unit($dp_id, $ay_id)
//	checks if  a given Degree Programme ID has some relation to an Assessment Unit at all (for a given academic year)
{
	if($ay_id > 0) $query = "
		SELECT * 
		FROM AssessmentUnitDegreeProgramme audp
		
		WHERE audp.degree_programme_id = $dp_id 
		AND audp.academic_year_id = $ay_id
		";

	else $query = "
		SELECT * 
		FROM AssessmentUnitDegreeProgramme audp
		
		WHERE audp.degree_programme_id = $dp_id 
		";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

?>