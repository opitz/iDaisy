<?php
//==================================< The Buttons >=======================================
//
// common component button functions to be used where needed 
// to be included in other scripts
// 2012-08-01 - 1. Version    cloned from unit buttons
// 2012-08-14 - 1. Version    now preserving selected department code
// 2012-09-24 - 1. Version    included attributes, streamlined parameters
//
//========================================================================================

//--------------------------------------------------------------------------------------------------------------
function component_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";
	if (component_has_teaching($tc_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".component_teaching_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_component_teaching_button()."</TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_component_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function component_teaching_button()
//	display a button to display/hide teaching information
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];														// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='tc_id' value='$tc_id'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_component_teaching)
	{
		$html = $html."<input type='hidden' name='show_component_teaching' value=0>";
		$html = $html."<input type='submit' value='Hide Teaching Details'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_component_teaching' value=1>";
		$html = $html."<input type='submit' value='Show Teaching Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_component_teaching_button()
//	display a (dummy) button when there is NO teaching
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];														// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='tc_id' value='$tc_id'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	$html = $html."<input type='hidden' name='show_component_teaching' value=0>";
	$html = $html."<input type='submit' value='NO Teaching Details'></FORM>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function export_component_button()
//	display a button to export a result into an Excel file
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];														// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='query' value='$query'>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='tc_id' value='$tc_id'>";
	$html = $html."<input type='hidden' name='show_component_teaching' value='$show_component_teaching'>";
	$html = $html."<input type='hidden' name='excel_export' value=1>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";

	$html = $html."<input type='submit' value='Export to Excel'></FORM>";
	return $html;
}

//===================================< Attributes >=======================================

//--------------------------------------------------------------------------------------------------
function component_has_teaching($tc_id, $ay_id)
//	checks if  a given Teaching Component ID has some teaching at all (for a given academic year)
{
	if($ay_id > 0) $query = "
		SELECT * 
		FROM TeachingInstance ti 
		INNER JOIN Term t ON t.id = ti.term_id 
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		
		WHERE tc.id = $tc_id 
		AND t.academic_year_id = $ay_id
		";
	else $query = "
		SELECT * 
		FROM TeachingInstance ti 
		INNER JOIN Term t ON t.id = ti.term_id 
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id

		WHERE tc.id = $tc_id 
		";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

?>