<?php

//==================================================================================================
//
//	Separate file with common Academic / Staff functions
//	Last changes: Matthias Opitz --- 2012-10-05
//
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function staff_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	
	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];
	
	print "<FORM ACTION='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<input type='hidden' name='query_type' value='staff'>";

//	print the query input fields
	print "<TABLE BORDER=0>";
	
	print start_row(250);
		print "Academic Year:" ;
	print new_column(300);
		print academic_year_options();
		if(!$_POST['ay_id']) print " <FONT COLOR =GREY>Select a year for stint values</FONT>";		
	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options($department_code) . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
	print start_row(0) . "Full Name:" . new_column(0) . "<input type='text' name = 'fullname_q' value='".$_POST['fullname_q']."' size=50>" . end_row();		
	print start_row(0) . "Forename:" . new_column(0) . "<input type='text' name = 'forename_q' value='".$_POST['forename_q']."' size=50>" . end_row();		
	if(current_user_is_in_DAISY_user_group("Overseer")) print start_row(0) . "WebAuth Code:" . new_column(0) . "<input type='text' name = 'webauth_q' value='".$_POST['webauth_q']."' size=50>" . end_row();		
	if(current_user_is_in_DAISY_user_group("Overseer")) print start_row(0) . "Employee Number:" . new_column(0) . "<input type='text' name = 'employee_nr_q' value='".$_POST['employee_nr_q']."' size=50>" . end_row();		

	print "</TABLE>";

	print "<HR>";
	
//	display the option checkboxes
	print "<TABLE BORDER=0>";
	print start_row(250);		// 1st row
		print  "Include non-academic staff:";
	print new_column(60);
		if ($_POST['non_academic']) print "<input type='checkbox' name='non_academic' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='non_academic' value='TRUE'>";
	print new_column(190);
		 print "Include borrowed staff:";
	print new_column(0);
		if ($_POST['include_borrowed_staff'])  print "<input type='checkbox' name='include_borrowed_staff' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='include_borrowed_staff' value='TRUE'>";
	print end_row();

	print start_row(0);		// 2nd row
		print "Include staff without stint:";
	print new_column(0);
		if ($_POST['include_zero_stint']) print "<input type='checkbox' name='include_zero_stint' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='include_zero_stint' value='TRUE'>";
	print new_column(0);
		print "Include inactive staff:";
	print new_column(0);
		if ($_POST['non_actv'])  print "<input type='checkbox' name='non_actv' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='non_actv' value='TRUE'>";
	print end_row();

	print start_row(0);		// 3rd row
		print "Show manually addedd staff only:";
	print new_column(0);
		if ($_POST['manual_only']) print "<input type='checkbox' name='manual_only' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='manual_only' value='TRUE'>";
	print new_column(0);
		print "Show department sums:";
	print new_column(0);
		if ($_POST['show_sums']) print "<input type='checkbox' name='show_sums' value='TRUE' checked='checked'>";
		else print "<input type='checkbox' name='show_sums' value='TRUE'>";
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

//---------------------------------------------------------------------------------------------------------------
function show_staff_list()
{
	$this_page = this_page();
	
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];													// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code
	$dept_id = get_dept_id($department_code);


	$query = "
		SELECT DISTINCT
		d.id AS 'D_ID',
		e.id AS 'E_ID',
		p.dept_stint_obligation AS 'STOB',
		p.manual AS 'P_MANUAL', ";

	if ($dept_id) $query = $query . "
		IF(d.id != $dept_id ,CONCAT('<FONT COLOR=GREY>', d.department_code,' - ', d.department_name,'</FONT>'), CONCAT(d.department_code,' - ',d.department_name)) AS 'Department', ";
	else $query = $query . "CONCAT(d.department_code,' - ',d.department_name) AS 'Department', ";

	$query = $query . "
		CONCAT('<A HREF=$this_page?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Full Name', ";

/*
	if(current_user_is_in_DAISY_user_group("Overseer")) 
		$query = $query."
			e.opendoor_employee_code AS 'Employee Number', 
			e.webauth_code AS 'WebAuth / SSO', 
			";
*/
	$query = $query."
		sc.staff_classification_name AS 'Classification',
		IF(e.email != '', CONCAT('<A HREF=mailto:', e.email, '>', e.email, '</A>'), '') AS 'Email',
		p.person_status AS 'Status'

		FROM Employee e
		LEFT JOIN Post p on p.employee_id = e.id
		LEFT JOIN Department d ON d.id = p.department_id
		LEFT JOIN StaffClassification sc ON sc.id = p.staff_classification_id

		LEFT JOIN PostOtherDepartment pod ON pod.post_id = p.id
		LEFT JOIN Department od ON od.id = pod.other_department_id 

		WHERE 1 = 1 
	";

	if(!$_POST['non_academic']) $query = $query."AND sc.staff_classification_code LIKE 'A%'";

	if($_POST['fullname_q']) $query = $query."AND e.fullname LIKE '".$_POST['fullname_q']."%' ";
	if($_POST['forename_q']) $query = $query."AND e.forename LIKE '%".$_POST['forename_q']."%' ";
	if($_POST['webauth_q']) $query = $query."AND e.webauth_code LIKE '%".$_POST['webauth_q']."%' ";
	if($_POST['employee_nr_q']) $query = $query."AND e.opendoor_employee_code LIKE '%".$_POST['employee_nr_q']."%' ";

	if(!$_POST['non_actv']) $query = $query."AND p.person_status = 'ACTV' ";
	if($_POST['manual_only']) $query = $query."AND e.manual = '1' ";
	if($department_code)
	{ 
		if($_POST['include_borrowed_staff']) $query = $query."AND ((SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND p2.person_status = p.person_status AND d2.department_code LIKE '$department_code%') OR od.department_code LIKE '$department_code%')";
		else $query = $query."AND (SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND p2.person_status = p.person_status AND d2.department_code LIKE '$department_code%') ";
	}
	$query = $query."ORDER BY e.fullname";
	
	$table = get_data($query);

	if(!$excel_export)
	{		
		print_header('Academic Overview');

		staff_query_form(); 
		print "<HR>";
	}

	$new_table = array();

	$dept_stob = 0;
	$dept_teaching_stint = 0;
	$dept_supervision_stint = 0;
			
//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
		$d_id = $row['D_ID'];
		$e_id = $row['E_ID'];
		$stob = $row['STOB'];
		$p_man = $row['P_MANUAL'];

		$is_lent = is_lent($e_id, $d_id);
		$is_borrowed = is_borrowed($e_id, $dept_id);

//	if an academic year was selected do some more...
		if($ay_id > 0) 
		{			
//	get leave reduction and show netto Stint Obligation
			if ($is_borrowed)
				$row['StOb'] = '--';
			else
			{
				if($d_id) $leave_reduction_factor = get_leave_reduction($e_id, $ay_id, $d_id);
				if($leave_reduction_factor) 
					$row['StOb'] = round($stob - $stob * $leave_reduction_factor,2);
//					$row['StOb'] = $stob - $stob * $leave_reduction_factor;
				else
					$row['StOb'] = $stob;
				if($d_id == $dept_id) $dept_stob = $dept_stob + $row['StOb'] ;
			}

			if($d_id AND !$is_borrowed)
			{
				$teaching_stint = get_teaching_stint($e_id, $ay_id, $d_id);
				$supervision_stint = get_supervision_stint($e_id, $ay_id, $d_id);
				if($d_id == $dept_id) 	// count stint sums for the selected department only
				{
					$dept_teaching_stint = $dept_teaching_stint + $teaching_stint;
					$dept_supervision_stint = $dept_supervision_stint + $supervision_stint;
				}
			} else
			{
				$teaching_stint = get_borrowed_teaching_stint($e_id, $ay_id, $dept_id);
				$supervision_stint = get_borrowed_supervision_stint($e_id, $ay_id, $dept_id);

				$dept_teaching_stint = $dept_teaching_stint + $teaching_stint;
				$dept_supervision_stint = $dept_supervision_stint + $supervision_stint;
			}
			$st_total = $teaching_stint + $supervision_stint;
//			$row['St Teach'] = $teaching_stint;
//			$row['St Superv'] = $supervision_stint;
			$row['Stint'] = "<B>".round($st_total,2)."</B>";
			$balance = $st_total - $row['StOb'];
			if($balance < 0) $colour = 'RED';
			else $colour = 'GREEN';
			$row['Balance'] = "<B><FONT COLOR=$colour>".round($balance,2)."</FONT></B>";
		}
			
		$notes = '';
		if($is_lent) $notes = "lent";
		if($is_borrowed) $notes = "borrowed";
		if($row['P_MANUAL']) 
			if($notes) $notes = $notes . ", manual";
			else $notes = "manual";
		$row['Remarks'] = $notes;
	
		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...

		if($_POST['include_zero_stint'] OR !$ay_id ) $new_table[] = $row;
		else	 if($st_total) $new_table[] = $row;
	}
	if($_POST['show_sums'])
	{
//	add a blank row
		$row ['Department'] = "";
		$row ['Full Name'] = "";
		$row ['Employee Number'] = "";
		$row ['WebAuth / SSO'] = "";
		$row ['Email'] = "";
		$row ['Status'] = "";
		$row ['StOb'] = "";
		$row ['Stint'] = "";
		$row ['Balance'] = "";
		$row ['Remarks'] = "";
		$new_table[] = $row;

//	add the summary rows
		$department = get_dept_code($dept_id)." - ".get_dept_name_from_id($dept_id);
		$dept_stint_sum = $dept_teaching_stint + $dept_supervision_stint;
		$dept_balance = $dept_stint_sum - $dept_stob;
		if($dept_balance < 0) $colour = 'RED';
		else $colour = 'GREEN';
	
		$row ['Department'] = "<U><B>$department:</B></U>";
		$row ['Full Name'] = "<U><B>Sums</B></U>";
		$row['StOb'] = "<U><B>".round($dept_stob,2)."</B></U>";
		$row['Stint'] = "<U><B>".round($dept_stint_sum,2)."</B></U>";
		$row['Balance'] = "<U><B><FONT COLOR=$colour>".round($dept_balance,2)."</FONT></B></U>";
		$new_table[] = $row;
	}

//	now do the output
	$table_width = array('Status' => 50, 'Notes' => 80, 'StOb' => 50, 'Stint' => 50, 'Balance' => 50);
	$column_width = array('Status' => 50, 'Notes' => 80, 'StOb' => 50, 'Stint' => 50, 'Balance' => 50);
//	if ($excel_export AND $ay_id) export2excel($new_table, $export_title);
	if ($excel_export) export2csv($new_table, "iDAISY_Academic_Report_");
//	if ($excel_export) dprint('huhu!');
	else print_table($new_table, $column_width, TRUE);
}

//---------------------------------------------------------------------------------------------------------------
function show_staff_details()
{
	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
//	if still no e_id get webauth user id and write it back to $_POST
	if(!$e_id) $e_id = get_employee_id_from_webauth(current_user_webauth());
	$_POST['e_id'] = $e_id;

//	get the employee record for a given employee ID
	$query = "SELECT * FROM Employee WHERE id = $e_id";

	$result = get_data($query);
	$e_data = $result[0];
	
	$date = date('l jS \of F Y g:i:s');
	$e_fullname = $e_data['fullname'];
	
	if ($_POST['excel_export'])
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$export_date = date('y-m-d');
		$excel_title = "Stint Report for $e_fullname on $export_date";
		excel_header($excel_title);
	}

	print_header("Staff Details for <FONT COLOR=DARKBLUE>$e_fullname</FONT>");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: <B>$academic_year</B>";
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$_POST['excel_export'])
		staff_switchboard();

	show_employee_details($e_data);
	$e_posts = show_post_details();
	show_borrowed_post_details();
 	print "<HR>";
	if($_POST['show_teaching']) show_teaching_details_by_year($e_posts);
	if($_POST['show_supervising']) show_supervising_details_by_year($e_posts);
	if($_POST['show_leave']) show_leave_details_by_year();
	if($_POST['show_publication']) show_publication_details();
}

//---------------------------------------------------------------------------------------------------------------
function show_employee_details($e_data)
{
//	find out if the logged in user is allowed to edit data in DAISY
	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 

	$is_editor = current_user_is_in_DAISY_user_group("Overseer");
	
	$e_id = $e_data['id'];
	$e_webauth_code = $e_data['webauth_code'];

//	get DAISY user roles
	$roles = get_DAISY_roles($e_webauth_code);
	
	if($roles)
	{
		$user_text = 'DAISY Role(s):';
		$user_role = '';
		foreach($roles AS $role)
		{
			if($user_role != '') $user_role = $user_role.", ";
			$user_role = $user_role.$role['User Type'];
		}
	}
	
	if($e_data['manual'] == '1') $manual = 'Manual';
//	print details from a given Employee record
	print "<TABLE BORDER=0>";
	print "<TR><TD>";
//	print "<H3>Employee Details</H3>";
	print "<H3>".$e_data['title']." ".$e_data['forename']." ".$e_data['initials']." ".$e_data['surname']."</H3>";
	print "</TD><TD>";
//	print "&nbsp;";
//	print "</TD><TD>";
	if($user_text) print "($user_role $super_admin) ";
	if($manual) print "<FONT COLOR=#FF6600>(manually) </FONT>";
	if($is_editor AND !$excel_export) print "<FONT SIZE=2>-> <A HREF='https://daisy.socsci.ox.ac.uk/employee/".$e_data['id']."/edit' TARGET=NEW>Edit in DAISY</A></FONT><P>";
	print "</TD></TR>";
//	print "<TR><TD></TD><TD></TD></TR>";
	print "</TABLE>";

	print "<TABLE BORDER=0>";
	
	print "<TR>";
//	print 	"<TD WIDTH=180><B>Name:</B></TD>";
//	print 	"<TD WIDTH = 300>".$e_data['title']." ".$e_data['forename']." ".$e_data['initials']." ".$e_data['surname']."</TD>";
//	print 	"<TD WIDTH = 50></TD>";
//	print 	"<TD WIDTH = 120><B>$user_text</B></TD>";
//	print 	"<TD WIDTH = 300>$user_role $super_admin</TD>";
//	print "</TR><TR>";

//	print 	"<TD WIDTH=180><B>$user_text</B></TD>";
//	print 	"<TD WIDTH = 300>$user_role $super_admin</TD>";
//	print 	"<TD WIDTH = 50></TD>";
//	print 	"<TD WIDTH = 120><B>$user_text</B></TD>";
//	print 	"<TD WIDTH = 300>$user_role $super_admin</TD>";
//	print "</TR><TR>";

	print 	"<TD WIDTH=180><B>Employee Number:</B> </TD>";
	print 	"<TD>".$e_data['opendoor_employee_code']."</TD>";
	if(current_user_is_in_DAISY_user_group("Super-Administrator"))
	{
		print 	"<TD WIDTH = 50></TD>";
		print 	"<TD WIDTH = 120><B>Webauth ID:</B></TD>";
		print 	"<TD WIDTH = 300>".$e_data['webauth_code']."</TD>";
	}
	print "</TR><TR>";
	print 	"<TD WIDTH=120><B>Email:</B> </TD>";
	print 	"<TD><A HREF='mailto:".$e_data['email']."'>".$e_data['email']."</A></TD>";
	if(current_user_is_in_DAISY_user_group("Overseer"))
	{
		print 	"<TD WIDTH = 50></TD>";
		print 	"<TD WIDTH = 120><B>DAISY ID:</B></TD>";
		print 	"<TD WIDTH = 300>".$e_data['id']."</TD>";
	}
//	print "</TR><TR>";
//	if($manual) print "<TD><I>Staff added manually</I></TD>";
	print "</TR>";
	


//	print "<TR><TD><A HREF='https://daisy.socsci.ox.ac.uk/employee/".$e_data['id']."/edit' TARGET=NEW>Edit in DAISY</A></TD></TR>";
	print "</TABLE>";
//	print "<HR>";
}

//---------------------------------------------------------------------------------------------------------------
function show_post_details()
//	print teaching details for a given Employee ID and Academic Year
{
	$ay_id = $_GET['ay_id'];														// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];															// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];												// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);
	$borrowed = employee_is_borrowed($e_id, $dept_id);
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Administrator");
	$title_printed = FALSE;
	
//	select the data
	$query = "
		SELECT
		d.id AS 'D_ID',
		p.id AS 'P_ID',
#		d.department_name AS 'Department',
		CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
		sc.staff_classification_name AS 'Staff Class.',
		IF(p.manual = 1, 'YES','') AS 'Manual',
		p.person_status AS 'Status'";
		if(!$borrowed) $query = $query.",
		p.grade AS 'Grade', 
		p.dept_stint_obligation AS 'Stint Obl.'";
		$query = $query."
		
		FROM Post p
		INNER JOIN Department d ON d.id = p.department_id
		LEFT JOIN StaffClassification sc ON sc.id = p.staff_classification_id

		LEFT JOIN SSDCategory ssdc ON ssdc.id = p.ssd_category_id
		
		";
//		if($ay_id > 1) $query = $query."
//		LEFT Join EmployeeAcademicLeave eal ON eal.employee_id = p.employee_id AND t.id IN (SELECT t2.id from Term t2 WHERE t2.academic_year_id = $ay_id)
//		";
		$query = $query."
		
		WHERE p.employee_id = $e_id
		ORDER BY d.department_code
		";
		
//print "<HR>$query<HR>";		

	$table = get_data($query);

//	now do for each post (= table row)
	if($table)
	{
		$e_posts = array();		// the array for information about the post(s) of an employee
		$new_table = array();
		$st_total = 0;
		foreach($table AS $row)
		{
			if($row['Status'] == 'ACTV')
			{
				$new_row = array();
				$p_id = $row['P_ID'];
				$new_row['D_ID'] = $row['D_ID'];
				$new_row['Department'] = $row['Department'];
				$stob = $row['Stint Obl.'];
				if(!$borrowed) $new_row['StOb'] = $stob;
				
				if($ay_id > 0)
				{
					if(!$borrowed) 
					{
						$leave_reduction_factor = get_leave_reduction($e_id, $ay_id, $new_row['D_ID']);
						if($leave_reduction_factor) 
							$row['Act.StOb'] = round($stob - $stob * $leave_reduction_factor,2);
						else
							$row['Act.StOb'] = $stob;
					}

					if(!$borrowed) $teaching_stint = get_teaching_stint($e_id, $ay_id, $new_row['D_ID']);
					else $teaching_stint = get_borrowed_teaching_stint($e_id, $ay_id, $dept_id);
					$row['T Stint'] = $teaching_stint;
					if(!$borrowed) $supervision_stint = get_supervision_stint($e_id, $ay_id, $new_row['D_ID']);
					else $supervision_stint = get_borrowed_supervision_stint($e_id, $ay_id, $dept_id);
					$row['S Stint'] = $supervision_stint;
					$stint_total = $teaching_stint + $supervision_stint;
					if(!$borrowed) 
					{
						$balance = $stint_total - $row['Act.StOb'];
						if($balance < 0) $colour = 'RED';
						else $colour = 'GREEN';
						$row['Balance'] = "<B><FONT COLOR=$colour>".$balance."</FONT></B>";
					}
				} 
				
				if(!$borrowed) $new_row['CorrStOb'] = $row['Corr. Obl.'];
				$e_posts[] = $new_row;
			}else
			{
				$row['Act.StOb'] = '--';
				$row['T Stint'] = '--';
				$row['S Stint'] = '--';
				$row['Balance'] = '--';
				}		

//			if($is_editor) $row['DAISY'] = "<A HREF=https://daisy.socsci.ox.ac.uk/post/$p_id/edit TARGET=NEW>Edit</A>";
			
			if(current_user_is_in_DAISY_user_group("Administrator")) $query = $query.", CONCAT('<A HREF=https://daisy.socsci.ox.ac.uk/post/', p.id, '/edit TARGET=NEW>Edit</A>') AS 'DAISY'";

			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			$new_table[] = $row;
		}

		if(current_user_is_in_DAISY_user_group("Administrator") OR !current_user_is_in_DAISY_user_group("Editor"))
		{
			print "<HR><H3>Post Details</H3>";
			if($borrowed) print "<FONT COLOR=#FF6600><B>This is a borrowed staff member.</B><BR>All details on this page will show only stint values related to the borrowing department.<BR>
			Only department(s) the staff member has a post with will see the complete report.<P /></FONT>";

//			define column width in table
			$table_width = array('Status' => 50, 'Department' => 300, 'Staff Class.' => 450);

			print_table($new_table, $table_width, FALSE);
		}
		return $e_posts;
	} else return FALSE;
}

//---------------------------------------------------------------------------------------------------------------
function show_borrowed_post_details()
//	print teaching details for a given Employee ID
{
	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
	$title_printed = FALSE;
	
//	define column width in table
	$table_width = array('Department' => 300);

//	select the data
	$query = "
		SELECT
#		d.department_name AS 'Department'
		CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
		p.person_status AS 'Status'
		
		FROM Post p
		INNER JOIN PostOtherDepartment pod on pod.post_id = p.id
		LEFT JOIN Department d ON d.id = pod.other_department_id
		LEFT JOIN StaffClassification sc ON sc.id = p.staff_classification_id
		
		WHERE p.employee_id = $e_id
		ORDER BY d.department_code
		";
	$table = get_data($query);

	if($table)
	{
		print "<H4>Borrowed by</H4>";
		print_table($table, $table_width, FALSE);
	}
}

//---------------------------------------------------------------------------------------------------------------
function show_teaching_details_by_year($e_posts)
//	print teaching details / stint for a given Employee ID and given Academic Year
{
	$given_ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];															// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];												// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id
	$this_page = this_page();

	$borrowed = employee_is_borrowed($e_id, $dept_id);
	
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Editor");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width = array('Department' => 300, 'Subject' => 450, 'Type' => 350, 'Tariff' => 50, 'Term' => 60, 'Sess.' => 60, 'Perc.' => 60, 'Stint' => 60);

//	set up a new array that takes the department ID(s) the employee has a post with
	$total_stob = 0;
	$e_post_d_id = array();
	foreach($e_posts AS $e_post)
	{
		$e_post_d_id[] = $e_post['D_ID'];
		$total_stob = $total_stob + $e_post['StOb'];
	}

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
	
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
//	select the data
		$query = "
			SELECT DISTINCT
			ti_d.id AS 'TI_D_ID',
			tc_d.id AS 'TC_D_ID',
			ti.id AS 'TI_ID',
			tc.id AS 'TC_ID',

			CONCAT(ti_d.department_code, ' - ', ti_d.department_name) AS 'Department',
			CONCAT('<A HREF=$this_page?tc_id=',tc.id,'&ay_id=$ay_id&department_code=$department_code>',tc.subject,'</A>') AS 'Subject',

			tct.title AS 'Type',
			tctt.stint AS 'Tariff',

			t.term_code AS 'Term',
			ti.sessions AS 'Sess.',
			ti.percentage AS 'Perc.',

			ROUND((ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

			FROM TeachingInstance ti
			INNER JOIN Term t ON t.id = ti.term_id
			INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id

			LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND IF((SELECT COUNT(*) FROM TeachingComponentAssessmentUnit tcau2 WHERE tcau2.teaching_component_id = tc.id AND tcau2.academic_year_id = t.academic_year_id) > 1 ,tcau.assessment_unit_id != 99999, 1=1) AND tcau.academic_year_id = t.academic_year_id

			LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
			LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id

			INNER JOIN Department ti_d ON ti_d.id = ti.department_id
			INNER JOIN Department tc_d ON tc_d.id = tc.department_id		
		
			WHERE ti.employee_id = $e_id
			AND t.academic_year_id = $ay_id ";
			if($borrowed) $query = $query."AND ti.department_id = $dept_id";
			$query = $query."

			ORDER BY ti_d.department_code, t.startdate, tc.subject
			";

//dprint($query);
		$table = get_data($query);
		
//	for each component
		$sum_stint = array();		//we will sum the stint earned by department in this array
		$new_table = array();
		$tc_row = array();
		if($table) foreach($table AS $row)
		{
			$tc_d_id = $row['TC_D_ID'];
			$ti_d_id = $row['TI_D_ID'];
			$ti_id = $row['TI_ID'];
//	sum up the stints earned by department
			$dept = $row['Department'];
			
			if(in_array($ti_d_id, $e_post_d_id)) $row['Stint Calc'] = 'Post';
			else 
				if(count($e_posts)>1) $row['Stint Calc'] = 'Other Split';
				else $row['Stint Calc'] = 'Other';
				
			if(in_array($ti_d_id, $e_post_d_id)) $sum_stint["$dept"] = $sum_stint["$dept"] + $row['Stint'];
			else
			{
				foreach($e_posts AS $e_post)
				{
					$dept = $e_post['Department'];
					if($total_stob > 0) $stint_part = $row['Stint'] * $e_post['StOb'] / $total_stob;
					else $stint_part = $row['Stint'] / count($e_posts);		// if there is not total StOb at all simply divide by the number of posts
					$sum_stint["$dept"] = $sum_stint["$dept"] + $stint_part;
				}
			}

//	Display a DAISY edit link for each instance
			if($is_editor) $row['DAISY'] = "<A HREF=https://daisy.socsci.ox.ac.uk/teaching_instance/$ti_id/edit TARGET=NEW>Edit</A>";
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the TI_ID that is too ugly to display...

//	Only display information for a new TC, otherwise blank out repetitive TC information and show only AU related data
/*
			if($row['Subject'] != $tc_row['Subject']) $tc_row = $row;		//remember the first row for a TC
			else 
			{
				$row['Term'] = '';
				$row['Subject'] = '';
				$row['Type'] = '';
				$row['Sess.'] = '';
				$row['Tariff'] = '';
			}
*/
			$new_table[] = $row;
		}

//	Put in a row before the summary row
        $row['Term'] = "";
        $row['Subject'] = "";
        $row['Type'] = "";
        $row['Tariff'] = "";
        $row['Sess.'] = "";
        $row['Perc.'] = "";
        $row['Students'] = "";
        $row['Assessment Unit'] = "";
        $row['DAISY'] = "";
        $row['Stint Calc'] = "";

        $row['Department'] = "<HR>";
        $row['Stint'] = "<HR>";

		if($table) $new_table[] = $row;

        $row['Department'] = "";
        $row['Stint'] = "";

//	Put in the summary row
        $array_keys = array_keys($sum_stint);
        $total_stint = 0;
        foreach($array_keys as $dept)
        {
//         	$row['Term'] = "";
           	$row['Department'] = "<B>".$dept."</B>";
        	$row['Stint'] = "<B>".$sum_stint[$dept]."</B>";
        	$total_stint = $total_stint + $sum_stint[$dept];
//        	print $dept." - ".$sum_stint[$dept]."<BR>";
			$new_table[] = $row;
		}
		if(sizeof($sum_stint) > 1)
		{
        	$row['Department'] = "<B><U>Total</U></B>";
        	$row['Stint'] = "<B><U>".$total_stint."</U></B>";
			$new_table[] = $row;
		}

//	Now print the table
		if($new_table)
		{
			if(!$title_printed)
			{
				print "<HR><H3>Teaching Details</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, FALSE);			
		}
	}
}

//---------------------------------------------------------------------------------------------------------------
function show_supervising_details_by_year($e_posts)
//	print supervising details / stint for a given Employee ID and academic year
{
	$given_ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];															// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];												// if not GET then try POST
	
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$borrowed = employee_is_borrowed($e_id, $dept_id);
	
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Overseer");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width = array('Department' => 300, 'Student' => 450, 'Type' => 350, 'OGY' => 50, 'Term' => 50, 'MT (%)' => 60, 'HT (%)' => 60, 'TT (%)' => 60, 'Stint' => 60);

//	set up a new array that takes the department ID(s) the employee has a post with
	$total_stob = 0;
	$e_post_d_id = array();
	if($e_posts) foreach($e_posts AS $e_post)
	{
		$e_post_d_id[] = $e_post['D_ID'];
		$total_stob = $total_stob + $e_post['StOb'];
	}
	
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
		
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		$rowcount = 0;
//		$table = FALSE;
		
//	select the student data - including Degree Programme = SLOW!!
/*
		$query = "
			SELECT DISTINCT
#			sv.id AS 'SV_ID',
			d.id AS 'SV_D_ID',
			dp.id AS 'DP_ID',
			st.id AS 'ST_ID',
			svt.id AS 'SVT_ID',
			CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
			CONCAT('<A HREF=student_details.php?st_id=', st.id, '>', st.surname, ', ', st.forename, '</A>') AS 'Student',
			dp.title AS 'Degree Programme',
#			svt.title AS 'Type',
			svt.supervision_type_code AS 'Type',
			sdp.oxford_graduate_year as 'OGY',
			sv.oxford_graduate_year as 'OGY2'
			
			FROM Supervision sv
			INNER JOIN Term t ON t.id = sv.term_id
			INNER JOIN AcademicYear ay ON ay.id = t.academic_year_id
			LEFT JOIN Student st ON st.id = sv.student_id
#			LEFT JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = ay.id AND (sdp.status = 'INACTIVE' OR sdp.status = 'ENROLLED' OR sdp.status = 'COMPLETED')
#			LEFT JOIN StudentDegreeProgramme sdp ON sdp.student_id = st.id AND sdp.academic_year_id = ay.id AND (sdp.status = 'INACTIVE' OR sdp.status = 'ENROLLED' OR sdp.status = 'INTERMIT')
			INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 
			INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id AND dp.degree_programme_type != 'UGRAD'
			
			LEFT JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
			
			INNER JOIN Department d ON d.id = sv.department_id
			
			WHERE sv.employee_id = $e_id
			AND ay.id = $ay_id
			AND sdp.oxford_graduate_year > 0 ";
			if($borrowed) $query = $query. "AND sv.department_id = $dept_id ";
			$query = $query."
			ORDER BY d.department_code, st.surname, st.forename, svt.title
			";
*/

//	select the student data w/o Degree Programme = FAST!
		$query = "
			SELECT DISTINCT
#			sv.id AS 'SV_ID',
			d.id AS 'SV_D_ID',
			st.id AS 'ST_ID',
			svt.id AS 'SVT_ID',
			CONCAT(d.department_code,' - ',d.department_name) AS 'Department', ";
			
			if(current_user_is_in_DAISY_user_group("Super-Administrator") OR (current_user_is_in_DAISY_user_group("Super-Administrator") AND employee_is_affiliated($e_id, $dept_id))) 
			$query = $query."CONCAT('<A HREF=student_details.php?st_id=', st.id, '&department_code=$department_code>', st.surname, ', ', st.forename, '</A>') AS 'Student', ";
			else 
			$query = $query."CONCAT(st.surname, ', ', st.forename) AS 'Student', ";
			$query = $query."
			svt.supervision_type_code AS 'Type',
			sv.oxford_graduate_year as 'OGY'
			
			FROM Supervision sv
			INNER JOIN Term t ON t.id = sv.term_id
			INNER JOIN AcademicYear ay ON ay.id = t.academic_year_id
			INNER JOIN Student st ON st.id = sv.student_id
			
			LEFT JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
			
			INNER JOIN Department d ON d.id = sv.department_id
			
			WHERE sv.employee_id = $e_id
			AND ay.id = $ay_id ";
//			$query = $query."AND sv.oxford_graduate_year > 0 ";
			if($borrowed) $query = $query. "AND sv.department_id = $dept_id ";
			$query = $query."
			ORDER BY d.department_code, svt.title, st.surname, st.forename
			";

		$table = get_data($query);
	
//	for each supervision
		$sum_stint = array();		//we will sum the stint earned by department in this array
		$new_table = array();
		if($table) foreach($table AS $row)
		{
//	get the terms and their percentages per student
			$st_id = $row['ST_ID'];
//			$st_dp_id = $row['DP_ID'];
			$svt_id = $row['SVT_ID'];
			$sv_d_id = $row['SV_D_ID'];
//	sum up the stints earned by department
			$dept = $row['Department'];			

//			$st_dp = $row['Degree Programme'];

//	get the terms for which a student is supervised and the percentages
/*
			$query = "
				SELECT
				sv.id AS 'SV_ID',
				t.term_code AS 'Term',
				sv.percentage AS 'Percentage',
				svtt.stint AS 'Tariff',
				IF(sdp.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sdp.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0)) AS 'Stint'				

				FROM Supervision sv
				INNER JOIN Term t ON t.id = sv.term_id
#				INNER JOIN AcademicYear ay ON ay.id = t.academic_year_id
#				LEFT JOIN Student st ON st.id = sv.student_id
				LEFT JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND (sdp.status = 'INACTIVE' OR sdp.status = 'ENROLLED' OR sdp.status = 'COMPLETED')
#				INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND sdp.status = 'ENROLLED'
#				INNER JOIN DegreeProgramme dp ON dp.id = sdp.degree_programme_id AND dp.degree_programme_type != 'UGRAD'
				
				INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
				INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

				WHERE sv.employee_id = $e_id
				AND sv.student_id = $st_id
				AND sdp.degree_programme_id = '$st_dp_id'
				AND t.academic_year_id = $ay_id
				AND svt.id = $svt_id
				ORDER BY t.startdate
				";
*/
			if($st_id) $query = "
				SELECT
				sv.id AS 'SV_ID',
				t.term_code AS 'Term',
				sv.percentage AS 'Percentage',
				svtt.stint AS 'Tariff',
				IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0)) AS 'Stint'				

				FROM Supervision sv
				INNER JOIN Term t ON t.id = sv.term_id
				
				INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
				INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

				WHERE sv.employee_id = $e_id
				AND sv.student_id = $st_id
				AND t.academic_year_id = $ay_id
				AND svt.id = $svt_id
				ORDER BY t.startdate
				";

			$terms = get_data($query);

			$row['MT (%)'] = '';
			$row['HT (%)'] = '';
			$row['TT (%)'] = '';

			$student_supervision_stint = 0;
			if($terms) foreach($terms AS $term)
			{
				$row[substr($term['Term'],0,2).' (%)'] = $term['Percentage'];
				$student_supervision_stint = $student_supervision_stint + $term['Stint'];
				$sv_id = $term['SV_ID'];
			}
			
//			$new_table[$rowcount++] = array_merge(array_slice($row, 1), $term_table);
			$row['Stint'] = round($student_supervision_stint, 2);
			
//	mark the stint calculation
			if(strstr($row['Type'], 'DPhil')) $row['Stint Calc'] = 'DPhil';
			else
				if(in_array($sv_d_id, $e_post_d_id)) $row['Stint Calc'] = 'Post';
				else 
					if(count($e_posts)>1) $row['Stint Calc'] = 'Other Split';
					else $row['Stint Calc'] = 'Other';
				
//	do the stint calculation
			if(in_array($sv_d_id, $e_post_d_id) AND !strstr($row['Type'], 'DPhil'))  // any teaching and supervision - except DPhil - for a post dept counts towards that StOb
				$sum_stint["$dept"] = $sum_stint["$dept"] + $student_supervision_stint;
			else	//	the stint is split between post depts according to their StOb
			{
				if($e_posts) foreach($e_posts AS $e_post)
				{
					$dept = $e_post['Department'];
					if($total_stob > 0) $stint_part = $student_supervision_stint * $e_post['StOb'] / $total_stob;
					else $stint_part = $student_supervision_stint / count($e_posts);		// if there is not total StOb at all simply divide by the number of posts
					$sum_stint["$dept"] = $sum_stint["$dept"] + $stint_part;
				}
			}
			
//			$sum_stint["$dept"] = $sum_stint["$dept"] + $student_supervision_stint;
//	link to DAISY
			if($is_editor) $row['DAISY'] = "<A HREF=https://daisy.socsci.ox.ac.uk/supervision/".$sv_id."/edit TARGET=NEW>Edit</A>";

			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
//			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			$new_table[] = $row;
		}

//	now calculate the sums and add it to the table

        $row['Student'] = "";
        $row['Degree Programme'] = "";
        $row['Type'] = "";
        $row['OGY'] = "";
        $row['MT (%)'] = "";
        $row['HT (%)'] = "";
        $row['TT (%)'] = "";
        $row['Stint Calc'] = "";
        if($is_editor) $row['DAISY'] = "";

        $row['Department'] = "<HR>";
        $row['Stint'] = "<HR>";

		if($table) $new_table[] = $row;

        $row['Department'] = "";
        $row['Stint'] = "";

        $array_keys = array_keys($sum_stint);
        $total_stint = 0;
        foreach($array_keys as $dept)
        {
//         	$row['Term'] = "";
           	$row['Department'] = "<B>".$dept."</B>";
        	$row['Stint'] = "<B>".ROUND($sum_stint[$dept], 2)."</B>";
        	$total_stint = $total_stint + ROUND($sum_stint[$dept],2);
//        	print $dept." - ".$sum_stint[$dept]."<BR>";
			$new_table[] = $row;
		}
		if(sizeof($sum_stint) > 1)
		{
        	$row['Department'] = "<B><U>Total</U></B>";
        	$row['Stint'] = "<B><U>".$total_stint."</U></B>";
			$new_table[] = $row;
		}

		if($row AND $new_table)
		{
			if(!$title_printed)
			{
				print "<HR><H3>Supervising Details</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($new_table, $table_width, FALSE);
		}
	}
}

//---------------------------------------------------------------------------------------------------------------
function show_leave_details_by_year()
//	print leave details for a given Employee ID
{
	$given_ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$given_ay_id) $given_ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];															// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];												// if not GET then try POST
	
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Editor");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width = array('Term' => 50, 'Type' =>350);

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
		
	foreach($ac_years AS $ac_year)
	{
		$ay_id = $ac_year['id'];
		
//	select the data
		$query = "
			SELECT
			CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
			t.term_code AS 'Term',
			lt.leave_type_name AS 'Type',
			eal.leave_percentage AS '%' ";

			if($is_editor) $query = $query.", 
			CONCAT('<A HREF=https://daisy.socsci.ox.ac.uk/employee_academic_leave/', eal.id, '/edit TARGET=NEW>Edit in DAISY</A>') AS 'Edit'";

			$query = $query."
			FROM EmployeeAcademicLeave eal
			INNER JOIN Term t ON t.id = eal.term_id
			INNER JOIN Department d ON d.id = eal.department_id
			LEFT JOIN LeaveType lt ON lt.id = eal.leave_type_id
					
			WHERE eal.employee_id = $e_id
			AND t.academic_year_id = $ay_id
			ORDER BY d.department_code, t.startdate
			";
		
		$table = get_data($query);
		if($table)
		{
			if(!$title_printed)
			{
				print "<HR><H3>Leave Details</H3>";
				$title_printed = TRUE;
			}
			print"<H4>".$ac_year['label']."<H4>";
			print_table($table, $table_width, FALSE);
		}
	}
}

//---------------------------------------------------------------------------------------------------------------
function show_publication_details()
//	print publication details for a given Employee ID
{
	$e_id = $_GET["e_id"];															// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];												// if not GET then try POST
	
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Editor");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Title'] = 750;
	$table_width['Type'] = 150;
	$table_width['Publisher'] = 150;

//	select the data
	$query = "
		SELECT
		d.department_name AS 'Department',
		pub.title AS 'Title',
		pt.publication_type_name AS 'Type',
		pub.ISBN,
		pl.publisher_name AS 'Publisher',
		YEAR(pub.publicationdate) AS 'Year',
		j.journal_name AS 'Journal',
		rr.label AS 'REF Rank'";

		if($is_editor) $query = $query.",
		CONCAT('<A HREF=https://daisy.socsci.ox.ac.uk/publication/', pub.id, '/edit TARGET=NEW>Edit in DAISY</A>') AS 'Edit'";

		$query = $query."
		FROM Publication pub
		INNER JOIN PublicationEmployee pube on pube.publication_id = pub.id
		INNER JOIN Department d ON d.id = pub.department_id
		
		LEFT JOIN RefRank rr ON rr.id = pube.ref_rank_id
		
		LEFT JOIN Publisher pl ON pl.id = pub.publisher_id
		LEFT JOIN PublicationType pt ON pt.id = pub.publication_type_id
		LEFT JOIN Journal j ON j.id = pub.journal_id
		
		WHERE pube.employee_id = $e_id
		ORDER BY rr.label, d.department_name ASC, pub.publicationdate DESC, pub.title ASC
		";
		
	$table = get_data($query);

	if($table)
	{
		print "<HR><H3>Publications</H3>";
		print_table($table, $table_width, FALSE);
	}
}

//---------------------------------------------------------------------------------------------------------------
function get_academic_year($ay_id)
// returns the name(label) of the given academic year
{
	$query = "SELECT * FROM AcademicYear WHERE id = $ay_id";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['label'];
}	

//--------------------------------------------------------------------------------------------------------------
function get_teaching_stint($e_id, $ay_id, $dept_id)
{
//	get post information for the given employee
	$query = "
		SELECT
		d.id AS 'D_ID',
		p.id AS 'P_ID',
		CONCAT(d.department_code, ' - ', d.department_name) AS 'Department',
		p.dept_stint_obligation AS 'StOb'

		FROM Post p
		INNER JOIN Department d ON d.id = p.department_id
		WHERE p.employee_id = $e_id
		AND p.person_status = 'ACTV'
		";
	$e_posts = get_data($query);
	
	$e_post_d_id = array();
	$total_stob = 0;
	if($e_posts) foreach($e_posts AS $e_post)
	{
		$e_post_d_id[] = $e_post['D_ID'];
		$total_stob = $total_stob + $e_post['StOb'];	
	}

//	select the teaching data
	$query = "
		SELECT DISTINCT
		tc_d.id AS 'TC_D_ID',
		ti_d.id AS 'TI_D_ID',
		ti.id AS 'TI_ID',
		tc.id AS 'TC_ID',

#		CONCAT(tc_d.department_code, ' - ', tc_d.department_name) AS 'Department',
		CONCAT(ti_d.department_code, ' - ', ti_d.department_name) AS 'Department',
		CONCAT('<A HREF=component_details.php?tc_id=',tc.id,'&department_code=$department_code>',tc.subject,'</A>') AS 'Subject',

		tct.title AS 'Type',
		tctt.stint AS 'Tariff',

		t.term_code AS 'Term',
		ti.sessions AS 'Sess.',
		ti.percentage AS 'Perc.',

		ROUND((ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

		FROM TeachingInstance ti
		INNER JOIN Term t ON t.id = ti.term_id
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id

		LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND IF((SELECT COUNT(*) FROM TeachingComponentAssessmentUnit tcau2 WHERE tcau2.teaching_component_id = tc.id AND tcau2.academic_year_id = t.academic_year_id) > 1 ,tcau.assessment_unit_id != 99999, 1=1) AND tcau.academic_year_id = t.academic_year_id

		LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id
		INNER JOIN Department ti_d ON ti_d.id = ti.department_id
		INNER JOIN Department tc_d ON tc_d.id = tc.department_id		
		
		WHERE ti.employee_id = $e_id
		AND t.academic_year_id = $ay_id

		ORDER BY tc_d.department_code, t.startdate, tc.subject
		";

	$table = get_data($query);

	$sum_stint = array();		//we will sum the stint earned by department in this array
	$teach_stint_sum = 0;	
	$tc_row = array();

//	for each component
	if($table) foreach($table AS $row)
	{
		$tc_d_id = $row['TC_D_ID'];
		$ti_d_id = $row['TI_D_ID'];
		$ti_id = $row['TI_ID'];
//	sum up the stints earned by the given department			
		if(in_array($ti_d_id, $e_post_d_id))	// if the teaching belongs to a post department
		{
			if($ti_d_id == $dept_id) $teach_stint_sum = $teach_stint_sum + $row['Stint'];	// add the stint earned if the post department is the given department
		} else	// teaching done for a department with no post
		{
			if($e_posts) foreach($e_posts AS $e_post)
			{
				if($total_stob > 0) $stint_part = $row['Stint'] * $e_post['StOb'] / $total_stob;
				else $stint_part = $row['Stint'] / count($e_posts);		// if there is not total StOb at all simply divide by the number of posts
				if($e_post['D_ID'] == $dept_id) $teach_stint_sum = $teach_stint_sum + $stint_part; // add the partial stint earned if the post dept is the given dept
//				if($tc_d_id == $dept_id) $teach_stint_sum = $teach_stint_sum + $stint_part; // add the partial stint earned if the post dept is the given dept
			}
		}
	}
	return $teach_stint_sum;
}

//--------------------------------------------------------------------------------------------------------------
function get_borrowed_teaching_stint($e_id, $ay_id, $dept_id)
{
//	select the teaching data
	$query = "
		SELECT DISTINCT
		(ROUND((ti.sessions * ti.percentage / 100 * tctt.stint),2)) AS 'Stint'

		FROM TeachingInstance ti
		INNER JOIN Term t ON t.id = ti.term_id
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id

		LEFT JOIN TeachingComponentAssessmentUnit tcau ON tcau.teaching_component_id = tc.id AND IF((SELECT COUNT(*) FROM TeachingComponentAssessmentUnit tcau2 WHERE tcau2.teaching_component_id = tc.id AND tcau2.academic_year_id = t.academic_year_id) > 1 ,tcau.assessment_unit_id != 99999, 1=1) AND tcau.academic_year_id = t.academic_year_id

		LEFT JOIN TeachingComponentType tct ON tct.id = tcau.teaching_component_type_id
		LEFT JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = t.academic_year_id
		
		WHERE ti.employee_id = $e_id
		AND t.academic_year_id = $ay_id
		AND ti.department_id = $dept_id
		";
	$instances = get_data($query);
	$teach_stint = 0;
	if($instances) foreach($instances AS $instance)
		$teach_stint = $teach_stint + $instance['Stint'];

	return $teach_stint;
}

//--------------------------------------------------------------------------------------------------------------
function get_supervision_stint($e_id, $ay_id, $d_id)
// returns the sum of supervision stint earned by a given employee in a given academic year for a given department
{
//	First get all ACTV posts of the given employee
	$query = "
		SELECT 
		*
		FROM Post p 
		
		WHERE p.employee_id = $e_id
		AND p.person_status = 'ACTV'
		";
	$posts = get_data($query);
//	sum um the total stint obligation and put the post id into a table for reference later
	$total_stob = 0;
	$actv_post_ids = array();
	if($posts) foreach($posts AS $post)
	{
		$total_stob = $total_stob + $post['dept_stint_obligation'];
		$actv_post_dept_ids[] = $post['department_id'];
	}
	$dept_stob = get_stint_obligation($e_id, $d_id);

	
//	now query for all supervision EXCEPT DPhil supervision for the post with the given department $d_id
	$query = "
SELECT

FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code NOT LIKE '%DPhil%'
AND sv.department_id = $d_id
AND e.id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$sv_stint = $row['S Stint'];
	
	
//	now query for all supervision EXCEPT DPhil supervision for departments without a post
	$query = "
SELECT

FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code NOT LIKE '%DPhil%' 
AND e.id = $e_id
";
	if($actv_post_dept_ids) foreach($actv_post_dept_ids AS $post_dept_id)
		$query = $query."AND sv.department_id != $post_dept_id ";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$other_sv_stint = $row['S Stint'];

//	the other stint will be accounted to the given department accordingly to the stint obligation share	
	if($total_stob) $sv_stint = $sv_stint + $other_sv_stint * $dept_stob / $total_stob;
	else if(sizeof($actv_post_dept_ids)) $sv_stint = $sv_stint + $other_sv_stint / sizeof($actv_post_dept_ids);

//	Finally get all DPhil supervision
	$query = "
SELECT

FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code LIKE '%DPhil%' 
AND e.id = $e_id
";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$dphil_sv_stint = $row['S Stint'];

//	ALL DPhil stint will be accounted to the given department accordingly to the stint obligation share	
	if($total_stob) $sv_stint = $sv_stint + $dphil_sv_stint * $dept_stob / $total_stob;
	else if(sizeof($actv_post_dept_ids)) $sv_stint = $sv_stint + $dphil_sv_stint / sizeof($actv_post_dept_ids);	
	
	return $sv_stint;
}

//--------------------------------------------------------------------------------------------------------------
function get_borrowed_supervision_stint($e_id, $ay_id, $dept_id)
// returns the sum of supervision stint borrowed from a department using one of their academics
{
//	now query for all supervision for the department $dept_id that borrowed the academic
	$query = "
SELECT
FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

WHERE t.academic_year_id = $ay_id
AND sv.department_id = $dept_id
AND e.id = $e_id
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$sv_stint = $row['S Stint'];
	
	return $sv_stint;
}

//--------------------------------------------------------------------------------------------------------------
function get_stint_obligation($e_id, $d_id)
//	returns stint obligation of a given academic with a given department
{
	$query = "
SELECT
*

FROM Post p

WHERE p.department_id = $d_id
AND p.employee_id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$row = mysql_fetch_assoc($result);
	
	return $row['dept_stint_obligation'];
}

//--------------------------------------------------------------------------------------------------------------
function get_leave_reduction($e_id, $ay_id, $d_id)
//	returns the factor by which the stint obligation is reduced for an academic
//	due to academic leave for a given year and a department
{
	$query = "
SELECT
SUM(eal.leave_percentage) / 300 AS 'leave_perc'

FROM EmployeeAcademicLeave eal
INNER JOIN Term t on t.id = eal.term_id
INNER JOIN Employee e ON e.id = eal.employee_id
INNER JOIN Department d ON d.id = eal.department_id

WHERE t.academic_year_id = $ay_id
AND eal.department_id = $d_id
AND eal.employee_id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$row = mysql_fetch_assoc($result);
	return $row['leave_perc'];
}

//==================================< The Buttons >=======================================

//--------------------------------------------------------------------------------------------------------------
function staff_switchboard()
{
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST

	$_POST['e_id']	 = $e_id;
	

	print "<TABLE BGCOLOR=LIGHTGREY BORDER = 0>";
	print "<TR>";

	print "<TD WIDTH=400 ALIGN=LEFT>".reload_ay_button()."</TD>";

	print "<TD WIDTH=200 ALIGN=LEFT>".teaching_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".supervising_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".leave_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".publication_button()."</TD>";
	
	print "<TD WIDTH=250 ALIGN=LEFT></TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".export_button()."</TD>";
	print "<TD WIDTH=200 ALIGN=LEFT>".new_query_button()."</TD>";
	print "<TR>";
	print "</TABLE>";
	print "<HR>";
}

//--------------------------------------------------------------------------------------------------------------
function teaching_button()
//	display a button to display/hide teaching information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['e_id']) $_POST['e_id'] = $_GET['e_id'];		// get employee ID e_id
	$e_id = $_POST['e_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if (has_teaching($e_id, $ay_id)) 
	{
		if($_POST['show_teaching'])
		{
			$html = $html."<input type='hidden' name='show_teaching' value=0>";
			$html = $html."<input type='submit' value='Hide Teaching Details'></FORM>";
		} else
		{
			$html = $html."<input type='hidden' name='show_teaching' value=1>";
			$html = $html."<input type='submit' value='Show Teaching Details'></FORM>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_teaching' value=0>";
		$html = $html."<input type='submit' value='NO Teaching Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function supervising_button()
//	display a button to display/hide supervising information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['e_id']) $_POST['e_id'] = $_GET['e_id'];		// get employee ID e_id
	$e_id = $_POST['e_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if (has_supervising($e_id, $ay_id)) 
	{
		if($_POST["show_supervising"])
		{
			$html = $html."<input type='hidden' name='show_supervising' value=0>";
			$html = $html."<input type='submit' value='Hide Supervising Details'></FORM>";
		} else
		{
			$html = $html."<input type='hidden' name='show_supervising' value=1>";
			$html = $html."<input type='submit' value='Show Supervising Details'></FORM>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_supervising' value=0>";
		$html = $html."<input type='submit' value='NO Supervising Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function leave_button()
//	display a button to display/hide leave information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['e_id']) $_POST['e_id'] = $_GET['e_id'];		// get employee ID e_id
	$e_id = $_POST['e_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

	if (has_leave($e_id, $ay_id)) 
	{
		if($_POST["show_leave"])
		{
			$html = $html."<input type='hidden' name='show_leave' value=0>";
			$html = $html."<input type='submit' value='Hide Leave Details'></FORM>";
		} else
		{
			$html = $html."<input type='hidden' name='show_leave' value=1>";
			$html = $html."<input type='submit' value='Show Leave Details'></FORM>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_leave' value=0>";
		$html = $html."<input type='submit' value='NO Leave Details'></FORM>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function publication_button()
//	display a button to display/hide publication information
{
	if(!$_POST['ay_id']) $_POST['ay_id'] = $_GET['ay_id'];	// get academic_year_id
	$ay_id = $_POST['ay_id'];
	if(!$_POST['e_id']) $_POST['e_id'] = $_GET['e_id'];		// get employee ID e_id
	$e_id = $_POST['e_id'];
	
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
	$html = $html . write_post_vars_html();

//	if (has_published($_GET['e_id']) OR has_published($_POST['e_id'])) 
	if (has_published($e_id)) 
	{
		if($_POST['show_publication'])
		{
			$html = $html."<input type='hidden' name='show_publication' value=0>";
			$html = $html."<input type='submit' value='Hide Publications'></FORM>";
		} else
		{
			$html = $html."<input type='hidden' name='show_publication' value=1>";
			$html = $html."<input type='submit' value='Show Publications'></FORM>";
		}
	} else
	{
		$html = $html."<input type='hidden' name='show_publication' value=0>";
		$html = $html."<input type='submit' value='NO Publications'></FORM>";
	}
	return $html;
}

//==================================< Attributes >=======================================

//---------------------------------------------------------------------------------------------------------------
function is_lent($e_id, $d_id)
// returns TRUE if the given employee is borrowed from the given department
{
	$department_code = $_POST['department_code'];								// get department_code of selection
	$dept_id = get_dept_id($department_code);

	if($dept_id) 
	{
		$query = "
			SELECT
			*
			FROM PostOtherDepartment pod
			INNER JOIN Post p ON p.id = pod.post_id
		
			WHERE pod.other_department_id != $d_id
			AND p.employee_id = $e_id
			AND (SELECT COUNT(*) FROM Post WHERE employee_id = $e_id AND department_id = $d_id) > 0
			";
		if(get_data($query)) return TRUE;
		else return FALSE;
	} else return FALSE;
}

//---------------------------------------------------------------------------------------------------------------
function is_borrowed($e_id, $d_id)
// returns TRUE if the given employee is borrowed from the given department
{
	$department_code = $_POST['department_code'];								// get department_code of selection
	$dept_id = get_dept_id($department_code);

	if($dept_id) 
	{
		$query = "
			SELECT
			*
			FROM PostOtherDepartment pod
			INNER JOIN Post p ON p.id = pod.post_id
		
			WHERE pod.other_department_id = $d_id
			AND p.employee_id = $e_id
			AND (SELECT COUNT(*) FROM Post WHERE employee_id = $e_id AND department_id = $d_id) = 0
			";
//dprint($query);
		if(get_data($query)) return TRUE;
		else return FALSE;
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function has_teaching($e_id, $ay_id)
//	checks if  a given Employee ID has some teaching at all (for a given academic year)
{
	if($ay_id > 0) $query = "SELECT * FROM TeachingInstance ti INNER JOIN Term t ON t.id = ti.term_id WHERE ti.employee_id = $e_id AND t.academic_year_id = $ay_id";
	else $query = "SELECT * FROM TeachingInstance WHERE employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function has_supervising($e_id, $ay_id)
//	checks if  a given Employee ID has some supervising at all (for a given academic year)
{
	if($ay_id > 0) $query = "SELECT * FROM Supervision sv INNER JOIN Term t ON t.id = sv.term_id WHERE sv.employee_id = $e_id AND t.academic_year_id = $ay_id";
	else $query = "SELECT * FROM Supervision where employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function has_leave($e_id, $ay_id)
//	checks if  a given Employee ID has some leave at all (for a given academic year)
{
	if($ay_id > 0) $query = "SELECT * FROM EmployeeAcademicLeave eal INNER JOIN Term t ON t.id = eal.term_id WHERE eal.employee_id = $e_id AND t.academic_year_id = $ay_id";
	else $query = "SELECT * FROM EmployeeAcademicLeave where employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function has_published($e_id)
//	checks if  a given Employee ID has published something at all
{
	$query = "SELECT * FROM PublicationEmployee where employee_id = $e_id";

	if(get_data($query)) return TRUE; 
	else return FALSE;
}


?>
