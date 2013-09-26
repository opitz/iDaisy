<?php

//==================================================================================================
//
//	Separate file with office holding report functions
//	Last changes: Matthias Opitz --- 2013-05-28
//
//==================================================================================================

//========================< Programme Query >=======================
//--------------------------------------------------------------------------------------------------------------
function show_office_query()
{
	if(!$_POST['excel_export'])
	{	
		office_query_form();
		if(!$_POST['query_type'] AND !$_POST['cte_id'])
		{
			print_office_intro();
//			print_office_help();
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function office_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];

	$ay_id = $_POST['ay_id'];														// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code

	$course_title_q = $_POST['course_title_q'];										// get course_title_q

	print "<form action='$actionpage' method=POST>";
	print "<input type='hidden' name='query_type' value='office'>";

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
	print start_row(0) . "Office:" . new_column(0) . office_options() . end_row();		
	print start_row(0) . "Office holder:" . new_column(0) . "<input type='text' name = 'office_holder_q' value='".$_POST['office_holder_q']."' size=50>" . end_row();		
//	print start_row(0) . "Report Type:" . new_column(0) . office_report_options() . end_row();		
	print "</TABLE>";

	print "<HR>";
//	display the option checkboxes
//	print "<TABLE BORDER=0>";
//
//	print start_row(500);		// 1st row
//		 print "List Office Holders:";
//	print new_column(60);
//		print html_checkbox('cttee_members');
//
//	print end_row();

//	print "</TABLE>";

//	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//--------------------------------------------------------------------------------------------------------------
function office_report_options()
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
function print_office_intro()
{
	$text = "
		<B>This report shows the offices and office holdings of the department.</B><BR />
		<P>	Just click on 'Go!' to get a summary list of all offices related to  the department.<BR />
	";
	
	print "<HR>";
	print "<H4><FONT FACE = 'Arial' COLOR=DARKBLUE>Introduction</FONT></H4>";
	print "<FONT FACE='Arial'>".$text."</FONT>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function print_office_help()
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
function office_report()
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$table = office_list();

//	$table = cleanup($table,1);								//removing all internally used values from the table
	
	return $table;
}

//--------------------------------------------------------------------------------------------------------------
function office_list()
//	get the list of offices
{
	$ay_id = $_POST['ay_id'];								// get academic_year_id
	$department_code = $_POST['department_code'];		// get department code

	$dp_title_q = $_POST['dp_title_q'];						// get au_title_q
	$dp_id = $_POST['dp_id'];								// get dp_id

//	get the degree programmes

//	if a single committe is selected list its members
	if($_POST['cte_id']) $_POST['cttee_members'] = TRUE;

	$query = " 
SELECT ";
if(!$department_code) $query = $query."d.department_name AS 'Department', ";
$query = $query . "off.title AS 'Office', ";
if(!$_POST['e_id']) $query = $query . "e.fullname AS 'Holder', ";
$query = $query . "
#DATE(ofh.startdate) AS 'Start Date',
#DATE(ofh.enddate) AS 'End Date',
s_t.term_code AS 'Start Term',
e_t.term_code AS 'End Term'

FROM Office off
INNER JOIN Department d ON d.id = off.department_id
LEFT JOIN  OfficeHolding ofh ON ofh.office_id = off.id
LEFT JOIN Employee e ON e.id = ofh.employee_id
LEFT JOIN Term s_t ON s_t.id = ofh.start_term_id
LEFT JOIN Term e_t ON e_t.id = ofh.end_term_id

WHERE 1=1
	";
	if($department_code AND !$_POST['e_id']) $query = $query."AND d.department_code LIKE '%$department_code%' ";
	if($_POST['off_id']) $query = $query."AND off.id = ". $_POST['off_id'];
	if($_POST['office_holder_q']) $query = $query."AND e.fullname LIKE '%".$_POST['office_holder_q'] ."%' ";
	if($_POST['e_id']) $query = $query."AND e.id = " . $_POST['e_id'];
//	if($in_year) $query = $query."AND YEAR(ofh.startdate) <= '$in_year' AND YEAR(ofh.enddate) >= '$in_year' ";
	if($in_year) $query = $query."AND YEAR(s_t.startdate) <= '$in_year' AND YEAR(e_t.enddate) >= '$in_year' ";

	$query = $query." ORDER BY d.department_name, off.title, s_t.startdate	";

//d_print($query);

	$table = get_data($query);

	return $table;
}





?>