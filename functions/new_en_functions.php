<?php

//==================================================================================================
//
//	Separate file with committee functions
//	Last changes: Matthias Opitz --- 2012-10-01
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function new_en_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code


	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='new_en'>";

	print "<TABLE BORDER=0>";

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print "</TABLE>";

	print "<HR>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function show_new_en_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET['e_id'];															// get employee ID
	if(!$e_id) $e_id = $_POST['e_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$db_name = get_database_name();

//	define column width in output table
	$table_width = array('Department' =>300, 'Staff Member' =>350, 'Employee Number' => 200, 'Old Employee Number' => 200, 'Status' => 60);

	$query = " 
SELECT DISTINCT 
e.id AS 'E_ID', ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";
$query = $query . "
e.fullname AS 'Staff Member',
e.opendoor_employee_code AS 'Employee Number',
e.old_opendoor_employee_code AS 'Old Employee Number', 
e.status AS 'Status'

FROM Post p
INNER JOIN Employee e ON e.id = p.employee_id
INNER JOIN Department d ON d.id = p.department_id

WHERE 1=1
AND e.opendoor_employee_code != e.old_opendoor_employee_code
AND e.old_opendoor_employee_code > 0
	";
	if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";

	$query = $query." ORDER BY e.fullname	";
//dprint($query);
	$table = get_data($query);
	
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		$e_id = array_shift($row);
		$row['Staff Member'] = "<A HREF=$this_page?e_id=$e_id>".$row['Staff Member']."</A>";
		$row['Employee Number'] = "<B>".$row['Employee Number']."</B>";
		$new_table[] = $row;
	}

	if($new_table) 
	{
		if ($excel_export AND $_POST['query_type'] == 'new_en') export2csv($new_table, "iDAISY_New_EN_");
		else 
//		if(1 == 1)
		{
			print_header("Staff Members with New Employee Numbers");
			new_en_query_form(); 
			print "<HR>";
			print_table($new_table, $table_width, 0);
		}
	} else if(!$e_id) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

?>