<?php


//=============================================================================================================
//
//	Script to update teaching data from a CSV file into DAISY
//	
//	Last Update by Matthias Opitz, 2013-09-20
//
//=============================================================================================================
$version = '130801.1';		//	start
$version = '130809.1';		//	only resolve rows with all data needed (TC_ID, employee ID, term) and ignore the others
$version = '130903.3';		//	added option to export failed rows
$version = '130917.1';		//	allow ID > 9999
$version = '130920.3';		//	show a success message when everything goes right!
$version = '130925.1';		//	allow uppercase column names

//==============================================< Here we go! >====================================================

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';
include '../includes/common_export_functions.php';


$conn = open_daisydb();
$starttime = start_timer(); 

if(!$_POST['failed_only']) print_header("Update - Teaching data from CSV file");


if(current_user_is_in_DAISY_user_group("Overseer"))
	go_ahead();
else
	show_no_mercy();

//	stop the timer
$totaltime = stop_timer($starttime);
if(!$_POST['failed_only']) show_footer($version, $totaltime);
mysql_close($conn);


//--------------------------------------------------------------------------------------------------
function go_ahead()
{
	if(!$_POST['failed_only'])
	{
		upload_file();
		print "<HR>";
	}

	if ($_FILES["file"]["error"] > 0)
	{
	  	echo "Error: " . $_FILES["file"]["error"] . " - ".csv_error_explained($_FILES["file"]["error"])."<br />";
	}
	else
		if ($_FILES["file"]["size"] > 0) 
		{
			if($_POST['tabula_rasa'])
			{
				delete_teaching_instances();				// delete all current teaching instances of the selected year and add the ones from the file	
			}
//			count_multiple_instances();
			
//			test_data();
			import_data();
		} else print "<FONT FACE=Arial>Please select a CSV file to upload.</FONT>";
}

//--------------------------------------------------------------------------------------------------------------
function upload_file()
{
	$actionpage = $_SERVER["PHP_SELF"];
	print "
		<FONT FACE=Arial>
		<form action='$actionpage' method='post' enctype='multipart/form-data'>
		<TABLE>
		<TR>
			<TD>
				<label for='file'><FONT SIZE=4>Filename:</FONT></label>
			</TD><TD>
				<input type='file' name='file' id='file' />
			</TD>
		</TR>
		<TR>
			<TD>
				<FONT SIZE=4>Export failed records:</FONT> 
			</TD><TD>
				<input type='checkbox' name='failed_only' value='TRUE'>
			</TD>
		</TR>
		<TR>
			<TD COLSPAN=2><FONT SIZE=2>Checking this will return a csv file with all failed rows which may be edited and uploaded again.</FONT></TD>
		</TR>
			<TD>&nbsp;</TD>
		<TR>
		</TR>
			<TD>&nbsp;</TD>
		<TR>
		</TR>
		<TR>
			<TD></TD>
			<TD>
				<input type='submit' name='submit' value='Submit' />
			</TD>
		</TR>
		</TABLE>
		</form>
		</FONT>
	";
}

//--------------------------------------------------------------------------------------------------
function show_file_details()
{
	echo "Upload: " . $_FILES["file"]["name"] . "<br />";
	echo "Type: " . $_FILES["file"]["type"] . "<br />";
	echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
	echo "Stored in: " . $_FILES["file"]["tmp_name"];
}

//--------------------------------------------------------------------------------------------------
function delete_teaching_instances()
// delete all teaching instances of the currently selected department and academic year
{
	$ay_id = $_POST['ay_id'];
	$department_code = $_POST['department_code'];
	
	dprint("Deleting all related data to Teaching Instances now!");
	$query ="
		SELECT ti.*
		FROM TeachingInstance ti
		INNER JOIN Term t ON t.id = ti.term_id
		INNER JOIN Department d ON d.id = ti.department_id
		
		WHERE 1=1
		AND d.department_code LIKE '$department_code%'
		AND t.academic_year_id = $ay_id
	";
	$result = get_data($query);
	
	if($result) foreach($result AS $item)
	{
		delete_attendance_data($item['id']);
		delete_teaching_session_data($item['id']);
	}

	dprint("Now deleting all Teaching Instances!");
	$query ="
		DELETE ti.*
		FROM TeachingInstance ti
		INNER JOIN Term t ON t.id = ti.term_id
		INNER JOIN Department d ON d.id = ti.department_id
		
		WHERE 1=1
		AND d.department_code LIKE '$department_code%'
		AND t.academic_year_id = $ay_id
	";
	return mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function delete_teaching_instance($id)
// delete the teaching instance wit the given ID and all related children
{
	delete_attendance_data($id);
	delete_teaching_session_data($id);

	$query ="DELETE FROM TeachingInstance WHERE id = $id ";
//dprint($query);
	return mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function delete_attendance_data($ti_id)
//	delete all Attendance records related to a given Teaching Instance
{
	$query ="DELETE FROM Attendance WHERE teaching_instance_id = $ti_id";

	return mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function delete_teaching_session_data($ti_id)
//	delete all Attendance records related to a given Teaching Instance
{
	$query ="DELETE FROM TeachingSession WHERE teaching_instance_id = $ti_id";

	return mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
}


//--------------------------------------------------------------------------------------------------
function test_data()
{
	$file = $_FILES['file']['name'];
	$tmp_file = $_FILES['file']['tmp_name'];

	$table = csv2table($tmp_file, 1);

	p_table($table);
}

//--------------------------------------------------------------------------------------------------
function lower_case_keys($array)
// force all keys on the array to be lower case
{
	$keys = array_keys($array);
	
	if($keys) foreach($keys AS $key)
	{
		$low_key = strtolower($key);
		$array[$low_key] = $array[$key];
		if($low_key != $key) unset($array[$key]);
	}
	return $array;
}

//--------------------------------------------------------------------------------------------------
function import_data()
{
	$file = $_FILES['file']['name'];
	$tmp_file = $_FILES['file']['tmp_name'];

	$table = csv2table($tmp_file, 1);
	
	
	$added = array();
	$updated = array();
	$failed = array();
	$deleted = array();
	
	if($table) foreach($table AS $row)
	{
		$row = lower_case_keys($row);	// make sure all keys are lower case - even if not entered that way

		if(!is_numeric($row['percentage'])) $row['percentage'] = 100;
		if(!is_numeric($row['sessions'])) $row['sessions'] = 0;
		if(!is_numeric($row['count'])) $row['count'] = 1;

		
		if($row['delete'] == 'yes')
		{
			$id = teaching_exists($row);
			if($id>0)
			{
				delete_teaching_instance($id);
				$deleted[] = $row;
			}
		}
		
		
		elseif(get_term_id($row['term']) AND get_employee_id($row['employee number']) AND component_exists(substr($row['component code'],5)))
		{
			$action = import_row($row);
			if($action == 'add') $added[] = $row;
			if($action == 'update') $updated[] = $row;
		} else
		{
			if(!get_term_id($row['term'])) $row['reason for failure'] = 'Incorrect term code';
			elseif(!get_employee_id($row['employee number'])) $row['reason for failure'] = 'Cannot find employee';
			elseif(!component_exists(substr($row['component code'],5))) $row['reason for failure'] = 'Component missing or component code wrong';
			else $row['reason for failure'] = 'Error 400';
			$failed[] = $row;
		}
	}

	if($_POST['failed_only'])
	{
		if($failed) export2csv($failed, "Rows failed to import  ");
		else alert("The data was uploaded successfully without any errors!");
	}
	else
	{
		if($added)
		{
			print "<H2>Added rows: </H2><P>";
			p_table($added);
			print "<P>";
		}
		if($updated)
		{
			print "<H2>Updated rows: </H2><P>";
			p_table($updated);
			print "<P>";
		}
		if($deleted)
		{
			print "<H2>Deleted rows: </H2><P>";
			p_table($deleted);
			print "<P>";
		}
		if($failed)
		{
			print "<H2>Failed rows: </H2><P>";
			p_table($failed);
			print "<P>";
		}
	}
}

//--------------------------------------------------------------------------------------------------
function import_data0()
{
	$file = $_FILES['file']['name'];
	$tmp_file = $_FILES['file']['tmp_name'];

	$table = csv2table($tmp_file, 1);
	$added = array();
	$updated = array();
	$failed = array();
	$deleted = array();
	
	if($table) foreach($table AS $row)
	{
		if(!is_numeric($row['percentage'])) $row['percentage'] = 100;
		if(!is_numeric($row['sessions'])) $row['sessions'] = 0;
		if(!is_numeric($row['count'])) $row['count'] = 1;

		if($row['delete'] == 'yes')
		{
			$id = teaching_exists($row);
			if($id>0)
			{
				delete_teaching_instance($id);
				$deleted[] = $row;
			}
		}
		elseif(get_term_id($row['term']) AND get_employee_id($row['employee number']) AND component_exists(substr($row['component code'],5)))
		{
			$action = import_row($row);
			if($action == 'add') $added[] = $row;
			if($action == 'update') $updated[] = $row;
		} else
		{
			if(!get_term_id($row['term'])) $row['reason for failure'] = 'Incorrect term code';
			elseif(!get_employee_id($row['employee number'])) $row['reason for failure'] = 'Cannot find employee';
			elseif(!component_exists(substr($row['component code'],5))) $row['reason for failure'] = 'Component missing or component code wrong';
			else $row['reason for failure'] = 'Error 400';
			$failed[] = $row;
		}
	}
	
	if($_POST['failed_only'])
	{
		if($failed) export2csv($failed, "Rows failed to import  ");
	}
	else
	{
		if($added)
		{
			print "<H2>Added rows: </H2><P>";
			p_table($added);
			print "<P>";
		}
		if($updated)
		{
			print "<H2>Updated rows: </H2><P>";
			p_table($updated);
			print "<P>";
		}
		if($deleted)
		{
			print "<H2>Deleted rows: </H2><P>";
			p_table($deleted);
			print "<P>";
		}
		if($failed)
		{
			print "<H2>Failed rows: </H2><P>";
			p_table($failed);
			print "<P>";
		}
	}
}

//--------------------------------------------------------------------------------------------------
function import_row($row)
{
	$action = FALSE;
	if($row)
	{
		if(teaching_exists($row))	// check if the teaching exits - if so update the record
		{
			update_teaching($row);
			$action = 'update';
		}
		else				// add a new record
		{
			add_teaching($row);
			$action = 'add';
		}
	}
	return $action;
}

//--------------------------------------------------------------------------------------------------
function get_employee_id($e_nr)
// return the id of an employee with the given employee number
{
	$query = "SELECT * FROM Employee WHERE opendoor_employee_code = '$e_nr'";
	$res = get_data($query);
	if($res) return $res[0]['id'];
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_term_id($term_code)
// return the id of a term with the given code
{
	$query = "SELECT * FROM Term WHERE term_code = '$term_code'";
	$res = get_data($query);
	if($res) return $res[0]['id'];
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function component_exists($id)
// check if the component with the given ID - which may have been entered with an error (humans...!) - is known at all
{
	if($id > 0)
	{
		$query = "SELECT * FROM TeachingComponent WHERE id = ".$id;
		return get_data($query);
	}
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function teaching_exists($row)
// check if the employee is known
{
	$ay_id = $_POST['ay_id'];
	$department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);			// get department id
//	$tc_id = substr($row['component code'],5,4);
	$tc_id = substr($row['component code'],5);
	if ($tc_id < 1) return FALSE;
	
	$query = "
	SELECT ti.*
	FROM TeachingInstance ti
	INNER JOIN Employee e ON e.id = ti.employee_id
	INNER JOIN Term t ON t.id = ti.term_id
	INNER JOIN Department d ON d.id = ti.department_id
	
	WHERE 1=1
	AND e.opendoor_employee_code = '".$row['employee number']."'
	AND t.term_code = '".$row['term']."'
	AND ti.teaching_component_id = ".substr($row['component code'],5)."

	AND IF(ti.instance_count > 0, ti.instance_count = ".$row['count'].", 1=1)

	AND d.department_code = '".substr($row['component code'],0,4)."'
	";
	if($res = get_data($query)) return $res[0]['id'];
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function update_teaching($row)
// currently only updating the percentage and number of sessions
{
	$ay_id = $_POST['ay_id'];
	$department_code = $_POST['department_code'];
	$dept_id = get_dept_id($department_code);			// get department id
//	$tc_id = substr($row['component code'],5,4);
	$tc_id = substr($row['component code'],5);
	
	$query = "
	UPDATE TeachingInstance ti
	INNER JOIN Employee e ON e.id = ti.employee_id
	INNER JOIN Term t ON t.id = ti.term_id
	INNER JOIN Department d ON d.id = ti.department_id
	
	SET
	ti.sessions = ".$row['sessions'].", 
	ti.percentage = ".$row['percentage'].", 
	ti.updated_at = NOW()
	
	WHERE 1=1
	AND e.opendoor_employee_code = '".$row['employee number']."'
	AND t.term_code = '".$row['term']."'
	AND ti.teaching_component_id = ".substr($row['component code'],5)."
	AND d.department_code = '".substr($row['component code'],0,4)."'
	AND IF(".$row['count']." > 0, ti.instance_count = ".$row['count'].", 1=1)
	";
	
//	dprint($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function add_teaching($row)
{
	$ay_id = $_POST['ay_id'];
	$department_code = $_POST['department_code'];
	$dept_id = get_dept_id(substr($row['component code'],0,4));			// get department id
	$tc_id = substr($row['component code'],5);
	

	$query = "
		INSERT INTO TeachingInstance
		( 
			department_id,
			employee_id,
			teaching_component_id,
			term_id,
			sessions,
			percentage,
			instance_count,
			created_at
		)
		VALUES
		(
			".$dept_id.",
			".get_employee_id($row['employee number']).",
			".substr($row['component code'],5).",
			".get_term_id($row['term']).",
			".$row['sessions'].",
			".$row['percentage'].",
			".$row['count'].",
			NOW()
		)
	";
	
//	dprint($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
}

?> 