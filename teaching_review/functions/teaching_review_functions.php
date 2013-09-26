<?php

//==================================================================================================
//
//	Separate file with teaching review report functions
//	Last changes: Matthias Opitz --- 2013-03-18
//
//==================================================================================================

//========================< Teaching Query >=========================
//--------------------------------------------------------------------------------------------------------------
function show_teaching_review_query()
{
	if(!$_POST['excel_export'])
	{	
		teaching_review_query_form();
		if(!$_POST['query_type'])
		{
			print_teaching_review_intro();
			print_teaching_review_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function teaching_review_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='teaching'>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Assessment Unit Title:" . new_column(0) . "<input type='text' name = 'au_title_q' value='".$_POST['au_title_q']."' size=50>" . end_row();		
//	print start_row(0) . "Report Type:" . new_column(0) . teaching_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";

	print start_row(190);		// 1st row
		print  "Exclude PGR Modules:";
	print new_column(60);
		print html_checkbox('hide_unrelated');

	print new_column(330);
		 print "Exclude Assessment Units without teaching:";
	print new_column(60);
		print html_checkbox('exclude_no_teaching');
/*
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

	print start_row(190);		// 2nd row
//	print new_column(190);
		print "Show Teaching Details:";
	print new_column(60);
		print html_checkbox('show_teaching_details');

	print new_column(150);
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
function teaching_review_report_options()
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
function print_teaching_review_intro()
{
	$text = "<B>This report shows teaching provision for each Assessment Unit (AU) owned by your department in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.</B><BR />
	<P>	
	Just click on 'Go!' to get a list of all Assessment Units owned by the department.
	<P>
	The report presents the information in two stages:
	<P>
	1.	Summary information<BR />
	A list of all AUs owned by the department according to the search criteria specified (please see help below for details).

	<P>	
	2.	Further details<BR />
	To select an AU, click on its title for details.
	<P>
	To further see the details for an assessment unit listed please click on its title.<BR />
	The next screen will allow to show teaching details and / or enrollment details for each assessment unit.
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_teaching_review_help()
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
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Assessment Unit Code</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>You can enter a (part of a) Assessment Unit code to narrow the search.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Assessment Unit Title</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>You can enter a (part of a) Assessment Unit title to narrow the search.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "<TR>";
			print "<TD>";
				print "<B><FONT FACE='Arial' COLOR=DARKBLUE>Exclude PGR Modules</FONT></B>:";
			print "</TD><TD>";
				print "<FONT FACE='Arial'>Check this to exclude PGR Modules from the report.</FONT>";
			print "</TD>";
		print "</TR><TR>";

		print "</TR>";
	print "</TABLE>";
	print "<P><I><FONT FACE='Arial' COLOR=DARKBLUE>Please note</FONT><FONT FACE='Arial'>: You can combine selection criteria</I></FONT>";
}

//=======================< Teaching Report >=========================
//--------------------------------------------------------------------------------------------------------------
function teaching_review_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = au_list();
//	$table = add_au_link($table);
//	$table = amend_students_per_au($table);


	if($_POST['show_teaching_details'])
	{
		$table = amend_teaching_details($table);
		if ($_POST['show_sub_totals']) 
		{
			$table = clean_repeating_teaching_rows($table);
			$table = insert_sub_totals($table);
		}
	} else
	{
		$table = amend_teaching_summary($table);
		$table = insert_totals($table);
	}

	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_teaching_review_details($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];
		
//	amend core and type
		if( is_part_of_programme($au_id, $ay_id))
		{
			if(is_core($au_id, $ay_id)) $row['Core / Option'] = 'Core';
			else $row['Core / Option'] = 'Option';
			if(is_pgrad($au_id, $ay_id)) $row['Unit Type'] = 'PGRAD';
			else $row['Unit Type'] = 'UGRAD';
		} else
		{
			$row['Core / Option'] = 'Option';
			$row['Unit Type'] = '';
		}

//	amend students enrolled
		$row['AU Students'] =  students_per_au($ay_id, $au_id);
		
//	get the student norms
		$student_norm_data = get_student_norm($au_id, $ay_id);
		$row['Norm Student Provision - Stint'] = number_format($student_norm_data['Stint'],2);
		$row['Norm Student Provision - Hours'] = number_format($student_norm_data['Hours'],2);

//	get the dept norms
		$dept_norm_data =get_dept_norm($au_id, $ay_id, $row['AU Students']);
		$row['Norm Dept Provision - Stint'] = number_format($dept_norm_data['Stint'],2);
		$row['Norm Dept Provision - Hours'] = number_format($dept_norm_data['Hours'],2);

//	get assessment unit stint and hours
		$row['Actual AU Stint'] =  number_format(au_stint($ay_id, $au_id),2);
		$row['Actual AU Stint Share'] =  number_format(au_stint_share($ay_id, $au_id),2);
		$row['Actual AU Hours'] =  number_format(au_hours($ay_id, $au_id),2);

//	amend stint per student
		if($row['AU Students'] > 0) $row['Actual AU stint / student'] = number_format($row['Actual AU Stint'] / $row['AU Students'],2);
		else $row['Actual AU stint / student'] = '';
		if($row['AU Students'] > 0) $row['Actual AU stint share / student'] = number_format($row['Actual AU Stint Share'] / $row['AU Students'],2);
		else $row['Actual AU stint share / student'] = '';
		
//	change assessment unit title into link
		$row['Assessment Unit / PGR Module'] = au_link($au_id, $row['Assessment Unit / PGR Module'] );

//	do the teaching component details
		$components = tc_per_au($ay_id, $au_id);
		if ($components) foreach($components AS $component)
		{
			$tc_id = $component['id'];
			$tc_attributes = tc_attributes($ay_id, $au_id, $tc_id);
			$tc_students = get_all_tc_students($ay_id, $tc_id);
			
//			$row[''] =  $component[''];
			$row['Teaching Component name'] =  $component['subject'];
			$row['TC Type'] =  $tc_attributes['type'];
			if($tc_attributes['capacity'] > 0) $row['Cap.'] =  $tc_attributes['capacity'];
			else $row['Cap.'] =  'no limit';
			$row['Norm sess'] =  $tc_attributes['sessions_planned'];
			$row['Stint/sess'] =  $tc_attributes['stint'];
			$row['Hour/sess'] =  $tc_attributes['hours'];
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
			$row['Hour/sess'] =  '';
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
function amend_teaching_review_summary($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];

//	amend students enrolled
		$row['AU Students'] =  students_per_au($ay_id, $au_id);
		
//	amend core and type
		if( is_part_of_programme($au_id, $ay_id))
		{
			if(is_core($au_id, $ay_id)) $row['Core / Option'] = 'Core';
			else $row['Core / Option'] = 'Option';
			if(is_pgrad($au_id, $ay_id)) $row['Unit Type'] = 'PGRAD';
			else $row['Unit Type'] = 'UGRAD';
		} else
		{
			$row['Core / Option'] = 'Option';
			$row['Unit Type'] = '';
		}

//	get the student norms
		$student_norm_data = get_student_norm($au_id, $ay_id);
		$row['Norm Student Provision - Stint'] = number_format($student_norm_data['Stint'],2);
		$row['Norm Student Provision - Hours'] = number_format($student_norm_data['Hours'],2);

//	get the dept norms
		$dept_norm_data =get_dept_norm($au_id, $ay_id, $row['AU Students']);
		$row['Norm Dept Provision - Stint'] = number_format($dept_norm_data['Stint'],2);
		$row['Norm Dept Provision - Hours'] = number_format($dept_norm_data['Hours'],2);

//	get assessment unit stint and hours
		$row['Actual AU Stint'] =  number_format(au_stint($ay_id, $au_id),2);
		$row['Actual AU Stint Share'] =  number_format(au_stint_share($ay_id, $au_id),2);
		$row['Actual AU Hours'] =  number_format(au_hours($ay_id, $au_id),2);

//	amend stint per student
		if($row['AU Students'] > 0) $row['Actual AU stint / student'] = number_format($row['Actual AU Stint'] / $row['AU Students'],2);
		else $row['Actual AU stint / student'] = '';
		if($row['AU Students'] > 0) $row['Actual AU stint share / student'] = number_format($row['Actual AU Stint Share'] / $row['AU Students'],2);
		else $row['Actual AU stint share / student'] = '';
		
//	change assessment unit title into link
		$row['Assessment Unit / PGR Module'] = au_link($au_id, $row['Assessment Unit / PGR Module'] );
		
		$new_table[] = $row;		
	}
	if($new_table) return $new_table;
	else return $table;
}


//--------------------------------------------------------------------------------------------------------------
function insert_sub_totals($table)
//	inserts a sub-total row for each assessment unit with more than one row
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];
		if(!isset($prev_au_id)) $prev_au_id = $au_id;
		if(!isset($prev_au_title)) $prev_au_title = $row['Assessment Unit / PGR Module'];
		
		if($au_id != $prev_au_id AND needs_sub_totals($ay_id, $prev_au_id))
		{
			$blank_row = blank_line($row);
			$totals_row = blank_line($row);

//	AU code showing "Sub-Totals" and title
			$totals_row['AU Code'] = "Sub-Totals ";
			$totals_row['Assessment Unit / PGR Module'] = "$prev_au_title";

//	amend students enrolled
			$totals_row['AU Students'] =  students_per_au($ay_id, $prev_au_id);
		
//	amend core and type
		if( is_part_of_programme($prev_au_id, $ay_id))
		{
			if(is_core($prev_au_id, $ay_id)) $totals_row['Core / Option'] = 'Core';
			else $totals_row['Core / Option'] = 'Option';
			if(is_pgrad($prev_au_id, $ay_id)) $totals_row['Unit Type'] = 'PGRAD';
			else $totals_row['Unit Type'] = 'UGRAD';
		} else
		{
				$totals_row['Core / Option'] = 'Option';
				$totals_row['Unit Type'] = '';
		}

//	get the student norms
		$student_norm_data = get_student_norm($prev_au_id, $ay_id);
			$totals_row['Norm Student Provision - Stint'] = number_format($student_norm_data['Stint'],2);
			$totals_row['Norm Student Provision - Hours'] = number_format($student_norm_data['Hours'],2);

//	get the dept norms
		$dept_norm_data =get_dept_norm($prev_au_id, $ay_id, $row['AU Students']);
			$totals_row['Norm Dept Provision - Stint'] = number_format($dept_norm_data['Stint'],2);
			$totals_row['Norm Dept Provision - Hours'] = number_format($dept_norm_data['Hours'],2);

//	get assessment unit stint and hours
			$totals_row['Actual AU Stint'] =  number_format(au_stint($ay_id, $prev_au_id),2);
			$totals_row['Actual AU Stint Share'] =  number_format(au_stint_share($ay_id, $prev_au_id),2);
			$totals_row['Actual AU Hours'] =  number_format(au_hours($ay_id, $prev_au_id),2);

//	amend stint per student
			if($totals_row['AU Students'] > 0) $totals_row['Actual AU stint / student'] = number_format($totals_row['Actual AU Stint'] / $totals_row['AU Students'],2);
			else $totals_row['Actual AU stint / student'] = '';
			if($totals_row['AU Students'] > 0) $totals_row['Actual AU stint share / student'] = number_format($totals_row['Actual AU Stint Share'] / $totals_row['AU Students'],2);
			else $totals_row['Actual AU stint share / student'] = '';
		
			$new_table[] = format_summary_row($totals_row);

		}
		
		$new_table[] = $row;
		$prev_au_id = $au_id;
		$prev_au_title = $row['Assessment Unit / PGR Module'];
		
	}
	if($new_table) return $new_table;
	else return $table;
}

//--------------------------------------------------------------------------------------------------------------
function clean_repeating_teaching_rows($table)
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
			$row['Core / Option'] = '';
			$row['Unit Type'] = '';
			$row['AU Students'] = '';
			$row['Norm Student Provision - Stint'] = '';
			$row['Norm Student Provision - Hours'] = '';
			$row['Norm Dept Provision - Stint'] = '';
			$row['Norm Dept Provision - Hours'] = '';
			$row['Actual AU Stint'] = '';
			$row['Actual AU Stint Share'] = '';
			$row['Actual AU Hours'] = '';
			$row['Actual AU stint / student'] = '';
			$row['Actual AU stint share / student'] = '';
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

	$total_norm_dept_stint = 0;
	$total_norm_dept_hours = 0;
	$total_act_dept_stint = 0;
	$total_act_dept_stint_share = 0;
	$total_act_dept_hours = 0;
	
	if($table) foreach($table AS $row)
	{
		if($row['AU_ID'] > 0)
		{
			$total_norm_dept_stint = $total_norm_dept_stint  + $row['Norm Dept Provision - Stint'];
			$total_norm_dept_hours = $total_norm_dept_hours + $row['Norm Dept Provision - Hours'];
			$total_act_dept_stint = $total_act_dept_stint + $row['Actual AU Stint'];
			$total_act_dept_stint_share = $total_act_dept_stint_share + $row['Actual AU Stint Share'];
			$total_act_dept_hours = $total_act_dept_hours + $row['Actual AU Hours'];
		}
		$new_table[] = $row;
	}
	$new_table[] = blank_line($row);
	$totals_row = blank_line($row);
	IF($_POST['excel_export'])
	{
		$totals_row['AU Code'] = "Total:";
		$totals_row['Norm Dept Provision - Stint'] = $total_norm_dept_stint;
		$totals_row['Norm Dept Provision - Hours'] = $total_norm_dept_hours;
		$totals_row['Actual AU Stint'] = $total_act_dept_stint;
		$totals_row['Actual AU Stint Share'] = $total_act_dept_stint_share;
		$totals_row['Actual AU Hours'] = $total_act_dept_hours;
	}
	else
	{
		$totals_row['AU Code'] = "<B><U>Total:</U></B>";
		$totals_row['Norm Dept Provision - Stint'] = "<B><U>" . $total_norm_dept_stint . "</U></B>";
		$totals_row['Norm Dept Provision - Hours'] = "<B><U>" . $total_norm_dept_hours . "</U></B>";
		$totals_row['Actual AU Stint'] = "<B><U>" . $total_act_dept_stint . "</U></B>";
		$totals_row['Actual AU Stint Share'] = "<B><U>" . $total_act_dept_stint_share . "</U></B>";
		$totals_row['Actual AU Hours'] = "<B><U>" . $total_act_dept_hours . "</U></B>";
	}
	
	$new_table[] = $totals_row;
	
	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function au_list()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$au_title_q = $_POST['au_title_q'];						// get au_title_q

	$query = "
		SELECT DISTINCT
		au.id AS 'AU_ID',
	";
// show a department column only when used on divisional level or above
	if(strlen($department_code) < 4) $query = $query."d.department_name AS 'Department', ";

	$query = $query."
		au.assessment_unit_code AS 'AU Code',
		au.title AS 'Assessment Unit / PGR Module'
		
		FROM AssessmentUnit au
		INNER JOIN Department d ON d.id = au.department_id ";
/*	if($_POST['exclude_no_teaching']) $query = $query."
		INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id AND audp.academic_year_id = $ay_id 
	";
	*/
	if($_POST['exclude_no_teaching']) $query = $query."
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = $ay_id 
		INNER JOIN TeachingInstance ti ON ti.teaching_component_id = tcau.teaching_component_id
		INNER JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id 
	";
	if($_POST['hide_unrelated']) $query = $query."INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id ";

	$query = $query."
		WHERE au.assessment_unit_code LIKE '%$au_code_q%'
		AND au.title LIKE '%$au_title_q%'
		AND d.department_code LIKE '$department_code%'
		
		ORDER BY d.department_code, au.assessment_unit_code";

//d_print($query);

		return get_data($query);
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
		$dept_norm_data = get_dept_norm($au_id, $ay_id, $row['AU Students']);
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

	if(!isset($students) OR !is_numeric($students)) $students = 0;
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
				if(is_core($au_id, $ay_id)) $row['Core / Option'] = 'Core';
				else $row['Core / Option'] = 'Option';
				if(is_pgrad($au_id, $ay_id)) $row['Unit Type'] = 'PGRAD';
				else $row['Unit Type'] = 'UGRAD';
			} else
			{
				$row['Core / Option'] = 'Option';
				$row['Unit Type'] = '';
			}

		$new_table[] = $row;
	}
	if($new_table) return $new_table;
	else return $table;
}

//--------------------------------------------------------------------------------------------------------------
function amend_stint_per_student($table)
{
	$ay_id = $_POST['ay_id'];
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		if($row['AU Students'] > 0) $row['Actual AU stint / student'] = number_format($row['Actual AU Stint'] / $row['AU Students'],2);
		else $row['Actual AU stint / student'] = '';
		if($row['AU Students'] > 0) $row['Actual AU stint share / student'] = number_format($row['Actual AU Stint Share'] / $row['AU Students'],2);
		else $row['Actual AU stint share / student'] = '';
		$new_table[] = $row;
	}
	if($new_table) return $new_table;
	else return $table;
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

?>