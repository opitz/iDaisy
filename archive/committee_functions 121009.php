<?php

//==================================================================================================
//
//	Separate file with committee functions
//	Last changes: Matthias Opitz --- 2012-10-01
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function committee_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$cttee_title_q = $_POST['cttee_title_q'];										// get cttee_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='cttee'>";

	print "<TABLE BORDER=0>";
/*
	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();
	print end_row();
*/
	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(250) . "Committee Name:" . new_column(300) . "<input type='text' name = 'cttee_title_q' value='$cttee_title_q' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "Include Committee Members:";
	print new_column(60);
		if ($_POST['ctte_members']) print "<input type='checkbox' name='ctte_members' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='ctte_members' value='TRUE'>";
	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
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

}

//--------------------------------------------------------------------------------------------------------------
function show_committee_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$cttee_title_q = $_POST['cttee_title_q'];										// get cttee_title_q

	$db_name = get_database_name();

//	define column width in output table
	$table_width = array('Staff Member' => 250, 'Term' => 60, 'Type' => 100, 'Percentage' => 60, 'Notes' => 300, 'Approval' => 100);

	$query = " 
SELECT ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";
$query = $query . "
#cte.name AS 'Committee',
CONCAT('<A HREF=$this_page?cte_id=', cte.id, '&ay_id=$ay_id&department_code=$department_code>',cte.name,'</A>') AS 'Committee',
cte.committee_type AS 'Type',
e.fullname AS 'Responsible',
cte.temporal_status AS 'Status'

FROM Committee cte
INNER JOIN Department d ON d.id = cte.department_id
LEFT JOIN Employee e ON e.id = cte.employee_id

WHERE 1=1
	";
	if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($cttee_title_q) $query = $query."AND cte.name LIKE '%$cttee_title_q%' ";

	$query = $query." ORDER BY d.department_name, cte.name	";
dprint($query);
	$table = get_data($query);

	if($table) 
	{
		if ($excel_export) export2csv($table, "iDAISY_Committee_Report_");
		else 
		{
			print_header("Committee Report");
			committee_query_form(); 
			print "<HR>";
			print_table($table, $table_width, TRUE);
		}
	} else print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function show_committee_details()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Committee Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$cte_id = $_GET['cte_id'];								// get committee ID dp_id
	if(!$cte_id) $cte_id = $_POST['cte_id'];
	$_POST['cte_id'] = $cte_id;
	
//	print the header and stuff
	print_header("Committee Details");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: $academic_year";
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$excel_export)
		committee_switchboard();

//	get the degree programme record for a given  ID
	if(!$query)
		$query = "
			SELECT * 
			FROM Committee
			WHERE id = $cte_id
			";

	$result = get_data($query);
	$cte_data = $result[0];

	show_committee_title($cte_data);
	show_committee_members($cte_data['id']);
}

//--------------------------------------------------------------------------------------------------------------
function show_committee_title($cte_data)
//	print details for a given committee record
{
	print "<H3>".$cte_data['name']." (".$cte_data['committee_code'].")</H3>";
}

//--------------------------------------------------------------------------------------------------------------
function show_committee_members($cte_id)
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
function committee_switchboard()
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