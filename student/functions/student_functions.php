<?php

//==================================================================================================
//
//	Separate file with student report functions
//	Last changes: Matthias Opitz --- 2013-07-01
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_student_query()
{
	if(!isset($_POST['excel_export']))
	{	
		student_query_form();
		if(!isset($_POST['query_type']) AND !isset($_POST['surname_q']) AND !isset($_POST['forename_q']))
		{
			print_student_intro();
//			print_student_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function student_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];										// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];								// get course_title_q

	print "<FONT FACE = 'Arial'>";
	
	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='stud'>";

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(250);
		print "Degree Programme:";
	print new_column(300);
		print degree_programme_options();

	print start_row(0) . "Surname:" . new_column(0) . "<input type='text' name = 'surname_q' value='".$_POST['surname_q']."' size=50>" . end_row();		
	print start_row(0) . "Forename:" . new_column(0) . "<input type='text' name = 'forename_q' value='".$_POST['forename_q']."' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

	print_query_buttons();
	print "</FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function student_report_options()
// shows the options for an student report
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

//--------------------------------------------------------------------------------------------------------------
function print_student_intro()
{
	$text = "
		<B>This report shows student data.</B><BR />
		<P>	Just click on 'Go!' to get a summary list of all ENROLLED students related to the department for the selected academic year.<BR />
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_studente_help()
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

//======================< Student Report >========================
//--------------------------------------------------------------------------------------------------------------
function student_report()
{
	$ay_id = $_POST['ay_id'];				// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);		// get department id
	$s_id = $_GET['s_id'];					// get student ID
	
	if($s_id)
	{
		$student = read_record('Student', $s_id);
		
		print "<FONT FACE = 'Arial'>";
		print "<HR>";
		print "<H3>" . $student['surname'] . ", " . $student['forename'] . "</H3><P>";
		
		print "<B>DAISY ID</B> : " . $student['id'] . "<BR>";
		print "<B>OSS Student Code</B> : " . $student['oss_student_code'] . "<BR>";
		print "<B>Webauth</B> : " . $student['webauth_code'] . "<BR>";
		print "<HR>";
		print "<P>";

		$programmes = get_student_programmes($s_id, $ay_id);
		print "<H4>Degree Programmes</H4>";
		p_table($programmes);

		$units = get_student_teaching($s_id, $ay_id);
		print "<H4>Teaching</H4>";
		p_table($units);
		
		$supervision = get_student_supervision($s_id, $ay_id);
		print "<H4>Supervison</H4>";
		p_table($supervision);
		
	}
	
//	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function get_student_programmes($s_id, $ay_id)
{
	$query = "
SELECT
#dp.title AS 'Degree Programme',
CONCAT('<A HREF=../teaching.php?dp_id=',dp.id,'>',dp.title,'</A>') AS 'Degree Programme',
dp.degree_programme_type AS 'Type', 
sdp.status AS 'Status',
sdp.start_date AS 'Started',
sdp.status AS 'Status',
sdp.year_of_student AS 'YoS',
sdp.oxford_graduate_year AS 'OGY'



FROM StudentDegreeProgramme sdp
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id

WHERE 1=1
AND sdp.academic_year_id = $ay_id
AND sdp.student_id = $s_id 
	";
	return get_data($query);
}



//--------------------------------------------------------------------------------------------------------------
function get_student_teaching($s_id, $ay_id)
{
	$query = "
SELECT
t.term_code AS 'Term',
#au.title AS 'Assessment Unit',
CONCAT('<A HREF=../teaching.php?au_id=',au.id,'>',au.title,'</A>') AS 'Assessment Unit',
tc.subject AS 'Subject',
tct.display_title AS 'Type',

#e.fullname AS 'Lecturer',
CONCAT('<A HREF=../academic.php?e_id=',e.id,'>',e.fullname,'</A>') AS 'Lecturer',
ti.percentage AS '%',
ti.sessions AS 'Sessions'

FROM StudentAssessmentUnit sau
INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id 
LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.assessment_unit_id = au.id AND tcau.academic_year_id = sau.academic_year_id 
LEFT JOIN TeachingComponent tc ON tc.id = tcau.teaching_component_id
LEFT JOIN TeachingInstance ti ON ti.teaching_component_id = tc.id
LEFT JOIN Term t ON t.id = ti.term_id AND t.academic_year_id = tcau.academic_year_id

LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id

LEFT JOIN Employee e ON e.id = ti.employee_id


WHERE 1=1
AND sau.student_id = $s_id 
AND sau.academic_year_id = $ay_id 

ORDER BY t.startdate, au.title, tc.subject, e.fullname
	";
	return get_data($query);
}



//--------------------------------------------------------------------------------------------------------------
function get_student_supervision($s_id, $ay_id)
{
	$query = "
SELECT
t.term_code AS 'Term',
#e.fullname AS 'Supervisor',
CONCAT('<A HREF=../academic.php?e_id=',e.id,'>',e.fullname,'</A>') AS 'Supervisor',
svt.title AS 'Type',
sv.percentage AS '%',
DATE(sv.startdate) AS 'Start Date'


FROM Supervision sv
INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

WHERE 1=1
AND t.academic_year_id = $ay_id
AND sv.student_id = $s_id

ORDER BY t.startdate, svt.title
	";
	return get_data($query);
}



//--------------------------------------------------------------------------------------------------------------
function student_list()
//	get the list of students
{
	$ay_id = $_POST['ay_id'];				// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$dp_title_q = $_POST['dp_title_q'];			// get au_title_q
	$dp_id = $_POST['dp_id'];				// get dp_id
	$surname_q = $_POST['surname_q'];			// get surname_q
	$forename_q = $_POST['forename_q'];			// get forename_q

	$query = " 
SELECT ";
		if(strlen($department_code)<4) $query = $query."d.department_name AS 'Department', ";
		$query = $query . "
#CONCAT(s.surname,', ',s.forename) AS 'Student',
CONCAT('<A HREF=index.php?s_id=',s.id,'>',s.surname,', ',s.forename,'</A>') AS 'Student',
dp.title AS 'Degree Programme',
sdp.year_of_student AS 'Student Year',
sdp.status AS 'Status',
dp.degree_programme_type AS 'Type'


FROM Student s
INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = s.id
INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id
INNER JOIN Department d ON d.id = s.department_id


WHERE 1=1
AND sdp.academic_year_id = $ay_id ";
	if(!$surname_q AND !$forename_q) $query = $query . "AND sdp.status = 'ENROLLED'

			";
	if($department_code) $query = $query."AND d.department_code LIKE '$department_code%' ";
	if($dp_id) $query = $query."AND dp.id = $dp_id ";
	if($surname_q) $query = $query."AND s.surname LIKE '%$surname_q%' ";
	if($forename_q) $query = $query."AND s.forename LIKE '%$forename_q%' ";

	$query = $query." ORDER BY s.surname, s.forename	";

	if(isset($_POST['deb'])) if($_POST['deb'] == 'query') d_print($query);
	$table = get_data($query);

	return $table;
}





?>