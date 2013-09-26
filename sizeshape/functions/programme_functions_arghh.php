<?php

//==================================================================================================
//
//	Separate file with teaching report functions
//	Last changes: Matthias Opitz --- 2013-05-09
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_programme_query()
{
	if(!$_POST['excel_export'])
	{	
		programme_query_form();
		if(!$_POST['query_type'])
		{
			print_programme_intro();
			print_programme_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function programme_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];							// get academic_year_id
	$department_code = $_POST['department_code'];					// get department_code

	$course_title_q = $_POST['course_title_q'];					// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='programme'>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print start_row(0) . "Degree Programme Title:" . new_column(0) . "<input type='text' name = 'dp_title_q' value='".$_POST['dp_title_q']."' size=50>" . end_row();		
	print start_row(0) . "Degree Programme:" . new_column(0) . degree_programme_options() . end_row();		
//	print start_row(0) . "Report Type:" . new_column(0) . programme_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";
//	display the option checkboxes
	print "<TABLE BORDER=0>";

//	print start_row(500);		// 1st row
	print "<TR><TD WIDTH=500 COLSPAN=3>";		// 1st row
		 print "Exclude Degree Programmes and Assessment Units without teaching:";
	print new_column(60);
		print html_checkbox('exclude_no_teaching');
/*

	print new_column(380);
		 print "Exclude Assessment Units without teaching:";
	print new_column(60);
		print html_checkbox('exclude_no_teaching');

	print new_column(190);
		print "Show Teaching Details:";
	print new_column(60);
		print html_checkbox('show_teaching_details');

	print new_column(150);
		print "Show Sub-Totals:";
	print new_column(60);
		print html_checkbox('show_sub_totals');
*/
//	print new_column(100);
//		print "Hide Hours:";
//	print new_column(60);
//		print html_checkbox('hide_hours');

	print end_row();

	print start_row(180);		// 2nd row
//	print new_column(190);
		print "Show Teaching Details:";
	print new_column(100);
		print html_checkbox('show_teaching_details');

//	print start_row(190);		// 3rd row
	print new_column(200);
		print "Show Sub-Totals in Details:";
	print new_column(60);
		print html_checkbox('show_sub_totals');
	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function programme_report_options()
// shows the options for a ses report
{
	$query_type = $_POST['query_type'];					// get course_report_type
	
	$options = array();

	$options[] = array('option 1', 'aaa');
	$options[] = array('option 2', 'bbb');
	$options[] = array('option 3', 'ccc');
	$options[] = array('option 4', 'ddd');
	
	$html = "<select name='query_type'>";
	foreach($options AS $option)
	{
		$option_label = $option[0];
		$option_value = $option[1];
		if($option_value==$query_type) $html = $html."<option VALUE='$option_value' SELECTED='selected'>$option_label</option>";
		else $html = $html."<option VALUE='$option_value'>$option_label</option>";
	}
	$html = $html."</select>";

	return $html;
}

///--------------------------------------------------------------------------------------------------------------
function print_programme_intro()
{
	$text = "<B>This report shows teaching provision for each Assessment Unit (AU) related to a Degree Programme owned by your department <BR />in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.</B><BR />
	<P>	
	Just click on 'Go!' to get a summary list of all Assessment Units related to Degree Programmes owned by the department.<BR />";
	if (current_user_is_in_DAISY_user_group('Super-Administrator') OR current_user_is_in_DAISY_user_group('Divisional-Reporter')) $text = $text . "<FONT COLOR=#FF6600><B>Please note:</B></FONT> running the report <B>for a whole division</B> may take  <B>five(5!) minutes or more</B>. Once started please be patient and do not reload the page.
";
	else $text = $text . "<FONT COLOR=#FF6600><B>Please note:</B></FONT> the report will may run for <B>one minute or more</B>. Once started please be patient and do not reload the page.
";
	$text = $text . "
	<P>
	The report presents the information in two stages:
	<P>
	1.	Summary information<BR />
	A list of all AUs owned by the department according to the search criteria specified (please see help below for details).

	<P>	
	2.	Further details<BR />
	If you select the 'Show Details' option you will get a detailed list of Assessment Units and related Teaching Components and Instances.<BR />
	You may click on the title of a listed Assessment Unit to open a single detail report on that Assessment Unit. 
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_programme_help()
{
	print "<HR>";
	print "<H4><FONT FACE='Arial' COLOR=DARKBLUE>Help</FONT></H4>";
	print "<TABLE>";

		print "<TR>";
			print "<TD WIDTH=250>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Academic Year</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>The current Academic Year is pre-selected; you can select another by clicking on it.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Degree Programme Title</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>You can enter a (part of a) Degree Programme title to narrow the results.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Exclude Assessment Units without teaching</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>When checked all Assessment Units that have no related teaching for the selected academic year will be left out of the report.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Show Teaching Details</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>Check this if you want detailed teaching information in the reports.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Show Sub-Totals in Details</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>If you check this a sub total will be shown for each Assessment Unit with more than one Teaching Instance.<BR />
				<B>Please note</B> that this report is only effective when the 'Show Teaching Details' option is checked.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT FACE='Arial' COLOR=DARKBLUE>Please note</FONT><FONT FACE='Arial'>: You can combine selection criteria</I></FONT>";
}

//======================< Programme Report >========================
//--------------------------------------------------------------------------------------------------------------
function programme_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = dp_au_list();
	if($_POST['show_teaching_details'])
	{
		$table = amend_programme_details($table);
		if ($_POST['show_sub_totals']) 
		{
			$table = clean_repeating_programme_rows($table);
			$table = insert_sub_totals($table);
			$table = insert_totals($table);
		}
	} else
	{
		$table = amend_programme_summary($table);
		$table = insert_totals($table);
	}
//	$table = amend_students_per_au($table);


	$table = cleanup($table,2);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function dp_au_list()
//	get all assessment units of - possibly - all degree programmes of a given department and academic year
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$dp_title_q = $_POST['dp_title_q'];						// get au_title_q
	$dp_id = $_POST['dp_id'];								// get dp_id

//	get the degree programmes

	$query = "
		SELECT DISTINCT
		dp.id AS 'DP_ID',
		'' AS 'AU_ID',
		dp.degree_programme_code AS 'DP Code',
		CONCAT('',dp.title,'') AS 'Degree Programme' 		

		FROM DegreeProgramme dp 
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
		INNER JOIN Department d ON d.id = dpd.department_id  
		
		WHERE 1=1
		AND dpd.academic_year_id = $ay_id
		AND dp.degree_programme_code LIKE '%%'
		AND dp.title LIKE '%$dp_title_q%' ";
		if($dp_id) $query = $query . "AND dp.id = $dp_id";
		$query = $query . "
		AND d.department_code LIKE '$department_code%' 

		GROUP BY d.department_code, dp.degree_programme_code 
		ORDER BY d.department_code, dp.degree_programme_code	
	";

//d_print($query);

	$table = array();
	$programmes = get_data($query);

	if($programmes) foreach($programmes AS $row)
	{
		$dp_id = $row['DP_ID'];
	
		$units = au_per_dp($ay_id, $dp_id);
		
		
		if($units) foreach($units AS $unit)
		{
			$row['AU_ID'] = $unit['AU_ID'];
			$row['AU provided by'] = $unit['Department'];
			$row['AU Code'] = $unit['AU Code'];
			$row['Assessment Unit / PGR Module'] = $unit['Assessment Unit'];
			$row['Core / Option'] = $unit['Core / Option'];
			$row['Unit Type'] = $unit['Unit Type'];

			$row['Prog. (all) AU Students'] = '';
//			$row['Prog. AU Students'] = '';
//			$row['All AU Students'] = '';
//			$row['DP %'] = '';
//			$row['DP Dept Norm Stint'] = '';
//			$row['DP Student Actual Stint'] = '';



			$table[] = $row;
		}
/*
		else // empty fields
		{
			if(!$_POST['exclude_no_teaching'])
			{
				$row['AU_ID'] = '';
				$row['AU provided by'] = '';
				$row['AU Code'] = 'no';
				$row['Assessment Unit / PGR Module'] = '';
				$row['Core / Option'] = '';
				$row['Unit Type'] = '';

				$table[] = $row;
			}
		}
*/
	}
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function au_per_dp($ay_id, $dp_id)
//	returns all assessment_units related to the given degree programme in the given academic year
{
	$query ="
		SELECT  DISTINCT
		au.id AS 'AU_ID',
		d.department_name AS 'Department',
		au.assessment_unit_code AS 'AU Code',
		CONCAT('',au.title,'') AS 'Assessment Unit',
		audp.core_option AS 'Core / Option',
		audp.unit_type AS 'Unit Type'

		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id
		INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id";

	if($_POST['exclude_no_teaching']) $query = $query."
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id 
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
		INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id 
	";
	if($_POST['hide_unrelated']) $query = $query."INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id ";

	$query = $query."

		WHERE audp.academic_year_id = $ay_id
		AND audp.degree_programme_id = $dp_id

		ORDER BY audp.core_option, au.assessment_unit_code
	";
//d_print($query);
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function amend_programme_details($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$dp_id = $row['DP_ID'];
		$au_id = $row['AU_ID'];
		
//	change assessment unit title into link
		$row['Assessment Unit / PGR Module'] = au_link($au_id, $row['Assessment Unit / PGR Module'] );

//	get assessment unit stint and hours
		if($au_id > 0)
		{
//			$row['Norm AU Stint'] =  number_format(au_norm_stint_share($ay_id, $au_id),2);
//			$row['Student Actual  AU Stint'] =  number_format(au_stint($ay_id, $au_id),2);
//			$row['Student Actual  AU Stint'] =  number_format(au_stint_share($ay_id, $au_id),2);
//			$row['Norm AU Hours old'] =  number_format(au_norm_hours($ay_id, $au_id),2);
			$hours = get_au_hours($ay_id, $dp_id, $au_id);
			$row['Student Norm AU Hours'] =  number_format($hours['stud_norm'],2);
			$row['Dept Norm AU Hours'] =  number_format($hours['dept_norm'],2);
			$row['Student Actual AU Hours'] =  number_format($hours['stud_actual'],2);
//			$row['Dept Actual AU Hours'] =  number_format($hours['dept_actual'],2);

			$stint = get_au_stint($ay_id, $dp_id, $au_id);
			$row['Student Norm AU Stint'] =  number_format($stint['stud_norm'],2);
			$row['Dept Norm AU Stint'] =  number_format($stint['dept_norm'],2);
//			$row['Student Actual  AU Stint'] =  number_format($stint['stud_actual'],2);
			$row['Dept Actual  AU Stint'] =  number_format($stint['dept_actual'],2);

		}
		else
		{
//			$row['Norm AU Stint'] =  number_format(0,2);
//			$row['Student Actual  AU Stint'] =  number_format(0,2);
//			$row['Actual AU Stint Share'] =  number_format(0,2);
			$row['Student Norm AU Hours'] =  number_format(0,2);
			$row['Dept Norm AU Hours'] =  number_format(0,2);
			$row['Student Actual AU Hours'] =  number_format(0,2);
//			$row['Dept Actual AU Hours'] =  number_format(0,2);

			$row['Student Norm AU Stint'] =  number_format(0,2);
			$row['Dept Norm AU Stint'] =  number_format(0,2);
//			$row['Student Actual  AU Stint'] =  number_format(0,2);
			$row['Dept Actual  AU Stint'] =  number_format(0,2);
		}
	
//	get degree programme students
		if($au_id > 0)
		{
			$au_students =  students_per_au($ay_id, $au_id);						// get the number of allstudents enrolled into a given AU
			$au_dp_students = count_prog_au_students($ay_id, $au_id, $dp_id);		// get the number of students enrolled into a given AU and DP
		}
		else
		{
			$au_students =  0;
			$au_dp_students = 0;
		}
//		if($_POST['excel_export']) $row['Prog. Students'] = "$au_dp_students of $au_students";
//		else $row['Prog. Students'] = "$au_dp_students <FONT COLOR = GREY>of $au_students</FONT>";
//		$row['All AU Students'] = $au_students;
//		$row['Prog. AU Students'] = $au_dp_students;
		$row['Prog. (all) AU Students'] = "$au_dp_students ($au_students)";
	
//	amend Degree Programme percentage
		if($au_dp_students > 0) 
		{
			$row['DP %'] = number_format($au_dp_students / $au_students * 100, 2);
//			$row['DP Dept Norm Stint'] = number_format($au_dp_students / $au_students * au_norm_stint_share($ay_id, $au_id), 2);
//			$row['DP Student Actual Stint'] = number_format($au_dp_students / $au_students * au_stint_share($ay_id, $au_id), 2);
			$row['DP Dept Norm Stint'] = number_format($au_dp_students / $au_students * $row['Dept Norm AU Stint'], 2);
			$row['DP Student Norm Stint'] = number_format($au_dp_students / $au_students * $row['Student Norm AU Stint'], 2);
//			$row['DP Student Actual Stint'] = number_format($au_dp_students / $au_students * $row['Student Actual AU Stint'], 2);
//			$row['DP Stint Share'] = number_format($au_dp_students / $au_students * $row['Actual AU Stint Share'], 2);
		}
		else
		{
			$row['DP %'] = number_format(0, 2);
			$row['DP Dept Norm Stint'] = number_format(0, 2);
			$row['DP Student Actual Stint'] = number_format(0, 2);
//			$row['DP Stint Share'] = number_format(0, 2);
		}

//	do the teaching component details
		if($au_id > 0) $components = tc_per_au($ay_id, $au_id);
		else $components = 0;
		if ($components) foreach($components AS $component)
		{
			$tc_id = $component['id'];
			$tc_attributes = tc_attributes($ay_id, $au_id, $tc_id);
			$tc_students = count_all_tc_students($ay_id, $tc_id);
			
//			$row[''] =  $component[''];
			$row['Teaching Component name'] =  $component['subject'];
			$row['TC Type'] =  $tc_attributes['type'];
			$row['Cap.'] =  $tc_attributes['capacity'];
			$row['Norm sess'] =  $tc_attributes['sessions_planned'];
			$row['Stint/sess'] =  $tc_attributes['stint'];
//			$row['Norm Stint'] =  $tc_attributes['stint'] * $tc_attributes['sessions_planned'];
			$row['Hour/sess'] =  $tc_attributes['hours'];
//			$row['Norm Hours'] =  $tc_attributes['hours'] * $tc_attributes['sessions_planned'];
			$row['TC Students'] =  $tc_students;

//	do the teaching instance details
			$instances = array();
			$instances = ti_per_tc($ay_id, $tc_id);
			if($instances) foreach($instances AS $instance)
			{
				$ti_id = $instance['id'];
				$employee = ti_employee($ti_id);

//				$row[''] = $instance[''];
				$row['Term'] = $instance['term'];
				$row['Actual TI sess'] = $instance['sessions'];
				$row['Actual TI Stint'] = $row['Stint/sess'] * $instance['sessions'] * $instance['percentage'] / 100;
				$row['Actual TI Hours'] = $row['Hour/sess'] * $instance['sessions'] * $instance['percentage'] / 100;
				$row['Lecturer'] = $employee['fullname'];
				$row['%'] = $instance['percentage'];

				$new_table[] = $row;
			}
			else // write empty cells
			{
				$row['Term'] = '';
				$row['Actual TI sess'] = '';
				$row['Actual TI Stint'] = '';
				$row['Actual TI Hours'] = '';
				$row['Lecturer'] = '';
				$row['%'] = '';
				
				$new_table[] = $row;
			}
		}
		else // write even more empty cells
		{
			$row['Teaching Component name'] =  '';
			$row['TC Type'] =  '';
			$row['Cap.'] =  '';
			$row['Norm sess'] =  '';
			$row['Stint/sess'] =  '';
//			$row['Norm Stint'] =  '';
			$row['Hour/sess'] =  '';
//			$row['Norm Hours'] =  '';
			$row['TC Students'] =  '';

			$row['Term'] = '';
			$row['Actual TI sess'] = '';
			$row['Actual TI Stint'] = '';
			$row['Actual TI Hours'] = '';
			$row['Lecturer'] = '';
			$row['%'] = '';

			$new_table[] = $row;
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_programme_details0($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$dp_id = $row['DP_ID'];
		$au_id = $row['AU_ID'];
		
//	change assessment unit title into link
		$row['Assessment Unit / PGR Module'] = au_link($au_id, $row['Assessment Unit / PGR Module'] );

//	get assessment unit stint and hours
		if($au_id > 0)
		{
//			$row['Norm AU Stint'] = number_format(au_norm_stint_share($ay_id, $au_id),2);
//			$row['Student Actual  AU Stint'] = number_format(au_stint($ay_id, $au_id),2);
//			$row['Student Actual  AU Stint'] = number_format(au_stint_share($ay_id, $au_id),2);
//			$row['Dept Norm AU Hours'] =  number_format(au_norm_hours($ay_id, $au_id),2);
//			$row['Student Actual AU Hours'] =  number_format(au_hours($ay_id, $au_id),2);
			$hours = get_au_hours($ay_id, $dp_id, $au_id);
			$row['Student Norm AU Hours'] =  number_format($hours['stud_norm'],2);
			$row['Dept Norm AU Hours'] =  number_format($hours['dept_norm'],2);
			$row['Student Actual AU Hours'] =  number_format($hours['actual'],2);
		}
		else
		{
//			$row['Norm AU Stint'] = number_format(0,2);
//			$row['Student Actual  AU Stint'] = number_format(0,2);
//			$row['Actual AU Stint Share'] = number_format(0,2);
			$row['Student Norm AU Hours'] =  number_format(0,2);
			$row['Dept Norm AU Hours'] = number_format(0,2);
			$row['Student Actual AU Hours'] = number_format(0,2);
		}
	
//	get degree programme students
		if($au_id > 0)
		{
			$au_students =  students_per_au($ay_id, $au_id);						// get the number of allstudents enrolled into a given AU
			$au_dp_students = count_prog_au_students($ay_id, $au_id, $dp_id);		// get the number of students enrolled into a given AU and DP
		}
		else
		{
			$au_students =  0;
			$au_dp_students = 0;
		}
//		if($_POST['excel_export']) $row['Prog. Students'] = "$au_dp_students of $au_students";
//		else $row['Prog. Students'] = "$au_dp_students <FONT COLOR = GREY>of $au_students</FONT>";
//		$row['All AU Students'] = $au_students;
//		$row['Prog. AU Students'] = $au_dp_students;
		$row['Prog. (all) AU Students'] = "$au_dp_students ($au_students)";
	
//	amend Degree Programme percentage
		if($au_dp_students > 0) 
		{
			$row['DP %'] = number_format($au_dp_students / $au_students * 100, 2);
			$row['DP Dept Norm Stint'] = number_format($au_dp_students / $au_students * au_norm_stint_share($ay_id, $au_id), 2);
			$row['DP Student Actual Stint'] = number_format($au_dp_students / $au_students * au_stint_share($ay_id, $au_id), 2);
//			$row['DP Stint Share'] = number_format($au_dp_students / $au_students * $row['Actual AU Stint Share'], 2);
		}
		else
		{
			$row['DP %'] = number_format(0, 2);
			$row['DP Dept Norm Stint'] = number_format(0, 2);
			$row['DP Student Actual Stint'] = number_format(0, 2);
//			$row['DP Stint Share'] = number_format(0, 2);
		}

//	do the teaching component details
		if($au_id > 0) $components = tc_per_au($ay_id, $au_id);
		else $components = 0;
		if ($components) foreach($components AS $component)
		{
			$tc_id = $component['id'];
			$tc_attributes = tc_attributes($ay_id, $au_id, $tc_id);
			$tc_students = count_all_tc_students($ay_id, $tc_id);
			
//			$row[''] =  $component[''];
			$row['Teaching Component name'] =  $component['subject'];
			$row['TC Type'] =  $tc_attributes['type'];
			$row['Cap.'] =  $tc_attributes['capacity'];
			$row['Norm sess'] =  $tc_attributes['sessions_planned'];
			$row['Stint/sess'] =  $tc_attributes['stint'];
//			$row['Norm Stint'] =  $tc_attributes['stint'] * $tc_attributes['sessions_planned'];
			$row['Hour/sess'] =  $tc_attributes['hours'];
//			$row['Norm Hours'] =  $tc_attributes['hours'] * $tc_attributes['sessions_planned'];
			$row['TC Students'] =  $tc_students;

//	do the teaching instance details
			$instances = ti_per_tc($ay_id, $tc_id);
			if($instances) foreach($instances AS $instance)
			{
				$ti_id = $instance['id'];
				$employee = ti_employee($ti_id);

//				$row[''] = $instance[''];
				$row['Term'] = $instance['term'];
				$row['Actual TI sess'] = $instance['sessions'];
				$row['Actual TI Stint'] = $row['Stint/sess'] * $instance['sessions'] * $instance['percentage'] / 100;
				$row['Actual TI Hours'] = $row['Hour/sess'] * $instance['sessions'] * $instance['percentage'] / 100;
				$row['Lecturer'] = $employee['fullname'];
				$row['%'] = $instance['percentage'];
//				$row[''] = $instance[''];
				
				$new_table[] = $row;
			}
			else // write empty cells
			{
				$row['Term'] = '';
				$row['Actual TI sess'] = '';
				$row['Actual TI Stint'] = '';
				$row['Actual TI Hours'] = '';
				$row['Lecturer'] = '';
				$row['%'] = '';
				
				$new_table[] = $row;
			}
		}
		else // write even more empty cells
		{
			$row['Teaching Component name'] =  '';
			$row['TC Type'] =  '';
			$row['Cap.'] =  '';
			$row['Norm sess'] =  '';
			$row['Stint/sess'] =  '';
//			$row['Norm Stint'] =  '';
			$row['Hour/sess'] =  '';
//			$row['Norm Hours'] =  '';
			$row['TC Students'] =  '';

			$row['Term'] = '';
			$row['Actual TI sess'] = '';
			$row['Actual TI Stint'] = '';
			$row['Actual TI Hours'] = '';
			$row['Lecturer'] = '';
			$row['%'] = '';

			$new_table[] = $row;
		}
	}
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_programme_summary($table)
{
	$ay_id = $_POST['ay_id'];								// get academic year ID

	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$dp_id = $row['DP_ID'];
		$au_id = $row['AU_ID'];

//	change assessment unit title into link
		$row['Assessment Unit / PGR Module'] = au_link($au_id, $row['Assessment Unit / PGR Module'] );

//	get assessment unit stint and hours
		if($au_id > 0)
		{
//			$row['Norm AU Stint'] =  number_format(au_norm_stint_share($ay_id, $au_id),2);
//			$row['Student Actual  AU Stint'] =  number_format(au_stint($ay_id, $au_id),2);
//			$row['Student Actual  AU Stint'] =  number_format(au_stint_share($ay_id, $au_id),2);
//			$row['Norm AU Hours old'] =  number_format(au_norm_hours($ay_id, $au_id),2);
			$hours = get_au_hours($ay_id, $dp_id, $au_id);
			$row['Student Norm AU Hours'] =  number_format($hours['stud_norm'],2);
			$row['Dept Norm AU Hours'] =  number_format($hours['dept_norm'],2);
			$row['Student Actual AU Hours'] =  number_format($hours['stud_actual'],2);
//			$row['Dept Actual AU Hours'] =  number_format($hours['dept_actual'],2);

			$stint = get_au_stint($ay_id, $dp_id, $au_id);
			$row['Student Norm AU Stint'] =  number_format($stint['stud_norm'],2);
			$row['Dept Norm AU Stint'] =  number_format($stint['dept_norm'],2);
//			$row['Student Actual  AU Stint'] =  number_format($stint['stud_actual'],2);
			$row['Dept Actual  AU Stint'] =  number_format($stint['dept_actual'],2);

		}
		else
		{
//			$row['Norm AU Stint'] =  number_format(0,2);
//			$row['Student Actual  AU Stint'] =  number_format(0,2);
//			$row['Actual AU Stint Share'] =  number_format(0,2);
			$row['Student Norm AU Hours'] =  number_format(0,2);
			$row['Dept Norm AU Hours'] =  number_format(0,2);
			$row['Student Actual AU Hours'] =  number_format(0,2);
//			$row['Dept Actual AU Hours'] =  number_format(0,2);

			$row['Student Norm AU Stint'] =  number_format(0,2);
			$row['Dept Norm AU Stint'] =  number_format(0,2);
//			$row['Student Actual  AU Stint'] =  number_format(0,2);
			$row['Dept Actual  AU Stint'] =  number_format(0,2);
		}
	
//	get degree programme students
		if($au_id > 0)
		{
			$au_students =  students_per_au($ay_id, $au_id);			// get the number of allstudents enrolled into a given AU
			$au_dp_students = count_prog_au_students($ay_id, $au_id, $dp_id);		// get the number of students enrolled into a given AU and DP
		}
		else
		{
			$au_students =  0;
			$au_dp_students = 0;
		}
//		if($_POST['excel_export']) $row['Prog. Students'] = "$au_dp_students of $au_students";
//		else $row['Prog. Students'] = "$au_dp_students <FONT COLOR = GREY>of $au_students</FONT>";
//		$row['All AU Students'] = $au_students;
//		$row['Prog. AU Students'] = $au_dp_students;
		$row['Prog. (all) AU Students'] = "$au_dp_students ($au_students)";
	
//	amend Degree Programme percentage
		if($au_dp_students > 0) 
		{
			$row['DP %'] = number_format($au_dp_students / $au_students * 100, 2);
			$row['DP Dept Norm Stint'] = number_format($au_dp_students / $au_students * $row['Dept Norm AU Stint'], 2);
			$row['DP Student Norm Stint'] = number_format($au_dp_students / $au_students * $row['Student Norm AU Stint'], 2);
			$row['DP Student Actual Stint'] = number_format($au_dp_students / $au_students * $row['Student Actual AU Stint'], 2);
		}
		else
		{
			$row['DP %'] = number_format(0, 2);
			$row['DP Dept Norm Stint'] = number_format(0, 2);
			$row['DP Student Actual Stint'] = number_format(0, 2);
		}

/*
//	amend Degree Programme percentage
		if($au_dp_students > 0) 
		{
			$row['DP %'] = number_format($au_dp_students / $au_students * 100, 2);
			$row['DP Dept Norm Stint'] = number_format($au_dp_students / $au_students * au_norm_stint_share($ay_id, $au_id), 2);
			$row['DP Student Actual Stint'] = number_format($au_dp_students / $au_students * au_stint_share($ay_id, $au_id), 2);
//			$row['DP Stint Share'] = number_format($au_dp_students / $au_students * $row['Actual AU Stint Share'], 2);
		}
		else
		{
			$row['DP %'] = number_format(0, 2);
			$row['DP Dept Norm Stint'] = number_format(0, 2);
			$row['DP Student Actual Stint'] = number_format(0, 2);
//			$row['DP Stint Share'] = number_format(0, 2);
		}
*/
		$new_table[] = $row;		
	}
	if($new_table) return $new_table;
	else return $table;
}


//--------------------------------------------------------------------------------------------------
function count_prog_au_students($ay_id, $au_id, $dp_id)
//	get the number of  students enrolled to a given assessment unit and of a given degree programme  in a given academic year
{
	$query = "
		SELECT
		COUNT(DISTINCT sau.student_id) AS 'Students'

		FROM StudentAssessmentUnit sau
		INNER JOIN Student st ON st.id = sau.student_id
		INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = sau.academic_year_id

		WHERE sau.academic_year_id = $ay_id
		AND sau.assessment_unit_id = $au_id
		AND sdp.degree_programme_id = $dp_id
		AND sdp.status = 'ENROLLED' 
	";
//d_print($query);
	$result = get_data($query);
	$item = $result[0];
	return $item['Students'];
}

//--------------------------------------------------------------------------------------------------------------
function insert_sub_totals($table)
//	inserts a sub-total row for each assessment unit with more than one row
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$dp_id = $row['DP_ID'];
		if(!isset($prev_dp_id)) $prev_dp_id = $dp_id;
		$au_id = $row['AU_ID'];
		if(!isset($prev_au_id)) $prev_au_id = $au_id;
		
		if(!isset($prev_au_title)) $prev_au_title = $row['Assessment Unit / PGR Module'];

		if($prev_au_id > 0) 
		if($au_id != $prev_au_id AND needs_sub_totals($ay_id, $prev_au_id))
		{
			$blank_row = blank_line($row);
			$totals_row = blank_line($row);

			$totals_row['DP Code'] = "Sub-Totals ";
			$totals_row['AU Code'] = au_code($prev_au_id);
//			$totals_row['Assessment Unit / PGR Module'] = "$prev_au_title";
			$totals_row['Assessment Unit / PGR Module'] = au_title($prev_au_id);

//	amend core and type
		if( is_part_of_programme($prev_au_id, $ay_id))
		{
			if(is_core($prev_au_id, $ay_id)) $totals_row['Core / Option'] = 'Core';
			else $totals_row['Core / Option'] = 'Optional';
			if(is_pgrad($prev_au_id, $ay_id)) $totals_row['Unit Type'] = 'PGRAD';
			else $totals_row['Unit Type'] = 'UGRAD';
		} else
		{
				$totals_row['Core / Option'] = 'Optional';
				$totals_row['Unit Type'] = '';
		}

//	get assessment unit stint and hours
			$totals_row['Norm AU Stint'] = number_format(au_norm_stint_share($ay_id, $prev_au_id),2);
//			$totals_row['Actual AU Stint'] = number_format(au_stint($ay_id, $prev_au_id),2);
			$totals_row['Actual AU Stint'] = number_format(au_stint_share($ay_id, $prev_au_id),2);
//			$totals_row['Norm AU Hours'] = number_format(au_norm_hours($ay_id, $prev_au_id),2);
//			$totals_row['Actual AU Hours'] = number_format(au_hours($ay_id, $prev_au_id),2);

			$hours = get_au_hours($ay_id, $dp_id, $prev_au_id);
			$totals_row['Student Norm AU Hours'] =  number_format($hours['stud_norm'],2);
			$totals_row['Norm AU Hours'] =  number_format($hours['dept_norm'],2);
			$totals_row['Actual AU Hours'] =  number_format($hours['actual'],2);


//	get degree programme students
			if($au_id > 0)
			{
				$au_students =  students_per_au($ay_id, $prev_au_id);			// get the number of allstudents enrolled into a given AU
				$au_dp_students = count_prog_au_students($ay_id, $prev_au_id, $dp_id);	// get the number of students enrolled into a given AU and DP
			}
			else
			{
				$au_students =  0;
				$au_dp_students = 0;
			}
			$totals_row['AU Students'] = $au_students;
			$totals_row['Prog. AU Students'] = $au_dp_students;
	
//	amend Degree Programme percentage
			if($au_dp_students > 0) 
			{
				$totals_row['DP %'] = number_format($au_dp_students / $au_students * 100, 2);
				$totals_row['DP Stint'] = number_format($au_dp_students / $au_students * $totals_row['Actual AU Stint'], 2);
//				$totals_row['DP Stint Share'] = number_format($au_dp_students / $au_students * $totals_row['Actual AU Stint Share'], 2);
			}
			else
			{
				$totals_row['DP %'] = number_format(0, 2);
				$totals_row['DP Stint'] = number_format(0, 2);
//				$totals_row['DP Stint Share'] = number_format(0, 2);
			}


			$new_table[] = format_summary_row($totals_row);
//			$new_table[] = $blank_row;
		}
		
		
		$new_table[] = $row;
		$prev_dp_id = $dp_id;
		$prev_au_id = $au_id;
		$prev_au_title = $row['Assessment Unit / PGR Module'];
		
	}
	if($new_table) return $new_table;
	else return $table;
}

//--------------------------------------------------------------------------------------------------------------
function clean_repeating_programme_rows($table)
//	do not print repeated names, types and status for a assessment units
{
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		if($current_au != $row['Assessment Unit / PGR Module'])
		{
			$current_au= $row['Assessment Unit / PGR Module'];
			$repeating = FALSE;
		} else $repeating = TRUE;
		
		if($repeating)
//		if(1 ==1)
		{
			$row['DP Code'] = '';
			$row['Degree Programme'] = '';
			$row['AU provided by'] = '';
			$row['AU Code'] = '';
			$row['Assessment Unit / PGR Module'] = '';
			$row['Core / Option'] = '';
			$row['Unit Type'] = '';
//			$row['Norm AU Stint'] = '';
//			$row['Student Actual  AU Stint'] = '';
//			$row['Actual AU Stint Share'] = '';
			$row['Student Norm AU Hours'] =  '';
			$row['Dept Norm AU Hours'] = '';
			$row['Student Actual AU Hours'] = '';
//			$row['All AU Students'] = '';
			$row['Prog. (all) AU Students'] = '';
//			$row['Prog. AU Students'] = '';
			$row['DP %'] = '';
			$row['DP Dept Norm Stint'] = '';
			$row['DP Student Actual Stint'] = '';
			$row['DP Stint Share'] = '';
		}
		$new_table[] = $row;
	}
	
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function needs_sub_totals($ay_id, $au_id)
//	returns TRUE if a given assessment unit could need a sub total because it has more than one teaching instance in the given academic year
{
	$query = "
		SELECT
		COUNT(ti.id) AS 'count'
		
		FROM TeachingComponentAssessmentUnit tcau 
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
		INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id
		
		WHERE tcau.academic_year_id = $ay_id
		AND tcau.assessment_unit_id = $au_id
	";
	$result = get_data($query);
	if($result[0]['count'] > 1) return TRUE;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function insert_totals($table)
//	inserts a total row for all assessment units
{
	$new_table = array();

	$sum_norm_au_stint_per_dept = array();
	$sum_au_stint_per_dept = array();
	$sum_dp_stint_per_dept = array();
	
	$dp_stint_sum = 0;
	$au_stint_sum = 0;

	$total_norm_dept_stint = 0;
	$total_norm_dept_hours = 0;
	$total_act_dept_stint = 0;
	$total_act_dept_hours = 0;

//	summarize AU and DP stint for each providing department	
	if($table) foreach($table AS $row)
	{
		if($row['AU_ID'] > 0)
		{
//			$sum_norm_au_stint_per_dept[$row['AU provided by']] = $sum_norm_au_stint_per_dept[$row['AU provided by']] + $row['Norm AU Stint'];
//			$sum_au_stint_per_dept[$row['AU provided by']] = $sum_au_stint_per_dept[$row['AU provided by']] + $row['Actual Student AU Stint'];
			$sum_au_stint_share_per_dept[$row['AU provided by']] = $sum_au_stint_share_per_dept[$row['AU provided by']] + $row['Actual AU Stint Share'];

			$sum_norm_au_stud_hours_per_dept[$row['AU provided by']] = $sum_norm_au_stud_hours_per_dept[$row['AU provided by']] + $row['Student Norm AU Hours'];
			$sum_norm_au_hours_per_dept[$row['AU provided by']] = $sum_norm_au_hours_per_dept[$row['AU provided by']] + $row['Dept Norm AU Hours'];
			$sum_au_hours_per_dept[$row['AU provided by']] = $sum_au_hours_per_dept[$row['AU provided by']] + $row['Student Actual AU Hours'];

			$sum_dp_norm_stint_per_dept[$row['AU provided by']] = $sum_dp_norm_stint_per_dept[$row['AU provided by']] + $row['DP Dept Norm Stint'];	
			$sum_dp_stint_per_dept[$row['AU provided by']] = $sum_dp_stint_per_dept[$row['AU provided by']] + $row['DP Student Actual Stint'];	
			$sum_dp_stint_share_per_dept[$row['AU provided by']] = $sum_dp_stint_share_per_dept[$row['AU provided by']] + $row['DP Stint Share'];	

//			$au_norm_stint_sum = $au_norm_stint_sum + $row['Norm AU Stint'];
//			$au_stint_sum = $au_stint_sum + $row['Actual Student AU Stint'];
			$au_stint_share_sum = $au_stint_share_sum + $row['Actual AU Stint Share'];

			$au_norm_stud_hours_sum = $au_norm_stud_hours_sum + $row['Student Norm AU Hours'];
			$au_norm_hours_sum = $au_norm_hours_sum + $row['Dept Norm AU Hours'];
			$au_hours_sum = $au_hours_sum + $row['Student Actual AU Hours'];

			$dp_norm_stint_sum = $dp_norm_stint_sum + $row['DP Dept Norm Stint'];
			$dp_stint_sum = $dp_stint_sum + $row['DP Student Actual Stint'];
			$dp_stint_share_sum = $dp_stint_share_sum + $row['DP Stint Share'];
		}
		$new_table[] = $row;
	}

//	now amend the table with a summary line for each providing department
	$row = blank_line($row);
	$new_table[] = blank_line($row);

	$departments = array_keys($sum_au_stint_per_dept);

	foreach($departments AS $department)
	{
		$row['AU provided by'] = $department;
//		$row['Norm AU Stint'] = $sum_norm_au_stint_per_dept[$department];
//		$row['Student Actual  AU Stint'] = $sum_au_stint_per_dept[$department];
//		$row['Student Actual  AU Stint'] = $sum_au_stint_share_per_dept[$department];
		$row['Student Norm AU Hours'] = $sum_norm_au_stud_hours_per_dept[$department];
		$row['Dept Norm AU Hours'] = $sum_norm_au_hours_per_dept[$department];
		$row['Student Actual AU Hours'] = $sum_au_hours_per_dept[$department];
		$row['DP Dept Norm Stint'] = $sum_dp_norm_stint_per_dept[$department];
		$row['DP Student Actual Stint'] = $sum_dp_stint_per_dept[$department];
//		$row['DP Stint Share'] = $sum_dp_stint_share_per_dept[$department];
		$new_table[] = $row;
	}
	$new_table[] = blank_line($row);

	IF($_POST['excel_export'])
	{
		$row['AU provided by'] = 'Total:';
//		$row['Norm AU Stint'] = $au_norm_stint_sum;
//		$row['Student Actual  AU Stint'] = $au_stint_sum;
//		$row['Student Actual  AU Stint'] = $au_stint_share_sum;
		$row['Student Norm AU Hours'] = $au_norm_stud_hours_sum;
		$row['Dept Norm AU Hours'] = $au_norm_hours_sum;
		$row['Student Actual AU Hours'] = $au_hours_sum;
		$row['DP Dept Norm Stint'] = $dp_norm_stint_sum;
		$row['DP Student Actual Stint'] = $dp_stint_sum;
//		$row['DP Stint Share'] = $dp_stint_share_sum;
	}
	else
	{
		$row['AU provided by'] = '<B><U>Total:</U></B>';
//		$row['Norm AU Stint'] = "<B><U>" . $au_norm_stint_sum . "</U></B>";
//		$row['Student Actual  AU Stint'] = "<B><U>" . $au_stint_sum . "</U></B>";
//		$row['Student Actual  AU Stint'] = "<B><U>" . $au_stint_share_sum . "</U></B>";
		$row['Student Norm AU Hours'] = "<B><U>" . $au_norm_stud_hours_sum . "</U></B>";
		$row['Dept Norm AU Hours'] = "<B><U>" . $au_norm_hours_sum . "</U></B>";
		$row['Student Actual AU Hours'] = "<B><U>" . $au_hours_sum . "</U></B>";
		$row['DP Dept Norm Stint'] = "<B><U>" . $dp_norm_stint_sum . "</U></B>";
		$row['DP Student Actual Stint'] = "<B><U>" . $dp_stint_sum . "</U></B>";
//		$row['DP Stint Share'] = "<B><U>" . $dp_stint_share_sum . "</U></B>";
	}
	
	$new_table[] = $row;
	
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function tc_per_au($ay_id, $au_id)
//	returns all teaching components related to the given assessment unit in the given academic year
{
	$query ="
		SELECT
		tc.*
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON  tcau.teaching_component_id = tc.id
		
		WHERE tcau.academic_year_id = $ay_id
		AND tcau.assessment_unit_id = $au_id
	";
//d_print($query);
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function tc_attributes($ay_id, $au_id, $tc_id)
//	returns the teaching component type and tariffs for a given assessment unit, teaching component and academic year
{
	$query ="
		SELECT
		tcau.capacity AS 'capacity',
		tcau.sessions_planned AS 'sessions_planned',
		tct.title as 'type',
		IF(tcau.stint_override > 0, tcau.stint_override, tctt.stint) AS 'stint',
		tctt.hours AS 'hours'
		
		FROM TeachingComponentAssessmentUnit tcau
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		INNER JOIN TeachingComponent tc ON tc.id =tcau.teaching_component_id
		
		WHERE tcau.academic_year_id = $ay_id
		AND tcau.assessment_unit_id = $au_id
		AND tcau.teaching_component_id = $tc_id
	";
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function tc_attributes0($ay_id, $au_id, $tc_id)
//	returns the teaching component type and tariffs for a given assessment unit, teaching component and academic year
{
	$query ="
		SELECT
		tcau.capacity AS 'capacity',
		tcau.sessions_planned AS 'sessions_planned',
		tct.title as 'type',
		tctt.stint AS 'stint',
		tctt.hours AS 'hours'
		
		FROM TeachingComponentAssessmentUnit tcau
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE tcau.academic_year_id = $ay_id
		AND tcau.assessment_unit_id = $au_id
		AND tcau.teaching_component_id = $tc_id
	";
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function ti_per_tc($ay_id, $tc_id)
//	returns all teaching instances related to the given teaching component in the given academic year
{
	$query ="
		SELECT
		ti.*,
		t.term_code AS 'term'
		
		FROM TeachingInstance ti
		INNER JOIN Term t ON t.id = ti.term_id
		
		WHERE t.academic_year_id = $ay_id
		AND ti.teaching_component_id = $tc_id
	";
//d_print($query);
	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function ti_employee($ti_id)
//	returns the employee for the lecturer of a given teaching instance
{
	$query ="
		SELECT
		e.*
		
		FROM Employee e
		INNER JOIN TeachingInstance ti ON ti.employee_id = e.id
		
		WHERE ti.id = $ti_id
	";
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function amend_norms($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];
		
//	get the student norms
		$student_norm_data = get_student_norm($au_id, $ay_id);
		$row['Norm Student Provision - Stint'] = $student_norm_data['Stint'];
		$row['Norm Student Provision - Hours'] = $student_norm_data['Hours'];

//	get the number of enrolled students
//		$row['Students Entered'] = count_au_students($au_id, $ay_id);
		
//	get the dept norms
//		$dept_norm_data = get_dept_norm($au_id, $ay_id);
		$dept_norm_data = get_dept_norm($au_id, $ay_id, $row['Students']);
		$row['Norm Dept Provision - Stint'] = $dept_norm_data['Stint'];
		$row['Norm Dept Provision - Hours'] = $dept_norm_data['Hours'];

//	get the teaching component data per assessment unit

		$new_table[] = $row;
	}
	if($new_table) return $new_table;
	else return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_norm($au_id, $ay_id)
// returns the norm stint and hour values for one student attending this assessment unit in an array
{
	$student_norm = array();
	$query = "
		SELECT
		SUM(tctt.stint * tc.sessions_planned) AS 'Stint',
		SUM(tctt.hours * tc.sessions_planned) AS 'Hours'
		
		FROM AssessmentUnit au
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id
		INNER JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE au.id = $au_id 
		AND tcau.academic_year_id = $ay_id
		";
//d_print($query);
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function get_dept_norm($au_id, $ay_id, $students)
// returns the norm stint and hour values that needs to be provided by the department to satisfy all enrolled students
// if there no capacity limit or the number of students is 0 assume the minimum (= student) norm
{
	$dept_norm = array();

	if(!isset($students)) $students = 0;
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


//--------------------------------------------------------------------------------------------------------------
function amend_core_type($table)
//	amends the table with 'Core/Opt' and  'Unit Type' columns
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
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

		$new_table[] = $row;
	}
	if($new_table) return $new_table;
	else return $table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_stint_per_student0($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		if($row['Students'] > 0) $row['Actual stint / student'] = number_format($row['Student Actual  AU Stint'] / $row['Students'],2);
		else $row['Actual stint / student'] = '';
		$new_table[] = $row;
	}
	if($new_table) return $new_table;
	else return $table;
}





//========================< SES Enrolment >=========================
//--------------------------------------------------------------------------------------------------------------
function student_ses_enrolment_report_by_course()
//	shows the Doctoral Training Courses (AU) into which students of a given department or division are enrolled
{
	$table = get_own_students_per_ses_course();			// get all students of a selected department or division that are enrolled into any graduate course
	if ($_POST['show_terms'])  $table = amend_term($table);
	
	$table = cleanup($table,1);									//removing all internally used values from the table
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function student_ses_enrolment_report_by_dept()
//	shows the Doctoral Training Courses (AU) into which students of a given department or division are enrolled
{
	$table = get_own_students_per_ses_course_dept();	// get all students of a selected department or division that are enrolled into any graduate course

//	$table = cleanup($table,2);									//removing all internally used values from the table
	return $table;
}

//-----------------------------------------------------------------------------------------
function get_own_students_per_ses_course()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$query ="
SELECT
au.id AS 'AU_ID',
au.assessment_unit_code AS 'AU Code',
au.title AS 'Assessment Unit / PGR Module',
au_d.department_name AS 'Provided by',
#COUNT(sdp.student_id) AS 'Whole Students',
FORMAT(SUM(sdp.student_id/sdp.student_id * dpd.percentage / 100),1) AS 'Students'

FROM StudentDegreeProgramme sdp
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = $ay_id
INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.year_of_programme = 1 AND dpd.academic_year_id = $ay_id
INNER JOIN Department d ON d.id = dpd.department_id

WHERE 1 = 1 ";
		if($_POST['query_type'] == 'non_ses') $query = $query."AND au.import_into_SES != 1 AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		else $query = $query."AND au.import_into_SES = 1 ";
		if($_POST['query_type'] == 'pgr_only') $query = $query."AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		if($_POST['query_type'] == 'au_only') $query = $query."AND (au.assessment_unit_code NOT LIKE '2B%' AND au.assessment_unit_code NOT LIKE '3C%' AND au.assessment_unit_code NOT LIKE '4D%' AND au.assessment_unit_code NOT LIKE '5E%') ";

		if($ay_id) $query = $query."AND sau.academic_year_id = $ay_id ";

$query = $query . "
AND sdp.status = 'ENROLLED'
AND d.department_code LIKE '$department_code%'

GROUP BY au.title
ORDER BY au_d.department_name, au.title
	";
	return get_data($query);
}

//-----------------------------------------------------------------------------------------
function get_own_students_per_ses_course_dept()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$query ="
SELECT
au_d.department_name AS 'Graduate Training provided by',
#au.assessment_unit_code AS 'AU Code',
#au.title AS 'Assessment Unit / PGR Module',
#COUNT(sdp.student_id) AS 'Whole Students',
FORMAT(SUM(sdp.student_id/sdp.student_id * dpd.percentage / 100),1) AS 'Own Students enrolled'

FROM StudentDegreeProgramme sdp
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = $ay_id
INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
INNER JOIN Department au_d ON au_d.id = au.department_id

INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = sdp.degree_programme_id AND dpd.year_of_programme = 1 AND dpd.academic_year_id = $ay_id
INNER JOIN Department d ON d.id = dpd.department_id

WHERE 1 = 1 ";
		if($_POST['query_type'] == 'non_ses') $query = $query."AND au.import_into_SES != 1 AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		else $query = $query."AND au.import_into_SES = 1 ";
		if($_POST['query_type'] == 'pgr_only') $query = $query."AND (au.assessment_unit_code LIKE '2B%' OR au.assessment_unit_code LIKE '3C%' OR au.assessment_unit_code LIKE '4D%' OR au.assessment_unit_code LIKE '5E%') ";
		if($_POST['query_type'] == 'au_only') $query = $query."AND (au.assessment_unit_code NOT LIKE '2B%' AND au.assessment_unit_code NOT LIKE '3C%' AND au.assessment_unit_code NOT LIKE '4D%' AND au.assessment_unit_code NOT LIKE '5E%') ";

		if($ay_id) $query = $query."AND sau.academic_year_id = $ay_id ";

$query = $query . "
AND sdp.status = 'ENROLLED'
AND d.department_code LIKE '$department_code%'

GROUP BY au_d.department_name
ORDER BY au_d.department_name
	";
//d_print($query);
	return get_data($query);
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
	$query = "
		SELECT * 
		FROM TeachingInstance ti 
		INNER JOIN Term t ON t.id = ti.term_id 
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		INNER JOIN TeachingComponentAssessmentUnit tcau 
			ON tcau.teaching_component_id = tc.id 
			AND tcau.academic_year_id = t.academic_year_id
		
		WHERE tcau.assessment_unit_id = $au_id 
		";

	if($ay_id > 0) $query = $query . "
		AND t.academic_year_id = $ay_id
		";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function unit_has_enrollment($au_id, $ay_id)
//	checks if  a given Assessment Unit ID has some enrollment at all (for a given academic year)
{
	$query = "
		SELECT * 
		FROM StudentAssessmentUnit sau 
		
		WHERE sau.assessment_unit_id = $au_id 
		";
	if($ay_id > 0) $query = $query . "
		AND t.academic_year_id = $ay_id
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

//--------------------------------------------------------------------------------------------------
function get_au_dept($au_id)
//	returns the name of the department that provides a given Assessment Unit
{
	$query = "
		SELECT CONCAT('<B>',d.department_name,' (',d.department_code,')</B>') AS 'Department'
		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id
		
		WHERE au.id = $au_id
	";

	$res_table=get_data($query);
	return $res_table[0]['Department'];
}

//--------------------------------------------------------------------------------------------------
function get_dp_title($dp_id)
//	returns the degree programme title (+ code) from the degree programme with the given id
{
	$query = "
		SELECT
		CONCAT(title,' (',degree_programme_code,')') AS 'Title'
		FROM DegreeProgramme
		
		WHERE id = $dp_id
	";

	$res_table=get_data($query);
	return $res_table[0]['Title'];
}


?>