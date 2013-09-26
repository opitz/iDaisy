<?php

//==================================================================================================
//
//	Separate file with grant functions
//	Last changes: Matthias Opitz --- 2012-11-05
//
//==================================================================================================

//----------------------------------------------------------------------------------------
function grant_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$grant_title_q = $_POST['grant_title_q'];										// get grant_title_q
	$investigator_q = $_POST['investigator_q'];											// get investigator_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='grant'>";

	print "<TABLE BORDER=0>";
/*
	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();
	print end_row();
*/
	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(250) . "Grant Title:" . new_column(300) . "<input type='text' name = 'grant_title_q' value='$grant_title_q' size=50>" . end_row();		
	print start_row(250) . "Investigator Name:" . new_column(300) . "<input type='text' name = 'investigator_q' value='$investigator_q' size=50>" . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "List Grant Investigators:";
	print new_column(60);
		if ($_POST['investigators']) print "<input type='checkbox' name='investigators' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='investigators' value='TRUE'>";
	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function show_grant_list()
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

	$grant_title_q = $_POST['grant_title_q'];										// get grant_title_q
	$investigator_q = $_POST['investigator_q'];										// get investigator_q

	$db_name = get_database_name();

//	define column width in output table
	$table_width = array( 'Grant' =>550, 'Type' => 100, 'Status' => 60, 'PI' => 250, 'Start Date' => 90, 'End Date' => 90);

	$query = " 
SELECT DISTINCT ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";
$query = $query . "
CONCAT('<A HREF=$this_page?rg_id=', rg.id, '&ay_id=$ay_id&department_code=$department_code>',rg.title,'</A>') AS 'Grant',
e.fullname AS 'PI',
DATE(rg.startdate) AS 'Start Date', 
DATE(rg.enddate) AS 'End Date', 
fs.label AS 'Scheme',
fu.funder_name AS 'Funder',
fut.label AS 'Funding Type'

 ";

if ($_POST['investigators']) $query = $query . ",
i_e.fullname AS 'Member',
ras.research_award_staff_type AS 'Role'
#, DATE(ras.startdate) AS 'Start Date' 
#, DATE(ras.enddate) AS 'End Date' ";
$query = $query . "

FROM ResearchGrant rg 
INNER JOIN Funder fu ON fu.id = rg.funder_id
INNER JOIN Employee e ON e.id = rg.employee_id
INNER JOIN FundingScheme fs ON fs.id = rg.funding_scheme_id
LEFT JOIN Department d ON d.id = rg.oxford_lead_dept_id
LEFT JOIN FunderType fut ON fut.id = rg.funder_type_id
LEFT JOIN ResearchAwardStaff ras ON ras.research_grant_id = rg.id
LEFT JOIN Employee i_e ON i_e.id = ras.employee_id

WHERE 1=1
	";
	if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($grant_title_q) $query = $query."AND rg.title LIKE '%$grant_title_q%' ";
	if($investigator_q) $query = $query."AND e.fullname LIKE '%$investigator_q%' ";
	if($e_id) $query = $query."AND e.id = $e_id ";	

	$query = $query." ORDER BY d.department_name, rg.title	";
//d_print($query);
	$table = get_data($query);

//	do not print repeated names, types and status for a committee
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		if($current_grant != $row['Grant'])
		{
			$current_grant= $row['Grant'];
			$new_grant = TRUE;
		} else $new_grant = FALSE;
		
		if(!$new_grant)
		{
			$row['Grant'] = '';
			$row['PI'] = '';
			$row['Scheme'] = '';
			$row['Funder'] = '';
			$row['Type'] = '';
		}
		$new_table[] = $row;
	}

	if($new_table) 
	{
		if ($excel_export) export2csv($new_table, "iDAISY_Grant_Report_");
		else 
		{
			if($e_id)
			{
				print "<H3>Research Grants</H3>";
			} else
			{
				print_header("Research Grant Report");
				grant_query_form(); 
				print "<HR>";
			}
			print_table($new_table, $table_width, 1);
		}
	} else print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function show_grant_details()
{
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Research Grant Report";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$rg_id = $_GET['rg_id'];								// get committee ID dp_id
	if(!$rg_id) $rg_id = $_POST['rg_id'];
	$_POST['rg_id'] = $rg_id;
	
//	print the header and stuff
	print_header("Research Grant Details");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	if($excel_export)
	{
		print "Selected Academic Year: $academic_year";
		print "<HR>";
	} else
		grant_switchboard(); 	//	print Buttons for Interface if displayed on screen only

//	get the degree programme record for a given  ID
	if(!$query)
		$query = "
			SELECT 
			rg.id AS 'RG_ID', 
			rg.title AS 'rg_title', 
			rp.title AS 'rp_title', 
			cur.currency_code AS 'currency',
			rg.amount_requested,
			rg.amount_awarded,
			rg.overhead_amount,
			DATE(rg.startdate) AS 'startdate',
			DATE(rg.enddate) AS 'enddate'
			 
			FROM ResearchGrant rg
			LEFT JOIN ResearchProject rp ON rp.id = rg.research_project_id
			LEFT JOIN Currency cur ON cur.id = rg.currency_id
			WHERE rg.id = $rg_id
			";

	$result = get_data($query);
	$rg_data = $result[0];

	show_grant_title($rg_data);
	show_grant_specs($rg_data);
	show_investigators($rg_data['RG_ID']);
}

//--------------------------------------------------------------------------------------------------------------
function show_grant_title($rg_data)
//	print title of given research grant record
{
	print "<H3>".$rg_data['rg_title']."</H3>";
}

//--------------------------------------------------------------------------------------------------------------
function show_grant_specs($rg_data)
//	print details of given research grant record
{
	$table = array();
	
	if($rg_data['rp_title'])
	{
		$row = array();
		$row[0] = 'Project:';
		$row[1] = $rg_data['rp_title'];
		$table[] = $row;
	}
	
	$row = array();
	$row[0] = 'Requested:';
	$row[1] = $rg_data['currency'] . " " . number_format($rg_data['amount_requested'], 2, '.', ',');
	$row[2] = 'Start Date:';
	$row[3] = $rg_data['startdate'];
	$table[] = $row;
		
	$row = array();
	$row[0] = 'Awarded:';
	$row[1] = $rg_data['currency'] . " " . number_format($rg_data['amount_awarded'], 2, '.', ',');
	$row[2] = 'End Date:';
	$row[3] = $rg_data['enddate'];
	$table[] = $row;
		
	$row = array();
	$row[0] = 'Overhead:';
	$row[1] = $rg_data['currency'] . " " . number_format($rg_data['overhead_amount'], 2, '.', ',');
	$table[] = $row;

/*		
	$row = array();
	$row[0] = '';
	$row[1] = $rg_data[''];
	$table[] = $row;
		
	$row = array();
	$row[2] = '';
	$row[3] = $rg_data[''];
	$table[] = $row;
		
*/		
	$tablewidth = array('0' => 150, '1' => 250, '2' => 100, '3' => 100);
	print_table($table, $tablewidth, 0);
}

//--------------------------------------------------------------------------------------------------------------
function show_investigators($rg_id)
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_POST['department_code'];								// get department_code

	$query = "
		SELECT
		e.id AS 'E_ID',
		CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Investigator',
		ras.research_award_staff_type AS 'Role',
		ras.startdate AS 'Start Date',
		ras.enddate AS 'End Date',
		ras.notes AS 'Notes'
		
		
		FROM ResearchAwardStaff ras
		INNER JOIN Employee e ON e.id = ras.employee_id
		
		WHERE ras.research_grant_id = $rg_id
		
		ORDER BY ras.research_award_staff_type, e.fullname
	";

	$investigators = get_data($query);

	$table = array();
	if($investigators) foreach($investigators AS $investigator)
	{
		$e_id = array_shift($investigator);
		
		$query = "
			SELECT 
			d.department_name AS 'Name',
			p.person_status AS 'Status'
			FROM Post p INNER JOIN Department d ON d.id = p.department_id
			WHERE p.employee_id = $e_id
		";
		$depts = get_data($query);
		
		$department = '';
		$i=0;
		if($depts) foreach($depts AS $dept)
		{
			if($i++ > 0) $department = $department . '<BR>';
			$department = $department . $dept['Name'] .' <FONT COLOR=GREY>('.$dept['Status'].')</FONT>';
		}
		$investigator['Department'] = $department;

		$table[] = $investigator;
	}
	
	$column_width = array('Investigator' => 300, 'Role' =>300, 'Start Date' => 100, 'End Date' => 100, 'Notes' =>300);
	if($table) 
	{
		print "<H4>Investigators:</H4>";
		print_table($table, $column_width,0);
	}
}

//--------------------------------------------------------------------------------------------------------------
function grant_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];
	$tc_id = $_GET["tc_id"];								// get programme ID dp_id
	if(!$tc_id) $tc_id = $_POST['tc_id'];
	

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

//	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";
	print "<TD WIDTH=400 ALIGN=LEFT></TD>";

	print "<TD WIDTH=200 ALIGN=LEFT></TD>";

	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}


?>