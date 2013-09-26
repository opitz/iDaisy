<?php
//===================================< Attributes >=======================================
//
// common unit attribute functions to be used where needed 
// to be included in other scripts
//
// 2012-09-19 - branched off from unit_attributes
//
//========================================================================================

//--------------------------------------------------------------------------------------------------
function programme_has_unit($dp_id)
//	checks if  a given Degree Programme ID has some relation to an Assessment Unit at all (for a given academic year)
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

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