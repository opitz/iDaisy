<?php

//==================================================================================================
//
//	Separate file with committee functions
//	Last changes: Matthias Opitz --- 2012-10-02
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function office_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$officer_q = $_POST['officer_q'];										// get cttee_title_q
	$office_holder_q = $_POST['office_holder_q'];									// get office_holder_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='office'>";

	print "<TABLE BORDER=0>";

	print start_row(250);
		print "Holding Office in:";
	print new_column(0);
		print in_year_options();
	print end_row();

	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Office:" . new_column(0) . "<input type='text' name = 'officer_q' value='$officer_q' size=50>" . end_row();		
	print start_row(0) . "Office Holder:" . new_column(0) . "<input type='text' name = 'office_holder_q' value='$office_holder_q' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
/*	
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
*/
}

//--------------------------------------------------------------------------------------------------------------
function show_office_list0()
{
	$actionpage = $_SERVER["PHP_SELF"];											// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];										// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$in_year = $_POST['in_year'];													// get in_year

	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET['e_id'];															// get employee ID
	if(!$e_id) $e_id = $_POST['e_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$officer_q = $_POST['officer_q'];												// get officer_q
	$office_holder_q = $_POST['office_holder_q'];									// get office_holder_q

//	define column width in output table
	$table_width = array('Department' => 300, 'Officer' => 450, 'Holder' => 250, 'Percentage' => 60, 'Notes' => 300, 'Approval' => 100);

	if(!$excel_export AND !$e_id)
	{
		print_header("Academic Office-Holding Report");
		office_query_form();
		print "<HR>";
	}

	$query = " 
SELECT ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";
$query = $query . "
off.title AS 'Officer',
e.fullname AS 'Holder', 
DATE(ofh.startdate) AS 'Start Date',
DATE(ofh.enddate) AS 'End Date'

FROM Officer off
INNER JOIN Department d ON d.id = off.department_id
LEFT JOIN  OfficeHolding ofh ON ofh.officer_id = off.id
LEFT JOIN Employee e ON e.id = ofh.employee_id

WHERE 1=1
	";
	if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($officer_q) $query = $query."AND off.title LIKE '%$officer_q%' ";
	if($office_holder_q) $query = $query."AND e.fullname LIKE '%$office_holder_q%' ";
	if($in_year) $query = $query."AND YEAR(ofh.startdate) <= '$in_year' AND YEAR(ofh.enddate) >= '$in_year' ";
	if($e_id) $query = $query."AND e.id = $e_id ";	
//	if($to_year) $query = $query."AND YEAR(ofh.enddate) <= '$to_year' ";

	$query = $query." ORDER BY d.department_name, off.title, ofh.startdate	";
//dprint($query);
	$table = get_data($query);

	if($table) 
	{
		if ($excel_export) export2csv($table, "Office-Holding_Report_");
		else 
		{
			if($e_id) 
			{
				print "<H3>Academic Offices</H3>";
				$print_line_numbers = FALSE;
			}
			else 
			{
				$print_line_numbers = TRUE;
			}
			print_table($table, $table_width, $print_line_numbers);
		}
	} else if(!$e_id AND !$excel_export) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function show_office_list()
{
	$actionpage = $_SERVER["PHP_SELF"];											// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];										// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$in_year = $_POST['in_year'];													// get in_year

	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET['e_id'];															// get employee ID
	if(!$e_id) $e_id = $_POST['e_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$officer_q = $_POST['officer_q'];												// get officer_q
	$office_holder_q = $_POST['office_holder_q'];									// get office_holder_q

//	define column width in output table
	$table_width = array('Department' => 300, 'Officer' => 450, 'Holder' => 250, 'Percentage' => 60, 'Notes' => 300, 'Approval' => 100);


	$query = " 
SELECT ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";
$query = $query . "
off.title AS 'Officer',
e.fullname AS 'Holder', 
DATE(ofh.startdate) AS 'Start Date',
DATE(ofh.enddate) AS 'End Date'

FROM Officer off
INNER JOIN Department d ON d.id = off.department_id
LEFT JOIN  OfficeHolding ofh ON ofh.officer_id = off.id
LEFT JOIN Employee e ON e.id = ofh.employee_id

WHERE 1=1
	";
	if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($officer_q) $query = $query."AND off.title LIKE '%$officer_q%' ";
	if($office_holder_q) $query = $query."AND e.fullname LIKE '%$office_holder_q%' ";
	if($in_year) $query = $query."AND YEAR(ofh.startdate) <= '$in_year' AND YEAR(ofh.enddate) >= '$in_year' ";
	if($e_id) $query = $query."AND e.id = $e_id ";	
//	if($to_year) $query = $query."AND YEAR(ofh.enddate) <= '$to_year' ";

	$query = $query." ORDER BY d.department_name, off.title, ofh.startdate	";
//dprint($query);
	$table = get_data($query);

	if($table) 
	{
		if ($excel_export AND !$_POST['e_id']) export2csv($table, "Office-Holding_Report_");
		else 
		{
			if($e_id) 
			{
				print "<HR>";
				print "<H3>Academic Offices</H3>";
				$print_line_numbers = FALSE;
			}
			else 
			{
				$print_line_numbers = TRUE;
			}
			print_table($table, $table_width, $print_line_numbers);
		}
	} else if(!$e_id AND !$excel_export) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function show_office_details()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Academic Office Details";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$off_id = $_GET['off_id'];								// get committee ID dp_id
	if(!$off_id) $off_id = $_POST['off_id'];
	$_POST['off_id'] = $off_id;
	
//	print the header and stuff
	print_header("Academic Office Details");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: $academic_year";
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$excel_export)
		office_switchboard();

//	get the office record for a given  ID
	$query = "
		SELECT * 
		FROM Officer
		WHERE id = $off_id
		";

	$result = get_data($query);
	$cte_data = $result[0];

	show_office_title($off_data);
	show_office_holders($off_data['id']);
}

//--------------------------------------------------------------------------------------------------------------
function show_office_title($off_data)
//	print details for a given committee record
{
	print "<H3>".$off_data['name']."</H3>";
}

//--------------------------------------------------------------------------------------------------------------
function show_office_holders($cte_id)
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_POST['department_code'];								// get department_code

	$query = "
		SELECT
#		e.fullname AS 'Member',
		CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Member',
		cms.role AS 'Role',
		cms.startdate AS 'Start Date',
		cms.enddate AS 'End Date',
		cms.re_electable AS 'Re-electable',
		cms.notes AS 'Notes'
		
		
		FROM CommitteeMembership cms
		INNER JOIN Employee e ON e.id = cms.employee_id
		
		WHERE cms.committee_id = $cte_id
		
		ORDER BY cms.role, e.fullname
	";
	
	$members = get_data($query);
	
	$column_width = array('Member' => 300, 'Role' =>300, 'Start Date' => 100, 'End Date' => 100, 'Notes' =>300);
	if($members) 
	{
		print "<H4>Members:</H4>";
		print_table($members, $column_width,0);
	}
}

//--------------------------------------------------------------------------------------------------------------
function office_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	
	$show_component_teaching = $_POST['show_component_teaching'];

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";

	print "<TD WIDTH=200 ALIGN=LEFT></TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}


?>