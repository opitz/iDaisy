<?php

//==================================================================================================
//
//	Separate file with balance report functions
//	Last changes: Matthias Opitz --- 2013-03-06
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_balance_query()
{
	if(!$_POST['excel_export'])
	{	
		balance_query_form();
		if(!$_POST['query_type'])
		{
//			print_balance_intro();
//			print_balance_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function balance_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='balance'>";

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
	print start_row(0) . "Report Type:" . new_column(0) . balance_report_options() . end_row();		

	print "</TABLE>";

	print "<HR>";
//	print balance_report_checkboxes();
	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function balance_report_checkboxes()
// shows the checkboxes for the report query
{
//	display the option checkboxes
	$html = $html . "<TABLE BORDER=0>";

//	$html = $html . start_row(500);		// 1st row
	$html = $html . "<TR><TD WIDTH=500 COLSPAN=3>";		// 1st row
		 $html = $html . "Exclude Degree Programmes and Assessment Units without teaching:";
	$html = $html . new_column(60);
		$html = $html . html_checkbox('exclude_no_teaching');
/*

	$html = $html . new_column(380);
		 $html = $html . "Exclude Assessment Units without teaching:";
	$html = $html . new_column(60);
		$html = $html . html_checkbox('exclude_no_teaching');

	$html = $html . new_column(190);
		$html = $html . "Show Teaching Details:";
	$html = $html . new_column(60);
		$html = $html . html_checkbox('show_teaching_details');

	$html = $html . new_column(150);
		$html = $html . "Show Sub-Totals:";
	$html = $html . new_column(60);
		$html = $html . html_checkbox('show_sub_totals');
*/
//	$html = $html . new_column(100);
//		$html = $html . "Hide Hours:";
//	$html = $html . new_column(60);
//		$html = $html . html_checkbox('hide_hours');

	$html = $html . end_row();

	$html = $html . start_row(180);		// 2nd row
//	print new_column(190);
		$html = $html . "Show Teaching Details:";
	$html = $html . new_column(100);
		$html = $html . html_checkbox('show_teaching_details');

//	$html = $html . start_row(190);		// 3rd row
	$html = $html . new_column(200);
		$html = $html . "Show Sub-Totals in Details:";
	$html = $html . new_column(60);
		$html = $html . html_checkbox('show_sub_totals');
	$html = $html . end_row();

	$html = $html . "</TABLE>";


	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function balance_report_options()
// shows the options for a ses report
{
	$query_type = $_POST['query_type'];					// get course_report_type
	
	$options = array();

	$options[] = array('Stint for using other Assessment Units', 'other_au');
	$options[] = array('Stint from others using Dept Assessment Units', 'own_au');
	$options[] = array('Stint for borrowed academics', 'borrow');
	$options[] = array('Stint from lent academics', 'lent');
	$options[] = array('Balance', 'balance');
	
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
function print_balance_intro()
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
function print_balance_help()
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

//========================< Balance Report >=========================
//--------------------------------------------------------------------------------------------------------------
function balance_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$other_au = other_au_report();
	$own_au = own_au_report();
	$borrowed = borrow_report();
	$lent = lent_report();
	
	$other_au_keys = array_keys($other_au[0]);
	$own_au_keys = array_keys($own_au[0]);
	$borrowed_keys = array_keys($borrowed[0]);
	$lent_keys = array_keys($lent[0]);
	
	
	$all_keys =  array_merge($other_au_keys, $own_au_keys, $borrowed_keys, $lent_keys);	
	$distinct_keys = unify_keys($all_keys);

	$balance = array();
	
	$sum_row = array_merge(array('Position' => 'Sums:'), $distinct_keys, array('From Others' => '', 'To Others' => ''));

//	other au
	$row = array_merge(array('Position' => $other_au['Position']), $distinct_keys, array('From Others' => '', 'To Others' => ''));
	$r_sum = 0;
	if($other_au_keys) foreach($other_au_keys AS $other_au_key)
	{
		if($other_au_key == 'Position') $row[$other_au_key] = $other_au[0][$other_au_key] ;
		else 
		{
			$row[$other_au_key] = $other_au[0][$other_au_key] * -1;
			$sum_row[$other_au_key] = $sum_row[$other_au_key] - $other_au[0][$other_au_key];
			$r_sum = $r_sum - $other_au[0][$other_au_key];
		}
	}
	$row['To Others'] = $r_sum;
	$sum_row['To Others'] = $sum_row['To Others'] + $r_sum;
	$balance[] = $row;
	
//	own au
	$row = array_merge(array('Position' => $own_au['Position']), $distinct_keys, array('From Others' => '', 'To Others' => ''));
	$r_sum = 0;
	if($own_au_keys) foreach($own_au_keys AS $own_au_key)
	{
		$sum_row[$own_au_key] = $sum_row[$own_au_key] + $own_au[0][$own_au_key];
		$row[$own_au_key] = $own_au[0][$own_au_key];
		$r_sum = $r_sum + $own_au[0][$own_au_key];
	}
	$row['From Others'] = $r_sum;
	$sum_row['From Others'] = $sum_row['From Others'] + $r_sum;	
	$balance[] = $row;
	
//	borrowed
	$row = array_merge(array('Position' => $borrowed['Position']), $distinct_keys, array('From Others' => '', 'To Others' => ''));
	$r_sum = 0;
	if($borrowed_keys) foreach($borrowed_keys AS $borrowed_key)
	{
		if($borrowed_key == 'Position') $row[$borrowed_key] = $borrowed[0][$borrowed_key] ;
		else
		{
			$sum_row[$borrowed_key] = $sum_row[$borrowed_key] - $borrowed[0][$borrowed_key];
			$row[$borrowed_key] = $borrowed[0][$borrowed_key] * -1;
			$r_sum = $r_sum - $borrowed[0][$borrowed_key] ;
		}
	}
	$row['To Others'] = $r_sum;
	$sum_row['To Others'] = $sum_row['To Others'] + $r_sum;
	$balance[] = $row;
	
//	lent
	$row = array_merge(array('Position' => $lent['Position']), $distinct_keys, array('From Others' => '', 'To Others' => ''));
	$r_sum = 0;
	if($lent_keys) foreach($lent_keys AS $lent_key)
	{
		$sum_row[$lent_key] = $sum_row[$lent_key] + $lent[0][$lent_key];
		$row[$lent_key] = $lent[0][$lent_key];
		$r_sum = $r_sum + $lent[0][$lent_key] ;
	}
	$row['From Others'] = $r_sum;
	$sum_row['From Others'] = $sum_row['From Others'] + $r_sum;
	$balance[] = $row;
	
//	sum
	$balance[] = blank_line($sum_row);
	$balance[] = blank_line($sum_row);
	
	$sum_row['Position'] = 'Sums:';
	$balance[] = format_summary_row($sum_row);
	
		
	return $balance;
}

//--------------------------------------------------------------------------------------------------------------
function unify_keys($all_keys)
{
	$distinct_keys = array();
	
	if($all_keys) foreach($all_keys AS $key)
	{
		if ($key != 'Position') $distinct_keys[$key] = '';
	}
	ksort($distinct_keys);
	return $distinct_keys;
}

//--------------------------------------------------------------------------------------------------------------
function programme_report0()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = dp_au_list();
	if($_POST['show_teaching_details'])
	{
		$table = amend_programme_details($table);
		if ($_POST['show_sub_totals'])  $table = insert_sub_totals($table);
	} else
	{
		$table = amend_programme_summary($table);
		$table = insert_totals($table);
	}
//	$table = amend_students_per_au($table);


	$table = cleanup($table,2);								//removing all internally used values from the table
	
	return $table;
}

//=======================< Student Stint Spent >======================
//--------------------------------------------------------------------------------------------------------------
function other_au_report()
//	get the stint share provided through assessment units of other departments to the  student share "owned" by a department
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	1.	get the assessment units provided by other departments and the number of own students  enrolled into
//		plus the  share of "own" students determined by the student load of the degree programmes
	$other_au_list = get_other_au();
	
//	2.	For each listed AU add the total number of students enrolled and the sum of stint used on it
	$other_au_list = amend_other_au($other_au_list);

//	3.	Summarize the results per other department
	$table = summarize_other_au($other_au_list);

//	$table = cleanup($table,2);								//removing all internally used values from the table

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_other_au()
//	get the assessment units provided by other departments that own students are enrolled into
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	get the assessment units and the student share

	$query = "
		SELECT
		au.id AS 'AU_ID',
		au_d.id AS 'AU_D_ID',
		au.assessment_unit_code,
		au.title,
		au_d.department_code,
		au_d.department_name,
		COUNT(DISTINCT sdp.student_id) AS 'students',
		FORMAT(COUNT(DISTINCT sdp.student_id) * dpd.percentage / 100,1) AS 'dept_student_share'

		FROM DegreeProgrammeDepartment dpd
		INNER JOIN Department d ON d.id = dpd.department_id

		INNER JOIN StudentDegreeProgramme sdp ON sdp.degree_programme_id = dpd.degree_programme_id AND sdp.academic_year_id = dpd.academic_year_id AND sdp.status = 'ENROLLED'

		INNER JOIN StudentAssessmentUnit sau ON sau.student_id = sdp.student_id AND sau.academic_year_id = sdp.academic_year_id
		INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
		INNER JOIN Department au_d ON au_d.id = au.department_id AND au_d.department_code != d.department_code

		WHERE 1=1
		AND dpd.academic_year_id = $ay_id
		AND dpd.year_of_programme = 1
		AND d.department_code LIKE '$department_code%'

		GROUP BY au.id
		ORDER BY au_d.department_name, au.assessment_unit_code	
	";

//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function amend_other_au($table)
//	amends the assessment units provided by other departments that own students are enrolled into
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];
		
		$row['total_au_students'] = students_per_au($ay_id, $au_id);
		$row['dept_au_percentage'] = number_format($row['dept_student_share'] / $row['total_au_students'] * 100, 2); 
		
		$row['au_stint'] = au_stint($ay_id, $au_id);
		$row['dept_au_stint_percentage'] = number_format($row['au_stint'] * $row['dept_au_percentage'] / 100, 2);
		
		$new_table[] = $row;
	}

	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function summarize_other_au($table)
//	summarize the stint percentage used by other departments
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$departments = array();
	$new_table = array();
	$departments['Position'] = 'Student Stint used by other Depts';
	if($table) foreach($table AS $row)
	{
		$departments[$row['department_name']] = $departments[$row['department_name']] + $row['dept_au_stint_percentage'];
	}
	$new_table[] = $departments;

	return $new_table;
}


//=======================< Student Stint Earned >======================
//--------------------------------------------------------------------------------------------------------------
function own_au_report()
//	get the stint share earned through providing assessment units to students of other departments
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	1.	get list of dept assessment units and enrolled students per degree programme
	$table = get_own_au();

//	2.	For each listed AU add the total number of students enrolled and the sum of stint used on it
	$table = amend_own_au($table);
	
//	3.	amend the table with the number of students from each department that are enrolled into each assessment unit
	$table = get_d_stint_per_au($table);					// from common_teaching_functions.php

//	4.	Summarize the results per other department
	$table = summarize_own_au($table);

//	$table = cleanup($table,2);								//removing all internally used values from the table

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_own_au()
//	get the own assessment units
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	get the assessment units and the student share

	$query = "
		SELECT

		au.id AS 'AU_ID',
		au.assessment_unit_code,
		au.title,
		au_d.department_code,
		au_d.department_name

		FROM AssessmentUnit au
		INNER JOIN Department au_d ON au_d.id = au.department_id
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id 

		WHERE 1=1
		AND au_d.department_code LIKE '$department_code%'
		AND tcau.academic_year_id = $ay_id

		ORDER BY au_d.department_name, au.assessment_unit_code	
	";

//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function amend_own_au($table)
//	amends the list of own assessment units 
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$au_id = $row['AU_ID'];
		
		$row['total_au_students'] = students_per_au($ay_id, $au_id);		
		$row['au_stint'] = au_stint($ay_id, $au_id);
		
		$new_table[] = $row;
	}

	return $new_table;
}

//--------------------------------------------------------------------------------------------------------------
function summarize_own_au($table)
//	summarize the stint percentage used by other department
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$department_name =  dept_name_from_code($department_code);
	$departments = array();
	$new_table = array();
	$columns = array_keys($table[0]);

	$departments['Position'] = 'Student Stint used from other Depts';
	if($table) foreach($table AS $row)
	{
		if($columns) foreach($columns AS $column)
		{
			if($column != $department_name) 		// exclude the own department
				$departments["$column"] = $departments["$column"] + $row["$column"];
		}
	}
	$new_table[] = $departments;

	return $new_table;
}

//=====================< Borrowed Teaching Stint >=====================
//--------------------------------------------------------------------------------------------------------------
function borrow_report()
//	get the stint share spent on borrowing staff from other departments
{
//	1.	get a list of borrowed stint units by department
	$table = get_borrowed_stint();

//	2.	Summarize the results per other department
	$table = summarize_borrowed($table);

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_borrowed_stint()
//	get borrowed stint by department borrowed from
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	get the assessment units and the student share

	$query = "
		SELECT

		p_d.department_name,

		FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

		FROM PostOtherDepartment pod
		INNER JOIN Department pod_d ON pod_d.id = pod.other_department_id 
		INNER JOIN Post p ON p.id = pod.post_id
		INNER JOIN Department p_d ON p_d.id = p.department_id AND p_d.id != pod_d.id
		INNER JOIN Employee e ON p.employee_id = e.id

		INNER JOIN TeachingInstance ti ON ti.employee_id = p.employee_id
		INNER JOIN Term t ON t.id = ti.term_id

		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id

		WHERE 1=1
		AND t.academic_year_id = $ay_id
		AND pod_d.department_code LIKE '$department_code%'

		GROUP BY p_d.department_name	
		ORDER BY p_d.department_name	
	";

//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function summarize_borrowed($table)
//	summarize the stint sharesd from borrowed staff by department
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$departments = array();
	$new_table = array();
	$departments['Position'] = 'Borrowed Stint from other Depts';
	if($table) foreach($table AS $row)
	{
		$departments[$row['department_name']] = $departments[$row['department_name']] + $row['Stint'];
	}
	$new_table[] = $departments;

	return $new_table;
}

//=======================< Lent Teaching Stint >=======================
//--------------------------------------------------------------------------------------------------------------
function lent_report()
//	get the stint share earned from lending staff to other departments
{
//	1.	get list of borrowed academics
	$table = get_lent_stint();

//	2.	Summarize the results per other department
	$table = summarize_lent($table);

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_lent_stint()
//	get borrowed stint by department borrowed from
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

//	get the assessment units and the student share

	$query = "
		SELECT

		pod_d.department_name,

		FORMAT(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

		FROM PostOtherDepartment pod
		INNER JOIN Department pod_d ON pod_d.id = pod.other_department_id 
		INNER JOIN Post p ON p.id = pod.post_id
		INNER JOIN Department p_d ON p_d.id = p.department_id AND p_d.id != pod_d.id
		INNER JOIN Employee e ON p.employee_id = e.id

		INNER JOIN TeachingInstance ti ON ti.employee_id = p.employee_id
		INNER JOIN Term t ON t.id = ti.term_id

		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.academic_year_id = t.academic_year_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id

		WHERE 1=1
		AND t.academic_year_id = $ay_id
		AND p_d.department_code LIKE '$department_code%'

		GROUP BY pod_d.department_name
		ORDER BY pod_d.department_name
	";

//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function summarize_lent($table)
//	summarize the lent stint shares by department lent to
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$departments = array();
	$new_table = array();
	$departments['Position'] = 'Lent Stint to other Depts';
	if($table) foreach($table AS $row)
	{
		$departments[$row['department_name']] = $departments[$row['department_name']] + $row['Stint'];
	}
	$new_table[] = $departments;

	return $new_table;
}




//===========================< Helpers >===========================
//--------------------------------------------------------------------------------------------------------------
function dept_name_from_code($dept_code)
//	returns the department name for a given department code
{
	$query = "
		SELECT department_name
		FROM Department
		WHERE department_code = '$dept_code'
	";
	$result = get_data($query);
	return $result[0]['department_name'];
}



?>