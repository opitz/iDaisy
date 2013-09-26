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
	$member_q = $_POST['member_q'];											// get member_q

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
	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print start_row(250) . "Committee Name:" . new_column(300) . "<input type='text' name = 'cttee_title_q' value='$cttee_title_q' size=50>" . end_row();		
	print start_row(250) . "Committee:" . new_column(0) . committee_options("") . end_row();
	print start_row(250) . "Member Name:" . new_column(300) . "<input type='text' name = 'member_q' value='$member_q' size=50>" . end_row();
//	else print start_row(0) . "Member:" . new_column(0) . staff_options("") . end_row();
//print start_row(0) . "Member:" . new_column(0) . staff_options("") . end_row();
	print "</TABLE>";

	print "<HR>";

//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "List Committee Members:";
	print new_column(60);
		if ($_POST['ctte_members']) print "<input type='checkbox' name='ctte_members' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='ctte_members' value='TRUE'>";
	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
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

	$e_id = $_GET['e_id'];															// get employee ID
	if(!$e_id) $e_id = $_POST['e_id'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];

	$cttee_title_q = $_POST['cttee_title_q'];										// get cttee_title_q
	$member_q = $_POST['member_q'];											// get member_q

	$db_name = get_database_name();

//	define column width in output table
	$table_width = array('Department' =>300, 'Committee' =>350, 'Type' => 100, 'Status' => 60, 'Member' => 250);
	
//	if one specific committee has been selected switch on listing members of that committee
	if($_POST['cte_id']) $_POST['ctte_members'] = TRUE;

	$query = " 
SELECT DISTINCT ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";

if($_POST['excel_export']) $query = $query."cte.name AS 'Committee', ";
else $query = $query."CONCAT('<A HREF=$this_page?cte_id=', cte.id, '&ay_id=$ay_id&department_code=$department_code>',cte.name,'</A>') AS 'Committee', ";
$query = $query."

#cte.committee_type AS 'Type',
ctet.committee_type_name AS 'Type',
cte.temporal_status AS 'Status' ";

if ($_POST['ctte_members']) $query = $query . ",
e.fullname AS 'Member',
ctms.role AS 'Role',
DATE(ctms.startdate) AS 'Start Date',
DATE(ctms.enddate) AS 'End Date' ";
$query = $query . "

FROM Committee cte 
INNER JOIN Department d ON d.id = cte.department_id 
LEFT JOIN CommitteeType ctet ON ctet.id = cte.committee_type_id
LEFT JOIN CommitteeMembership ctms ON ctms.committee_id = cte.id
LEFT JOIN Employee e ON e.id = ctms.employee_id 

WHERE 1=1
	";
	if($department_code) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($cttee_title_q) $query = $query."AND cte.name LIKE '%$cttee_title_q%' ";
	if($_POST['cte_id']) $query = $query."AND cte.id = ".$_POST['cte_id'];
	if($member_q) $query = $query."AND e.fullname LIKE '%$member_q%' ";
	if($e_id) $query = $query."AND e.id = $e_id ";	

	$query = $query." ORDER BY d.department_name, cte.name	";
//dprint($query);
	$table = get_data($query);

//	do not print repeated names, types and status for a committee
	$new_table = array();
	if($table) foreach($table AS $row)
	{
		if($current_committee != $row['Committee'])
		{
			$current_committee= $row['Committee'];
			$new_committee = TRUE;
		} else $new_committee = FALSE;
		
		if(!$new_committee)
		{
			$row['Committee'] = '';
			$row['Type'] = '';
			$row['Status'] = '';
		}
		$new_table[] = $row;
	}

	if($new_table) 
	{
//		if ($excel_export AND !$_POST['e_id']) export2csv($new_table, "iDAISY_Committee_Report_");
		if ($excel_export AND $_POST['query_type'] == 'cttee') export2csv($new_table, "iDAISY_Committee_Report_");
		else 
//		if(1 == 1)
		{
			if($e_id)
			{
				print "<HR>";
				print "<H3>Committees</H3>";
			} else
			{
				print_header("Committee Report");
				committee_query_form(); 
				print "<HR>";
			}
			print_table($new_table, $table_width, 0);
		}
	} else if(!$e_id) print "<FONT COLOR=RED><B>The query returned no results!</B></FONT>";
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
	if($excel_export)
	{
		print "Selected Academic Year: $academic_year";
		print "<HR>";
	} else
		committee_switchboard();		//	print Buttons for Interface if displayed on screen only

//	get the degree programme record for a given  ID
	if(!$query)
		$query = "
			SELECT * 
			FROM Committee
			WHERE id = $cte_id
			";

	$result = get_data($query);
	$cttee_data = $result[0];

	show_committee_title($cttee_data);
	show_committee_members($cttee_data['id']);
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

//	select the members of the given committee
	$query = "
		SELECT DISTINCT
		e.id AS 'E_ID',
		e.fullname AS 'Member',
#		CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Member',
		cms.role AS 'Role',
		DATE(cms.startdate) AS 'Start Date',
		DATE(cms.enddate) AS 'End Date',
#		cms.re_electable AS 'Re-electable',
		cms.notes AS 'Notes'
		
		
		FROM CommitteeMembership cms
		INNER JOIN Employee e ON e.id = cms.employee_id
		INNER JOIN AcademicYear ay ON cms.startdate <= ay.enddate AND cms.enddate >= ay.startdate
		
		WHERE cms.committee_id = $cte_id ";
		if ($ay_id > 0) $query = $query . "AND ay.id = $ay_id";
		$query = $query . "
		
		ORDER BY cms.role, e.fullname, cms.startdate
	";
	$members = get_data($query);

	$table = array();
	if($members) foreach($members AS $member)
	{
		$e_id = array_shift($member);
		
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
		$member['Department'] = $department;

		$table[] = $member;
	}
	
	$column_width = array('Member' => 300, 'Role' =>300, 'Start Date' => 100, 'End Date' => 100, 'Notes' =>300);
	if($table) 
	{
		print "<H4>Members:</H4>";
		print_table($table, $column_width,0);
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