<?php

//==================================================================================================
//
//	Separate file with leave report functions
//	Last changes: Matthias Opitz --- 2013-04-24
//	changed to use start and end term in leave
//	2013-05-31 - now using start_term_id in leave table
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_leave_query()
{
	if(!isset($_POST['excel_export']))
	{	
		leave_query_form();
		if(!isset($_POST['query_type']))
		{
			print_leave_intro();
//			print_leave_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function leave_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];										// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];								// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='leave'>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Staff Member:" . new_column(0) . "<input type='text' name = 'fullname_q' value='".$_POST['fullname_q']."' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function leave_report_options()
// shows the options for an office report
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
function print_leave_intro()
{
	$text = "
		<B>This report shows leave and buyouts by term of each academic staff member of the department for a selected academic year.</B><BR />
		<P>	Just click on 'Go!' to get a summary list of all leave related to the department for the selected academic year.<BR />
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_leave_help()
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

//======================< Leave Report >========================
//--------------------------------------------------------------------------------------------------------------
function leave_report()
{
	$ay_id = $_POST['ay_id'];				// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);		// get department id

	$table = leave_list();

//	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function leave_list()
//	get the list of leave
{
	$ay_id = $_POST['ay_id'];				// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$dp_title_q = $_POST['dp_title_q'];			// get au_title_q
	$dp_id = $_POST['dp_id'];				// get dp_id
	$fullname_q = $_POST['fullname_q'];			// get fullname_q

	$query = " 
SELECT ";
		if(strlen($department_code)<4) $query = $query."d.department_name AS 'Department', ";
		$query = $query . "
e.fullname AS 'Staff Member',
st.term_code AS 'Start Term',
et.term_code AS 'End Term',
lt. leave_type_name AS 'Type',
eal.leave_percentage AS 'Percentage',
eal.notes AS 'Notes',
aps.label AS 'Approval'

FROM EmployeeAcademicLeave eal
INNER JOIN Term st ON st.id = eal.start_term_id
INNER JOIN Term et ON et.id = eal.end_term_id
INNER JOIN Employee e ON e.id = eal.employee_id
INNER JOIN LeaveType lt ON lt.id = eal.leave_type_id
INNER JOIN Department d ON d.id = eal.department_id
INNER JOIN ApprovalStatus aps ON aps.id = eal.approval_status_id,
AcademicYear ay

WHERE 1=1
AND ay.id = $ay_id
			";
	if($department_code) $query = $query."AND d.department_code LIKE '$department_code%' ";
	if($fullname_q) $query = $query."AND e.fullname LIKE '%$fullname_q%' ";
//	if($ay_id > 0) $query = $query."AND st.academic_year_id = $ay_id";
	if($ay_id > 0) $query = $query."AND st.startdate < ay.enddate AND et.enddate > ay.startdate ";

	$query = $query." ORDER BY d.department_name, e.fullname, st.startdate	";

	if(isset($_POST['deb'])) if($_POST['deb'] == 'query') d_print($query);
	$table = get_data($query);

	return $table;
}





?>