<?php

//==================================================================================================
//
//	Special Report - Show all Teaching Component and their repations to Assessment Units
//
//	Last changes: 2013-04-15
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function TC_AU_report()
{

//  get all eaching Components and their related Assessment Units
    $table = TC_AU();
	
    return $table;
}

//--------------------------------------------------------------------------------------------------------------
function TC_AU()
//	Teaching Components and their related Assessment Units
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT 
#tc.*,
CONCAT('TC_',d.department_code,'_',tc.id) AS 'TC Code',
tc.subject AS 'Teaching Component',
CONCAT('TCT_',d.department_code,'_',tct.id) AS 'TCT Code',
tct.title,
tc.sessions_planned AS 'Sessions Planned',
tc.bookable AS 'Bookable',
au.assessment_unit_code AS 'AU Code',
au.title AS 'Assessment Unit',
''

FROM TeachingComponent tc
INNER JOIN Department d on d.id = tc.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
INNER JOIN AssessmentUnit au ON au.id = tcau.assessment_unit_id AND au.id != 99999
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id

WHERE 1=1
AND d.department_code LIKE '$department_code%'

ORDER BY tc.subject, au.assessment_unit_code


#LIMIT 10
		";
    if(isset($_POST['show_query'])) d_print($query);
	$table =get_data($query);

return $table;
}

//--------------------------------------------------------------------------------------------------------------
function TI_TC_report()
{
//  get all Teaching Instances and their related Teaching Components
    $table = TI_TC();
	
    return $table;
}

//--------------------------------------------------------------------------------------------------------------
function TI_TC()
//	Teaching Components and their related Teaching Instances
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT 
#ti.*,
CONCAT(d.department_code,'_',tc.id) AS 'TC Code',
tc.subject AS 'Teaching Component',
tc.sessions_planned AS 'Sessions Planned',
tc.bookable AS 'Bookable',
t.term_code AS 'Term',
e.opendoor_employee_code AS 'Employee Number',
e.fullname AS 'Lecturer',
ti.sessions AS 'Sessions Given',
ti.percentage AS '%',
''

FROM TeachingComponent tc
INNER JOIN Department d on d.id = tc.department_id
INNER JOIN TeachingInstance ti ON tc.id = ti.teaching_component_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Term t ON t.id = ti.term_id

WHERE 1=1
AND t.academic_year_id = $ay_id
AND d.department_code LIKE '$department_code%'

ORDER BY tc.subject, t.startdate, e.fullname


#LIMIT 10
		";
    if(isset($_POST['show_query'])) d_print($query);
	$table =get_data($query);

return $table;
}

?>