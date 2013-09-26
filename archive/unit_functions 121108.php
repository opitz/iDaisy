<?php

//==================================================================================================
//
//	Separate file with common unit functions
//	Last changes: Matthias Opitz --- 2012-11-08
//
//==================================================================================================
$version_cuf = "120718.1";			// 1st version
$version_cuf = "120724.1";			// completely new unit list
$version_cuf = "120731.1";			// added degree programme department to Enrolment Details
$version_cuf = "120809.1";			// cleanup
$version_cuf = "120810.1";			// no $webauth_code anymore
$version_cuf = "120823.1";			// changes in AU-DP relations
$version_cuf = "120924.1";			// cleaned up parameters

//----------------------------------------------------------------------------------------
function unit_query_form()
//	print the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
//	$au_code_q = $_POST['au_code_q'];									// get au_code_q
//	$au_title_q = $_POST['au_title_q'];										// get au_title_q
//	$hide_unrelated = $_POST['hide_unrelated'];							// get hide_unrelated

	print "<FONT FACE='Arial'>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<input type='hidden' name='query_type' value='unit'>";
	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";		
	print new_column(400);
		print academic_year_options();	
		if(!$_POST['ay_id']) print " <FONT COLOR =GREY>Select a year for stint values</FONT>";	
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) .  "Assessment Unit Code:" . new_column(0) . "<input type='text' name = 'au_code_q' value='".$_POST['au_code_q']."' size=50>". end_row();		
	print start_row(0) . "Assessment Unit Title:" . new_column(0) . "<input type='text' name = 'au_title_q' value='".$_POST['au_title_q']."' size=50>" . end_row();		

	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "Exclude PGR Modules:";
	print new_column(60);
		if ($_POST['hide_unrelated']) print "<input type='checkbox' name='hide_unrelated' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='hide_unrelated' value='TRUE'>";
	print new_column(190);
		 print "Show Teaching Details:";
	print new_column(0);
		if ($_POST['show_teaching_details_list'])  print "<input type='checkbox' name='show_teaching_details_list' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_teaching_details_list' value='TRUE'>";
	print end_row();

	print "</TABLE>";
	print "</FONT>";

	print "<HR>";
	
//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function show_unit_list0()
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$actionpage = $_SERVER["PHP_SELF"];											// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];										// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$au_code_q = $_POST['au_code_q'];											// get au_code_q
	$au_title_q = $_POST['au_title_q'];												// get au_title_q
	$hide_unrelated = $_POST['hide_unrelated'];										// get hide_unrelated

	$db_name = get_database_name($conn);

//	$date = date('l jS \of F Y g:i:s');
	if(!$excel_export)
	{
		print_header("Teaching by Assessment Unit");

		unit_query_form();
		print "<HR>";
	}

//	=========The Query =========
	$query = "
		SELECT DISTINCT
		au.id AS 'AU_ID',";
//	Show a department column only when no department was selected in the query
	if(!$department_code) $query = $query."CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',";
	$query = $query."
		au.assessment_unit_code AS 'Code',
		CONCAT('<A HREF=$this_page?au_id=', au.id, '&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit'
		
		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id ";
	if($hide_unrelated) $query = $query."INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id ";
	$query = $query."
		WHERE au.assessment_unit_code LIKE '%$au_code_q%'
		AND au.title LIKE '%$au_title_q%'
		";
	if($department_code) $query = $query."AND d.department_code LIKE '$department_code%'";
	$query = $query."ORDER BY d.department_code, au.assessment_unit_code";

	$table = get_data($query);
	$new_table = array();
	
	$sum_dept_norm = 0;
	$sum_dept_stint = 0;
	
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
//		$au_id = $row['AU_ID'];
		$au_id = array_shift($row);
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
			$row['Norm Student Provision - Stint (Dept)'] = $student_norm['Stint'];
			$row['Norm Student Provision - Hours (Dept)'] = $student_norm['Hours'];
			$row['Norm Dept Provision - Stint'] = $dept_norm['Stint'];
			$row['Norm Dept Provision - Hours'] = $dept_norm['Hours'];
			$row['Actual Dept Provision - Stint'] = get_unit_teaching_stint($au_id, $ay_id);
			$row['Actual Dept Provision - Hours'] = get_unit_teaching_hours($au_id, $ay_id);
			$row['Students Entered'] = count_au_students($au_id, $ay_id);
			if($row['Students Entered']) $row['Actual Dept Provision - Stint per Student Entered'] = number_format($row['Actual Dept Provision - Stint'] / $row['Students Entered'],2);
			else $row['Actual Dept Provision - Stint per Student Entered'] = '';
			
		}			
		$new_table[] = $row;
	}		

	$table_width = array('Code' => 100, 'Assessment Unit' => 600);
	print_table($new_table, $table_width, FALSE);
}

//--------------------------------------------------------------------------------------------------------------
function show_unit_list()
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$actionpage = $_SERVER["PHP_SELF"];											// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];										// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$au_code_q = $_POST['au_code_q'];											// get au_code_q
	$au_title_q = $_POST['au_title_q'];												// get au_title_q
	$hide_unrelated = $_POST['hide_unrelated'];										// get hide_unrelated

	$db_name = get_database_name($conn);

//	$date = date('l jS \of F Y g:i:s');
	if(!$excel_export)
	{
		print_header("Department Teaching Report");

		unit_query_form();
		print "<HR>";
	}

//	=========The Query =========
	$query = "
		SELECT DISTINCT
		au.id AS 'AU_ID',";
//	Show a department column only when no department was selected in the query
	if(!$department_code) $query = $query."CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',";
	$query = $query."
		au.assessment_unit_code AS 'Code',
		CONCAT('<A HREF=$this_page?au_id=', au.id, '&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit'
		
		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id ";
	if($hide_unrelated) $query = $query."INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id ";
	$query = $query."
		WHERE au.assessment_unit_code LIKE '%$au_code_q%'
		AND au.title LIKE '%$au_title_q%'
		";
	if($department_code) $query = $query."AND d.department_code LIKE '$department_code%'";
	$query = $query."ORDER BY d.department_code, au.assessment_unit_code";

//d_print($query);

	$table = get_data($query);
	$new_table = array();
	
	$sum_dept_norm = 0;
	$sum_dept_stint = 0;
	
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
//		$au_id = $row['AU_ID'];
		$au_id = array_shift($row);

		if($_POST['show_teaching_details_list'])
		{
			show_unit_details($au_id);
			print "<HR>";
		} else
		{
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
				$row['Norm Student Provision - Stint (Dept)'] = $student_norm['Stint'];
				$row['Norm Student Provision - Hours (Dept)'] = $student_norm['Hours'];
				$row['Norm Dept Provision - Stint'] = $dept_norm['Stint'];
				$row['Norm Dept Provision - Hours'] = $dept_norm['Hours'];
				$row['Actual Dept Provision - Stint'] = get_unit_teaching_stint($au_id, $ay_id);
				$row['Actual Dept Provision - Hours'] = get_unit_teaching_hours($au_id, $ay_id);
				$row['Students Entered'] = count_au_students($au_id, $ay_id);
				if($row['Students Entered']) $row['Actual Dept Provision - Stint per Student Entered'] = number_format($row['Actual Dept Provision - Stint'] / $row['Students Entered'],2);
				else $row['Actual Dept Provision - Stint per Student Entered'] = '';
			
			}			
			$new_table[] = $row;
		}
	}		
	if($new_table)
	{
		$table_width = array('Code' => 100, 'Assessment Unit' => 600);
		print_table($new_table, $table_width, FALSE);
	}
}

//--------------------------------------------------------------------------------------------------------------
function show_unit_details($au_id)
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

//	$au_id = $_GET["au_id"];								// get au_id
//	if(!$au_id) $au_id = $_POST['au_id'];
	$_POST['au_id'] = $au_id;
	
	$query = $_POST['query'];								// get query
	$query = stripslashes($query);

	$show_unit_teaching = $_POST['show_unit_teaching'];
	$show_enrollment = $_POST['show_enrollment'];
	
//	print the header and stuff
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
		
	if(!$_POST['show_teaching_details_list'])
	{
		print_header("Assessment Unit Details");
		if($excel_export)
		{
			print "Selected Academic Year: $academic_year";
			print "<HR>";
		} else
			unit_switchboard();
	}

//	get the degree programme record for a given  ID
	if(!$query)
		$query = "
			SELECT * 
			FROM AssessmentUnit
			WHERE id = $au_id
			";

	$result = get_data($query);
	$au_data = $result[0];

//	show_unit_specs($au_data);
	show_unit_title($au_data);
	if(!$_POST['show_teaching_details_list']) show_programmes_by_year();
//	if($_POST['show_unit_teaching']) show_unit_teaching_details_by_year();
	show_unit_teaching_details_by_year();
	if($_POST['show_enrollment']) show_enrollment_details_by_year();
}

//--------------------------------------------------------------------------------------------------------------
function show_unit_specs($au_data)
//	print details for a given assessment unit record
{
//	print "<H3>Assessment Unit Details</H3>";
	print "<H3>".$au_data['title']."</H3>";
	print "<TABLE BORDER=0>";
	
//	print "<TR>";
//	print 	"<TD WIDTH=120><B>Title:</B> </TD>";
//	print 	"<TD>".$au_data['title']."</TD>";
//	print "</TR>";
	print "<TR>";
	print 	"<TD WIDTH=120><B>Code:</B> </TD>";
	print 	"<TD>".$au_data['assessment_unit_code']."</TD>";
	print "</TR>";

	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function show_unit_title($au_data)
//	print details for a given assessment unit record
{
	print "<H3>".$au_data['title']." (".$au_data['assessment_unit_code'].")</H3>";
}

//--------------------------------------------------------------------------------------------------
function show_programmes_by_year0()
//	Show Programmes of which a unit is part of
{
	$given_ay_id = $_GET['ay_id'];												// get selected academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$au_id = $_GET["au_id"];													// get selected assessment_unit_id
	if(!$au_id) $au_id = $_POST['au_id'];

	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();
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
		CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
		audp.unit_type as 'Type',
		audp.core_option as 'Core / Option'
		
		FROM DegreeProgramme dp
		INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.degree_programme_id = dp.id
		INNER JOIN AcademicYear ay ON ay.id = audp.academic_year_id
		
		WHERE audp.assessment_unit_id = $au_id ";
		if($given_ay_id > 0) $query = $query."AND audp.academic_year_id = $given_ay_id ";
		$query = $query."
		
		ORDER BY ay.label, dp.degree_programme_code
		";
//dprint($query);
	$table = get_data($query);

	if($table)
	{
		print "<H4>Part of</H4>";
		print_table($table, $table_width, 0);
	} else
		print "No related Degree Programme!<P>";
}

//--------------------------------------------------------------------------------------------------
function show_programmes_by_year()
//	Show Programmes of which a unit is part of
{
	$given_ay_id = $_GET['ay_id'];												// get selected academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$au_id = $_GET["au_id"];													// get selected assessment_unit_id
	if(!$au_id) $au_id = $_POST['au_id'];

	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();
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
		CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
		audp.unit_type as 'Type',
		audp.core_option as 'Core / Option'
		
		FROM DegreeProgramme dp
		INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.degree_programme_id = dp.id
		INNER JOIN AcademicYear ay ON ay.id = audp.academic_year_id
		
		WHERE audp.assessment_unit_id = $au_id ";
		if($given_ay_id > 0) $query = $query."AND audp.academic_year_id = $given_ay_id ";
		$query = $query."
		
		ORDER BY ay.label, dp.degree_programme_code
		";
//dprint($query);
	$table = get_data($query);

	if($table)
	{
		print "<H4>Part of</H4>";
		print_table($table, $table_width, 0);
	} else
		print "No related Degree Programme!<P>";
}

//--------------------------------------------------------------------------------------------------
function show_unit_teaching_details_by_year()
//	print teaching details for a given Assessment Unit ID
{
	$given_ay_id = $_GET['ay_id'];												// get selected academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$au_id = $_GET["au_id"];													// get selected assessment_unit_id
	if(!$au_id) $au_id = $_POST['au_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$this_page = this_page();
	$title_printed = FALSE;
	
//	define column width in table
	$table_width = array('Term' => 50, 'Department' => 300, 'Subject' => 350, 'Plan Sess.' => 50, 'Sess. Stint' => 50, 'Given Sess.' => 50, 'Type' => 350, 'Unique TC Students' => 50, 'Assessment Units (Students)' => 350);

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
#CONCAT(au_d.department_code, ' - ', au_d.department_name) AS 'owned by', ";
if(1 == 2) $query = $query . "CONCAT(tc_d.department_code, ' - ', tc_d.department_name) AS 'Department', ";
$query = $query . "
CONCAT('<A HREF=$this_page?tc_id=',tc.id,'&ay_id=$given_ay_id&department_code=$department_code>',tc.subject,'</A>') AS 'Teaching Component Name',
tct.title AS 'Teaching Component Type',
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
//dprint($query);	
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
			$units = get_data($query);
			$academic_units = '';
			if($units) foreach($units AS $unit)
			{
//print_r($unit);
				$au_id = $unit['id'];
				$academic_units = $academic_units."<A HREF=$this_page?au_id=$au_id&ay_id=$given_ay_id&department_code=$department_code>".$unit['assessment_unit_code'].' - '.$unit['title'].'  ('.$unit['AU Students'].')'.'</A><BR>';
			}
			$row['Assessment Units (Students)'] = $academic_units;
			if($ti_id) $row['DAISY'] = "<A HREF=https://daisy.socsci.ox.ac.uk/teaching_instance/$ti_id/edit TARGET=NEW>TI</A>";
			else $row['DAISY'] = "--";
			if($tc_id) $row['DAISY'] = $row['DAISY']." / <A HREF=https://daisy.socsci.ox.ac.uk/teaching_component/$tc_id/edit TARGET=NEW>TC</A>";
			array_shift($row);
			array_shift($row);
			$new_table[] = $row;
		}
		if($new_table)
		{
			if(!$_POST['show_teaching_details_list'])
			{
				if(!$title_printed)
				{
					print "<HR><H3>Teaching Details</H3>";
					$title_printed = TRUE;
				}
				if(!$given_ay_id) print"<H4>".$ac_year['label']."<H4>";
			}
			print_table($new_table, $table_width, 0);
		}
	}
}

//--------------------------------------------------------------------------------------------------
function show_enrollment_details_by_year0()
//	print teaching details for a given Assessment Unit ID
{
	$given_ay_id = $_GET['ay_id'];												// get selected academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$au_id = $_GET["au_id"];													// get selected assessment_unit_id
	if(!$au_id) $au_id = $_POST['au_id'];

	$department_code = $_GET['department_code'];							// get selected department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();
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
			dp.id AS 'DP_ID',
			CONCAT(st.surname, ', ', st.forename) AS 'Student', 
			
#			dp.title AS 'Degree Programme',
			CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
			sdp.year_of_student AS 'Year',
			sdp.status AS 'Status'
			
			FROM StudentAssessmentUnit sau
			INNER JOIN Student st ON st.id = sau.student_id
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = sau.academic_year_id
			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
			
			WHERE sau.assessment_unit_id = $au_id
			AND sau.academic_year_id = $ay_id
			AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme WHERE student_id = st.id AND academic_year_id = sdp.academic_year_id AND status = 'ENROLLED'), sdp.status = 'ENROLLED', (sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED'))
			
			ORDER BY dp.title, st.surname, st.forename
			";
dprint($query);		
//		$table = get_data($query);
		

		if($table)
		{

			$new_table = array();
			foreach($table AS $row)
			{
//				$st_id = $row['ST_ID'];

//				$dp_id = $row['DP_ID'];
				$dp_id = array_shift($row);		// get the first element in the row and push it into $dp_id
				$yos = $row['Year'];

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

//				array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
				$new_table[] = $row;
			}

			if(!$title_printed)
			{
				print "<HR><H3>Student Details</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, 1);
		}
	}
	if (!$title_printed) print "<P><HR>No enrolled students!";

}

//--------------------------------------------------------------------------------------------------
function show_enrollment_details_by_year()
//	print teaching details for a given Assessment Unit ID
{
	$given_ay_id = $_GET['ay_id'];												// get selected academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$au_id = $_GET["au_id"];													// get selected assessment_unit_id
	if(!$au_id) $au_id = $_POST['au_id'];

	$department_code = $_GET['department_code'];							// get selected department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();
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
		
//	1st get the students enrolled in the assessment unit
		$query = "
			SELECT
			dp.id AS 'DP_ID',
			CONCAT(st.surname, ', ', st.forename) AS 'Student', 
			
#			dp.title AS 'Degree Programme',
			CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
			sdp.year_of_student AS 'Year',
			sdp.status AS 'Status'
			
			FROM StudentAssessmentUnit sau
			INNER JOIN Student st ON st.id = sau.student_id
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = sau.academic_year_id
			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
			
			WHERE sau.assessment_unit_id = $au_id
			AND sau.academic_year_id = $ay_id
			AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme WHERE student_id = st.id AND academic_year_id = sdp.academic_year_id AND status = 'ENROLLED'), sdp.status = 'ENROLLED', (sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED'))
			
			ORDER BY dp.title, st.surname, st.forename
			";
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
		$table = get_data($query);

//	2nd: get the degree programme and enrolment status of each student	
		if($table)
		{
			$new_table = array();
			foreach($table AS $row)
			{
				$st_id = array_shift($row);
				$query = "
					SELECT 
					dp.id AS 'DP_ID',
					CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
					sdp.year_of_student AS 'Year',
					sdp.status AS 'Status'
					
					FROM StudentDegreeProgramme sdp
					INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id

					WHERE sdp.student_id = $st_id
					AND sdp.academic_year_id = $ay_id
				";
				if (student_is_enrolled($st_id, $ay_id))
					$query = $query . " AND sdp.status = 'ENROLLED' ";
				else
					$query = $query . " AND (sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED') ";
					
				$result = get_data($query);
				$programme = $result[0];
				
				array_unshift($row, $programme['DP_ID']);
				$row['Degree Programme'] = $programme['Degree Programme'];
				$row['Year'] = $programme['Year'];
				$row['Status'] = $programme['Status'];
				
				$new_table[] = $row;
			}
			$table = $new_table;
		}

//	3rd: get the owning department(s) of the degree programme
		if($table)
		{
			$new_table = array();
			foreach($table AS $row)
			{
//				$st_id = $row['ST_ID'];

//				$dp_id = $row['DP_ID'];
				$dp_id = array_shift($row);		// get the first element in the row and push it into $dp_id
				$yos = $row['Year'];

				$dept = '';
				if($yos)
				{
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
					$i = 0;
					if($department_data) foreach($department_data AS $dept_data)
					{
						if($i++ > 0) $dept = $dept."<BR>";
						$dept = $dept.$dept_data['Dept'];
					}
				}
				$row['Prog Dept'] = $dept;

//				array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
				$new_table[] = $row;
			}

			if(!$title_printed)
			{
				print "<HR><H3>Student Details</H3>";
				$title_printed = TRUE;
			}
			if($given_ay_id == -1)print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, 1);
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

//----------------------------------------------------------------------------------------
function get_unit_teaching_hours($au_id, $ay_id)
//	get the teaching stint for an assessment unit for a give academic year
{
//	select the teaching data
	$query = "
SELECT
FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.hours),2) AS 'Hours'

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
	return $row['Hours'];
}

//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function count_tc_students($tc_id, $ay_id)
// returns number of students enrolled to an assessment unit in a given year
{
	$query = "
		SELECT COUNT(*) AS 'COUNT'
		
		FROM StudentAssessmentUnit sau
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = sau.assessment_unit_d AND tcau.academic_year_id = sau.academic_year_id
		
		WHERE tcau.teaching_component_id = $tc_id 
		AND sau.academic_year_id = $ay_id
		";
	$result = get_data($query);
	$row = $result[0];
	return $row['COUNT'];
}

//--------------------------------------------------------------------------------------------------------------
function count_au_students($au_id, $ay_id)
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
//d_print($query);
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function get_dept_norm00($au_id, $ay_id)
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

//--------------------------------------------------------------------------------------------------------------
function get_dept_norm0($au_id, $ay_id)
// returns the norm stint and hour values that needs to be provided by the department to satisfy all enrolled students
// if there no capacity limit or the number of students is 0 assume the minimum (= student) norm
{
	$dept_norm = array();
	$query = "
		SELECT
		SUM(IF(tcau.capacity > 0 AND (SELECT COUNT(sau.student_id) FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = au.id AND sau.academic_year_id = tcau.academic_year_id) > 1, CEIL((SELECT COUNT(sau.student_id) FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = au.id AND sau.academic_year_id = tcau.academic_year_id) / tcau.capacity), 1) * tctt.stint * tc.sessions_planned) AS 'Stint',
		SUM(IF(tcau.capacity > 0 AND (SELECT COUNT(sau.student_id) FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = au.id AND sau.academic_year_id = tcau.academic_year_id) > 1, CEIL((SELECT COUNT(sau.student_id) FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = au.id AND sau.academic_year_id = tcau.academic_year_id) / tcau.capacity), 1) * tctt.hours * tc.sessions_planned) AS 'Hours'

		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		INNER JOIN TeachingComponentType tct ON tct.id = tc.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE au.id = $au_id 
		AND tcau.academic_year_id = $ay_id
		";
//d_print($query);
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function get_dept_norm($au_id, $ay_id)
// returns the norm stint and hour values that needs to be provided by the department to satisfy all enrolled students
// if there no capacity limit or the number of students is 0 assume the minimum (= student) norm
{
	$dept_norm = array();
//	get the number of students enrolled to the given assessment unit in a given academic year
	$query = "SELECT COUNT(sau.student_id) AS 'Students' FROM StudentAssessmentUnit sau WHERE sau.assessment_unit_id = $au_id AND sau.academic_year_id = $ay_id";
	$result = get_data($query);
	$row = $result[0];
	$students = $row['Students'];

//	now calculate the dept norm using the capacity and the number of enrolled students
	$query = "
		SELECT
		SUM(IF(tcau.capacity > 0 AND $students > 1, CEIL($students / tcau.capacity), 1) * tctt.stint * tc.sessions_planned) AS 'Stint',
		SUM(IF(tcau.capacity > 0 AND $students > 1, CEIL($students / tcau.capacity), 1) * tctt.hours * tc.sessions_planned) AS 'Hours'

		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		INNER JOIN TeachingComponentType tct ON tct.id = tc.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE au.id = $au_id 
		AND tcau.academic_year_id = $ay_id
		";
//d_print($query);
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

//==================================< The Buttons >=======================================

//--------------------------------------------------------------------------------------------------------------
function unit_switchboard()
{
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	$_POST['au_id'] = $au_id;

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD ALIGN=LEFT BGCOLOR=LIGHTBLUE>".reload_ay_button()."</TD>";

//	print "<TD WIDTH=200 ALIGN=CENTER>".unit_teaching_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=CENTER>".unit_enrollment_button()."</TD>";

	print "<TD WIDTH=30></TD>";
	print "<TD ALIGN=LEFT BGCOLOR=LIGHTGREEN>".export_button()."</TD>";
	print "<TD ALIGN=LEFT BGCOLOR=PINK>".new_query_button()."</TD>";

	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function unit_teaching_button()
//	display a button to display/hide teaching information
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$au_id = $_GET["au_id"];								// get programme ID dp_id
	if(!$au_id) $au_id = $_POST['au_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = '';
	
//	$html = $html."<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();
	
	if (unit_has_teaching($au_id, $ay_id)) 
	{
		if($_POST['show_unit_teaching'])
		{
			$html = $html."<input type='hidden' name='show_unit_teaching' value=0>";
			$html = $html."<input type='submit' value='Hide Teaching Details'>";
		} else
		{
			$html = $html."<input type='hidden' name='show_unit_teaching' value=1>";
			$html = $html."<input type='submit' value='Show Teaching Details'>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_unit_teaching' value=0>";
		$html = $html."<input type='submit' value='NO Teaching Details'>";
	}
	$html = $html."</FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function unit_enrollment_button()
//	display a button to display/hide enrollment information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['au_id']) $_POST['au_id'] = $_GET['au_id'];	// get programme ID dp_id
	$au_id = $_POST['au_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
//	$html = $html."<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if (unit_has_enrollment($au_id, $ay_id)) 
	{
		if($_POST['show_enrollment'])
		{
			$html = $html."<input type='hidden' name='show_enrollment' value=0>";
			$html = $html."<input type='submit' value='Hide Student Details'></FORM>";
		} else
		{
			$html = $html."<input type='hidden' name='show_enrollment' value=1>";
			$html = $html."<input type='submit' value='Show Student Details'></FORM>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_enrollment' value=0>";
		$html = $html."<input type='submit' value='NO Student Details'></FORM>";
	}
	return $html;
}

//===================================< Attributes >=======================================

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
	if ($ay_id > 0) $query = $query."AND academic_year_id = $ay_id";
	$result = get_data($query);
	$row = $result[0];
	if($row['COUNT'] > 0) return TRUE;
	else return FALSE;
}

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

//--------------------------------------------------------------------------------------------------
function student_is_enrolled($st_id, $ay_id)
//	checks if  a given Assessment Unit ID has some enrollment at all (for a given academic year)
{
	$query = "
		SELECT * 
		FROM StudentDegreeProgramme
		
		WHERE student_id = $st_id 
		AND academic_year_id = $ay_id
		AND status = 'ENROLLED'
	";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}



?>