<?php

//==================================================================================================
//
//	Separate file with grant report functions
//	Last changes: Matthias Opitz --- 2013-04-23
//
//==================================================================================================

//========================< Grant Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_grant_query()
{
	if(!$_POST['excel_export'])
	{	
		grant_query_form();
		if(!$_POST['query_type'])
		{
			print_grant_intro();
//			print_grant_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function grant_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$grant_title_q = $_POST['grant_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='grant'>";

	print "<TABLE BORDER=0>";
/*
	print start_row(250);
		print "Academic Year:";
	print new_column(300);
		print academic_year_options();
*/
	if (current_user_is_in_DAISY_user_group('Divisional-Reporter')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print start_row(0) . "Degree Programme Title:" . new_column(0) . "<input type='text' name = 'dp_title_q' value='".$_POST['dp_title_q']."' size=50>" . end_row();		
//	print start_row(0) . "Grant Title:" . new_column(0) . grant_options() . end_row();		
	print start_row(250) . "Grant Title:" . new_column(0) . "<input type='text' name = 'grant_title_q' value='".$_POST['grant_title_q']."' size=50>" . end_row();		
	print start_row(0) . "PI Name:" . new_column(0) . "<input type='text' name = 'employee_q' value='".$_POST['employee_q']."' size=50>" . end_row();		
//	print start_row(0) . "Report Type:" . new_column(0) . programme_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";
//	display the option checkboxes
	print "<TABLE BORDER=0>";

	print "<TR><TD WIDTH=180 COLSPAN=3>";		// 1st row
		 print "Show line numbers:";
	print new_column(60);
		print html_checkbox('show_line_numbers');

	print end_row();

	print "</TABLE>";

	print "<HR>";


//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function print_grant_intro()
{
	$text = "<B>This report shows the grants related to staff of your department.</B><BR />
	<P>	
	Just click on 'Go!' to get a summary list of all grants related to the department.<BR />
	You can enter parts of the grant title or of the PI name to narrow down the resulting list.
	<P>
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_grant_help()
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
function grant_report()
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = grant_list();

//	$table = cleanup($table,1);					//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function grant_list()
//	get the list of grants
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code

	$dp_title_q = $_POST['dp_title_q'];				// get au_title_q
	$dp_id = $_POST['dp_id'];					// get dp_id

//	get the degree programmes

	$query = " 
SELECT DISTINCT ";
#if(strlen($department_code)<4) $query = $query."CONCAT(d.department_code,' - ',d.department_name) AS 'Department', ";
#if(strlen($department_code)<4) $query = $query."CONCAT(d2.department_code,' - ',d2.department_name) AS 'Department2', ";


if(strlen($department_code)<4) $query = $query."
IF(LENGTH(d.department_code) > 0, CONCAT(d.department_code,' - ',d.department_name), CONCAT(d2.department_code,' - ',d2.department_name)) AS 'Department',
";


$query = $query . "
e.fullname AS 'PI',
rg.title AS 'Grant Title',
DATE(rg.startdate) AS 'Start Date', 
DATE(rg.enddate) AS 'End Date', 
fs.label AS 'Scheme',
cur.currency_code AS 'Cur.',
rg.amount_requested AS 'Requested',
rg.amount_awarded AS 'Awarded', 
rg.overhead_amount AS 'Overhead',
fu.funder_name AS 'Funder'

FROM ResearchGrant rg 
INNER JOIN Funder fu ON fu.id = rg.funder_id
INNER JOIN Employee e ON e.id = rg.employee_id
INNER JOIN FundingScheme fs ON fs.id = rg.funding_scheme_id
LEFT JOIN Currency cur ON cur.id = rg.currency_id
LEFT JOIN Department d ON d.id = rg.oxford_lead_dept_id
LEFT JOIN Department d2 ON d2.id = rg.department_id
LEFT JOIN FunderType fut ON fut.id = rg.funder_type_id
LEFT JOIN ResearchAwardStaff ras ON ras.research_grant_id = rg.id
LEFT JOIN Employee i_e ON i_e.id = ras.employee_id

WHERE 1=1
	";
	if($department_code) $query = $query."AND (d.department_code LIKE '%$department_code%' OR d2.department_code LIKE '%$department_code%') ";
	if(isset($_POST['grant_title_q'])) $query = $query."AND rg.title LIKE '%" . $_POST['grant_title_q'] . "%' ";
	if(isset($_POST['employee_q'])) $query = $query."AND e.fullname LIKE '%" . $_POST['employee_q'] . "%' ";
	if($e_id) $query = $query."AND e.id = $e_id ";	

//	$query = $query." ORDER BY IF(LENGTH(d.department_code) > 0, CONCAT(d.department_code,' - ',d.department_name), CONCAT(d2.department_code,' - ',d2.department_name)), rg.enddate, e.fullname	";
	$query = $query." ORDER BY rg.startdate DESC, e.fullname	";

	deb_print($query);
	$table = get_data($query);

	return $table;
}

?>