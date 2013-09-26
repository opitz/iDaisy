<?php

//==================================================================================================
//
//	Separate file with committee report functions
//	Last changes: Matthias Opitz --- 2013-05-17
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_committee_query()
{
	if(!$_POST['excel_export'])
	{	
		cttee_query_form();
		if(!$_POST['query_type'] AND !$_POST['cte_id'])
		{
			print_cttee_intro();
//			print_cttee_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function cttee_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='cttee'>";

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
	print start_row(0) . "Committee:" . new_column(0) . committee_options() . end_row();		
	print start_row(0) . "Member Name:" . new_column(0) . "<input type='text' name = 'member_q' value='".$_POST['member_q']."' size=50>" . end_row();		
//	print start_row(0) . "Report Type:" . new_column(0) . programme_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";
//	display the option checkboxes
	print "<TABLE BORDER=0>";

//	print start_row(500);		// 1st row
	print "<TR><TD WIDTH=500 COLSPAN=3>";		// 1st row
		 print "List Committee Members:";
	print new_column(60);
		print html_checkbox('cttee_members');

	print end_row();

	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function print_cttee_intro()
{
	$text = "<B>This report shows the committees of your department and their members.</B><BR />
	<P>	
	Just click on 'Go!' to get a summary list of all committees related to  the department.<BR />
	
	<P>
	To list the members as well please select the appropriate option.<BR ?>
	Please note that when a single  committee is selected from the drop down menu the committee members will automatically be shown.
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_cttee_help()
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

//======================< Committee Report >========================
//--------------------------------------------------------------------------------------------------------------
function committee_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = cttee_list();
	if($_POST['cttee_members'])
	{
		$table = clean_repeating_cttee_rows($table);
	} 

//	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function cttee_list()
//	get the list of committees
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$dp_title_q = $_POST['dp_title_q'];						// get au_title_q
	$dp_id = $_POST['dp_id'];								// get dp_id

//	get the degree programmes

//	if a single committe is selected list its members
	if($_POST['cte_id']) $_POST['cttee_members'] = TRUE;

	$query = " 
SELECT DISTINCT ";
	if(!$department_code) $query = $query."d.department_name AS 'Department', ";

	if($_POST['excel_export']) $query = $query."cte.name AS 'Committee', ";
	else $query = $query."CONCAT('<A HREF=index.php?cte_id=', cte.id, '&ay_id=$ay_id&department_code=$department_code>',cte.name,'</A>') AS 'Committee', ";
	$query = $query."

#cte.committee_type AS 'Type1',
ctet.committee_type_name AS 'Type',
cte.temporal_status AS 'Status'
	";

	if ($_POST['cttee_members']) $query = $query . ",
e.fullname AS 'Member',
ctms.role AS 'Role',
#DATE(ctms.startdate) AS 'Start Date',
#DATE(ctms.enddate) AS 'End Date' ,
s_t.term_code AS 'Start Term',
e_t.term_code AS 'End Term'

	";

	if ($_POST['e_id']) $query = $query . ",
ctms.role AS 'Role',
#DATE(ctms.startdate) AS 'Start Date',
#DATE(ctms.enddate) AS 'End Date' ,
s_t.term_code AS 'Start Term',
e_t.term_code AS 'End Term'

	";
	$query = $query . "

FROM Committee cte 
INNER JOIN Department d ON d.id = cte.department_id 
LEFT JOIN CommitteeType ctet ON ctet.id = cte.committee_type_id
LEFT JOIN CommitteeMembership ctms ON ctms.committee_id = cte.id
LEFT JOIN Term s_t ON s_t.id = ctms.start_term_id
LEFT JOIN Term e_t ON e_t.id = ctms.end_term_id
LEFT JOIN Employee e ON e.id = ctms.employee_id 

WHERE 1=1
	";

	if($department_code AND !$_POST['e_id']) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($_POST['cte_id']) $query = $query."AND cte.id = ".$_POST['cte_id'];
	if($_POST['member_q']) $query = $query."AND e.fullname LIKE '%". $_POST['member_q'] . "%' ";
	if($_POST['e_id']) $query = $query."AND e.id = " . $_POST['e_id'];	

	$query = $query." ORDER BY d.department_name, cte.name	";

//d_print($query);

	$table = get_data($query);

	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function clean_repeating_cttee_rows($table)
//	do not print repeated names, types and status for a committee
{
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
	
	return $new_table;
}







?>