<?php

//==================================================================================================
//
//	Separate file with common leave functions
//	Last changes: Matthias Opitz --- 2012-09-13
//
//==================================================================================================
$version_ctcf = "120824.1";			// 1st version
$version_ctcf = "120813.1";			// added 

//--------------------------------------------------------------------------------------------------------------
function show_leave_list0()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];													// get academic_year_id
	$year = $_POST['year'];														// get year
	$department_code = $_POST['department_code'];							// get department_code

	$fullname_q = $_POST['fullname_q'];											// get fullname_q
	$forename_q = $_POST['forename_q'];											// get forename_q
	$surname_q = $_POST['surname_q'];											// get surname_q
	$webauth_q = $_POST['webauth_q'];											// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];									// get employee_nr_q


	$query = $_POST['query'];													// get query
	$query = stripslashes($query);

	$db_name = get_database_name();

//	$date = date('l jS \of F Y g:i:s');

//	build build the query using the input
	if(!$query)
	{
		$query = "
SELECT
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
	}
//dprint($query);
	$table = get_data($query);
	$new_table = array();
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
				
//	if an academic year was selected do some more...
		if($ay_id > 0) 
		{
		}
		
		$new_table[] = $row;
	}

//	Now print the whole lot
	if(!$excel_export)
	{
		print_header("Leave Report");
		leave_query_form(); 
		print "<HR>";
	}


	// specify column width by column title e.g. $table_width['Title'] = 200
	$table_width =array('Staff Member' => 350, 'Term' => 100, 'Percentage' => 100, 'Notes' => 200);

//	if ($excel_export AND $ay_id) export2excel($new_table, $export_title);
	if ($table AND $excel_export) export2csv($new_table, "iDAISY_Leave_Report_");
	else print_table($new_table, $table_width, TRUE);
	
	if($to_year < $from_year) print "Please review the dates selection for the publication year!<P>";

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
	$forename_q = $_POST['forename_q'];											// get forename_q
	$surname_q = $_POST['surname_q'];											// get surname_q
	$webauth_q = $_POST['webauth_q'];											// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];									// get employee_nr_q


	$query = $_POST['query'];													// get query
	$query = stripslashes($query);

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
//dprint($query);
		$table = get_data($query);

		if(!$title_printed)
		{
			print_header("Leave Report");
			leave_query_form(); 
			print "<HR>";
			$title_printed = TRUE;
		}
		if($table) 
//		if (1==1)
		{
			$some_result = TRUE;
			print"<H4>".$ac_year['label']."<H4>";
			if ($excel_export) export2csv($table, "iDAISY_Leave_Report_");
			else print_table($table, $table_width, TRUE);
		}
	}
	if(!$some_result) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

?>