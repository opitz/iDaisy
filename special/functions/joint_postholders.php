<?php

//==================================================================================================
//
//	Special Report -Show all SSD Joint Post Holders
//
//	Last changes: 2013-04-12
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function joint_postholder_report()
{

//	get all ACTV joint post holders that have at least on post in SSD
	$table = joint_postholders();
	
return $table;
}

//--------------------------------------------------------------------------------------------------------------
function joint_postholders()
//	get all ACTV joint post holders that have at least on post in SSD
{
//print "hallo?";
	$ay_id = $_POST['ay_id'];									// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);				// get department id

	$query = "
SELECT 

e.fullname AS 'Staff Member',
d.department_code AS 'Dept Code',
d.department_name AS 'Department',
sc. staff_classification_name AS 'Staff Class',
#(SELECT SUM(p1.dept_stint_obligation) FROM Post p1 WHERE p1.employee_id = e.id AND p1.person_status = 'ACTV') AS 'Sum StOb',
FORMAT(IF((SELECT SUM(p1.dept_stint_obligation) FROM Post p1 WHERE p1.employee_id = e.id AND p1.person_status = 'ACTV') = 0, 0.5, p.dept_stint_obligation / (SELECT SUM(p1.dept_stint_obligation) FROM Post p1 WHERE p1.employee_id = e.id AND p1.person_status = 'ACTV')),2) AS 'Appointment Split',
p.dept_stint_obligation AS 'Stint Obligation'

FROM Employee e
INNER JOIN Post p ON p.employee_id = e.id
INNER JOIN Department d ON d.id = p.department_id
INNER JOIN StaffClassification sc ON sc.id = p.staff_classification_id

WHERE 1=1
AND p.person_status = 'ACTV' 
#AND sc.staff_classification_code LIKE 'A%'

AND (SELECT COUNT(*) FROM Post p2 WHERE p2.employee_id = e.id AND p2.person_status = 'ACTV') >1 
# only showing joint post holders that have at least one post with a SSD dept
 AND (SELECT COUNT(*) FROM Post p3 INNER JOIN Department d1 ON d1.id = p3.department_id WHERE p3.employee_id = e.id AND p3.person_status = 'ACTV' AND d1.department_code LIKE '3C%') >0

ORDER BY e.fullname, d.department_code

LIMIT 1000
		";
//d_print($query);
	$table =get_data($query);

return $table;
}

?>