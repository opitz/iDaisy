<?php
//===================================< Attributes >=======================================
//
// common unit attribute functions to be used where needed 
// to be included in other scripts
//
// 2012-07-26 - branched off from staff_attributes
//
//========================================================================================

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