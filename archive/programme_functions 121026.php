<?php

//==================================================================================================
//
//	Separate file with common degree programme functions
//	Last changes: Matthias Opitz --- 2012-10-05
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function programme_query_form()
//	print the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
//	$dp_code_q = $_POST['dp_code_q'];						// get dp_code_q
//	$dp_title_q = $_POST['dp_title_q'];						// get dp_title_q

	print "<form action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	print write_post_vars_html();

	print "<input type='hidden' name='query_type' value='prog'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Code:" . new_column(0) . "<input type='text' name = 'dp_code_q' value='".$_POST['dp_code_q']."' size=50>" . end_row();		
	print start_row(0) . "Title:" . new_column(0) . "<input type='text' name = 'dp_title_q' value='".$_POST['dp_title_q']."' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print "<TABLE BORDER=0>";
	print start_row(250);
		print "<TABLE BORDER=0>";
		print_reset_button();
		print "</TABLE>";
	print new_column(190);
		print "<input type='submit' value='Go!'>";
		print "</FORM>";
	print new_column(0);
		print "<TABLE BORDER=0>";
		if($_POST['query_type']) print_export_button();
		print "</TABLE>";
	print end_row();
	print "</TABLE>";
}

//--------------------------------------------------------------------------------------------------------------
function show_programme_list0()
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Programme Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$dp_code_q = $_POST['dp_code_q'];								// get dp_code_q
	$dp_title_q = $_POST['dp_title_q'];									// get dp_title_q
	$actv_only = $_POST['actv_only'];									// get actv_only

	$query = $_POST['query'];											// get query
	$query = stripslashes($query);

	$parameter['ay_id'] = $_POST['ay_id'];								// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];		// get department_code
	$parameter['dp_code_q'] = $_POST['dp_code_q'];					// get dp_code_q
	$parameter['dp_title_q'] = $_POST['dp_title_q'];					// get dp_title_q
	$parameter['actv_only'] = $_POST['actv_only'];						// get actv_only

	$db_name = get_database_name($conn);

//	$date = date('l jS \of F Y g:i:s');
	if(!$excel_export)
	{
		print_header("Teaching by Degree Programme");
		programme_query_form();
		print "<HR>";
	}
//	build query part II - build the query using the input
//	if(!$query AND ($department_code OR $forename_q OR $forename_q OR $webauth_q OR $student_code_q))
	if(!$query)
	{
		$query = "
			SELECT DISTINCT
			dp.id AS 'DP_ID',";
//	Show a department column only when no department was selected in for the query
		if(strlen($department_code)<4) $query = $query."
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',";
		$query = $query."
			dp.degree_programme_code AS 'Code',
			CONCAT('<A HREF=$this_page?dp_id=', dp.id, '&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme'
			
			FROM DegreeProgramme dp
			INNER JOIN Department d ON d.id = dp.department_id 
			
			WHERE dp.degree_programme_code LIKE '%$dp_code_q%'
			AND dp.title LIKE '%$dp_title_q%'
			";

			if($department_code) $query = $query."AND (
														SELECT COUNT(*) 
														FROM DegreeProgrammeDepartment dpd2 
														INNER JOIN Department d2 ON d2.id = dpd2.department_id
														WHERE dpd2.degree_programme_id = dp.id 
														AND d2.department_code LIKE '$department_code%'
														) > 0
													";

//			if($department_code) $query = $query."AND d.department_code LIKE '$department_code%'";
			$query = $query."ORDER BY d.department_code, dp.degree_programme_code";
	}
dprint($query);
	$table = get_data($query);
	$new_table = array();
	
	$sum_dept_norm = 0;
	$sum_dept_stint = 0;
	
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
//		$dp_id = $row['DP_ID'];;
		$dp_id = array_shift($row);

//	if an academic year was selected do some more...
		if($ay_id > 0) 
		{
		}			

//		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		$new_table[] = $row;
	}		

	$table_width = array('Code' => 100, 'Degree Programme' => 600);
	print_table($new_table, $table_width, FALSE);
}

//--------------------------------------------------------------------------------------------------------------
function show_programme_list()
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
//dprint($department_code);
	$dept_id = get_dept_id($department_code);									// get department id

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Programme Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];											// get academic_year_id
	$dp_code_q = $_POST['dp_code_q'];								// get dp_code_q
	$dp_title_q = $_POST['dp_title_q'];									// get dp_title_q
	$actv_only = $_POST['actv_only'];									// get actv_only

	$query = $_POST['query'];											// get query
	$query = stripslashes($query);

	$parameter['ay_id'] = $_POST['ay_id'];								// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];		// get department_code
	$parameter['dp_code_q'] = $_POST['dp_code_q'];					// get dp_code_q
	$parameter['dp_title_q'] = $_POST['dp_title_q'];					// get dp_title_q
	$parameter['actv_only'] = $_POST['actv_only'];						// get actv_only

	$db_name = get_database_name($conn);

//	$date = date('l jS \of F Y g:i:s');
	if(!$excel_export)
	{
		print_header("Teaching by Degree Programme");
		programme_query_form();
		print "<HR>";
	}
//	build query part II - build the query using the input
//	if(!$query AND ($department_code OR $forename_q OR $forename_q OR $webauth_q OR $student_code_q))
	if(!$query)
	{
		$query = "
			SELECT DISTINCT
			dp.id AS 'DP_ID',";
//	Show a department column only when no department was selected in for the query
		if(strlen($department_code)<4) $query = $query."
			CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',";
		$query = $query."
			dp.degree_programme_code AS 'Code',
			CONCAT('<A HREF=$this_page?dp_id=', dp.id, '&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme'
			
			FROM DegreeProgramme dp 
			INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
			INNER JOIN Department d ON d.id = dpd.department_id 
			
			WHERE dp.degree_programme_code LIKE '%$dp_code_q%'
			AND dp.title LIKE '%$dp_title_q%'
			";
			if($department_code) $query = $query."AND d.department_code LIKE '$department_code%'";
			$query = $query."ORDER BY d.department_code, dp.degree_programme_code";
	}
if($_POST['debug']) dprint($query);
	$table = get_data($query);
	$new_table = array();
	
	$sum_dept_norm = 0;
	$sum_dept_stint = 0;
	
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
//		$dp_id = $row['DP_ID'];;
		$dp_id = array_shift($row);

//	if an academic year was selected do some more...
		if($ay_id > 0) 
		{
		}			

//		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		$new_table[] = $row;
	}		

	$table_width = array('Code' => 100, 'Degree Programme' => 600);
	print_table($new_table, $table_width, FALSE);
}

//--------------------------------------------------------------------------------------------------------------
function show_programme_details()
{
	if(!$_POST['show_programme_units']) $_POST['show_programme_units'] = 1;

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Degree Programme Details";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];
	
	$query = $_POST['query'];								// get query
	$query = stripslashes($query);

	$show_programme_teaching = $_POST['show_programme_teaching'];
	
//	print the header and stuff
	print_header("Degree Programme Details");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: $academic_year";
//	print_reset_button(this_page());
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$excel_export)
		programme_switchboard($dp_id);


//	get the degree programme record for a given  ID
	if(!$query)
		$query = "
			SELECT 
			dp.* 
		
			FROM DegreeProgramme dp

			WHERE dp.id = $dp_id
			";
			
	$result = get_data($query);
	$dp_data = $result[0];
	
	show_programme_title($dp_data);

	if(current_user_is_in_DAISY_user_group("Overseer"))
	{
		show_programme_owners($dp_data['id']);
		show_programme_enrolment_by_year($dp_data['id']);
	} 
	
	if($_POST['show_programme_units']) show_programme_units_by_year($dp_data['id']);
}

//--------------------------------------------------------------------------------------------------------------
function show_programme_specs($dp_data)
//	print details for a given degree programme record
{
	$given_ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

//	print "<H3>Degree Programme Details</H3>";
	print "<H3>".$dp_data['title']."</H3>";
	print "<TABLE BORDER=0>";
	
//	print "<TR>";
//	print 	"<TD WIDTH=120><B>Title:</B> </TD>";
//	print 	"<TD>".$dp_data['title']."</TD>";
//	print "</TR>";
	print "<TR>";
	print 	"<TD WIDTH=120><B>Code:</B> </TD>";
	print 	"<TD>".$dp_data['degree_programme_code']."</TD>";
	print "</TR>";

	if(current_user_is_in_DAISY_user_group("Overseer"))
	{
		print "<TR>";
		print 	"<TD WIDTH=120><B>ID:</B> </TD>";
		print 	"<TD>".$dp_data['id']."</TD>";
		print "</TR>";
	}

	print "</TABLE>";
	
//	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function show_programme_title($dp_data)
//	print the title of a given degree programme record
{
	$given_ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	print "<H3>".$dp_data['title']." (".$dp_data['degree_programme_code'].")</H3>";
	
	if(current_user_is_in_DAISY_user_group("Overseer"))
	{
		print "<TABLE BORDER=0>";
		print "<TR>";
		print 	"<TD WIDTH=120><B><FONT COLOR=#FF6600>ID:</FONT></B> </TD>";
		print 	"<TD><FONT COLOR=#FF6600>".$dp_data['id']."</FONT></TD>";
		print "</TR>";
		print "</TABLE>";
	}
}

//--------------------------------------------------------------------------------------------------
function show_programme_owners($dp_id)
//	Show owners of a degree programme and their ownership percentage
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$title_printed = FALSE;
	
//	define column width in table
	$table_width = array('Department' => 300, '% Year 1' => 100, '% Year 2' => 100, '% Year 3' => 100, '% Year 4' => 100, '% Year 5' => 100, '% Year 6' => 100, '% Year 7' => 100, '% Year 8' => 100, '% Year 9' => 100, '% Year 10' => 100);

//	get the owner departments of the programme
	$query = "
		SELECT DISTINCT
		d.id AS 'D_ID',
		d.department_name AS 'Department'
				
		FROM DegreeProgrammeDepartment dpd
		INNER JOIN Department d ON d.id = dpd.department_id
		
		WHERE dpd.degree_programme_id = $dp_id ";
		if($ay_id > 0) $query = $query."AND dpd.academic_year_id = $ay_id ";
		$query = $query."
		
		ORDER BY d.department_name
		";
	$table = get_data($query);

//	get the possible years of programme
	$query = "
		SELECT DISTINCT year_of_programme 
		FROM DegreeProgrammeDepartment 
		WHERE degree_programme_id = $dp_id ";
		if($ay_id > 0) $query = $query."AND academic_year_id = $ay_id ";

	$yops = get_data($query);

	if($table) foreach($table as $row)
	{
		$d_id = array_shift($row);
//	get the percentage of ownership per year of programme for a given academic year
		if($ay_id AND $yops) foreach($yops AS $yop)
		{
			$year_of_programme = $yop['year_of_programme'];
			$query = "
				SELECT dpd.percentage
				FROM DegreeProgrammeDepartment dpd 
				WHERE dpd.degree_programme_id = $dp_id 
				AND dpd.department_id = $d_id 
				AND dpd.year_of_programme = $year_of_programme 
				AND dpd.academic_year_id = $ay_id
			";
			$result = get_data($query);
			$item = $result[0];
			$perc = $item['percentage'];
			$row["% Year $year_of_programme"] = $perc;
		}

		$new_table[] = $row;
	} else $new_table = $table;



	if($new_table)
	{
		print "<H4><FONT COLOR=#FF6600>Ownership</FONT></H4>";
		print_table($new_table, $table_width, 2);
	} else
		print "No ownership found!<P>";
	print "<HR COLOR=#FF6600>";
}

//--------------------------------------------------------------------------------------------------
function show_enrolled_students_by_year($dp_id)
//	Show the enrolment of students
{
	$given_ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];			// get department code

	$this_page = this_page();
	$title_printed = FALSE;

//	define column width in table
	$table_width = array('Status' => 50, 'Department' => 300, 'Staff Class.' => 350);

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
/*
		$query = "
			SELECT DISTINCT
			CONCAT(st.surname, ', ', st.forename) AS 'Student', 
			CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
			sdp.year_of_student AS 'Year',
			sdp.status AS 'Status',
			CONCAT('<A HREF=$this_page?au_id=',au.id,'&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'AU'

  			FROM StudentDegreeProgramme sdp
  			INNER JOIN Student st ON st.id = sdp.student_id
  			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
  			INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
  			INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
  			
			WHERE sdp.academic_year_id = $ay_id
			AND sdp.degree_programme_id = $dp_id
			AND (sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED')
			
			ORDER BY
			au.title, dp.title, st.surname, st.forename
		";
*/
		$query = "
			SELECT DISTINCT
			sdp.year_of_student AS 'Stud. Year',
			CONCAT(st.surname, ', ', st.forename) AS 'Student', 
#			CONCAT('<A HREF=$this_page?dp_id=',dp.id,'&ay_id=$ay_id&department_code=$department_code>',dp.title,'</A>') AS 'Degree Programme',
			sdp.status AS 'Status'

  			FROM StudentDegreeProgramme sdp
  			INNER JOIN Student st ON st.id = sdp.student_id
  			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
  			INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
  			
			WHERE sdp.academic_year_id = $ay_id
			AND sdp.degree_programme_id = $dp_id
			AND (sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED')
			
			ORDER BY sdp.year_of_student, st.surname, st.forename
		";
//dprint($query);
		$table = get_data($query);
		
		if($table)
		{
			if(!$title_printed)
			{
				print "<H3>Enrolled Student List</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($table, $table_width, 1);
		}
	}
}

//--------------------------------------------------------------------------------------------------
function show_programme_enrolment_by_year($dp_id)
//	Show the enrolment of students
{
	$given_ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();
	$title_printed = FALSE;
	
	
//	define column width in table
	$table_width = array('Academic Year' => 300, 'Stud. Year 1' => 100, 'Stud. Year 1' => 100, 'Stud. Year 2' => 100, 'Stud. Year 3' => 100, 'Stud. Year 4' => 100, 'Stud. Year 5' => 100, 'Stud. Year 6' => 100, 'Stud. Year 7' => 100, 'Stud. Year 8' => 100, 'Stud. Year 9' => 100);

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

//	get the max year of student for any student enrolled or completed for the given degree programme
	$query = "
		SELECT
		MAX(year_of_student) AS 'MAX_YOS'

		FROM StudentDegreeProgramme
		WHERE degree_programme_id = $dp_id
		AND (status = 'ENROLLED' OR status = 'INTERMIT' OR status = 'INACTIVE' OR status = 'COMPLETED')
	";
	$result = get_data($query);
	$item = $result[0];
	$max_yos = $item['MAX_YOS'];
		
	$row = array();
	$table = array();
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		if(has_enrolled_students($ay_id, $dp_id))
		{
			$row['Academic Year'] = $ac_year['label'];

			for ($yos = 1; $yos <= $max_yos; $yos++) 
			{
   				 $query = "
   				 	SELECT
   				 	COUNT(DISTINCT sdp.student_id) AS 'Students'
   			 	
  					FROM StudentDegreeProgramme sdp
  					INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
 					WHERE sdp.academic_year_id = $ay_id
					AND sdp.degree_programme_id = $dp_id
					AND (sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED')
					AND sdp.year_of_student = $yos
  				 ";
				$result = get_data($query);
				$item = $result[0];
				$row["Stud. Year $yos"] = $item['Students'];
			}
			$table[] = $row;
		}
	}
	

	if($table)
	{
		print "<H3><FONT COLOR=#FF6600>Enrolled Students</FONT></H3>";
		print_table($table, $table_width, 2);
	} else
		print "No enrolment found!<P>";
}

//--------------------------------------------------------------------------------------------------
function show_programme_units_by_year($dp_id)
//	Show Assessment Units that are related to a given Degree Programme
{
	$given_ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$this_page = this_page();
	$title_printed = FALSE;
	
//	define column width in table
//	$table_width = array('Assessment Unit' => 500, 'Department' =>300);
	$table_width = array();

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

//	select the assessment units of the given degree programme
		$query = "
			SELECT 
			au.id AS 'AU_ID',
			d.department_name AS 'Department',
			au.assessment_unit_code AS 'Code',
			CONCAT('<A HREF=$this_page?au_id=',au.id,'&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit',
			audp.core_option AS 'Core / Option',
			audp.unit_type AS 'Unit Type'

			FROM AssessmentUnit au
			INNER JOIN Department d ON d.id = au.department_id
			INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id
		
			WHERE audp.academic_year_id = $ay_id
			AND audp.degree_programme_id = $dp_id

			ORDER BY audp.core_option, au.assessment_unit_code
		";

		if($_POST['show_components']) $query = "
			SELECT 
			au.id AS 'AU_ID',
			tc.id AS 'TC_ID',
			d.department_name AS 'Department',
			au.assessment_unit_code AS 'Code',
			CONCAT('<A HREF=$this_page?au_id=',au.id,'&ay_id=$ay_id&department_code=$department_code>',au.title,'</A>') AS 'Assessment Unit',
			audp.core_option AS 'Core / Option',
			audp.unit_type AS 'Unit Type',
			tc.subject AS 'Component',
			tct.title AS 'Type',
			tc.sessions_planned AS 'Norm Session'

			FROM AssessmentUnit au
			INNER JOIN Department d ON d.id = au.department_id
			INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id
			LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = audp.academic_year_id
			LEFT JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
			LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		
			WHERE audp.academic_year_id = $ay_id
			AND audp.degree_programme_id = $dp_id

			ORDER BY audp.core_option, au.assessment_unit_code
		";

		$table = get_data($query);

//	for each assessment unit amend the data
		$new_table = array();
		if($table) foreach($table AS $row)
		{
			$au_id = $row['AU_ID'];
			$au_id = array_shift($row);
			if($_POST['show_components']) $tc_id = $row['TC_ID'];
			if($_POST['show_components']) $tc_id = array_shift($row);

//	get the number of enrolled students of the given degree programme enrolled to each assessment unit in a given academic year	
//			if($_POST['show_students'])
			if (1==1)
			{
				$query = "
					SELECT
					COUNT(*) AS 'Students'
				
					FROM StudentDegreeProgramme sdp
					INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
				
					WHERE sdp.academic_year_id = $ay_id
					AND sdp.degree_programme_id = $dp_id
					AND (sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED')
					AND sau.assessment_unit_id = $au_id
				";
				$result = get_data($query);
				$item = $result[0];
				$prog_students = $item['Students'];
//				$row['Prog. Students'] = $prog_students;
			
//	get the number of enrolled students of ALL degree programmes enrolled to each assessment unit in a given academic year	
				$query = "
					SELECT
					COUNT(*) AS 'Students'
					
					FROM StudentAssessmentUnit 
					
					WHERE academic_year_id = $ay_id
					AND assessment_unit_id = $au_id
				";
				$result = get_data($query);
				$item = $result[0];
				$all_students = $item['Students'];
				$row['Prog. Students'] = $prog_students.'<FONT COLOR=GREY> of '.$all_students.'</FONT>';
/*

//	get the total stint value used by an assessment unit in a given academic year
				$query = "
					SELECT
					FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

					FROM TeachingInstance ti
					INNER JOIN Term t ON t.id = ti.term_id
					INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
					INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
					INNER JOIN TeachingComponentType tct ON tct.id =  tcau.teaching_component_type_id
					INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id
	
					WHERE t.academic_year_id = $ay_id
					AND tcau.assessment_unit_id = $au_id
				";
				$result = get_data($query);
				$item = $result[0];
				$stint_total = $item['Stint'];
//				$row['Stint Total'] = $stint_total;
//	calculate the stint percentage that goes to the given degree programme
				if($all_students)
				{
					$row['Prog. %'] = number_format($prog_students / $all_students * 100,2);
					$row['Prog. Stint'] = number_format($stint_total / $all_students * $prog_students,2);
				} else
				{
					$row['Prog. %'] = number_format(0,2);
					$row['Prog. Stint'] = number_format(0,2);
				}
*/
			} 


//	get the related teaching instances for each teaching component
			if($_POST['show_instances'])
			{
				$components = array();
				$query ="
					SELECT DISTINCT
					tc.sessions_planned * tctt.hours AS 'norm_hours',
					ti.sessions,
					e.fullname,
					t.term_code,
					ti.session_dates,
					loc.location_name,
					FORMAT(ti.sessions * ti.percentage / 100 * tctt.stint,2) as 'stint',
					FORMAT(ti.sessions * ti.percentage / 100 * tctt.hours,2) as 'hours'
					
					FROM TeachingInstance ti
					INNER JOIN Term t ON t.id =ti.term_id
					INNER JOIN Employee e ON e.id = ti.employee_id
					INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
					INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
					INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
					INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id
					LEFT JOIN Location loc ON loc.id = ti.location_id
					
					WHERE t.academic_year_id = $ay_id
					AND ti.teaching_component_id = $tc_id
				";
			
				if($tc_id) $components = get_data($query);
				if($components) foreach($components AS $component)
				{
					$row['Norm Hours'] = $component['norm_hours'];
					$row['Given Sessions'] = $component['sessions'];
					$row['Lecturer'] = $component['fullname'];
					$row['Term'] = $component['term_code'];
					$row['Session Dates'] = $component['session_dates'];
					$row['Location'] = $component['location_name'];
					$row['Stint'] = $component['stint'];
					$row['Hours'] = $component['hours'];

//	display the percentage and the stint share for the given degree programme
//					if($_POST['show_students'])
					if (1==1)
					{
						if($all_students)
						{
							$row['Prog. %'] = number_format($prog_students / $all_students * 100,2);
							$row['Prog. Stint'] = number_format($row['Stint'] / $all_students * $prog_students,2);
						} else
						{
							$row['Prog. %'] = number_format(0,2);
							$row['Prog. Stint'] = number_format(0,2);
						}
					} 
					$new_table[] = $row;
				} else 	// no components found - fill in empty fields
				{
					$row['Norm Hours'] = '';
					$row['Given Sessions'] = '';
					$row['Lecturer'] = '';
					$row['Term'] = '';
					$row['Session Dates'] = '';
					$row['Location'] = '';
					$row['Stint'] = '';
					$row['Hours'] = '';
			
//					if($_POST['show_students'])
					if(1==1)
					{
						$row['Prog. %'] = '';
						$row['Prog. Stint'] = '';
					}
					$new_table[] = $row;
				}
			} else 
			{
//				if($_POST['show_students'])
				if(1==1)
				{
/*
//	get the number of enrolled students of the given degree programme enrolled to each assessment unit in a given academic year	
					$query = "
						SELECT
						COUNT(*) AS 'Students'
				
						FROM StudentDegreeProgramme sdp
						INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
				
						WHERE sdp.academic_year_id = $ay_id
						AND sdp.degree_programme_id = $dp_id
						AND (sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT' OR sdp.status = 'INACTIVE' OR sdp.status = 'COMPLETED')
						AND sau.assessment_unit_id = $au_id
					";
					$result = get_data($query);
					$item = $result[0];
					$prog_students = $item['Students'];
//					$row['Prog. Students'] = $prog_students;
			
//	get the number of enrolled students of ALL degree programmes enrolled to each assessment unit in a given academic year	
					$query = "
						SELECT
						COUNT(*) AS 'Students'
					
						FROM StudentAssessmentUnit 
					
						WHERE academic_year_id = $ay_id
						AND assessment_unit_id = $au_id
					";
					$result = get_data($query);
					$item = $result[0];
					$all_students = $item['Students'];
*/
//	get the total stint value used by an assessment unit in a given academic year
					$query = "
						SELECT
						FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

						FROM TeachingInstance ti
						INNER JOIN Term t ON t.id = ti.term_id
						INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
						INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
						INNER JOIN TeachingComponentType tct ON tct.id =  tcau.teaching_component_type_id
						INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id
	
						WHERE t.academic_year_id = $ay_id
						AND tcau.assessment_unit_id = $au_id
					";

//	if showing cmponents get the total stint value used by a teaching component in a given academic year
					if($_POST['show_components'] AND $tc_id) $query = "
						SELECT
						FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

						FROM TeachingInstance ti
						INNER JOIN Term t ON t.id = ti.term_id
						INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
						INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
						INNER JOIN TeachingComponentType tct ON tct.id =  tcau.teaching_component_type_id
						INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id
	
						WHERE t.academic_year_id = $ay_id
						AND tcau.teaching_component_id = $tc_id
					";

					$result = get_data($query);
					$item = $result[0];
					$stint_total = $item['Stint'];
//					$row['Stint Total'] = $stint_total;
					$row['Prog. Students'] = $prog_students.'<FONT COLOR=GREY> of '.$all_students.'</FONT>';
					if($prog_students AND $all_students)
					{
						$row['Stint'] = number_format($stint_total,2);
						$row['Prog. %'] = number_format($prog_students / $all_students * 100,2);
						$row['Prog. Stint'] = number_format($stint_total / $all_students * $prog_students,2);
					} else
					{
						$row['Stint'] = number_format($stint_total,2);
						$row['Prog. %'] = number_format(0,2);
						$row['Prog. Stint'] = number_format(0,2);
					}
				} 

				$new_table[] = $row;
			}

		}

		if($new_table)
		{
			if(!$title_printed)
			{
				print "<HR><H3>Assessment Units</H3>";
				$title_printed = TRUE;
			}
			if($given_ay_id == -1) print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, 0);
		} else print "Query returned no results!";
	}
}

//--------------------------------------------------------------------------------------------------
function show_prog_teaching_details_by_year($dp_id, $given_ay_id)
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

//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function count_dp_students($dp_id, $ay_id)
// returns number of students enrolled to an degree programme in a given year
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

//----------------------------------------------------------------------------------------
function get_prog_teaching_stint($dp_id, $ay_id)
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

//==================================< The Buttons >=======================================

//--------------------------------------------------------------------------------------------------------------
function programme_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$dp_id = $_GET["dp_id"];								// get programme ID dp_id
	if(!$dp_id) $dp_id = $_POST['dp_id'];
	$_POST['dp_id'] = $dp_id;
	
	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT BGCOLOR=LIGHTBLUE>".reload_ay_button()."</TD>";

	print "<TD WIDTH=200 ALIGN=CENTER>".programme_units_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=CENTER>".show_components_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=CENTER>".show_instances_button()."</TD>";
	if($_POST['show_programme_units' == 1]) print "<TD WIDTH=200 ALIGN=CENTER>".show_students_button()."</TD>";

	print "<TD WIDTH=30></TD>";
	print "<TD ALIGN=LEFT BGCOLOR=LIGHTGREEN>".export_button()."</TD>";
	print "<TD ALIGN=LEFT BGCOLOR=PINK>".new_query_button()."</TD>";

	print "</TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function programme_units_button()
//	display a button to display/hide teaching information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['dp_id']) $_POST['dp_id'] = $_GET['dp_id'];	// get programme ID dp_id
	$dp_id = $_POST['dp_id'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
//	$html = "<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if (programme_has_unit($dp_id, $ay_id)) 
	{
		if($_POST['show_programme_units'])
		{
			$html = $html."<input type='hidden' name='show_programme_units' value=0>";
			$html = $html."<input type='hidden' name='show_components' value=0>";
			$html = $html."<input type='hidden' name='show_instances' value=0>";
			$html = $html."<input type='submit' value='Hide Assessment Units'>";
		} else
		{
			$html = $html."<input type='hidden' name='show_programme_units' value=1>";
			$html = $html."<input type='submit' value='Show Assessment Units'>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_programme_units' value=0>";
		$html = $html."<input type='submit' value='NO Assessment Units'>";
	}
	$html = $html."</FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function show_components_button()
//	display a button to display/hide component information
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
//	$html = "<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if($_POST['show_components'])
	{
		$html = $html."<input type='hidden' name='show_components' value=0>";
		$html = $html."<input type='hidden' name='show_instances' value=0>";
		$html = $html."<input type='submit' value='Hide Components'>";
	} else
	{
		$html = $html."<input type='hidden' name='show_units' value=1>";
		$html = $html."<input type='hidden' name='show_components' value=1>";
		$html = $html."<input type='submit' value='Show Components'>";
	}
	$html = $html."</FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function show_instances_button()
//	display a button to display/hide student information to reduce calculation time
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
//	$html = "<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if($_POST['show_instances'])
	{
		$html = $html."<input type='hidden' name='show_instances' value=0>";
		$html = $html."<input type='submit' value='Hide Instances'>";
	} else
	{
		$html = $html."<input type='hidden' name='show_units' value=1>";
		$html = $html."<input type='hidden' name='show_components' value=1>";
		$html = $html."<input type='hidden' name='show_instances' value=1>";
		$html = $html."<input type='submit' value='Show Instances'>";
	}
	$html = $html."</FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function show_students_button()
//	display a button to display/hide student information to reduce calculation time
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['dp_id']) $_POST['dp_id'] = $_GET['dp_id'];	// get programme ID dp_id
	$dp_id = $_POST['dp_id'];

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
//	$html = "<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if (programme_has_students($dp_id, $ay_id)) 
	{
		if($_POST['show_students'])
		{
			$html = $html."<input type='hidden' name='show_students' value=0>";
			$html = $html."<input type='submit' value='Hide Student Figures'>";
		} else
		{
			$html = $html."<input type='hidden' name='show_students' value=1>";
			$html = $html."<input type='submit' value='Show Student Figures'>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_students' value=0>";
		$html = $html."<input type='submit' value='NO Student Figures'>";
	}
	$html = $html."</FORM>";
	return $html;
}

//===================================< Attributes >=======================================

//--------------------------------------------------------------------------------------------------
function has_enrolled_students($ay_id, $dp_id)
//	returns TRUE if any enrolled student is given for a degree programme in a given year
{
	$query = "
		SELECT *
  		FROM StudentDegreeProgramme
		WHERE academic_year_id = $ay_id
		AND degree_programme_id = $dp_id
		AND (status = 'ENROLLED' OR status = 'INTERMIT' OR status = 'INACTIVE' OR status = 'COMPLETED')
	";
	if(get_data($query)) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function programme_has_unit($dp_id, $ay_id)
//	checks if  a given Degree Programme ID has some relation to an Assessment Unit at all (for a given academic year)
{
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

//--------------------------------------------------------------------------------------------------
function programme_has_students($dp_id, $ay_id)
//	checks if  a given Degree Programme ID has enrolled students for a given academic year
{
	if($ay_id > 0) $query = "
		SELECT * 
		FROM StudentDegreeProgramme sdp
		
		WHERE sdp.degree_programme_id = $dp_id 
		AND sdp.academic_year_id = $ay_id
		";

	else $query = "
		SELECT * 
		FROM StudentDegreeProgramme sdp
		
		WHERE sdp.degree_programme_id = $dp_id 
		";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

?>