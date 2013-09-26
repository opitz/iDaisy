<?php
//==================================================================================================
//
//	Separate file with common component functions
//	Last changes: Matthias Opitz --- 2012-09-28
//
//==================================================================================================
//	$version_ctcf = "120801.1";			// 1st version
//	$version_ctcf = "120809.1";			// added details
//	$version_ctcf = "120810.1";			// no $webauth_code anymore
//	2012-09-28							unification of code into one file
//--------------------------------------------------------------------------------------------------------------
function show_component_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();
	
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Component_Query_Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];			// get department_code
	$tc_subject_q = $_POST['tc_subject_q'];						// get tc_subject_q

	$query = $_POST['query'];								// get query
	$query = stripslashes($query);

	$parameter['ay_id'] = $_POST['ay_id'];								// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];			// get department_code
	$parameter['tc_subject_q'] = $_POST['tc_subject_q'];				// get tc_subject_q

	$db_name = get_database_name($conn);

//	$date = date('l jS \of F Y g:i:s');
	if(!$excel_export)
	{
		print_header("Teaching Components");
		print "<HR>";

		if ($query) print_export_button();
		component_query_form();
		print "<HR>";
	}
//	build query part II - build the query using the input
//	if(!$query AND ($department_code OR $forename_q OR $forename_q OR $webauth_q OR $student_code_q))
	if(!$query)
	{
		$query = "
			SELECT DISTINCT
			tc.id AS 'TC_ID',";
//	Show a department column only when no department was selected in for the query
		if(!$department_code) $query = $query."
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',";
		$query = $query."
			CONCAT('<A HREF=$this_page?tc_id=', tc.id, '&ay_id=$ay_id&department_code=$department_code>',tc.subject,'</A>') AS 'Subject'
			
			FROM TeachingComponent tc
			INNER JOIN Department d ON d.id = tc.department_id
			
			WHERE tc.subject LIKE '%$tc_subject_q%'
			";
			if($department_code) $query = $query."AND d.department_code LIKE '$department_code%'";
			$query = $query."ORDER BY tc.subject";
	}
	if ($query) 
	{
//		$start_time = time();
		$table_width = array();
		$table_width['Code'] = 100;
		$table_width['Title'] = 600;
		
		$table = get_data($query);
		$new_table = array();
		$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
		if($table) foreach($table as $row)
		{
			$tc_id = $row['TC_ID'];
//	if an academic year was selected do some more...
			if($ay_id > 0) 
			{
				$query = "
					SELECT
					CONCAT('<A HREF=$this_page?au_id=',au.id,'&ay_id=$ay_id&department_code=$department_code>',au.assessment_unit_code,' - ',au.title,'</A>') AS 'Unit'
					FROM TeachingComponentAssessmentUnit tcau
					INNER JOIN AssessmentUnit au ON au.id = tcau.assessment_unit_id
					WHERE tcau.academic_year_id = $ay_id
					AND tcau.teaching_component_id = $tc_id
					AND au.id != 99999
					ORDER BY au.assessment_unit_code
					";
				$units = get_data($query);
				$i=0;
				$units_text = '';
				if($units)
				{
					foreach($units AS $unit)
					{
						if($i++ > 0) $units_text = $units_text."<BR>";
						$units_text = $units_text.$unit['Unit'];
					}
				}
				$row['Assessment Units'] = $units_text;
			}			
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			$new_table[] = $row;
		}		

//      ***********************************************
		print_table($new_table, $table_width, FALSE);
//      ***********************************************
//		$end_time = time();
//		$diff_time = $end_time - $start_time;
//		print "<HR COLOR = LIGHTGREY><FONT COLOR = GREY>executed in $diff_time seconds";
	}
}

//--------------------------------------------------------------------------------------------------------------
function show_component_details()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Component_Query_Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];
	
//	print the header and stuff
	print_header("Teaching Component Details");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: $academic_year";
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$excel_export)
		component_switchboard($ay_id, $tc_id, $show_component_teaching);


//	get the degree programme record for a given  ID
	if(!$query)
		$query = "SELECT * FROM TeachingComponent WHERE id = $tc_id";
		
	$result = get_data($query);
	$tc_data = $result[0];
	
	show_component_specs($tc_data);
	show_units_by_year($tc_data['id'], $ay_id);
	if($show_component_teaching) show_component_teaching_details_by_year($tc_data['id'], $ay_id);
}

//--------------------------------------------------------------------------------------------------------------
function show_component_specs($tc_data)
//	print details for a given teaching component record
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$tc_id = $tc_data['id'];
	
	$query = "
SELECT DISTINCT
tc.subject AS 'Subject',
tct.title as 'Type'";
	if($ay_id) $query = $query.", 
tcau.sessions_planned AS 'Sessions planned',
tcau.capacity AS 'Capacity' ";
	else $query = $query.", 
'<I>Please select Academic Year</I>' AS 'Sessions planned',
'<I>for figures' AS 'Capacity</I>' ";

	$query = $query."
FROM TeachingComponentAssessmentUnit tcau
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id

WHERE 1=1
AND tcau.teaching_component_id = $tc_id
AND tcau.assessment_unit_id != 99999
		";
	if($ay_id) $query = $query."AND tcau.academic_year_id = $ay_id"; 
	$specs = get_data($query);	
	
	print "<H3>Teaching Component Details</H3>";
	print "<TABLE BORDER=0>";
	
	if($specs) foreach($specs AS $spec)
	{
		while ($item = current($spec))
		{
			$key = key($spec);

			print "<TR>";
			print 	"<TD WIDTH=150><B>".$key.":</B> </TD>";
			print 	"<TD>".$item."</TD>";
			print "</TR>";
			next($spec);
		}
	}

	print "</TABLE>";
	print "<HR>";
	

}

//--------------------------------------------------------------------------------------------------------------
function show_component_specs1($tc_data)
//	print details for a given teaching component record
{
	$tc_id = $tc_data['id'];
	
	$query = "
SELECT DISTINCT
ay.label AS 'Year',
tc.subject AS 'Subject',
tct.title as 'Type',
tcau.sessions_planned AS 'Sessions planned',
tcau.capacity AS 'Capacity'

FROM TeachingComponentAssessmentUnit tcau
INNER JOIN AcademicYear ay ON ay.id = tcau.academic_year_id
INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id

WHERE 1=1
AND tcau.teaching_component_id = $tc_id
AND tcau.assessment_unit_id != 99999 
		";
	if($ay_id) $query = $query."AND ay.id = $ay_id"; 
	$specs = get_data($query);	
	
	print "<H3>Teaching Component Details</H3>";
	$column_width = array('Year' => 50, 'Subject' => 300, 'Type' => 200, 'Sessions planned' => 20, 'Capacity' => 20);
	print_table($specs, $column_width,0);

	print "<HR>";
	

}

//--------------------------------------------------------------------------------------------------
function show_units_by_year($tc_id, $given_ay_id)
//	Show Units of which a component is part of
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();

	$title_printed = FALSE;
	
//	select the data
	$query = "
		SELECT 
#		$tc_id AS 'TC_ID',
		ay.label AS 'Year',
		au.assessment_unit_code AS 'Code',
		CONCAT('<A HREF=$this_page?au_id=', au.id, '&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit',
		COUNT(sau.student_id) AS 'Students'
		
		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.assessment_unit_id !=99999
		INNER JOIN AcademicYear ay ON ay.id = tcau.academic_year_id
		LEFT JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id AND sau.academic_year_id = ay.id
		
		WHERE tcau.teaching_component_id = $tc_id ";
		if($given_ay_id > 0) $query = $query."AND ay.id = $given_ay_id ";
		$query = $query."
		
		GROUP BY ay.label, au.assessment_unit_code
		ORDER BY ay.label, au.assessment_unit_code
		";
	$table = get_data($query);

	if($table)
	{
		print "<H4>Related to</H4>";
//		define column width in table
		$table_width = array('Year' => 50, 'Code' => 50, 'Assessment Unit' => 300);
		print_table($table, $table_width, 0);
	} else
		print "No related Assessment Unit!<P>";
}

//--------------------------------------------------------------------------------------------------
function show_component_teaching_details_by_year($tc_id, $given_ay_id)
//	print teaching details for a given Teaching Component ID
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id
//dprint($department_code);

	$this_page = this_page();
	$title_printed = FALSE;
	
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
	
//	now do for each year
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
//	select the data
		$query = "
SELECT DISTINCT
ti.id AS 'TI_ID',
tc.id AS 'TC_ID',
e.id AS 'E_ID',
t.term_code AS 'Term',
tct.title as 'Type',
IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tc.stint_override, tctt.stint)) AS 'Tariff',
ti.sessions AS 'Given Sess.', ";
//$query = $query."CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$given_ay_id&department_code=$department_code>', e.fullname, '</A>') AS 'Lecturer', ";
//else $query = $query."e.fullname AS 'Lecturer', ";
$query = $query."e.fullname AS 'Lecturer', ";
$query = $query."
ti.percentage AS '%',
#ROUND(tctt.stint * ti.sessions * ti.percentage / 100) AS 'Stint'
FORMAT(IF(ti.stint_override > 0, ti.stint_override, IF(tcau.stint_override > 0, tc.stint_override, tctt.stint)) * ti.sessions * ti.percentage / 100,2) AS 'Stint'

FROM TeachingComponent tc

LEFT JOIN TeachingInstance ti ON ti.teaching_component_id= tc.id AND ti.academic_year_id = $ay_id
LEFT JOIN Term t ON t.id = ti.term_id
LEFT JOIN Department tc_d ON tc_d.id = tc.department_id
LEFT JOIN Employee e ON e.id = ti.employee_id		

LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id

WHERE tc.id = $tc_id
AND t.academic_year_id = $ay_id

ORDER BY t.startdate, e.fullname
			";

		$table = get_data($query);
//	get the assessment units for each component
		$new_table = array();
		if($table) foreach($table AS $row)
		{			
			$ti_id = array_shift($row);
			$tc_id = array_shift($row);
			$e_id = array_shift($row);
			if(current_user_is_in_DAISY_user_group("Divisional-Reporter") OR (current_user_is_in_DAISY_user_group("Editor") AND employee_is_affiliated($e_id, $dept_id)))
			$row['Lecturer'] = "<A HREF=$this_page?e_id=$e_id&ay_id=$given_ay_id&department_code=$department_code>".$row['Lecturer']."</A>";
			
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
//			define column width in table
			$table_width = array('Term' => 50, 'Department' => 300, 'Subject' => 350, 'Type' => 200, 'Assessment Units (Students)' => 350);

			print_table($new_table, $table_width, 0);
		}
	}
}

//--------------------------------------------------------------------------------------------------
function show_component_teaching_details_by_year0($tc_id, $given_ay_id)
//	print teaching details for a given Teaching Component ID
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id
//dprint($department_code);

	$this_page = this_page();
	$title_printed = FALSE;
	
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
	
//	now do for each year
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
//	select the data
		$query = "
SELECT DISTINCT
ti.id AS 'TI_ID',
tc.id AS 'TC_ID',
e.id AS 'E_ID',
t.term_code AS 'Term',
tct.title as 'Type',
tctt.stint AS 'Tariff',
ti.sessions AS 'Given Sess.', ";
//$query = $query."CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$given_ay_id&department_code=$department_code>', e.fullname, '</A>') AS 'Lecturer', ";
//else $query = $query."e.fullname AS 'Lecturer', ";
$query = $query."e.fullname AS 'Lecturer', ";
$query = $query."
ti.percentage AS '%',
ROUND(tctt.stint * ti.sessions * ti.percentage / 100) AS 'Stint'

FROM TeachingComponent tc

LEFT JOIN TeachingInstance ti ON ti.teaching_component_id= tc.id AND ti.academic_year_id = $ay_id
LEFT JOIN Term t ON t.id = ti.term_id
LEFT JOIN Department tc_d ON tc_d.id = tc.department_id
LEFT JOIN Employee e ON e.id = ti.employee_id		

LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id

WHERE tc.id = $tc_id
AND t.academic_year_id = $ay_id

ORDER BY t.startdate, e.fullname
			";

		$table = get_data($query);
//	get the assessment units for each component
		$new_table = array();
		if($table) foreach($table AS $row)
		{			
			$ti_id = array_shift($row);
			$tc_id = array_shift($row);
			$e_id = array_shift($row);
			if(current_user_is_in_DAISY_user_group("Divisional-Reporter") OR (current_user_is_in_DAISY_user_group("Editor") AND employee_is_affiliated($e_id, $dept_id)))
			$row['Lecturer'] = "<A HREF=$this_page?e_id=$e_id&ay_id=$given_ay_id&department_code=$department_code>".$row['Lecturer']."</A>";
			
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
//			define column width in table
			$table_width = array('Term' => 50, 'Department' => 300, 'Subject' => 350, 'Type' => 200, 'Assessment Units (Students)' => 350);

			print_table($new_table, $table_width, 0);
		}
	}
}

//=====================================< Buttons >======================================

//--------------------------------------------------------------------------------------------------------------
function component_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	$_POST['tc_id'] = $tc_id;

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".component_teaching_button()."</TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function component_teaching_button()
//	display a button to display/hide teaching information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];		// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['tc_id']) $_POST['tc_id'] = $_GET['tc_id'];		// get teaching component ID tc_id
	$tc_id = $_POST['tc_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	
	if (component_has_teaching($tc_id, $ay_id)) 
	{
		if($_POST['show_component_teaching'])
		{
			$html = $html."<input type='hidden' name='show_component_teaching' value=0>";
			$html = $html."<input type='submit' value='Hide Teaching Details'></FORM>";
		} else
		{
			$html = $html."<input type='hidden' name='show_component_teaching' value=1>";
			$html = $html."<input type='submit' value='Show Teaching Details'></FORM>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_component_teaching' value=0>";
		$html = $html."<input type='submit' value='NO Teaching Details'></FORM>";
	}
	return $html;
}

//===================================< Attributes >=======================================

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