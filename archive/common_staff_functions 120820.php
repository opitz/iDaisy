<?php

//==================================================================================================
//
//	Separate file with common Academic / Staff functions
//	Last changes: Matthias Opitz --- 2012-08-14
//
//==================================================================================================
$version_caf = "120629.1";			// 1st version
$version_caf = "120703.1";			// fixed bug where more than one DP per student is shown for Supervision
$version_caf = "120706.1";			// added stint calculation to post details
$version_caf = "120710.1";			// fixed a bug when there is no post
$version_caf = "120716.1";			// added display_switchboard()
$version_caf = "120717.1";			// added show_staff_list(), show_staff_details() / renamed to common_staff_functions.php
$version_caf = "120724.1";			// show teaching related to components without activation for the academic year of the Instance (which is an error but I want to see it!)
$version_caf = "120726.1";			// renamed display_switchboard to staff_switchboard and moved to staff_buttons.php
$version_caf = "120727.1";			// bugfix for teaching stint calculation
$version_caf = "120807.1";			// bugfix for teaching stint calculation
$version_caf = "120810.1";			// now using Instance Department
$version_caf = "120810.2";			// no $webauth_code anymore
$version_caf = "120812.1";			// added correct stints for borrowed staff
$version_caf = "120814.1";			// added usage of new function employee_is_borrowed()
$version_caf = "120816.1";			// bugfix in supervision details

//--------------------------------------------------------------------------------------------------------------
function show_staff_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
/*
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}
*/

	$debug = $_POST['debug'];
//	$debug = 2;

	$ay_id = $_POST['ay_id'];													// get academic_year_id
	$department_code = $_POST['department_code'];								// get department_code
	$fullname_q = $_POST['fullname_q'];											// get fullname_q
	$forename_q = $_POST['forename_q'];											// get forename_q
	$surname_q = $_POST['surname_q'];											// get surname_q
	$webauth_q = $_POST['webauth_q'];											// get webauth_q
	$employee_nr_q = $_POST['employee_nr_q'];									// get employee_nr_q

	$academic_only = $_POST['academic_only'];									// get academic_only
	$non_academic = $_POST['non_academic'];										// get non_academic

	$non_actv = $_POST['non_actv'];												// get non_actv
	$manual_only = $_POST['manual_only'];										// get manual_only
	$include_borrowed_staff = $_POST['include_borrowed_staff'];					// get include_borrowed_staff


	$parameter['ay_id'] = $_POST['ay_id'];										// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];					// get department_code
	$parameter['fullname_q'] = $_POST['fullname_q'];							// get fullname_q
	$parameter['forename_q'] = $_POST['forename_q'];							// get forename_q
	$parameter['surname_q'] = $_POST['surname_q'];								// get surname_q
	$parameter['webauth_q'] = $_POST['webauth_q'];								// get webauth_q
	$parameter['employee_nr_q'] = $_POST['employee_nr_q'];						// get employee_nr_q

	$parameter['academic_only'] = $_POST['academic_only'];						// get academic_only
	$parameter['non_academic'] = $_POST['non_academic'];						// get non_academic

	$parameter['non_actv'] = $_POST['non_actv'];								// get non_actv
	$parameter['manual_only'] = $_POST['manual_only'];							// get manual_only
	$parameter['include_borrowed_staff'] = $_POST['include_borrowed_staff'];	// get include_borrowed_staff


	$query = $_POST['query'];									// get query
	$query = stripslashes($query);

//	$conn = open_daisydb();										// open DAISY database


//	build build the query if it not already exists
	if(!$query)
	{
		$query = "
			SELECT DISTINCT
#			e.fullname AS 'Full Name',
			d.id AS 'D_ID',
			e.id AS 'E_ID',
			p.dept_stint_obligation AS 'STOB',
			CONCAT(d.department_code,' - ',d.department_name) AS 'Department',
			
#			IF((SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND d2.department_code LIKE '$department_code%') > 0, CONCAT('<A HREF=index.php?e_id=', e.id, '&ay_id=$ay_id>',e.fullname,'</A>'),CONCAT('<A HREF=index.php?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>')) AS 'Full Name',
			CONCAT('<A HREF=index.php?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.fullname,'</A>') AS 'Full Name',
			
			e.opendoor_employee_code AS 'Employee Number',
#			CONCAT('<A HREF=academic_details.php?e_id=', e.id, '&ay_id=$ay_id&department_code=$department_code>',e.opendoor_employee_code,'</A>') AS 'Employee Number',
			e.webauth_code AS 'WebAuth / SSO',
			IF(e.email != '', CONCAT('<A HREF=mailto:', e.email, '>', e.email, '</A>'), '') AS 'Email',
#			sc.staff_classification_code AS 'SCC',
			p.person_status AS 'Status'
			";
//	when borrowed staff is included look if a post holder is not and if so if he is lent to other departments and note this
		if($department_code AND $include_borrowed_staff) $query = $query.",
			IF((SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND d2.department_code LIKE '$department_code%') > 0, IF((SELECT COUNT(*) FROM PostOtherDepartment pod WHERE pod.post_id = p.id AND pod.other_department_id != d.id) > 0, 'Lent',''),'Borrowed') AS 'Notes'
			";

//	when only employed staff is listed check if a post holder was lent to other departments and note it if so
		if($department_code AND !$include_borrowed_staff) $query = $query.",
			IF((SELECT COUNT(*) FROM PostOtherDepartment pod WHERE pod.post_id = p.id AND pod.other_department_id != d.id) > 0, 'Lent','') AS 'Notes'";

//	display brutto stint obligations for own post holders only
//		$query = $query.",
//			IF((SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND d2.department_code LIKE '$department_code%') > 0, p.dept_stint_obligation,' -- ') AS 'StOb (br)'
//			";

		$query = $query." 
			FROM Employee e
			LEFT JOIN Post p on p.employee_id = e.id
			LEFT JOIN Department d ON d.id = p.department_id
			LEFT JOIN StaffClassification sc ON sc.id = p.staff_classification_id
			
			LEFT JOIN PostOtherDepartment pod ON pod.post_id = p.id
			LEFT JOIN Department od ON od.id = pod.other_department_id 


			WHERE 1 = 1 
			";

			if(!$non_academic) $query = $query."AND sc.staff_classification_code LIKE 'A%'";
			
			if($fullname_q) $query = $query."AND e.fullname LIKE '$fullname_q%' ";
			if($forename_q) $query = $query."AND e.forename LIKE '%$forename_q%' ";
			if($webauth_q) $query = $query."AND e.webauth_code LIKE '%$webauth_q%' ";
			if($employee_nr_q) $query = $query."AND e.opendoor_employee_code LIKE '%$employee_nr_q%' ";

			if(!$non_actv) $query = $query."AND p.person_status = 'ACTV' ";
			if($manual_only) $query = $query."AND e.manual = '1' ";
			if($department_code)
			{ 
//				if($include_borrowed_staff) $query = $query."AND (d.department_code LIKE '$department_code%' OR od.department_code LIKE '$department_code%')";
				if($include_borrowed_staff) $query = $query."AND ((SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND p2.person_status = p.person_status AND d2.department_code LIKE '$department_code%') OR od.department_code LIKE '$department_code%')";
				else $query = $query."AND (SELECT COUNT(*) FROM Post p2 INNER JOIN Department d2 ON d2.id = p2.department_id WHERE p2.employee_id = e.id AND p2.person_status = p.person_status AND d2.department_code LIKE '$department_code%') ";
			}
			$query = $query."ORDER BY e.fullname";
	}
	
	$table = get_data($query);

	if(!$excel_export)
	{		
		print_header('Staff Overview');

		if ($query) print_export_button($parameter);
		staff_query_form(); 
		print "<HR>";
	}


	if ($query) 
	{
//		$start_time = time();
//      ***********************************************
		$table_width = array();	// specify column width by column title e.g. $table_width['Title'] = 200
		$table_width['Status'] = 50;
		$table_width['Notes'] = 80;
		$table_width['StOb'] = 50;
		$table_width['Stint'] = 50;
		$table_width['Balance'] = 50;

		$new_table = array();
		$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
		if($table) foreach($table as $row)
		{
			$d_id = $row['D_ID'];
			$e_id = $row['E_ID'];
			$stob = $row['STOB'];
			$notes = $row['Notes'];
			
//	if an academic year was selected do some more...
			if($ay_id > 0) 
			{
//	get leave reduction and show netto Stint Obligation
				if ($notes == 'Borrowed')
					$row['StOb'] = '--';
				else
				{
					$leave_reduction_factor = get_leave_reduction($e_id, $ay_id, $d_id);
					if($leave_reduction_factor) 
						$row['StOb'] = round($stob - $stob * $leave_reduction_factor,2);
//						$row['StOb'] = $stob - $stob * $leave_reduction_factor;
					else
						$row['StOb'] = $stob;
				}


				if($d_id AND $notes != 'Borrowed')
				{
					$teaching_stint = get_teaching_stint($e_id, $ay_id, $d_id);
					$supervision_stint = get_supervision_stint($e_id, $ay_id, $d_id);
				} else
				{
//					$teaching_stint = get_teaching_stint($e_id, $ay_id, $dept_id);
					$teaching_stint = get_borrowed_teaching_stint($e_id, $ay_id, $dept_id);
					$supervision_stint = get_borrowed_supervision_stint($e_id, $ay_id, $dept_id);
//					$teaching_stint = get_teaching_stint($e_id, $ay_id, $d_id);
//					$supervision_stint = get_supervision_stint($e_id, $ay_id, $d_id);
				}
				$st_total = $teaching_stint + $supervision_stint;
//				$row['St Teach'] = $teaching_stint;
//				$row['St Superv'] = $supervision_stint;
				$row['Stint'] = "<B>".round($st_total,2)."</B>";
				$balance = $st_total - $row['StOb'];
				if($balance < 0) $colour = 'RED';
				else $colour = 'GREEN';
				$row['Balance'] = "<B><FONT COLOR=$colour>".round($balance,2)."</FONT></B>";
			}
			
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			$new_table[] = $row;
		}
		
//		if ($excel_export AND $ay_id) export2excel($new_table, $export_title);
		if ($excel_export) export2csv($new_table, "DAISYinfo_Academic_Report_");
//		if ($excel_export) dprint('huhu!');
		else print_table($new_table, $table_width, TRUE);
//      ***********************************************
//		$end_time = time();
//		$diff_time = $end_time - $start_time;
//		print "<HR COLOR = LIGHTGREY><FONT COLOR = GREY>executed in $diff_time seconds";
	}
}

//-----------------------------------------------------------------------------------------
function show_staff_details()
{

	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself

	$excel_export = $_POST['excel_export'];					// flag that determines whether the outout goes to the screen or into an exported Excel file 
	if ($excel_export)
	{
//		$excel_title = "Query_".str_replace(" ", "_", $department['department_name']);
		$excel_title = "Query Result";
		excel_header($excel_title);
	}

	$debug = $_POST['debug'];
//	$debug = 2;
	$ay_id = $_GET['ay_id'];								// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$e_id = $_GET["e_id"];									// get employee ID e_id
	if(!$e_id) $e_id = $_POST['e_id'];						// if not GET then try POST
	
//	if(!$e_id)												// if still no e_id get webauth user id
//	{
//		$e_id = get_employee_id_from_webauth($webauth_code);
//	}
	if(!$e_id) $e_id = get_employee_id_from_webauth(current_user_webauth());
	
	$query = $_POST['query'];								// get query
	$query = stripslashes($query);

	$show_teaching = $_POST['show_teaching'];
	$show_supervising = $_POST['show_supervising'];
	$show_leave = $_POST['show_leave'];
	$show_publication = $_POST['show_publication'];

//	get the employee record for a given employee ID
	if(!$query)
		$query = "
			SELECT 
			e.* 
		
			FROM Employee e

			WHERE e.id = $e_id
			";
			
//	$start_time = time();
//	***********************************************

	$result = get_data($query);
	$e_data = $result[0];
			
//  ***********************************************

//	$end_time = time();
//	$diff_time = $end_time - $start_time;
	
	$date = date('l jS \of F Y g:i:s');
	$e_fullname = $e_data['fullname'];
	
	print_header("Staff Details for <FONT COLOR=DARKBLUE>$e_fullname</FONT>");
	
	if ($ay_id > 0)
		$academic_year = get_academic_year($ay_id);
	else
		$academic_year = "All Years";
	print "Selected Academic Year: <B>$academic_year</B>";
	print "<HR>";

//	print Buttons for Interface if displayed on screen only
	if(!$excel_export)
		staff_switchboard($ay_id, $e_id, $show_teaching, $show_supervising, $show_leave, $show_publication);

	show_employee_details($e_data, $excel_export);
	$e_posts = show_post_details($e_data['id'], $ay_id);
	show_borrowed_post_details($e_data['id']);
 	print "<HR>";
	if($show_teaching) show_teaching_details_by_year($e_data['id'], $ay_id, $e_posts);
	if($show_supervising) show_supervising_details_by_year($e_data['id'], $ay_id, $e_posts);
	if($show_leave) show_leave_details_by_year($e_data['id'], $ay_id);
	if($show_publication) show_publication_details($e_data['id']);
}

//--------------------------------------------------------------------------------------------------------------
function show_employee_details($e_data, $excel_export)
{
//	find out if the logged in user is allowed to edit data in DAISY
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
	print "<H3>Employee Details</H3>";
	print "</TD><TD>";
	print "&nbsp;";
	print "</TD><TD>";
	if($is_editor AND !$excel_export) print "<FONT SIZE=2><A HREF='https://daisy.socsci.ox.ac.uk/employee/".$e_data['id']."/edit' TARGET=NEW>Edit in DAISY</A></FONT><P>";
	print "</TD></TR>";
	print "<TR><TD></TD><TD></TD></TR>";
	print "</TABLE>";

	print "<TABLE BORDER=0>";
	
	print "<TR>";
	print 	"<TD WIDTH=180><B>Name:</B></TD>";
	print 	"<TD WIDTH = 300>".$e_data['title']." ".$e_data['forename']." ".$e_data['initials']." ".$e_data['surname']."</TD>";
	print 	"<TD WIDTH = 50></TD>";
	print 	"<TD WIDTH = 120><B>$user_text</B></TD>";
	print 	"<TD WIDTH = 300>$user_role $super_admin</TD>";
	print "</TR><TR>";
	print 	"<TD WIDTH=180><B>Employee Number:</B> </TD>";
	print 	"<TD>".$e_data['opendoor_employee_code']."</TD>";
	print 	"<TD WIDTH = 50></TD>";
	print 	"<TD WIDTH = 120><B>Webauth ID:</B></TD>";
	print 	"<TD WIDTH = 300>".$e_data['webauth_code']."</TD>";
	print "</TR><TR>";
	print 	"<TD WIDTH=120><B>Email:</B> </TD>";
	print 	"<TD><A HREF='mailto:".$e_data['email']."'>".$e_data['email']."</A></TD>";
	print 	"<TD WIDTH = 50></TD>";
	print 	"<TD WIDTH = 120><B>DAISY ID:</B></TD>";
	print 	"<TD WIDTH = 300>".$e_data['id']."</TD>";
	print "</TR><TR>";
	if($manual) print "<TD><I>Staff added manually</I></TD>";
	print "</TR>";
	


//	print "<TR><TD><A HREF='https://daisy.socsci.ox.ac.uk/employee/".$e_data['id']."/edit' TARGET=NEW>Edit in DAISY</A></TD></TR>";
	print "</TABLE>";
//	print "<HR>";
}

//--------------------------------------------------------------------------------------------------
function show_post_details($e_id, $ay_id)
//	print teaching details for a given Employee ID
{
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
			
			$query = $query.",
			CONCAT('<A HREF=https://daisy.socsci.ox.ac.uk/post/', p.id, '/edit TARGET=NEW>Edit</A>') AS 'DAISY'";

			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			$new_table[] = $row;
		}

		print "<HR><H3>Post Details</H3>";
		if($borrowed) print "<FONT COLOR=#FF6600><B>This is a borrowed staff member.</B><BR>All details on this page will show only stint values related to the borrowing department.<BR>
		Only department(s) the staff member has a post with will see the complete report.<P /></FONT>";
//	define column width in table
		$table_width['Status'] = 50;
		$table_width['Department'] = 300;
		$table_width['Staff Class.'] = 450;

		print_table($new_table, $table_width, FALSE);
		
		return $e_posts;
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function show_borrowed_post_details($e_id)
//	print teaching details for a given Employee ID
{
	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Status'] = 450;
	$table_width['Department'] = 300;
	$table_width['Staff Class.'] = 350;

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

//--------------------------------------------------------------------------------------------------
function show_teaching_details_by_year($e_id, $given_ay_id, $e_posts)
//	print teaching details / stint for a given Employee ID and given Academic Year
{
//	$borrowed = $_GET['borrowed'];							// if an academic is borrowed only show details for the borrowing department
//	if(!$borrowed) $borrowed = $_POST['borrowed'];

	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$borrowed = employee_is_borrowed($e_id, $dept_id);
	
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Editor");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Term'] = 50;
	$table_width['Department'] = 300;
	$table_width['Subject'] = 450;
	$table_width['Type'] = 250;

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
			CONCAT('<A HREF=index.php?tc_id=',tc.id,'&ay_id=$ay_id&department_code=$department_code>',tc.subject,'</A>') AS 'Subject',

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

//--------------------------------------------------------------------------------------------------
function show_supervising_details_by_year($e_id, $given_ay_id, $e_posts)
//	print supervising details / stint for a given Employee ID and academic year
{
	$department_code = $_GET['department_code'];								// get department code
	if(!$department_code) $department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);									// get department id

	$borrowed = employee_is_borrowed($e_id, $dept_id);
	
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Overseer");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Term'] = 50;
	$table_width['Student'] = 450;
	$table_width['Degree Programme'] = 450;
	$table_width['Department'] = 300;
	$table_width['Type'] = 230;

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

//--------------------------------------------------------------------------------------------------
function show_leave_details_by_year($e_id, $given_ay_id)
//	print leave details for a given Employee ID
{
//	find out if the logged in user is allowed to edit data in DAISY
	$is_editor = current_user_is_in_DAISY_user_group("Editor");

	$title_printed = FALSE;
	
//	define column width in table
	$table_width['Term'] = 50;
	$table_width['Type'] = 350;

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

//--------------------------------------------------------------------------------------------------
function show_publication_details($e_id)
//	print publication details for a given Employee ID
{
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

//--------------------------------------------------------------------------------------------------
function get_academic_year($ay_id)
// returns the name(label) of the given academic year
{
	$query = "SELECT * FROM AcademicYear WHERE id = $ay_id";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['label'];
}	

//----------------------------------------------------------------------------------------
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
			foreach($e_posts AS $e_post)
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

//----------------------------------------------------------------------------------------
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

//----------------------------------------------------------------------------------------
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

//----------------------------------------------------------------------------------------
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

//----------------------------------------------------------------------------------------
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

//----------------------------------------------------------------------------------------
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

//=====================================< Excel Functions >==========================================
//--------------------------------------------------------------------------------------------------
function excel_header($docname)		
//declares the type of data sent back to the browser.  Needs to be put into the code before any other output that goes to the user! Outgoing data will be identified as Excel file
{
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=".$docname.".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
}

?>
