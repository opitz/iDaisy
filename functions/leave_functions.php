<?php

//==================================================================================================
//
//	Separate file with common leave functions
//	
//	2012-10-01	added leave_query_form

//==================================================================================================
$version_ctcf = "120824.1";			// 1st version
$version_ctcf = "120813.1";			// added 

//----------------------------------------------------------------------------------------
function leave_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

//	$ay_id = $_POST['ay_id'];														// get academic_year_id
//	$department_code = $_POST['department_code'];								// get department_code
//	$fullname_q = $_POST['fullname_q'];											// get fullname_q

	print "<form action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	print write_post_vars_html();

	print "<input type='hidden' name='query_type' value='leave'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();
	print end_row();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Staff Member:" . new_column(0) . "<input type='text' name = 'fullname_q' value='".$_POST['fullname_q']."' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function show_leave_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$debug = $_POST['debug'];
//	$debug = 2;

//	$ay_id = $_POST['ay_id'];													// get academic_year_id
	$given_ay_id = $_POST['ay_id'];											// get selected academic_year_id
	$year = $_POST['year'];														// get year
	$department_code = $_POST['department_code'];							// get department_code
	$fullname_q = $_POST['fullname_q'];											// get fullname_q

	$db_name = get_database_name();

//	define column width in output table
	$table_width = array('Staff Member' => 250, 'Term' => 60, 'Type' => 100, 'Percentage' => 60, 'Notes' => 300, 'Approval' => 100);

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
		
	$some_result = FALSE;
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
		$query = "
			SELECT ";
		if(!$department_code) $query = $query."d.department_name AS 'Department', ";
		$query = $query . "
e.fullname AS 'Staff Member',
t.term_code AS 'Term',
lt. leave_type_name AS 'Type',
eal.leave_percentage AS 'Percentage',
eal.notes AS 'Notes',
aps.label AS 'Approval'

FROM EmployeeAcademicLeave eal
INNER JOIN Term t ON t.id = eal.term_id
INNER JOIN Employee e ON e.id = eal.employee_id
INNER JOIN LeaveType lt ON lt.id = eal.leave_type_id
INNER JOIN Department d ON d.id = eal.department_id
INNER JOIN ApprovalStatus aps ON aps.id = eal.approval_status_id

WHERE 1=1
			";

		if($academic_year_id) $query = $query."AND t.academic_year_id = $ay_id ";
		if($department_code) $query = $query."AND d.department_code LIKE '$department_code' ";
		if($fullname_q) $query = $query."AND e.fullname LIKE '%$fullname_q%' ";
		if($ay_id) $query = $query."AND t.academic_year_id = $ay_id";

		$query = $query." ORDER BY e.fullname, t.startdate	";

		$table = get_data($query);
//dprint($query);
		if(!$excel_export AND !$title_printed)
		{
			print_header("Leave Report");
			leave_query_form(); 
			print "<HR>";
			$title_printed = TRUE;
		}
		if($table) 
		{
			$some_result = TRUE;
			if ($excel_export) export2csv($table, "iDAISY_Leave_Report_");
			else 
			{
				print"<H4>".$ac_year['label']."<H4>";
				print_table($table, $table_width, TRUE);
			}
		}
	}
	if(!$some_result) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

?>