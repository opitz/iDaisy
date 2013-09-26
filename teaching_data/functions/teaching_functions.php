<?php

//==================================================================================================
//
//	Basic Reports - Teaching
//
//	Last changes: 2013-09-24
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function academics()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT
e.id AS 'E_ID',
";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
e.opendoor_employee_code AS 'Employee Number',
e.fullname AS 'Academic',
sc.staff_classification_name AS 'Classification', 
p.dept_stint_obligation AS 'Stint Obl.',
DATE(p.startdate) AS 'Startdate',
IF(YEAR(p.enddate) = '0000','', DATE(p.enddate)) AS 'Enddate',
p.person_status AS 'Current Status'

FROM Employee e
INNER JOIN Post p ON p.employee_id = e.id 
INNER JOIN Department d ON d.id = p.department_id 
INNER JOIN StaffClassification sc ON sc.id = p.staff_classification_id, 
AcademicYear ay

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND ay.id = $ay_id
AND p.startdate < ay.enddate AND IF(1984 < YEAR(p.enddate), ay.startdate < p.enddate, 1=1)
#AND sc.staff_classification_code LIKE 'A%'

ORDER BY d.department_name, e.fullname
	";

	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
//d_print($query);	
	$table = get_data($query);
	$table = add_academic_links($table, 'E_ID', 'Academic');
	$table = add_links($table, 'Employee', 'E_ID');
	return cleanup($table,1);
}

//--------------------------------------------------------------------------------------------------------------
function employees()
{
	
	$resultset = get_form_list('Employee');
	
//	$resultset = add_form_links($resultset, 'AssessmentUnit', 'AU_ID', 'Assessment Unit');
	$resultset = add_links($resultset, 'Employee', 'E_ID');
	return $resultset;
//	return cleanup($resultset,1);
}

//--------------------------------------------------------------------------------------------------------------
function assessment_units()
{
	
	$resultset = get_form_list('AssessmentUnit');
	
//	$resultset = add_form_links($resultset, 'AssessmentUnit', 'AU_ID', 'Assessment Unit');
	$resultset = add_links($resultset, 'AssessmentUnit', 'AU_ID');
//	return $resultset;
	return cleanup($resultset,1);
}

//--------------------------------------------------------------------------------------------------------------
function assessment_units0()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id
	
	$dataset = read_form_data('AssessmentUnit');
//	$dataset = read_form_data('TeachingData');
	
	$resultset = array();
	

	if($dataset) foreach($dataset AS $field)
	{
		$column = array();
		
		if(use_field($field))
		{
			if($field['label'] == '') $field['label'] = $field['field'];
			$query = "
				SELECT ".$field['field']." AS '".$field['label']."' 
				FROM ".$field['table']."
				WHERE 1=1 
			";
//			if(clip_by_academic_year_id($field['table'])) $query = $query . "AND " . "academic_year_id = " . $_POST['ay_id'] . " ";
			if(has_department_id($field['table'])) $query = $query . "AND department_id = $dept_id ";

//			dprint($query);
			$column = get_data($query);

			if(!$resultset) $resultset = $column;
			else
			{
				$new_resultset = array();
				$i = 0;
				if($column) foreach($column AS $value)
				{
//dprint($value);
					$row = $resultset[$i++];
					$row[$field['label']] = $value[$field['label']];
					$new_resultset[] = $row;
				}
				$resultset = $new_resultset;
			}


//			print_r($column); print "<HR>";
//p_table($column);
			
//			$resultset = array_merge($resultset, $column);
//			$resultset = $resultset + $column;
//			$resultset[$field['field']] = $column;
		}
	}	
//p_table($resultset);
//	if($resultset) foreach($resultset AS $result)
//	{
//		p_table($result);
//	}

	$resultset = add_links($resultset, 'AssessmentUnit', 'AU_ID');
	return $resultset;
}


//--------------------------------------------------------------------------------------------------------------
function assessment_units00()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT DISTINCT
";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
au.assessment_unit_code AS 'Code',
#au.title AS 'Assessment Unit',
CONCAT('<A HREF=index.php?table=AssessmentUnit&id=',au.id,'>',au.title,'</A>') AS 'Assessment Unit',
#CONCAT('<A HREF=../academic.php?e_id=',e.id,' TARGET=NEW>',e.fullname,'</A>') AS 'Lecturer', 
e.fullname AS 'Course Provider',
IF(au.manual = 1,'X','') AS 'Manual',
IF(au.doctoral_training = 1,'X','') AS 'Doctoral'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id
LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
LEFT JOIN Employee e ON e.id = au.course_provider_employee_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'

ORDER BY au.title
	";

	$query = "
SELECT DISTINCT
au.id AS 'AU_ID',
au.assessment_unit_code AS 'Code',
au.title AS 'Assessment Unit',
e.fullname AS 'Course Provider',
IF(au.manual = 1,'X','') AS 'Manual',
IF(au.doctoral_training = 1,'X','') AS 'Doctoral'

FROM AssessmentUnit au
INNER JOIN Department d ON d.id = au.department_id
LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
LEFT JOIN Employee e ON e.id = au.course_provider_employee_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'

ORDER BY au.title
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
	
	$table = get_data($query);
	$table = add_links($table, 'AssessmentUnit', 'AU_ID');
	return cleanup($table,1);
}


//--------------------------------------------------------------------------------------------------------------
function teaching_components1()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$form_name = 'TeachingComponent';

	$dataset = read_form_data($form_name);

	if($dataset)
	{	
		$query = " 
			SELECT DISTINCT
			tc.id AS 'TC_ID',
		";
		foreach($dataset AS $field)
		{
			if($field['list'] > 0) $query = $query . $field['table'].".".$field['field']." AS '".$field['label']."', ";
		}
		$query = $query . "'' ";
		
		$query = $query . "FROM ";
		foreach($dataset AS $field)
		{
			if($field['list'] > 0) $query = $query . "', ";
		}
	}
	
	


	$query = " 
SELECT DISTINCT
tc.id AS 'TC_ID',
";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
CONCAT(d.department_code,'-',tc.id) AS 'TC Code',
tc.subject AS 'Teaching Component',
tct.title AS 'Type',
'' AS 'Assessment Unit',
tc.sessions_planned AS 'Norm Sessions'


FROM TeachingComponent tc
INNER JOIN Department d ON d.id = tc.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'

ORDER BY d.department_code, tc.subject
	";
	if(isset($_POST['show_query'])) d_print($query);

	$table = get_data($query);
	$table = add_links($table, 'TeachingComponent', 'TC_ID');

	$table = insert_units($table);
	$_POST['query'] = $query;
	return cleanup($table,1);
}

//--------------------------------------------------------------------------------------------------------------
function teaching_components()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = " 
SELECT DISTINCT
tc.id AS 'TC_ID',
";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
CONCAT(d.department_code,'-',tc.id) AS 'TC Code',
tc.subject AS 'Teaching Component',
tct.title AS 'Type',
'' AS 'Assessment Unit',
tc.sessions_planned AS 'Norm Sessions'


FROM TeachingComponent tc
INNER JOIN Department d ON d.id = tc.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'

ORDER BY d.department_code, tc.subject
	";
	if(isset($_POST['show_query'])) d_print($query);

//d_print($query);
	$table = get_data($query);
	$table = add_links($table, 'TeachingComponent', 'TC_ID');

	$table = insert_units($table);
	$_POST['query'] = $query;
	return cleanup($table,1);
}

//--------------------------------------------------------------------------------------------------------------
function insert_units($table)
//	inserts all Assessment Units each Teaching Component in $table is related to and put it in the field 'Assessment Unit'
{
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$tc_id = $row['TC_ID'];
		$units = get_tc_units($tc_id);
		$the_field = "";
		$counter = 0;
		if($units) foreach($units AS $unit)
		{
			if($unit['assessment_unit_code'] != 'ZOM99999')
			{
				if(isset($_POST['excel_export']))
				{
					if($the_field != '') $the_field = $the_field . " || ";
				} else
				{
					if($the_field != '') $the_field = $the_field . ", <BR />";	
				}
//				$the_field = $the_field . $unit['assessment_unit_code'] . ' - ' . $unit['title'];
				$the_field = $the_field . $unit['title'] . ' (' . $unit['assessment_unit_code'] . ')';
			}
		}
		$row['Assessment Unit'] = $the_field;
		$new_table[] = $row;
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function teaching_instances()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

//	update the instance_count field of multiple instances by the same employee in the same term before output
	count_multiple_instances();
	
	$query = " 
SELECT DISTINCT
e.id AS 'E_ID',
ti.id AS 'TI_ID',
		";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
e.opendoor_employee_code AS 'employee number',
e.fullname AS 'lecturer', 
t.term_code AS 'term',
CONCAT(d.department_code,'-',tc.id) AS 'component code',
tc.subject AS 'teaching component',
tct.title AS 'type',
		";

//if(isset($_POST['excel_export'])) $query = $query . "
//e.fullname AS 'Lecturer', ";
//else $query = $query . "
//CONCAT('<A HREF=../academic.php?e_id=',e.id,' TARGET=NEW>',e.fullname,'</A>') AS 'Lecturer', ";

$query = $query . "
ti.instance_count AS 'count',
ti.sessions AS 'sessions',
ti.percentage AS 'percentage',
FORMAT(ti.sessions * tctt.hours * ti.percentage / 100,2) AS 'hours',
FORMAT(ti.sessions * tctt.stint * ti.percentage / 100,2) AS 'stint'

FROM TeachingInstance ti
INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
INNER JOIN Department d ON d.id = tc.department_id
#INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id != 99999 AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Term t ON t.id = ti.term_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND t.academic_year_id = $ay_id

ORDER BY d.department_code, e.fullname, t.startdate, tc.subject
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
//d_print($query);
	$table = get_data($query);
	$table = add_academic_links($table, 'E_ID', 'lecturer');
	$table = add_links($table, 'TeachingData', 'TI_ID');

	$table = cleanup($table,2);

	return $table;	
}

//--------------------------------------------------------------------------------------------------------------
function teaching_instances0()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = " 
SELECT DISTINCT
e.id AS 'E_ID',
ti.id AS 'TI_ID',
		";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
t.term_code AS 'Term',
CONCAT(d.department_code,'-',tc.id) AS 'TC Code',
#tc.code AS 'TC Code',
tc.subject AS 'Teaching Component',
tct.title AS 'Type',
e.opendoor_employee_code AS 'Employee Code',
e.fullname AS 'Lecturer', 
		";

//if(isset($_POST['excel_export'])) $query = $query . "
//e.fullname AS 'Lecturer', ";
//else $query = $query . "
//CONCAT('<A HREF=../academic.php?e_id=',e.id,' TARGET=NEW>',e.fullname,'</A>') AS 'Lecturer', ";

$query = $query . "
ti.sessions AS 'Sessions',
ti.percentage AS 'Percentage',
FORMAT(ti.sessions * tctt.stint * ti.percentage / 100,2) AS 'Stint'

FROM TeachingInstance ti
INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
INNER JOIN Department d ON d.id = tc.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id != 99999 AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Term t ON t.id = ti.term_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND t.academic_year_id = $ay_id

ORDER BY d.department_code, t.startdate, tc.subject, e.fullname
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
	$table = get_data($query);
	
	$table = add_academic_links($table, 'E_ID', 'Lecturer');
	$table = add_links($table, 'TeachingInstance', 'TI_ID');

	$table = cleanup($table,2);
	return $table;	
}

//--------------------------------------------------------------------------------------------------------------
function teaching_data1()
{
	
	$resultset = get_form_list('TeachingData');
	
	$resultset = add_links($table, 'TeachingData', 'TI_ID');
	return $resultset;
}

//--------------------------------------------------------------------------------------------------------------
function teaching_data()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

//	update the instance_count field of multiple instances by the same employee in the same term before output
	count_multiple_instances();
	
	$query = " 
SELECT DISTINCT
e.id AS 'E_ID',
ti.id AS 'TI_ID',
		";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
e.opendoor_employee_code AS 'employee number',
e.fullname AS 'lecturer', 
t.term_code AS 'term',
CONCAT(d.department_code,'-',tc.id) AS 'component code',
tc.subject AS 'teaching component',
tct.title AS 'type',
		";

//if(isset($_POST['excel_export'])) $query = $query . "
//e.fullname AS 'Lecturer', ";
//else $query = $query . "
//CONCAT('<A HREF=../academic.php?e_id=',e.id,' TARGET=NEW>',e.fullname,'</A>') AS 'Lecturer', ";

$query = $query . "
ti.instance_count AS 'count',
ti.sessions AS 'sessions',
ti.percentage AS 'percentage',
FORMAT(ti.sessions * tctt.hours * ti.percentage / 100,2) AS 'hours',
FORMAT(ti.sessions * tctt.stint * ti.percentage / 100,2) AS 'stint'

FROM TeachingInstance ti
INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
INNER JOIN Department d ON d.id = tc.department_id
#INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id != 99999 AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Term t ON t.id = ti.term_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND t.academic_year_id = $ay_id

ORDER BY d.department_code, e.fullname, t.startdate, tc.subject
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
//d_print($query);
	$table = get_data($query);
	$table = add_academic_links($table, 'E_ID', 'lecturer');
	$table = add_links($table, 'TeachingData', 'TI_ID');

	$table = cleanup($table,2);

	return $table;	
}

//--------------------------------------------------------------------------------------------------------------
function supervision()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT DISTINCT
tc.subject AS 'Teaching Component',
tct.title AS 'Type',
#e.fullname AS 'Lecturer',
CONCAT('<A HREF=../academic.php?e_id=',e.id,' TARGET=NEW>',e.fullname,'</A>') AS 'Lecturer',
t.term_code AS 'Term',
ti.sessions AS 'Sessions',
ti.percentage AS 'Percentage',
FORMAT(ti.sessions * tctt.stint * ti.percentage / 100,2) AS 'Stint'

FROM TeachingInstance ti
INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
INNER JOIN Department d ON d.id = tc.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN Term t ON t.id = ti.term_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND t.academic_year_id = $ay_id

ORDER BY tc.subject, t.startdate, e.fullname
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function teaching_stint_tariff()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT 
tct.title AS 'Teaching Component Type',
tctt.stint as 'Stint / Session', 
tctt.hours as 'Hours / Session',
d.department_name AS 'Initiated by'

FROM TeachingComponentType tct 
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id
INNER JOIN Department d ON d.id = tct.department_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND tctt.academic_year_id = $ay_id

ORDER BY tct.title
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function supervision_stint_tariff()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = "
SELECT 
svt.title AS 'Supervision Type',
svtt.stint as 'Stint / Academic Year'

FROM SupervisionType svt 
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id
INNER JOIN Department d ON d.id = svt.department_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'
AND svtt.academic_year_id = $ay_id

ORDER BY svt.title
	";
	if(isset($_POST['show_query'])) d_print($query);

	$_POST['query'] = $query;
	return get_data($query);	
}

//--------------------------------------------------------------------------------------------------------------
function ses_data()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$query = " 
SELECT DISTINCT
tc.id AS 'TC_ID',
";

if(strlen($department_code)<4) $query = $query."
CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
";

$query = $query . "
CONCAT(d.department_code,'-',tc.id) AS 'TC Code',
tc.subject AS 'Teaching Component',
tct.title AS 'Type',
'' AS 'Assessment Unit',
tc.sessions_planned AS 'Norm Sessions'


FROM TeachingComponent tc
INNER JOIN Department d ON d.id = tc.department_id
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id

WHERE 1=1 
AND d.department_code LIKE '$department_code%'

ORDER BY d.department_code, tc.subject
	";
	if(isset($_POST['show_query'])) d_print($query);

//d_print($query);
	$table = get_data($query);
	$table = add_links($table, 'TeachingComponent', 'TC_ID');

	$table = insert_units($table);
	$_POST['query'] = $query;
	return cleanup($table,1);
}

//==============================================< HELPERS>======================================================
//--------------------------------------------------------------------------------------------------------------
function add_academic_links($table, $id_column_name, $link_column_name)
//	add a link to the content of the link column pointing to the academic stint report based on the ID given 
{
	
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$query_type = $_POST['query_type'];				// get query_type

	$new_table = array();
	if($table) foreach($table AS $row)
	{	
		if($row[$id_column_name] > 0) $row[$link_column_name] = "<A HREF=../academic.php?e_id=".$row[$id_column_name]." TARGET=NEW>" . $row[$link_column_name] . "</A>";
		$new_table[] = $row;
	}
	
	return $new_table;
}
//--------------------------------------------------------------------------------------------------------------
function get_tc_units($tc_id)
//	get all Assessment Units a given Teaching Component is related to
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id

	$query = "
	SELECT
	au.*
	
	FROM AssessmentUnit au
	INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id
	
	WHERE tcau.teaching_component_id = $tc_id
	ORDER BY au.assessment_unit_code
	";
	
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------
function count_multiple_instances()
//	get all Teaching Instances where there is more than 1 instance for the same term, employee and teaching component and count them per employee
//	the counter is then stored in the record.
{
	$query = "
SELECT DISTINCT
e.id AS 'E_ID',
tc.id AS 'TC_ID',
t.term_code,
e.fullname,
tc.subject,
ti.sessions,
ti.percentage
#ti.*

FROM TeachingInstance ti
INNER JOIN Term t ON t.id = ti.term_id
INNER JOIN Employee e ON e.id = ti.employee_id
INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
INNER JOIN Department d ON d.id = ti.department_id

WHERE 1=1
#AND t.term_code = 'HT13'
AND t.academic_year_id = ".$_POST['ay_id']."
AND d.department_code LIKE '".$_POST['department_code']."%'
AND (SELECT COUNT(*) FROM TeachingInstance ti2 WHERE ti2.term_id = t.id AND ti2.employee_id = ti.employee_id AND ti2.teaching_component_id = ti.teaching_component_id) > 1

	";

		$result = get_data($query);
		
		if($result) foreach($result AS $row)
		{
			$query2 = "
				SELECT ti.*
				FROM TeachingInstance ti 
				INNER JOIN Term t ON t.id = ti.term_id
				
				WHERE 1=1
				AND t.academic_year_id = ".$_POST['ay_id']." 
				AND t.term_code = '".$row['term_code']."' 
				AND ti.employee_id = ".$row['E_ID']." 
				AND ti.teaching_component_id = ".$row['TC_ID']." 
			";
			$res = get_data($query2);
			if($res)
			{
				$i = 1;
				foreach($res AS $row2)
				{
					$rec_id = $row2['id'];
					if($rec_id)
					{
						$query3 = "
						UPDATE TeachingInstance
						SET instance_count = $i
						WHERE id = $rec_id
						";
						$result = mysql_query($query3) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
//						print "+";
					}
					$i++;
				}
			}
			
			
			
		}
}



?>