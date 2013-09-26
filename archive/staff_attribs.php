<?php
//==================================< The Buttons >=======================================
//
// common attribute functions to be used where needed 
// to be included in other scripts
//
// 2012-06-20 - added filter by academic year if set
//
//========================================================================================

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