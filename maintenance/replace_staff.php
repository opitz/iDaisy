<?php

//========================================================================================
//
//	Script to replace one (manually added) staff member with another
//	Last changes: Matthias Opitz --- 2013-06-03
//
//========================================================================================
$version = "120702.3";			// 1st version
$version = "120710.1";			// adding a LEFT Post for the Inheritor if s/he has none
$version = "120806.1";			// allowed blanks in Employee Number (for manually added staff)
$version = "121211.1";			// part of iDaisy maintenance
$version = "130603.1";			// new header logic

//include 'userlist.php';
include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

$conn = open_daisydb();									// open DAISY database
$db_name = get_database_name($conn);

// get the current academic year id
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();


if(current_user_is_in_DAISY_user_group("Overseer")) go_ahead($db_name);
else show_no_mercy();
//go_ahead();

mysql_close($conn);									// close database connection $conn

print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";
	

//========================================================================================
//				Functions
//========================================================================================
function go_ahead($db_name)
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

	$delinquent_id = $_POST['delinquent_id'];					// get delinquent_id
	$delinquent_en = $_POST['delinquent_en'];					// get delinquent employee number
	$inheritor_id = $_POST['inheritor_id'];						// get inheritor_id
	$inheritor_en = $_POST['inheritor_en'];						// get inheritor employee number

	$do_it = $_POST['do_it'];									// get the final go

	$query = $_POST['query'];									// get query
	$query = stripslashes($query);

//	$conn = open_daisydb();										// open DAISY database

//	$db_name = get_database_name($conn);


	show_header("Maintenance: Replace manually added Employees");

	print "<form action='$actionpage' method=POST>";

	enter_del_staff_en($delinquent_en);
	print " <BR /> ";
	enter_inh_staff_en($inheritor_en);
		
	print "<input type='submit' value='Next'>";
	print "</form>";

	$delinquent_id = get_staff_id($delinquent_en);
	$inheritor_id = get_staff_id($inheritor_en);
	
	if(!$delinquent_id AND $inheritor_id) print"Please select a staff member to replace!<br>";
	if($delinquent_id AND !$inheritor_id) print"Incorrect Employee Number - please reenter!<br>";

	print "<HR>";

	if($delinquent_id AND $inheritor_id)
	{
		$delinquent = get_employee_data($delinquent_id);
		$inheritor = get_employee_data($inheritor_id);

		$del_fullname = $delinquent['fullname'];
		$inh_fullname = $inheritor['fullname'];
//		$inh_id = $inheritor['id'];
		$inh_department_id = $inheritor['department_id'];

		$del_dept = $delinquent['department_id'];
		
//	we a clear to do it!
		if($do_it)
		{
//	If the Inheritor does not have a post with the department the delinquent had a post with it needs to be borrowed by that latter department
			$del_posts = get_post_data($delinquent_id);
			$inh_posts = get_post_data($inheritor_id);

//	check if the inheritor has at least one post record in DAISY, otherwise add a LEFT post for her/him
			if(!inh_posts)
			{
				add_left_post($inheritor_id, $inh_department_id);
				$inh_posts = get_post_data($inheritor_id);
			}

			$del_post = $del_posts[0]; // there is no manual staff member with a joint post so we just take the 1st(only) post department
			$del_post_dept_id = $del_post['department_id'];
				
			if(!$del_post_dept_id) $del_post_dept_id = $delinquent['department_id'];

//	Check if the department of the delinquents post is the same as the department of one of his posts
			$match = FALSE;
			if($inh_posts) foreach($inh_posts AS $inh_post)
			{
				if($inh_post['department_id'] == $del_post_dept_id) $match = TRUE;
			}
		
			if(!$match) // if not borrow the staff member for the department for which the delinquent had a post for
			{
				if(!staff_already_borrowed($inheritor_id, $del_post_dept_id)) borrow_staff($inheritor_id, $del_post_dept_id);
				if($inh_posts) 
				{
					foreach($inh_posts AS $inh_post)
						if(!post_already_borrowed($inh_post['id'], $del_post_dept_id)) borrow_post($inh_post['id'], $del_post_dept_id);
				}
//else?
				
			}

//	Now change the ID of the delinquent to the ID of the inheritor in ALL records of ALL tables in DAISY
			print "Replacing <FONT COLOR=RED><B>$del_fullname</B></FONT> with <FONT COLOR=GREEN><B>$inh_fullname</B></FONT> now: <P>";
			$affected_tables = get_affected_tables($db_name);
			$exception_list = array('Post', 'EmployeeOtherDepartment');
			foreach($affected_tables AS $affected_table)
			{
				if(!in_array($affected_table['affected_table'], $exception_list)) 
				{
					$affected_rows = replace_data($affected_table['affected_table'], $delinquent_id, $inheritor_id);
					if($affected_rows>0) print "<FONT COLOR = #FF6600>$affected_rows occurences changed in <B>'".$affected_table['affected_table']."'</B>!</FONT><BR>";
					else print "$affected_rows occurences changed in <B>'".$affected_table['affected_table']."'</B>!<BR>";
				}
			}
			
//	Finally delete the delinquent
			delete_delinquent($delinquent_id);
		} else
		{
			print "<H2><FONT COLOR=RED>ATTENTION! <BLINK>Destructive action ahaead!</BLINK></FONT></H2>";
			print "You are going to replace <B><FONT COLOR=RED>$del_fullname</FONT></B> with <B><FONT COLOR=GREEN>$inh_fullname</FONT></B> in <B>ALL</B> records in <B>ALL</B> tables in DAISY!<P>";
			print "If that is <B><I>not</I></B> what you want please go BACK in your web browser now!<P>";

			print "<form action='$actionpage' method=POST>";
			print "<input type='hidden' name='do_it' value=TRUE>";
			print "<input type='hidden' name='delinquent_id' value=$delinquent_id>";
			print "<input type='hidden' name='delinquent_en' value='$delinquent_en'>";
			print "<input type='hidden' name='inheritor_id' value=$inheritor_id>";
			print "<input type='hidden' name='inheritor_en' value='$inheritor_en'>";
			print "<input type='submit' value='This is what I want - go ahead and do it!'>";
			print "</form>";
		}
	} else
	{
		print "<H3>Please read and understand!</H3>";
		print "This interface allows you to replace staff and their posts in DAISY with any other staff member.<BR>";
		print "All related records of the former staff member will be transferred to the inheritor.<BR>";
		print "Technically spoken: During this process ALL occurrences of the former staff member will be replaced by the inheritor in ALL tables of DAISY!<BR>";
		print "Finally all Employee and Post records of the former staff member will be deleted.<P>";
		print "<FONT COLOR=RED><B>Running this script is IRREVERSIBLE! So please use it with caution and know what you are doing!</B></FONT><P>";
		
		print "Please select the staff member to replace from the left pull down menu and the inheritor from the right and click 'Ok'.<BR>";
		print "You will be prompted again to confirm your selection before any action will be taken.<BR>";
	}
}

//========================================================================================

//----------------------------------------------------------------------------------------
function get_staff_id($employee_number)
{
	$query = "SELECT * FROM Employee WHERE opendoor_employee_code = '$employee_number'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$row = mysql_fetch_assoc($result);
	return $row["id"];
}

//----------------------------------------------------------------------------------------
function staff_options00($selected_id)
// shows the staff members
{

//	get the list of departments
	$query = "
		SELECT 
			e.* 
		FROM 
			Employee e
		INNER JOIN Department d ON d.id = e.department_id AND d.department_code LIKE '%%'
			
		ORDER BY 
			fullname 
		ASC";

	$table = get_data($query);
	print "<select name='inheritor_id'>";
	print"<option value = ''>Please select staff member that will inherit all data from replaced staff member</option>";
	foreach($table AS $employee)
	{
		if($employee['id'] == $selected_id) print "<option value='".$employee['id']."' selected='selected'>".$employee['fullname']." (".$employee['opendoor_employee_code'].")</option>";
		else print "<option value='".$employee['id']."'>".$employee['fullname']." (".$employee['opendoor_employee_code'].")</option>";
	}
	print "</select>";
}

//----------------------------------------------------------------------------------------
function staff_options0($selected_inheritor_en)
// shows the staff members
{
	print "Please enter Employee Number of staff member that will inherit all data from replaced staff member: ";
	print "<textarea name = 'inheritor_en' rows=1 cols=42>$selected_inheritor_en</textarea>";		
}

//----------------------------------------------------------------------------------------
function enter_del_staff_en($selected_inheritor_en)
// shows the staff members
{
	print "Please enter Employee Number of staff member that will be <B>completely replaced</B>: ";
	print "<textarea name = 'delinquent_en' rows=1 cols=42>$selected_inheritor_en</textarea>";		
}

//----------------------------------------------------------------------------------------
function enter_inh_staff_en($selected_inheritor_en)
// shows the staff members
{
	print "Please enter Employee Number of staff member that will inherit all data from replaced staff member: ";
	print "<textarea name = 'inheritor_en' rows=1 cols=42>$selected_inheritor_en</textarea>";		
}

//----------------------------------------------------------------------------------------
function manual_staff_options($selected_id)
// shows the staff members
{

//	get the list of departments
	$query = "
		SELECT 
			* 
		FROM 
			Employee 
		WHERE
			manual = '1'
		ORDER BY 
			fullname 
		ASC";

	$table = get_data($query);
	
	print "<select name='delinquent_id'>";
	print"<option value = ''>Please select staff member to replace</option>";
	foreach($table AS $employee)
	{
		if($employee['id'] == $selected_id) print "<option value='".$employee['id']."' selected='selected'>".$employee['fullname']." (".$employee['opendoor_employee_code'].")</option>";
		else print "<option value='".$employee['id']."'>".$employee['fullname']." (".$employee['opendoor_employee_code'].")</option>";
	}
	print "</select>";
}

//----------------------------------------------------------------------------------------
function get_employee_data($e_id)
{
	$query = "SELECT * FROM Employee WHERE id = $e_id";
	$result = get_data($query);
	return $result[0];
}

//----------------------------------------------------------------------------------------
function get_post_data($e_id)
{
	$query = "SELECT * FROM Post WHERE employee_id = $e_id";
	$result = get_data($query);
	return $result;
}

//----------------------------------------------------------------------------------------
function staff_already_borrowed($e_id, $to_dept_id)
{
	if(!$to_dept_id) return FALSE;
	
	$query = "SELECT * FROM EmployeeOtherDepartment WHERE employee_id = $e_id AND other_department_id = $to_dept_id";
	$result = get_data($query);
	if (sizeof($result) > 0) return TRUE;
	else return FALSE;
}

//----------------------------------------------------------------------------------------
function borrow_staff($e_id, $to_dept_id)
{
	if(!$to_dept_id) return FALSE;

	$dept_name = get_dept_name($to_dept_id);
	$query = "INSERT INTO EmployeeOtherDepartment (employee_id, other_department_id) VALUES ($e_id, $to_dept_id)";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	print "Inheritor employee now borrowed by $dept_name.<BR>";
	return $result;
}

//----------------------------------------------------------------------------------------
function add_left_post($e_id, $dept_id)
{
	if(!$dept_id) return FALSE;

	$dept_name = get_dept_name($dept_id);
	$query = "INSERT INTO Post (employee_id, department_id, staff_classification_id, manual, person_status) VALUES ($e_id, $dept_id, 1, 1, 'LEFT')";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	print "New LEFT post with $dept_name created for Inheritor.<BR>";
	return mysql_insert_id();	// return the ID of the just added record
}

//----------------------------------------------------------------------------------------
function post_already_borrowed($p_id, $to_dept_id)
{
	if(!$to_dept_id) return FALSE;
	
	$query = "SELECT * FROM PostOtherDepartment WHERE post_id = $p_id AND other_department_id = $to_dept_id";
	$result = get_data($query);
	if (sizeof($result) > 0) return TRUE;
	else return FALSE;
}

//----------------------------------------------------------------------------------------
function borrow_post($p_id, $to_dept_id)
{
	if(!$to_dept_id) return FALSE;

	$dept_name = get_dept_name($to_dept_id);
	$query = "INSERT INTO PostOtherDepartment (post_id, other_department_id) VALUES ($p_id, $to_dept_id)";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	print "Inheritor post now borrowed by $dept_name.<BR>";
	return $result;
}

//----------------------------------------------------------------------------------------
function get_dept_name($dept_id)
{
	if(!$dept_id) return "No Department!";
	$query = "SELECT * FROM Department WHERE id = $dept_id";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$row = mysql_fetch_assoc($result);
	return $row["department_name"];
}

//----------------------------------------------------------------------------------------
function get_affected_tables($db_name)
// select all tables in the DAISY database that do have an employee_id so they will be affected by the transfer
{
	$query = "SHOW TABLES";
	$tables = get_data($query);
	$affected_tables = array();
	foreach($tables as $table)
	{
		$table_header = "Tables_in_".$db_name;
		$table_name = $table["$table_header"];
//		$query = "SHOW COLUMNS FROM $table_name WHERE Field = 'employee_id'";
		$query = "SHOW COLUMNS FROM $table_name WHERE Field LIKE '%employee_id'";
		$columns = get_data($query);
		if(sizeof($columns) > 0) 
		{
			$row['affected_table'] = $table_name;
			$affected_tables[] = $row;
		}
	}
	return $affected_tables;
}

//----------------------------------------------------------------------------------------
function replace_data($table_name, $delinquent_id, $inheritor_id)
{
//	get the data field names that are affected
	$query = "SHOW COLUMNS FROM $table_name WHERE FIELD LIKE '%employee_id'";
	$aff_fields = get_data($query);

	$aff_rows = 0;	
	if($aff_fields) foreach($aff_fields AS $aff_field)
	{
		$field_name = $aff_field['Field'];
		$query = "
			UPDATE $table_name
			SET $field_name = $inheritor_id
			WHERE $field_name = $delinquent_id
		";
		$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
		$aff_rows = $aff_rows + mysql_affected_rows();
	}
	return $aff_rows;
}

//----------------------------------------------------------------------------------------
function replace_data0($table_name, $delinquent_id, $inheritor_id)
{
	$query = "
		UPDATE $table_name
		SET employee_id = $inheritor_id
		WHERE employee_id = $delinquent_id
		";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	return mysql_affected_rows();
}

//----------------------------------------------------------------------------------------
function delete_delinquent($delinquent_id)
{
//	first delete the Post and PostOtherDepartment records
	$query = "
		DELETE p.*, pod.*
		FROM Post p LEFT JOIN PostOtherDepartment pod ON pod.post_id = p.id
		WHERE p.employee_id = $delinquent_id
		";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());

// and then delete the employee and its borrowings
	$query = "
		DELETE e.*, eod.*
		FROM Employee e LEFT JOIN EmployeeOtherDepartment eod ON eod.employee_id = e.id
		WHERE e.id = $delinquent_id
		";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	print "<P>The Delinquent and his/her posts were removed from DAISY completely!<BR>";
	return mysql_affected_rows();
}

?>
