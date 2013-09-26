<?php

//==================================================================================================
//
//	Separate file with size & shape report functions
//	Last changes: Matthias Opitz --- 2013-05-16
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_sizeshape_query()
{
	if(!$_POST['excel_export'])
	{	
		sizeshape_query_form();
		if(!$_POST['query_type'])
		{
			print_sizeshape_intro();
			print_sizeshape_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function sizeshape_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];							// get academic_year_id
	$department_code = $_POST['department_code'];					// get department_code

	$course_title_q = $_POST['course_title_q'];					// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='sizeshape'>";

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
	print start_row(0) . "Degree Programme:" . new_column(0) . non_dphil_degree_programme_options() . end_row();		
//	print start_row(0) . "Report Type:" . new_column(0) . sizeshape_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";
//	display the option checkboxes
	print "<TABLE BORDER=0>";

	print start_row(250);		// 2nd row
//	print new_column(250);
		print "Show Assessment Unit Details:";
	print new_column(100);
		print html_checkbox('show_unit_details');

	print new_column(290);
		print "Show Teaching Component Details:";
	print new_column(100);
		print html_checkbox('show_component_details');

//	print start_row(190);		// 3rd row
	print new_column(200);
//		print "Show Sub-Totals in Details:";
	print new_column(60);
//		print html_checkbox('show_sub_totals');
	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function sizeshape_report_options()
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
function print_sizeshape_intro()
{
	$text = "<B>This report shows student norm hours for each Assessment Unit (AU) related to a Degree Programme owned by your department <BR />in various levels of detail including the norm provision for each AU in stint and hours, and the number of students entered.</B><BR />
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
function print_sizeshape_help()
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

//======================< Size & Shape Report >=======================
//--------------------------------------------------------------------------------------------------------------
function row_form()
{
	$row = array();
	
//	$row[''] = '';

//	$row['Department'] = '';
	$row['Department'] = '';
	$row['Degree Programme'] = '';
	$row['DP Students'] = '';
	$row['Avg. Student Hours'] = '';
//	$row['AU Code'] = '';
//	$row['Assessment Unit'] = '';
//	$row['AU Students'] = '';
//	$row['TC Subject'] = '';
//	$row['TC Students'] = '';
//	$row['Tariff Hours'] = '';
//	$row['Norm Sessions'] = '';

	return $row;
}

//--------------------------------------------------------------------------------------------------------------
function screen_bold($text)
//	return any given text embraced by <B> and </B> HTML tags
{
	if(isset($_POST['excel_export'])) return $text;
	else return "<B>".$text."</B>";
}

//--------------------------------------------------------------------------------------------------------------
function sizeshape_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id
	$dp_id = $_POST['dp_id'];								// get degree programme id
	
	$table = array();
	$row = row_form();
	
//	get all degree programmes involved
	$degree_programmes = get_degree_programmes1($ay_id, $dept_id, $dp_id, $department_code);

//	1st loop: for each degree programme do	
	if($degree_programmes) foreach($degree_programmes AS $degree_programme)
	{
		$dp_id = $degree_programme['DP_ID'];
//		$row['Department'] = $degree_programme['Department'];
		$row['Department'] = screen_bold($degree_programme['department_name']);
//		$row['DP Code'] = screen_bold($degree_programme['degree_programme_code']);
		$row['Degree Programme'] = screen_bold($degree_programme['title']);

// get all ENROLLED students of the degree programme for a given academic year
		$dp_students = get_dp_students($ay_id, $dp_id);
		
		$student_count = 0;
		$sum_student_norm_hours = 0;

		$au_list = array();
		$tc_list = array();

//	2nd loop: for each students of a degree programme do
		if($dp_students) foreach($dp_students AS $dp_student)
		{
			$s_id = $dp_student['id'];

//	3rd loop: for each assessment unit a student is enrolled into do
//	get all distinct assessment units a student is enrolled into

			if(isset($_POST['show_unit_details']))
				$student_assessment_units = get_student_units($ay_id, $s_id);
			if($student_assessment_units) foreach($student_assessment_units AS $unit)
			{
				$au_list[$unit['title']]['id'] = $unit['AU_ID'];
				$au_list[$unit['title']]['core_option'] = $unit['core_option'];
				$au_list[$unit['title']]['dp_students']++;
				
//	get all distinct teaching components a student is enrolled into
				if(isset($_POST['show_component_details']))
					$student_components = get_student_unit_components($ay_id, $unit['AU_ID'], $s_id);

//	4th loop: for each component a student is enrolled into do
				$student_norm_hours = 0;
				$au_tc_list = array();
				
				if($student_components) foreach($student_components AS $component)
				{
//					$student_norm_hours = $student_norm_hours + $component['tc_norm_hours'];

					$au_tc_list[$component['subject']]['id'] = $component['TC_ID'];
					$au_tc_list[$component['subject']]['type'] = $component['type'];
					$au_tc_list[$component['subject']]['sessions_planned'] = $component['sessions_planned'];
					$au_tc_list[$component['subject']]['hours'] = $component['hours'];
					$au_tc_list[$component['subject']]['tc_norm_hours'] = $component['tc_norm_hours'];
					$au_tc_list[$component['subject']]['dp_students']++;

					$tc_list[$component['subject']]['id'] = $component['TC_ID'];
					$tc_list[$component['subject']]['type'] = $component['type'];
					$tc_list[$component['subject']]['sessions_planned'] = $component['sessions_planned'];
					$tc_list[$component['subject']]['hours'] = $component['hours'];
					$tc_list[$component['subject']]['tc_norm_hours'] = $component['tc_norm_hours'];
					$tc_list[$component['subject']]['dp_students']++;
				}
				
				$au_list[$unit['title']]['tc_list'] = $au_tc_list;
//print_r( $au_tc_list);
//print "<HR>";
			}

//	get the unique number of norm hours of a student over ALL TC s/he is enrolled into via the AU
			$sum_student_norm_hours =  $sum_student_norm_hours  + get_student_norm_hours($ay_id, $s_id);		
			$student_count++;
		} 
		$row['DP Students'] = screen_bold($student_count);
//	get the average norm hours of a student
		if($student_count > 0) $dp_student_norm_hours = $sum_student_norm_hours / $student_count;
		else $dp_student_norm_hours = 0;
		$row['Avg. Student Hours'] = screen_bold(round($dp_student_norm_hours,0));

//	show Assessment Unit details if the option is set
		if(isset($_POST['show_unit_details']))
		{
			if($au_list) 
			{
//				ksort($au_list);		// sort array by key
				$au_keys = array_keys($au_list);
			} else 
			{
				$au_keys = FALSE;
				$row['Assessment Unit'] = '';
				$row['DP AU Students'] = '';
			}

			$au_counter = 0;

			if($au_keys) foreach($au_keys AS  $au_key)
			{
				if($au_counter++ > 0) 	//	blank out repeating DP information after row 1
				{
					$row['Department'] = '';
					$row['Degree Programme'] = '';
					$row['DP Students'] = '';
					$row['Avg. Student Hours'] = '';
				}
//				$row['AU ID'] = $au_list[$au_key]['id'] ;
				$row['Assessment Unit'] = $au_key;

//				$row['C / O'] = $au_list[$au_key]['core_option'] ;

				$row['DP AU Students'] = $au_list[$au_key]['dp_students'] ;
				$au_id= $au_list[$au_key]['id'] ;

//	show Teaching Component Details for each Assessment Unit if the option is set
				if(isset($_POST['show_component_details']))
				{
					$au_tc_list = $au_list[$au_key]['tc_list'] ;
//print_r($au_tc_list);
//print"<HR>";
					if($au_tc_list) 
					{
						ksort($au_tc_list);		// sort array by key
						$au_tc_keys = array_keys($au_tc_list);
					} else 
					{
						$au_tc_keys = FALSE;
						$row['Teaching Component'] = '';
						$row['Type'] = '';
						$row['Norm Sessions'] = '';
						$row['Hours / Sess.'] = '';
						$row['TC Norm Hours'] = '';
						$row['DP TC Students'] = '';
//						$row['AU TC Students'] = '';
//						$row['ALL TC Students'] = '';
					}

					$tc_counter = 0;
					if($au_tc_keys) foreach($au_tc_keys AS  $au_tc_key)
					{
//						if($student_count > 0) $row[$tc_key] = round($tc_norm_hours[$tc_key] / $student_count, 0);
						if($tc_counter++ > 0) 
						{
							$row['Department'] = '';
							$row['Degree Programme'] = '';
							$row['DP Students'] = '';
							$row['Avg. Student Hours'] = '';
							$row['Assessment Unit'] = '';
							$row['DP AU Students'] = '';
						}
						$row['Teaching Component'] = $au_tc_key;
						$row['Type'] = $au_tc_list[$au_tc_key]['type'] ;
						$row['Norm Sessions'] = $au_tc_list[$au_tc_key]['sessions_planned'] ;
						$row['Hours / Sess.'] = $au_tc_list[$au_tc_key]['hours'] ;
						$row['TC Norm Hours'] = $au_tc_list[$au_tc_key]['tc_norm_hours'] ;
						$tc_id = $au_tc_list[$au_tc_key]['id'] ;
						if($tc_id) $au_tc_students = count_au_tc_students($ay_id, $au_id, $tc_id);
						else $au_tc_students = 0;
						$row['DP TC Students'] = $tc_list[$au_tc_key]['dp_students'] ;
//						$row['AU TC Students'] = $au_tc_students;
						$table[] = $row;
					} else $table[] = $row;
				}
//				$table[] = $row;
			} else 		$table[] = $row;

			$table[] = blank_line($row);
		}
		
//	show  Teaching Component details ONLY when options are set accordingly
		elseif(!isset($_POST['show_unit_details']) AND isset($_POST['show_component_details']))
		{
			if($tc_list) 
			{
				ksort($tc_list);		// sort array by key
				$tc_keys = array_keys($tc_list);
			} else 
			{
				$tc_keys = FALSE;
				$row['Teaching Component'] = '';
				$row['Type'] = '';
				$row['Norm Sessions'] = '';
				$row['Hours / Sess.'] = '';
				$row['TC Norm Hours'] = '';
				$row['DP TC Students'] = '';
//				$row['ALL TC Students'] = '';
			}

			$counter = 0;
			if($tc_keys) foreach($tc_keys AS  $tc_key)
			{
//				if($student_count > 0) $row[$tc_key] = round($tc_norm_hours[$tc_key] / $student_count, 0);
				if($counter++ > 0) 
				{
					$row['Department'] = '';
					$row['Degree Programme'] = '333';
					$row['DP Students'] = '';
					$row['Avg. Student Hours'] = '';
				}
				$row['Teaching Component'] = $tc_key;
				$row['Type'] = $tc_list[$tc_key]['type'] ;
				$row['Norm Sessions'] = $tc_list[$tc_key]['sessions_planned'] ;
				$row['Hours / Sess.'] = $tc_list[$tc_key]['hours'] ;
				$row['TC Norm Hours'] = $tc_list[$tc_key]['tc_norm_hours'] ;
				$tc_id = $tc_list[$tc_key]['id'] ;
				if($tc_id) $tc_students = count_tc_students($ay_id, $tc_id);
				else $tc_students = 0;
				$row['DP TC Students'] = $tc_list[$tc_key]['dp_students'] ;
//				$row['ALL TC Students'] = $tc_students;
				$table[] = $row;
			} else 		$table[] = $row;
//			$blank_row = blank_line($row);
			$table[] = blank_line($row);
		}
		else $table[] = $row;
	}

//dprint("dp_id = $dp_id");
//	get all assessment units (of the selected degree programme(s))
//	$dp_assessment_units = get_dp_au($ay_id, $dp_id, $dept_id);

//	get all enrolled students of


//	$table = cleanup($table,2);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_norm_hours($ay_id, $s_id)
//	get the norm hours of a given student in a given academic year 
{
//	get all distinct teaching components a student is enrolled into
	$student_components = get_student_components($ay_id, $s_id);

//	3rd loop: for each component a student is enrolled into do
	$student_norm_hours = 0;
	if($student_components) foreach($student_components AS $component)
	{
		$student_norm_hours = $student_norm_hours + $component['tc_norm_hours'];
	}
//dprint($student_norm_hours);
	return $student_norm_hours;
}

//--------------------------------------------------------------------------------------------------------------
function get_degree_programmes1($ay_id, $dept_id, $dp_id, $department_code)
//	get a list of degree programmes
{
	$query = "
		SELECT  DISTINCT
		d.id AS 'D_ID',
		d.department_name,
		dp.id AS 'DP_ID',
		dp.*

		FROM DegreeProgramme dp 
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
		INNER JOIN Department d ON d.id = dpd.department_id  
		
		WHERE 1=1
		AND d.department_code LIKE '$department_code%' 

		AND (dp.degree_programme_code = '001590'
		OR dp.degree_programme_code = '001600'
		OR dp.degree_programme_code = '001660'
		OR dp.degree_programme_code = '001670'
		OR dp.degree_programme_code = '001673'
		OR dp.degree_programme_code = '001320'
		OR dp.degree_programme_code = '001430'
		OR dp.degree_programme_code = '001440'
		OR dp.degree_programme_code = '001705'
		OR dp.degree_programme_code = '001715'
		OR dp.degree_programme_code = '002740'
		OR dp.degree_programme_code = '002620'
		OR dp.degree_programme_code = '002850'
		OR dp.degree_programme_code = '003075'
		OR dp.degree_programme_code = '003076'
		OR dp.degree_programme_code = '003085'
		OR dp.degree_programme_code = '003095'
		OR dp.degree_programme_code = '001675'
		OR dp.degree_programme_code = '002690'
		OR dp.degree_programme_code = '002750'
		OR dp.degree_programme_code = '002931'
		OR dp.degree_programme_code = '003450'
		OR dp.degree_programme_code = '002640'
		OR dp.degree_programme_code = '003030'
		OR dp.degree_programme_code = '003640'
		OR dp.degree_programme_code = '003021'
		OR dp.degree_programme_code = '002245'
		OR dp.degree_programme_code = '002970'
		OR dp.degree_programme_code = '003150'
		OR dp.degree_programme_code = '003160'
		OR dp.degree_programme_code = '003170'
		OR dp.degree_programme_code = '003145'
		OR dp.degree_programme_code = '003080'
		OR dp.degree_programme_code = '003330'
		OR dp.degree_programme_code = '002980'
		OR dp.degree_programme_code = '003690'
		OR dp.degree_programme_code = '003200'
		OR dp.degree_programme_code = '003320'
		OR dp.degree_programme_code = '003900'
		OR dp.degree_programme_code = '002990'
		OR dp.degree_programme_code = '003980'
		OR dp.degree_programme_code = '003680'
		OR dp.degree_programme_code = '003190'
		OR dp.degree_programme_code = '003210'
		OR dp.degree_programme_code = '002650'
		OR dp.degree_programme_code = '002921'
		OR dp.degree_programme_code = '002660'
		OR dp.degree_programme_code = '002911'
		OR dp.degree_programme_code = '003300'
		OR dp.degree_programme_code = '003310'
		OR dp.degree_programme_code = '003830'
		OR dp.degree_programme_code = '001410'
		OR dp.degree_programme_code = '001470'
		OR dp.degree_programme_code = '001610'
		OR dp.degree_programme_code = '001700'
		OR dp.degree_programme_code = '001671'
		OR dp.degree_programme_code = '003640' 
		)


		ORDER BY d.department_name, dp.title	
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_degree_programmes($ay_id, $dept_id, $dp_id, $department_code)
//	get a given degree programme or all degree programmes of a given (set of) department(s) and academic year
//	excluding all DPhil (PGRAD_R) degree programmes as they are not of interest in this report
{
	$query = "
		SELECT DISTINCT
		dp.id AS 'DP_ID',
		dp.*

		FROM DegreeProgramme dp 
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
		INNER JOIN Department d ON d.id = dpd.department_id  
		
		WHERE 1=1
		AND dp.degree_programme_type != 'PGRAD_R'
		AND dpd.academic_year_id = $ay_id
		";
		if($dp_id) $query = $query . "AND dp.id = $dp_id ";
		if(!isset($_POST['include_ugrad'])) $query = $query . " AND dp.degree_programme_type != 'UGRAD' ";
		$query = $query . "
		AND d.department_code LIKE '$department_code%' 

		ORDER BY dp.title	
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_dp_students($ay_id, $dp_id)
//	get the students enrolled into a given degree programme in a given academic year
{
	$query = "
		SELECT 
		s.*
		
		FROM Student s
		INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = s.id AND sdp.status = 'ENROLLED'
		
		WHERE 1=1
		AND sdp.academic_year_id = $ay_id
		AND sdp.degree_programme_id = $dp_id

		ORDER BY s.surname, s.forename	
	";
//d_print($query);

	return get_data($query);
}

//--------------------------------------------------------------------------------------------------------------
function get_student_dp_units($ay_id, $dp_id, $s_id)
//	get all assessment units a given student of a given degree programme is enrolled into  in a given academic year
{
	$query = "
		SELECT DISTINCT
		au.id AS 'AU_ID',
		au.title,
		IF(audp.core_option = 'Core', 'C', 'O') AS 'core_option'
		
		FROM AssessmentUnit au
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id
		INNER JOIN AssessmentUnitDegreeProgramme audp ON audp.assessment_unit_id = au.id
		WHERE 1=1
		AND sau.academic_year_id = $ay_id
		AND audp.academic_year_id = $ay_id
		AND sau.student_id = $s_id
		AND audp.degree_programme_id = $dp_id

		ORDER BY audp.core_option, au.title
	";
//d_print($query);

	$table = get_data($query);
//	if($_POST['dp_id']) p_table($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_units($ay_id, $s_id)
//	get all assessment units a given student is enrolled into  in a given academic year
{
	$query = "
		SELECT DISTINCT
		au.id AS 'AU_ID',
		au.title
		
		FROM AssessmentUnit au
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = au.id
		
		WHERE 1=1
		AND sau.academic_year_id = $ay_id
		AND sau.student_id = $s_id

		ORDER BY au.title
	";
//d_print($query);

	$table = get_data($query);
//	if($_POST['dp_id']) p_table($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_components($ay_id, $s_id)
//	get all teaching components a given student is enrolled into via the assesments units in a given academic year
{
	$query = "
		SELECT DISTINCT
		tc.id AS 'TC_ID',
		tc.subject,
		tct.title AS 'type',
		tc.sessions_planned,
		tctt.hours AS 'hours',
		tc.sessions_planned * tctt.hours AS 'tc_norm_hours'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id != 99999
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id 
		
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND sau.student_id = $s_id

		ORDER BY tc.subject
	";
//d_print($query);

	$table = get_data($query);
//	if($_POST['dp_id']) p_table($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_unit_components($ay_id, $au_id, $s_id)
//	get all teaching components a given student is enrolled into via the given assesments unit in a given academic year
{
	$query = "
		SELECT DISTINCT
		tc.id AS 'TC_ID',
		tc.subject,
		tct.title AS 'type',
		tc.sessions_planned,
		tctt.hours AS 'hours',
		tc.sessions_planned * tctt.hours AS 'tc_norm_hours'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id != 99999
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id 
		
		INNER JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tcau.teaching_component_type_id AND tctt.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND sau.student_id = $s_id
		AND sau.assessment_unit_id = $au_id

		ORDER BY tc.subject
	";
//if($au_id == 100727) d_print($query);

	$table = get_data($query);
//	if($_POST['dp_id']) p_table($table);
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function count_tc_students($ay_id, $tc_id)
//	count the students enrolled into a given teaching component - via related assessment units - in a given academic year
{
	$query = "
		SELECT 
		COUNT(DISTINCT sau.student_id) AS 'students'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id !=99999
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND tcau.teaching_component_id = $tc_id
	";
//d_print($query);

	$result = get_data($query);
	return $result[0]['students'];
}


//--------------------------------------------------------------------------------------------------------------
function count_au_tc_students($ay_id, $au_id, $tc_id)
//	count the students enrolled into a given teaching component - via a given assessment units - in a given academic year
{
	$query = "
		SELECT 
		COUNT(DISTINCT sau.student_id) AS 'students'
		
		FROM TeachingComponent tc
		INNER JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND tcau.assessment_unit_id !=99999
		INNER JOIN StudentAssessmentUnit sau ON sau.assessment_unit_id = tcau.assessment_unit_id AND sau.academic_year_id = tcau.academic_year_id
		
		WHERE 1=1
		AND tcau.academic_year_id = $ay_id
		AND tcau.teaching_component_id = $tc_id
		AND tcau.assessment_unit_id = $au_id
	";
//d_print($query);

	$result = get_data($query);
	return $result[0]['students'];
}


?>