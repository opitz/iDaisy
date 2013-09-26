<?php

//==================================================================================================
//
//	Separate file with common unit functions
//	Last changes: Matthias Opitz --- 2012-08-10
//
//==================================================================================================
$version_cuf = "120718.1";			// 1st version
$version_cuf = "120724.1";			// completely new unit list
$version_cuf = "120731.1";			// added degree programme department to Enrolment Details
$version_cuf = "120809.1";			// cleanup
$version_cuf = "120810.1";			// no $webauth_code anymore
$version_cuf = "120823.1";			// changes in AU-DP relations

//--------------------------------------------------------------------------------------------------------------
function show_unit_list()
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$au_code_q = $_POST['au_code_q'];								// get au_code_q
	$au_title_q = $_POST['au_title_q'];									// get au_title_q
	$actv_only = $_POST['actv_only'];									// get actv_only
	$show_unrelated = $_POST['show_unrelated'];						// get show_unrelated

	$query = $_POST['query'];											// get query
	$query = stripslashes($query);

	$parameter['ay_id'] = $_POST['ay_id'];								// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];		// get department_code
	$parameter['au_code_q'] = $_POST['au_code_q'];					// get au_code_q
	$parameter['au_title_q'] = $_POST['au_title_q'];					// get au_title_q
	$parameter['actv_only'] = $_POST['actv_only'];						// get actv_only
	$parameter['show_unrelated'] = $_POST['show_unrelated'];			// get show_unrelated

	$db_name = get_database_name($conn);

//	$date = date('l jS \of F Y g:i:s');
	if(!$excel_export)
	{
		print_header("Teaching by Assessment Unit");

		if ($query) print_export_button($parameter);
		unit_query_form();
		print "<HR>";
	}
//	build query part II - build the query using the input
//	if(!$query AND ($department_code OR $forename_q OR $forename_q OR $webauth_q OR $student_code_q))
	if(!$query)
	{
		$query = "
			SELECT DISTINCT
			au.id AS 'AU_ID',";
//	Show a department column only when no department was selected in for the query
		if(!$department_code) $query = $query."
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',";
		$query = $query."
			au.assessment_unit_code AS 'Code',
			CONCAT('<A HREF=$this_page?au_id=', au.id, '&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit'
			
			FROM AssessmentUnit au
			INNER JOIN Department d ON d.id = au.department_id ";
		if(!$show_unrelated) $query = $query."INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id ";
		$query = $query."
			WHERE au.assessment_unit_code LIKE '%$au_code_q%'
			AND au.title LIKE '%$au_title_q%'
			";
			if($department_code) $query = $query."AND d.department_code LIKE '$department_code%'";
			$query = $query."ORDER BY d.department_code, au.assessment_unit_code";
	}

	$table = get_data($query);
	$new_table = array();
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
		$au_id = $row['AU_ID'];
		if( is_part_of_programme($au_id, $ay_id))
		{
			if(is_core($au_id, $ay_id)) $row['Core/Opt'] = 'Core';
			else $row['Core/Opt'] = 'Option';
			if(is_pgrad($au_id, $ay_id)) $row['Unit Type'] = 'PGRAD';
			else $row['Unit Type'] = 'UGRAD';
		} else
		{
			$row['Core/Opt'] = '';
			$row['Unit Type'] = '';
		}
//	if an academic year was selected do some more...
		if($ay_id > 0) 
		{
			$student_norm = get_student_norm($au_id, $ay_id);
			$dept_norm = get_dept_norm($au_id, $ay_id);
			$row['Students'] = count_students($au_id, $ay_id);
			$row['Stud Norm Stint'] = $student_norm['Stint'];
			$row['Dept Norm Stint'] = $dept_norm['Stint'];
			$row['Stint Delivered'] = get_unit_teaching_stint($au_id, $ay_id);
			if($row['Students']) $row['Stint / Student'] = number_format($row['Stint Delivered'] / $row['Students'],2);
			else $row['Stint / Student'] = '';
			
		}			
		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		$new_table[] = $row;
	}		

	$table_width = array('Code' => 100, 'Assessment Unit' => 600);
	print_table($new_table, $table_width, FALSE);
}

//--------------------------------------------------------------------------------------------------------------
function subval_sort($a,$subkey) 
{
	foreach($a as $k=>$v) 
	{
		$b[$k] = strtolower($v[$subkey]);
	}
	asort($b);
	foreach($b as $key=>$val) 
	{
		$c[] = $a[$key];
	}
	return $c;
}


//--------------------------------------------------------------------------------------------------------------
function show_unit_details()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$query = $_POST['query'];								// get query
	$query = stripslashes($query);

	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];
	
//	print the header and stuff
	print_header("Assessment Unit Details");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: $academic_year";
//	print_reset_button(this_page());
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$excel_export)
		unit_switchboard($ay_id, $au_id, $show_unit_teaching, $show_enrollment);


//	get the degree programme record for a given  ID
	if(!$query)
		$query = "
			SELECT 
			au.* 
		
			FROM AssessmentUnit au

			WHERE au.id = $au_id
			";
			
	$result = get_data($query);
	$au_data = $result[0];
	
	show_unit_specs($au_data);
	show_programmes_by_year($au_data['id'], $ay_id);
	if($show_unit_teaching) show_unit_teaching_details_by_year($au_data['id'], $ay_id);
	if($show_enrollment) show_enrollment_details_by_year($au_data['id'], $ay_id);
}

//--------------------------------------------------------------------------------------------------------------
function show_unit_specs($au_data)
//	print details for a given assessment unit record
{
	print "<H3>Assessment Unit Details</H3>";
	print "<TABLE BORDER=0>";
	
	print "<TR>";
	print 	"<TD WIDTH=120><B>Title:</B> </TD>";
	print 	"<TD>".$au_data['title']."</TD>";
	print "</TR>";
	print "<TR>";
	print 	"<TD WIDTH=120><B>Code:</B> </TD>";
	print 	"<TD>".$au_data['assessment_unit_code']."</TD>";
	print "</TR>";

	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function show_programmes_by_year($au_id, $given_ay_id)
//	Show Programmes of which a unit is part of
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Status'] = 50;
	$table_width['Department'] = 300;
	$table_width['Staff Class.'] = 350;

//	select the data
	$query = "
		SELECT DISTINCT
		ay.label AS 'Year',
		dp.degree_programme_code AS 'Code',
#		CONCAT('<A HREF=programme_details.php?dp_id=', dp.id, '&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
		dp.title AS 'Degree Programme',
		audp.unit_type as 'Type',
		audp.core_option as 'Core / Option'
		
		FROM DegreeProgramme dp
		INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.degree_programme_id = dp.id
		INNER JOIN AcademicYear ay ON ay.id = audp.academic_year_id
		
		WHERE audp.assessment_unit_id = $au_id ";
		if($given_ay_id > 0) $query = $query."AND ay.id = $given_ay_id ";
		$query = $query."
		
		ORDER BY ay.label, dp.degree_programme_code
		";
	$table = get_data($query);

	if($table)
	{
		print "<H4>Part of</H4>";
		print_table($table, $table_width, 0);
	} else
		print "No related Degree Programme!<P>";
}

//--------------------------------------------------------------------------------------------------
function show_unit_teaching_details_by_year($au_id, $given_ay_id)
//	print teaching details for a given Assessment Unit ID
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$this_page = this_page();
	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Term'] = 50;
	$table_width['Department'] = 300;
	$table_width['Subject'] = 350;
	$table_width['Type'] = 350;
	$table_width['Assessment Units (Students)'] = 350;

//	list by Academic Year
//	get list of Academic Years
	$query = "
		SELECT
		*
		FROM AcademicYear ay
		WHERE 1=1
		";
	if($given_ay_id > 0) $query = $query."AND id = $given_ay_id ";
	$query = $query."ORDER BY ay.startdate";
	$ac_years = get_data($query);
	
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
//	select the data
		$query = "
SELECT
ti.id AS 'TI_ID',
tc.id AS 'TC_ID',
#CONCAT(au_d.department_code, ' - ', au_d.department_name) AS 'owned by',
CONCAT(tc_d.department_code, ' - ', tc_d.department_name) AS 'Department',
CONCAT('<A HREF=$this_page?tc_id=',tc.id,'&ay_id=$given_ay_id&department_code=$department_code>',tc.subject,'</A>') AS 'Subject',
tct.title AS 'Type',
tcau.capacity AS 'Cap.',
tc.sessions_planned AS 'Plan Sess.',
tctt.stint AS 'Sess. Stint',
t.term_code AS 'Term',
ti.sessions AS 'Given Sess.',
CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$given_ay_id&department_code=$department_code>', e.fullname, '</A>') AS 'Lecturer',
ti.percentage AS '%'

FROM TeachingComponent tc

LEFT JOIN TeachingInstance ti ON ti.teaching_component_id= tc.id AND ti.academic_year_id = $ay_id
LEFT JOIN Term t ON t.id = ti.term_id
#LEFT JOIN AcademicYear ay ON ay.id = t.academic_year_id

LEFT JOIN TeachingComponentAssessmentUnit tcau on tcau.teaching_component_id = tc.id
#LEFT JOIN TeachingComponentAssessmentUnit tcau on tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id != 99999 AND tcau.academic_year_id = t.academic_year_id

LEFT JOIN TeachingComponentType tct ON tct.id = tc.teaching_component_type_id
LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = $ay_id

LEFT JOIN Department ti_d ON ti_d.id = ti.department_id
LEFT JOIN Department tc_d ON tc_d.id = tc.department_id

LEFT JOIN AssessmentUnit au ON au.id = tcau.assessment_unit_id
LEFT JOIN Department au_d ON au_d.id = au.department_id

LEFT JOIN Employee e ON e.id = ti.employee_id		

WHERE tcau.assessment_unit_id = $au_id
AND tcau.academic_year_id = $ay_id

ORDER BY tc_d.department_code, t.startdate, tc.subject
			";
//print "<HR>$query<HR>";		
		$table = get_data($query);
		
//	get the assessment units for each component
		$new_table = array();
		if($table) foreach($table AS $row)
		{
			$ti_id = $row['TI_ID'];
			$tc_id = $row['TC_ID'];
			
			$tc_students = get_all_tc_students($tc_id, $ay_id);
			$row['Unique TC Students'] = $tc_students;
			
			$query = "
				SELECT 
				au.*,
				COUNT(DISTINCT sau.student_id) AS 'AU Students'
			
				FROM AssessmentUnit au
				LEFT JOIN TeachingComponentAssessmentUnit tcau on tcau.assessment_unit_id = au.id AND tcau.assessment_unit_id != 99999
				LEFT JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = $ay_id
				
				WHERE tcau.academic_year_id = $ay_id
				AND tcau.teaching_component_id = $tc_id
				
				GROUP BY au.id
				";
//print "<HR>$query<HR>";
			$units = get_data($query);
			$academic_units = '';
			if($units) foreach($units AS $unit)
			{
//print_r($unit);
				$au_id = $unit['id'];
				$academic_units = $academic_units."<A HREF=$this_page?au_id=$au_id&ay_id=$given_ay_id&department_code=$department_code>".$unit['assessment_unit_code'].' - '.$unit['title'].'  ('.$unit['AU Students'].')'.'</A><BR>';
			}
			$row['Assessment Units (Students)'] = $academic_units;
			$row['DAISY'] = "<A HREF=https://daisy.socsci.ox.ac.uk/teaching_instance/$ti_id/edit TARGET=NEW>Edit</A>";
			array_shift($row);
			array_shift($row);
			$new_table[] = $row;
		}
		if($new_table)
		{
			if(!$title_printed)
			{
				print "<HR><H3>Teaching Details</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, 0);
		}
	}
}

//--------------------------------------------------------------------------------------------------
function show_enrollment_details_by_year($au_id, $given_ay_id)
//	print teaching details for a given Assessment Unit ID
{
	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Student'] = 350;

//	list by Academic Year
//	get list of Academic Years
	$query = "
		SELECT
		*
		FROM AcademicYear ay
		WHERE 1=1
		";
	if($given_ay_id > 0) $query = $query."AND id = $given_ay_id ";
	$query = $query."ORDER BY ay.startdate";
	$ac_years = get_data($query);
	
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
//	select the data
		$query = "
			SELECT
			st.id AS 'ST_ID',
			CONCAT(st.surname, ', ', st.forename) AS 'Student'
			
			FROM StudentAssessmentUnit sau
			INNER JOIN Student st ON st.id = sau.student_id
			
			WHERE sau.assessment_unit_id = $au_id
			AND sau.academic_year_id = $ay_id
			
			ORDER BY st.surname, st.forename
			";
//print "<HR>$query<HR>";		
		$table = get_data($query);
		

		if($table)
		{

			$new_table = array();
			foreach($table AS $row)
			{
//				$st_id = $row['ST_ID'];
				$st_id = array_shift($row);		// get the first element in the row and push it into $st_id
				$query = "
					SELECT
					dp.id AS 'DP_ID',
					dp.title,
					sdp.year_of_student,
					sdp.status
					
					FROM StudentDegreeProgramme sdp 
					INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
					
					WHERE sdp.student_id = $st_id
					AND sdp.academic_year_id = $ay_id
					AND (sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT' OR sdp.status = 'INACTIV' OR sdp.status = 'COMPLETED')
					";
				$student_data = get_data($query);
				if($student_data) foreach($student_data AS $st_data)
				{
					$dp_id = $st_data['DP_ID'];
					$yos = $st_data['year_of_student'];
					$row['Degree Programme'] = $st_data['title'];
					$row['Year'] = $st_data['year_of_student'];
					$row['Status'] = $st_data['status'];

//	get the owning department(s) of the degree programme
					$query = "
						SELECT
						d.department_code,
						d.department_name,
						dpd.percentage,
						CONCAT(d.department_code,'-',d.department_name,' (',dpd.percentage,'%)') AS 'Dept'
					
						FROM DegreeProgrammeDepartment dpd 
						INNER JOIN Department d ON d.id = dpd.department_id
					
						WHERE dpd.degree_programme_id = $dp_id
						AND dpd.academic_year_id = $ay_id
						AND dpd.year_of_programme = $yos
						";
					$department_data = get_data($query);
					$dept = '';
					$i = 0;
					if($department_data) foreach($department_data AS $dept_data)
					{
						if($i++ > 0) $dept = $dept."<BR>";
						$dept = $dept.$dept_data['Dept'];
					}
					$row['Prog Dept'] = $dept;
				
				} else
				{
					$row['Degree Programme'] = '<FONT COLOR=RED> No data</FONT>';
					$row['Year'] = '';
					$row['Status'] = '';
					$row['Prog Dept'] = '';
				}

//				array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
				$new_table[] = $row;
			}

			if(!$title_printed)
			{
				print "<HR><H3>Enrolment Details</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, 0);
		}
	}
	if (!$title_printed) print "<P><HR>No enrolled students!";

}

//----------------------------------------------------------------------------------------
function get_unit_teaching_stint($au_id, $ay_id)
//	get the teaching stint for an assessment unit for a give academic year
{
//	select the teaching data
	$query = "
SELECT
FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

FROM AssessmentUnit au
INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id 
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id

WHERE tcau.academic_year_id = $ay_id
AND au.id = $au_id
		";

	$table = get_data($query);
	$row = $table[0];
	$au_stint = $row['Stint'];

	return $au_stint;
}

//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function count_students($au_id, $ay_id)
// returns number of students enrolled to an assessment unit in a given year
{
	$query = "
		SELECT COUNT(*) AS 'COUNT'
		
		FROM StudentAssessmentUnit 
		
		WHERE assessment_unit_id = $au_id 
		AND academic_year_id = $ay_id
		";
	$result = get_data($query);
	$row = $result[0];
	return $row['COUNT'];
}

//--------------------------------------------------------------------------------------------------------------
function is_part_of_programme($au_id, $ay_id)
// returns TRUE if the given assessment unit has a relation with at least one degree programme
{
	$query = "
		SELECT COUNT(*) AS 'COUNT'
		
		FROM AssessmentUnitDegreeProgramme 
		
		WHERE assessment_unit_id = $au_id 
		";
//	if ($ay_id) $query = $query."AND academic_year_id = $ay_id";
	$result = get_data($query);
	$row = $result[0];
	if($row['COUNT'] > 0) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function is_pgrad($au_id, $ay_id)
// returns TRUE if the given assessment unit has a PGRAD unit type with at least one degree programme
{
	$query = "
		SELECT COUNT(*) AS 'COUNT'
		
		FROM AssessmentUnitDegreeProgramme 
		
		WHERE unit_type LIKE 'PGRAD%' 
		AND assessment_unit_id = $au_id 
		";
//	if ($ay_id) $query = $query."AND academic_year_id = $ay_id";
	$result = get_data($query);
	$row = $result[0];
	if($row['COUNT'] > 0) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function is_core($au_id, $ay_id)
// returns TRUE if the given assessment unit is CORE for at least one degree programme
{
	$query = "
		SELECT COUNT(*) AS 'COUNT'
		
		FROM AssessmentUnitDegreeProgramme 
		
		WHERE core_option = 'Core' 
		AND assessment_unit_id = $au_id 
		";
	if ($ay_id) $query = $query."AND academic_year_id = $ay_id";
	$result = get_data($query);
	$row = $result[0];
	if($row['COUNT'] > 0) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_norm($au_id, $ay_id)
// returns the norm stint and hour values for one student attending this assessment unit
{
	$student_norm = array();
	$query = "
		SELECT
		SUM(tctt.stint * tc.sessions_planned) AS 'Stint',
		SUM(tctt.hours * tc.sessions_planned) AS 'Hours'
		
		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		INNER JOIN TeachingComponentType tct ON tct.id = tc.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE au.id = $au_id 
		AND tcau.academic_year_id = $ay_id
		";
//dprint($query);
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function get_dept_norm($au_id, $ay_id)
// returns the norm stint and hour values that needs to be provided by the department to satisfy all enrolled students
{
	$dept_norm = array();
	$query = "
		SELECT
		SUM(IF(tcau.capacity > 0, CEIL((SELECT COUNT(sau.student_id) FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = au.id AND sau.academic_year_id = tcau.academic_year_id) / tcau.capacity), 1) * tctt.stint * tc.sessions_planned) AS 'Stint',
		SUM(IF(tcau.capacity > 0, CEIL((SELECT COUNT(sau.student_id) FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = au.id AND sau.academic_year_id = tcau.academic_year_id) / tcau.capacity), 1) * tctt.hours * tc.sessions_planned) AS 'Hours'

		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		INNER JOIN TeachingComponentType tct ON tct.id = tc.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE au.id = $au_id 
		AND tcau.academic_year_id = $ay_id
		";
//dprint($query);
	$result = get_data($query);
	return $result[0];
}

//----------------------------------------------------------------------------------------
function get_all_tc_students($tc_id, $ay_id)
//	get all students enrolled to any assessment unit using a giving teaching component and a given academic year
{
	$query = "
		SELECT
		COUNT(DISTINCT sau.student_id) AS 'TC Students'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = $ay_id
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id
		
		WHERE tc.id = $tc_id
		";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
		$row = mysql_fetch_assoc($result);
		return $row['TC Students'];
}


?>