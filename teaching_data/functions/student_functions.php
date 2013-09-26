<?php

//==================================================================================================
//
//	Basic Reports - Students
//
//	Last changes: 2013-05-07
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function students()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$dp_id = $_POST['dp_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT DISTINCT
CONCAT(st.surname,', ',st.forename) AS 'Student',
st.oss_student_code AS 'OSS ID',
dp.title AS 'Degree Programme',
d.department_name AS 'Department',
sdp.year_of_student AS 'Student Year'

FROM Student st
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.status = 'ENROLLED'
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.academic_year_id = sdp.academic_year_id
INNER JOIN Department d ON d.id = dpd.department_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND sdp.academic_year_id = $ay_id ";
if($dp_id > 0) $query = $query . "AND dp.id = $dp_id ";
$query = $query . "
ORDER BY st.surname, st.forename 
	";
	if(isset($_POST['show_query'])) d_print($query);

	return get_data($query);	
}



?>