<?php
//==================================< The Buttons >=======================================
//
// common button functions to be used where needed 
// to be included in other scripts
// 2012-06-01 - 1. Version    ==   Happy birthday, Winnie!
// 2012-07-26 - added staff_switchboard
// 2012-07-26 - added publication status to update ay button
// 2012-09-24 - included attributes, streamlined parameters
//
//========================================================================================

//--------------------------------------------------------------------------------------------------------------
function staff_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST

	$_POST['e_id']	 = $e_id;
	
	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";

	if (has_teaching($e_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".teaching_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_teaching_button()."</TD>";
	if (has_supervising($e_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".supervising_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_supervising_button()."</TD>";
	if (has_leave($e_id, $ay_id)) print "<TD WIDTH=200 ALIGN=LEFT>".leave_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_leave_button()."</TD>";
	if (has_published($e_id)) print "<TD WIDTH=200 ALIGN=LEFT>".publication_button()."</TD>";
	else print "<TD WIDTH=200 ALIGN=LEFT>".no_publication_button()."</TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function teaching_button()
//	display a button to display/hide teaching information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='show_publication' value='$show_publication'>";
//	$html = $html."<input type='hidden' name='borrowed' value='$borrowed'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_teaching)
	{
		$html = $html."<input type='hidden' name='show_teaching' value=0>";
		$html = $html."<input type='submit' value='Hide Teaching Details'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_teaching' value=1>";
		$html = $html."<input type='submit' value='Show Teaching Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_teaching_button()
//	display a (dummy) button when there is NO teaching
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='show_publication' value='$show_publication'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	$html = $html."<input type='hidden' name='show_teaching' value=0>";
	$html = $html."<input type='submit' value='NO Teaching Details'></FORM>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function supervising_button()
//	display a button to display/hide supervising information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='show_publication' value='$show_publication'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_supervising)
	{
		$html = $html."<input type='hidden' name='show_supervising' value=0>";
		$html = $html."<input type='submit' value='Hide Supervising Details'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_supervising' value=1>";
		$html = $html."<input type='submit' value='Show Supervising Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_supervising_button()
//	display a (dummy) button when there is NO supervising
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='show_publication' value='$show_publication'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	$html = $html."<input type='hidden' name='show_supervising' value=0>";
	$html = $html."<input type='submit' value='NO Supervising Details'></FORM>";

	return $html;
}

//--------------------------------------------------------------------------------------------------
function leave_button()
//	display a button to display/hide leave information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_publication' value='$show_publication'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_leave)
	{
		$html = $html."<input type='hidden' name='show_leave' value=0>";
		$html = $html."<input type='submit' value='Hide Leave Details'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_leave' value=1>";
		$html = $html."<input type='submit' value='Show Leave Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_leave_button()
//	display a (dummy) button when there is NO leave
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_publication' value='$show_publication'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	$html = $html."<input type='hidden' name='show_leave' value=0>";
	$html = $html."<input type='submit' value='NO Leave Details'></FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function publication_button()
//	display a button to display/hide publication information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	if($show_publication)
	{
		$html = $html."<input type='hidden' name='show_publication' value=0>";
		$html = $html."<input type='submit' value='Hide Publications'></FORM>";
	} else
	{
		$html = $html."<input type='hidden' name='show_publication' value=1>";
		$html = $html."<input type='submit' value='Show Publications'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function no_publication_button()
//	display a (dummy) button when there is NO publication
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	
	$html = $html."<input type='hidden' name='show_publication' value=0>";
	$html = $html."<input type='submit' value='NO Publications'></FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function export_button0()
//	display a button to export a result into an Excel file
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
	$html = $html."<FORM action='$actionpage' method=POST>";
	$html = $html."<input type='hidden' name='ay_id' value='$ay_id'>";
	$html = $html."<input type='hidden' name='e_id' value='$e_id'>";
	$html = $html."<input type='hidden' name='show_teaching' value='$show_teaching'>";
	$html = $html."<input type='hidden' name='show_supervising' value='$show_supervising'>";
	$html = $html."<input type='hidden' name='show_leave' value='$show_leave'>";
	$html = $html."<input type='hidden' name='department_code' value='$department_code'>";
	$html = $html."<input type='hidden' name='excel_export' value=1>";

	$html = $html."<input type='submit' value='Export to Excel'></FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function new_query_button0()
//	display a button for a new query
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";

	$html = $html."<input type='submit' value='- Reset -'></FORM>";
	return $html;
}

//==================================< Attributes >=======================================

//--------------------------------------------------------------------------------------------------
function has_teaching($e_id, $ay_id)
//	checks if  a given Employee ID has some teaching at all (for a given academic year)
{
	if($ay_id > 0) $query = "SELECT * FROM TeachingInstance ti INNER JOIN Term t ON t.id = ti.term_id WHERE ti.employee_id = $e_id AND t.academic_year_id = $ay_id";
	else $query = "SELECT * FROM TeachingInstance WHERE employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function has_supervising($e_id, $ay_id)
//	checks if  a given Employee ID has some supervising at all (for a given academic year)
{
	if($ay_id > 0) $query = "SELECT * FROM Supervision sv INNER JOIN Term t ON t.id = sv.term_id WHERE sv.employee_id = $e_id AND t.academic_year_id = $ay_id";
	else $query = "SELECT * FROM Supervision where employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function has_leave($e_id, $ay_id)
//	checks if  a given Employee ID has some leave at all (for a given academic year)
{
	if($ay_id > 0) $query = "SELECT * FROM EmployeeAcademicLeave eal INNER JOIN Term t ON t.id = eal.term_id WHERE eal.employee_id = $e_id AND t.academic_year_id = $ay_id";
	else $query = "SELECT * FROM EmployeeAcademicLeave where employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function has_published($e_id)
//	checks if  a given Employee ID has published something at all
{
	$query = "SELECT * FROM PublicationEmployee where employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}


?>