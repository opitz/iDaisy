<?php
//===================================< Attributes >=======================================
//
// common component attribute functions to be used where needed 
// to be included in other scripts
//
// 2012-08-01 - branched off from unit_attributes
// 2012-08-09 - bugfix
//
//========================================================================================

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