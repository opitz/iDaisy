<?php


//=============================================================================================================
//
//	Script to update HR data from HRIS via CSV file into DAISY incl. Grade!
//	
//	Last Update by Matthias Opitz, 2012-12-19
//
//=============================================================================================================
$version = '121219.3';
//==============================================< Here we go! >====================================================

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

global $lf;
global $par;
global $hr;

$lf = "<BR>";
$par="<P>";
$hr="<HR>";

print $lf."<B>DAISY update</B> |  'Employee' and 'Post' data from HRIS  v.$version $par";
print " $hr ";
//print "'-' = normal Employee Number | '_' = old Employee Number | '/' = Email | '+' = NEW! $par ";

$conn = open_daisydb();

if(current_user_is_in_DAISY_user_group("Overseer"))
	go_ahead();
else
	show_no_mercy();

mysql_close($conn);


//--------------------------------------------------------------------------------------------------
function go_ahead()
{
	upload_file();
	print "<HR>";

	if ($_FILES["file"]["error"] > 0)
	{
	  	echo "Error: " . $_FILES["file"]["error"] . "<br />";
	}
	else
		if ($_FILES["file"]["size"] > 0) 
		{
			set_staff_status_to_LEFT();			// set all ACTV staff to LEFT  before updating the data from HRIS
			set_post_status_to_LEFT();			// set all ACTV post to LEFT (and enter today as enddate!) before updating the data from HRIS
	
//			set_ssd_staff_status_to_LEFT();			// set all ACTV staff to LEFT  before updating the data from HRIS
//			set_ssd_post_status_to_LEFT();			// set all ACTV post to LEFT (and enter today as enddate!) before updating the data from HRIS

//			test_hris_data();
			import_hris_data();
		} else print "Please select a valid HRIS file to upload.";
}

//--------------------------------------------------------------------------------------------------
function csv2table($filename, $has_headers)
//	open the file withe the given filename and return the contents in a table - with or without headers
{
	if (($handle = fopen($filename, "r")) !== FALSE) 	// open the file for reading
	{
		$assoc_data = array();
		$table = array();
		$row_count = 1;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if ($has_headers AND $row_count++ == 1)
			{
				$table_headers = $data;
				$tables = count($table_headers);
			}
			else
			{
				for ($c=0; $c < $tables; $c++)
				{
					$assoc_data[$table_headers[$c]] = $data[$c];
				}
				
				$table[] = $assoc_data;		
			}
//			$row_count++;
		}
		fclose($handle);
		return $table;		
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function upload_file()
{
	$actionpage = $_SERVER["PHP_SELF"];
	print "
		<form action='$actionpage' method='post'
		enctype='multipart/form-data'>
		<label for='file'>Filename:</label>
		<input type='file' name='file' id='file' /> 
		<br />
		<input type='submit' name='submit' value='Submit' />
		</form>
	";
}

//--------------------------------------------------------------------------------------------------
function process_file()
{
	echo "Upload: " . $_FILES["file"]["name"] . "<br />";
	echo "Type: " . $_FILES["file"]["type"] . "<br />";
	echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
	echo "Stored in: " . $_FILES["file"]["tmp_name"];
}

//--------------------------------------------------------------------------------------------------
function test_hris_data()
{
	$hris_file = $_FILES['file']['name'];
	$hris_tmp_file = $_FILES['file']['tmp_name'];

	$hris_table = csv2table($hris_tmp_file, 1);
	$num_of_recs = count($hris_table);
	print "read $num_of_recs records from $hris_file".$nl;
	if($_SERVER['TERM']) show_progress($hris_table);
	else print_table($hris_table, array(),0);
}

//--------------------------------------------------------------------------------------------------
function import_hris_data()
{
	$nl = "<BR>";
	
	$hris_file = $_FILES['file']['name'];
	$hris_tmp_file = $_FILES['file']['tmp_name'];
	$hris_table = csv2table($hris_tmp_file, 1);

	$num_of_recs = count($hris_table);
	print "read $num_of_recs records from $hris_file".$nl;

//	now check each appointment in the hris table 
	$en_table = array();
	$ten_table = array();
	$mail_table = array();
	$new_table = array();
	$en_updated = 0;
	$ten_updated = 0;
	$mail_updated = 0;
	$added = 0;

	$i = 0;
	if($hris_table) foreach($hris_table AS $row)
	{
//		if($i == 250) break; //	break for testing purposes

		$row['E_ID'] = $e_id;
		if($e_id = get_DAISY_employee_id_from_en($row['Personnel Number']))
		{
			$row['E_ID'] = $e_id;
			$en_table[] = $row;
//			$row['Trent Employee Number'] = '';		// do not preserve any old Employee Number
			update_staff($e_id, $row);
			import_hris_posts($e_id, $row);			
			$en_updated++;
			if($_SERVER['TERM']) print "-";
		} 
		elseif($e_id = get_DAISY_employee_id_from_en($row['Trent Employee Number']))
		{
			$row['E_ID'] = $e_id;
			$ten_table[] = $row;
			update_staff($e_id, $row);
			import_hris_posts($e_id, $row);		
			$ten_updated++;
			if($_SERVER['TERM']) print "_";
		}
		elseif($e_id = get_DAISY_employee_id_from_email($row['Email']))
		{
			$row['E_ID'] = $e_id;
			$mail_table[] = $row;
//			$row['Trent Employee Number'] = '';		// do not preserve any old Employee Number
			update_staff($e_id, $row);
			import_hris_posts($e_id,$row);		
			$mail_updated++;
			if($_SERVER['TERM']) print "/";
		}
		else
		{
			$new_table[] = $row;
			$new_e_id = add_staff($row);
			if($new_e_id)
			{
				add_post($new_e_id, $row);
				$added++;
				if($_SERVER['TERM']) print "+";
			}
		}	
		if($_SERVER['TERM'] AND ++$i % 100 == 0) print " : $i".$nl;
	}

	if($en_table)
	{
		print "Employee Number".$nl;
		print_table($en_table, array(), 1);
	}
	
	if($ten_table)
	{
		print "Old Employee Number".$nl;
		print_table($ten_table, array(), 1);
	}

	if($mail_table)
	{
		print "Email".$nl;
		print_table($mail_table, array(), 1);
	}

	if($new_table)
	{
		print "NEW!".$nl;
		print_table($new_table, array(), 1);
	}
	print $nl;
	print "Updates using EN: $en_updated".$nl;
	print "Updates using old EN: $ten_updated".$nl;
	print "Updates using Email: $mail_updated".$nl;
	print "Newly added: $added".$nl;
}

// ================================================< Staff >======================================================
//--------------------------------------------------------------------------------------------------
function set_staff_status_to_LEFT()
// set status of post to 'LEFT' for all ACTV posts
{
	global $lf;
	global $par;
	global $hr;

//	$today = date('d-M-y');
	
	print "$lf Resetting personal status for all non-manual staff  $lf";
	$query ="
		UPDATE Employee e
		SET e.status = 'LEFT'
		WHERE e.status = 'ACTV' AND (e.manual != 1 OR e.manual IS NULL)
		";
	return mysql_query($query) or die (with_obituary($query) . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function set_ssd_staff_status_to_LEFT()
// set status of post to 'LEFT' for all ACTV SSD posts
{
	global $lf;
	global $par;
	global $hr;

//	$today = date('d-M-y');
	
	print "$lf Resetting personal status for all non-manual SSD staff  $lf";
	$query ="
		UPDATE Employee e 
		INNER JOIN Department d ON d.id = e.department_id
		SET e.status = 'LEFT'
		WHERE e.status = 'ACTV' AND (e.manual != 1 OR e.manual IS NULL)
		AND d.department_code LIKE '3C%'
		";
	return mysql_query($query) or die (with_obituary($query) . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function add_staff0($assoc_data)
// add a staff record and return the id of the new record
{
	if($assoc_data['Personnel Number'] > 9990000)		//	if this is a college post holder
	{
		$dept_id = get_DAISY_department_id('00');
		$sub_unit_id = get_DAISY_sub_unit_id('00');
	} else
	{
		$dept_id = get_DAISY_department_id($assoc_data['Cost Centre Code']);
		$sub_unit_id = get_DAISY_sub_unit_id($assoc_data['Cost Centre Code']);
	}
	
	if($assoc_data['Person Status'] == 'Active') $status = 'ACTV';
	else $status = 'LEFT';			

	$query = "
		INSERT INTO Employee ( 
			created_at,
			opendoor_employee_code,
			department_id,
			sub_unit_id,
			sub_unit_code,
			status,
			surname,
			forename,
			initials,
			title,
			gender,
			fullname ";
			if(strlen($assoc_data['Single Sign On']) > 5) $query = $query .", webauth_code ";
			if(strlen($assoc_data['Email']) > 7) $query = $query .", email ";
			if($assoc_data['STAFFID']) $query = $query .", ref_hesa_staff_identifier ";
			if($assoc_data['Unit of Assessment'] > 0) $query = $query .", ref_unit_of_assessment ";
			if($assoc_data['Multiple Submissions'] == 'A' OR $assoc_data['Multiple Submissions'] == 'B') $query = $query .", ref_multiple_submission ";
			$query = $query . " 
		)
		VALUES (
			CURDATE(),
			'".$assoc_data['Personnel Number']."',
			'".get_DAISY_department_id($assoc_data['Cost Centre Code'])."',
			'".get_DAISY_sub_unit_id($assoc_data['Cost Centre Code'])."',
			'".$assoc_data['Cost Centre Code']."',
			'".$status."',
			'".addslashes(ucwords(strtolower($assoc_data['Surname'])))."',
			'".addslashes(ucwords(strtolower($assoc_data['Forename'])))."',
			'".$assoc_data['Initials']."',
			'".$assoc_data['Title']."',
			'".$assoc_data['Gender']."',
			'".addslashes(ucwords(strtolower($assoc_data['Full Name'])))."'
			";
			if(strlen($assoc_data['Single Sign On']) > 5) $query = $query . ", '".$assoc_data['Single Sign On']."' ";
			if(strlen($assoc_data['Email']) > 7) $query = $query . ", '".$assoc_data['Email']."' ";
			if($assoc_data['STAFFID']) $query = $query . ", '".$assoc_data['STAFFID']."' ";
			if($assoc_data['Unit of Assessment'] > 0) $query = $query . ", '".$assoc_data['Unit of Assessment']."' ";
			if($assoc_data['Multiple Submissions'] == 'A' OR $assoc_data['Multiple Submissions'] == 'B') $query = $query . ", '".$assoc_data['Multiple Submissions']."' ";
			$query = $query . "
		)
		";

	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());	
//dprint($query);	
//	echo 'S';
	return mysql_insert_id();
}

//--------------------------------------------------------------------------------------------------
function add_staff($row)
// add a staff record and return the id of the new record
{
	if($row['Personnel Number'] > 9990000)		//	if this is a college post holder
	{
		$dept_id = get_DAISY_department_id('00');
		$sub_unit_id = get_DAISY_sub_unit_id('00');
	} else
	{
		$dept_id = get_DAISY_department_id($row['Cost Centre Code']);
		$sub_unit_id = get_DAISY_sub_unit_id($row['Cost Centre Code']);
	}
	
	if($row['Person Status'] == 'Active') $status = 'ACTV';
	else $status = 'LEFT';			

	$query = "
		INSERT INTO Employee ( 
			created_at,
			opendoor_employee_code,
			department_id,
			sub_unit_id,
			sub_unit_code,
			status,
			surname,
			forename,
			initials,
			title,
			gender,
			fullname,
			old_opendoor_employee_code ";
//			if(strlen($row['Trent Employee Number']) > 3) $query = $query .", old_opendoor_employee_code ";
			if(strlen($row['Single Sign On']) > 5) $query = $query .", webauth_code ";
			if(strlen($row['Email']) > 7) $query = $query .", email ";
			if($row['STAFFID']) $query = $query .", ref_hesa_staff_identifier ";
			if($row['Unit of Assessment'] > 0) $query = $query .", ref_unit_of_assessment ";
			if($row['Multiple Submissions'] == 'A' OR $row['Multiple Submissions'] == 'B') $query = $query .", ref_multiple_submission ";
			$query = $query . " 
		)
		VALUES (
			CURDATE(),
			'".$row['Personnel Number']."',
			'".$dept_id."',
			'".$sub_unit_id."',
			'".$row['Cost Centre Code']."',
			'".$status."',
			'".addslashes(ucwords(strtolower($row['Surname'])))."',
			'".addslashes(ucwords(strtolower($row['Forename'])))."',
			'".$row['Initials']."',
			'".$row['Title']."',
			'".$row['Gender']."',
			'".addslashes(ucwords(strtolower($row['Full Name'])))."'
			'".$row['Trent Employee Number']."',
			";
//			if(strlen($row['Trent Employee Number']) > 3) $query = $query . ", '".$row['Trent Employee Number']."' ";
			if(strlen($row['Single Sign On']) > 5) $query = $query . ", '".$row['Single Sign On']."' ";
			if(strlen($row['Email']) > 7) $query = $query . ", '".$row['Email']."' ";
			if($row['STAFFID']) $query = $query . ", '".$row['STAFFID']."' ";
			if($row['Unit of Assessment'] > 0) $query = $query . ", '".$row['Unit of Assessment']."' ";
			if($row['Multiple Submissions'] == 'A' OR $row['Multiple Submissions'] == 'B') $query = $query . ", '".$row['Multiple Submissions']."' ";
			$query = $query . "
		)
		";

	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());	
//dprint($query);	
//	echo 'S';
	return mysql_insert_id();
}

//--------------------------------------------------------------------------------------------------
function update_staff0($e_id, $assoc_data)
// update the staff record
{
	$initials = $assoc_data['Initials'];
	$full_name_parts = explode(' ', $assoc_data['Full Name']);
	$title = end($full_name_parts);
	$initials = prev($full_name_parts);			//	get the original uppercase Initials
	$r_initials = ucwords(strtolower($initials));	//	Make them Ab

	$fullname = addslashes(ucwords(strtolower($assoc_data['Full Name'])));
	$fullname = str_replace(' '.$r_initials.' ', ' '.$initials.' ', $fullname);
	
	$query ="
		UPDATE Employee
		SET 
			updated_at = CURDATE(), ";
			$query = $query ."opendoor_employee_code = '".$assoc_data['Personnel Number']."', ";
			if(strlen($assoc_data['Single Sign On']) > 5) $query = $query ."webauth_code = '".$assoc_data['Single Sign On']."', ";
			if(strlen($assoc_data['STAFFID']) > 5) $query = $query ."ref_hesa_staff_identifier = '".$assoc_data['STAFFID']."', ";
			if($assoc_data['Unit of Assessment'] > 0) $query = $query ."ref_unit_of_assessment = '".$assoc_data['Unit of Assessment']."', ";
			if($assoc_data['Multiple Submissions'] == 'A' OR  $assoc_data['Multiple Submissions'] == 'B') $query = $query ."ref_multiple_submission = '".$assoc_data['Multiple Submissions']."', ";
			$query = $query ."sub_unit_code = '".$assoc_data['Cost Centre Code']."', ";
			
			if($assoc_data['Person Status'] == 'Active') $status = 'ACTV';
			else $status = 'LEFT';			
			$query = $query ."status = '$status', ";

			$query = $query ."surname = '".addslashes(ucwords(strtolower($assoc_data['Surname'])))."', ";
			$query = $query ."forename = '".addslashes(ucwords(strtolower($assoc_data['Forename'])))."', ";
			$query = $query ."initials = '".$initials."', ";
			$query = $query ."title = '".addslashes(utf8_decode($assoc_data['Title']))."', ";
			$query = $query ."gender = '".$assoc_data['Gender']."', ";
			if(strlen($assoc_data['Trent Employee Number']) > 3) $query = $query ."old_opendoor_employee_code = '".$assoc_data['Trent Employee Number']."', ";
//			$query = $query ."fullname = '".addslashes(ucwords(strtolower($assoc_data['Full Name'])))."', ";
			$query = $query ."fullname = '".$fullname."' ";
			if(strlen($assoc_data['Email']) > 7) $query = $query .", email = '".$assoc_data['Email']."' ";

			$query = $query ."WHERE id = $e_id
		";

//dprint($query);	
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
//	echo 's';
	return $result;
}

//--------------------------------------------------------------------------------------------------
function update_staff($e_id, $row)
// update the staff record
{
	if($row['Personnel Number'] > 9990000)		//	if this is a college post holder
	{
		$dept_id = get_DAISY_department_id('00');
		$sub_unit_id = get_DAISY_sub_unit_id('00');
	} else
	{
		$dept_id = get_DAISY_department_id($row['Cost Centre Code']);
		$sub_unit_id = get_DAISY_sub_unit_id($row['Cost Centre Code']);
	}
	
	$initials = $row['Initials'];
	$full_name_parts = explode(' ', $row['Full Name']);
	$title = end($full_name_parts);
	$initials = prev($full_name_parts);			//	get the original uppercase Initials
	$r_initials = ucwords(strtolower($initials));	//	Make them Ab

	$fullname = addslashes(ucwords(strtolower($row['Full Name'])));
	$fullname = str_replace(' '.$r_initials.' ', ' '.$initials.' ', $fullname);
	
	$query ="
		UPDATE Employee
		SET 
			updated_at = CURDATE(), ";
			$query = $query ."opendoor_employee_code = '".$row['Personnel Number']."', ";
			if(strlen($row['Single Sign On']) > 5) $query = $query ."webauth_code = '".$row['Single Sign On']."', ";
			if(strlen($row['STAFFID']) > 5) $query = $query ."ref_hesa_staff_identifier = '".$row['STAFFID']."', ";
			if($row['Unit of Assessment'] > 0) $query = $query ."ref_unit_of_assessment = '".$row['Unit of Assessment']."', ";
			if($row['Multiple Submissions'] == 'A' OR  $row['Multiple Submissions'] == 'B') $query = $query ."ref_multiple_submission = '".$row['Multiple Submissions']."', ";

//			$query = $query ."sub_unit_code = '".$row['Cost Centre Code']."', ";
			$query = $query ."department_id = '".$dept_id."', ";
			$query = $query ."sub_unit_id = '".$sub_unit_id."', ";
			
			if($row['Person Status'] == 'Active') $status = 'ACTV';
			else $status = 'LEFT';			
			$query = $query ."status = '$status', ";

			$query = $query ."surname = '".addslashes(ucwords(strtolower($row['Surname'])))."', ";
			$query = $query ."forename = '".addslashes(ucwords(strtolower($row['Forename'])))."', ";
			$query = $query ."initials = '".$initials."', ";
			$query = $query ."title = '".addslashes(utf8_decode($row['Title']))."', ";
			$query = $query ."gender = '".$row['Gender']."', ";
//			if(strlen($row['Trent Employee Number']) > 3) $query = $query ."old_opendoor_employee_code = '".$row['Trent Employee Number']."', ";
			$query = $query ."old_opendoor_employee_code = '".$row['Trent Employee Number']."', ";
//			$query = $query ."fullname = '".addslashes(ucwords(strtolower($row['Full Name'])))."', ";
			$query = $query ."fullname = '".$fullname."' ";
			if(strlen($row['Email']) > 7) $query = $query .", email = '".$row['Email']."' ";

			$query = $query ."WHERE id = $e_id
		";

//dprint($query);	
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
//	echo 's';
	return $result;
}

// ================================================< Posts >======================================================
//--------------------------------------------------------------------------------------------------
function set_post_status_to_LEFT()
// set status of post to 'LEFT' for all posts
{
	global $lf;
	global $par;
	global $hr;

//	$today = date('d-M-y');
	
	print "$lf Resetting personal status for all non-manual post holders $par";
	$query ="
		UPDATE Post p
		SET 
			p.person_status = 'LEFT', 
			p.enddate = CURDATE() 
		WHERE p.person_status = 'ACTV' AND (p.manual != 1 OR p.manual IS NULL)
		";
	return mysql_query($query) or die (with_obituary($query) . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function set_ssd_post_status_to_LEFT()
// set status of post to 'LEFT' for all SSD posts
{
	global $lf;
	global $par;
	global $hr;

//	$today = date('d-M-y');
	
	print "$lf Resetting personal status for all non-manual SSD post holders $par";
	$query ="
		UPDATE Post p
		INNER JOIN Department d ON d.id = p.department_id
		SET 
			p.person_status = 'LEFT', 
			p.enddate = CURDATE() 
		WHERE p.person_status = 'ACTV' AND (p.manual != 1 OR p.manual IS NULL)
		AND d.department_code LIKE '3C%'
		";
	return mysql_query($query) or die (with_obituary($query) . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function import_hris_posts($e_id, $row)
//	either update an exiting post or add a new one
{
	if($row['Personnel Number'] > 9990000)	//	if this is a college post holder
		$dept_id = get_DAISY_department_id('00');
	else
		$dept_id = get_DAISY_department_id($row['Cost Centre Code']);
	
	if($p_id = get_DAISY_post_id($e_id, $dept_id))
	{
		update_post($p_id, $row);
	} else
	{
		add_post($e_id, $row);
	}
}

//--------------------------------------------------------------------------------------------------
function add_post($e_id, $row)
// add a staff record
{	
	if($row['Person Status'] == 'Active') $status = 'ACTV';
	else $status = 'LEFT';

	if($row['Personnel Number'] > 9990000)	//	if this is a college post holder
	{
		$dept_id = get_DAISY_department_id('00');
		$sub_unit_id = get_DAISY_sub_unit_id('00');
		if($row['Staff Class Code'] === NULL OR strstr($row['Staff Class Code'], '-')) $row['Staff Class Code'] = 'AC'; 
//		$row['Staff Class Code'] = 'AC';
	} else
	{
		$dept_id = get_DAISY_department_id($row['Cost Centre Code']);
		$sub_unit_id = get_DAISY_sub_unit_id($row['Cost Centre Code']);
	}
	
	if($dept_id AND $sub_unit_id)
	{
		$query = "
			INSERT INTO Post ( 
				created_at,
				employee_id,
				fullname,
				grade,
				staff_classification_id,
				person_status,
				startdate,
				enddate,
				department_id,
				sub_unit_id
			) VALUES (
				CURDATE(),
				'".$e_id."',
				'".addslashes(ucwords(strtolower($row['Full Name'])))."',
				'".$row['Grade']."',
				'".get_DAISY_staff_class_id($row['Staff Class Code'])."',
				'".$status."',
				'".date('Y-m-d', strtotime($row['Contract Start Date']))."', ";
				if(date('Y', strtotime($row['Date Left'])) > 1980) $query = $query . "'".date('Y-m-d', strtotime($row['Date Left']))."', ";
				else $query = $query . "'', ";
				$query = $query . "
				'".$dept_id."',
				'".$sub_unit_id."'
			)
			";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
//dprint($query);

		return mysql_insert_id();
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function update_post0($post_id, $row)
// update the post record
{
	if($row['Person Status'] == 'Active') $status = 'ACTV';
	else $status = 'LEFT';
	
//	$staff_class_id = get_DAISY_staff_class_id($row['Staff Class Code'])

	$query ="
		UPDATE Post
		SET 
			person_status = '".$status."',
			staff_classification_code = '".$row['Staff Class Code']."',
			staff_classification_id = '".get_DAISY_staff_class_id($row['Staff Class Code'])."',
			grade = '".$row['Grade']."',
			startdate = '".$row['Contract Start Date']."',
			enddate = '".$row['Date Left']."',
			fullname = '".addslashes(ucwords(strtolower($row['Full Name'])))."'

		WHERE id = $post_id
		";

	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
//dprint($query);
//	echo 'p';
	return $result;
}

//--------------------------------------------------------------------------------------------------
function update_post($post_id, $row)
// update the post record
{
	if($row['Person Status'] == 'Active') $status = 'ACTV';
	else $status = 'LEFT';
	
//	$staff_class_id = get_DAISY_staff_class_id($row['Staff Class Code'])
	if($row['Personnel Number'] > 9990000)	//	if this is a college post holder
		if($row['Staff Class Code'] === NULL OR strstr($row['Staff Class Code'], '-')) $row['Staff Class Code'] = 'AC'; 
//		$row['Staff Class Code'] = 'AC';
	
	$query ="
		UPDATE Post
		SET 
			updated_at = CURDATE(),
			person_status = '".$status."',
			staff_classification_code = '".$row['Staff Class Code']."',
			staff_classification_id = '".get_DAISY_staff_class_id($row['Staff Class Code'])."',
			grade = '".$row['Grade']."',
			startdate = '".date('Y-m-d', strtotime($row['Contract Start Date']))."', ";
			if(date('Y', strtotime($row['Date Left'])) > 1980) $query = $query . "enddate = '".date('Y-m-d', strtotime($row['Date Left']))."', ";
			else $query = $query . "enddate = '', ";
			$query = $query . " fullname = '".addslashes(ucwords(strtolower($row['Full Name'])))."'

		WHERE id = $post_id
		";

	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
//dprint($query);
	return $result;
}

//===============================================< Helpers >======================================================
//--------------------------------------------------------------------------------------------------
function get_data0($query)
//	do the query and store the result in a table that is returned
{

	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());	

	while ($row = mysql_fetch_assoc($result))	
	{
		$table[] = $row;
	}

	return $table;		
}

//--------------------------------------------------------------------------------------------------
function with_obituary($query)
{
	if($_SERVER['TERM']) return  "Could not execute query:\n$query\nError = ";
	else return  "Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR> Error = ";
}

//--------------------------------------------------------------------------------------------------
function arraysearch($array, $key, $value)
{
    $results = array();

    if (is_array($array))
    {
        if (isset($array[$key]) && $array[$key] == $value)
            $results[] = $array;

        foreach ($array as $subarray)
            $results = array_merge($results, arraysearch($subarray, $key, $value));
    }

    return $results;
}

//--------------------------------------------------------------------------------------------------
function print_table0($table, $table_width, $switch)
//
{
//	$switch == 0	:	print NO line numbers and NORMAL header	
//	$switch == 1	:	DO print line numbers and NORMAL header	
//	$switch == 2	:	print NO line numbers and SPECIAL header	
//	$switch == 3	:	DO print line numbers and SPECIAL header	
	
	$header_colour = "DARKBLUE";
	$special_header_colour = "#FF6600";
	
	$header_font_colour = "WHITE";
	$linecount_bkgnd_colour = "LIGHTBLUE";
	$special_linecount_bkgnd_colour = "#FFCC66";

	$line_colour_1 = "WHITE";
	$line_colour_2 = "LIGHTGRAY";
	$alert_colour = "RED";
	
	$color[1] = "white"; 
	$color[2] = "lightgrey"; 

	if($switch === 2 OR $switch === 3) 
	{
		$header_colour = $special_header_colour;
		$linecount_bkgnd_colour = $special_linecount_bkgnd_colour;
	}

//	check if there is anything to print at all
	if ($table)
	{
//	get the keys from the table and use them as column titles
		$keys = array_keys($table[0]);	
		
//	if keys could be found print the table
		if ($keys)
		{
			if($switch % 2) print "Query returned ".count($table)." lines<BR>";
			print "<TABLE BORDER = 0>";
			print "<TR bgcolor=$header_colour>";
			if($switch % 2) print "<TH WIDTH = 30></TH>";
			foreach ($keys as $column_name) 
			{
				$width = $table_width["$column_name"];
				print "<TH WIDTH='$width'><FONT COLOR=$header_font_colour>$column_name</FONT></TH>";
			}
			print "</TR>";		
//	now print the rows
			$linecount = 0;
			foreach ($table as $row)
			{
				if ($line_colour == $line_colour_1)
					$line_colour = $line_colour_2;
				else
					$line_colour = $line_colour_1;

				print "<TR bgcolor=$line_colour>";
				$linecount++;
				if($switch % 2) print "<TD bgcolor = $linecount_bkgnd_colour>$linecount</TD>";
				$fieldcount = 0;
				foreach ($row as $field)
				{	
					$utf8_field = utf8_decode($field);
					print "<TD>$utf8_field</TD>";
				}
				$prev_row = $row;
				print "</TR>";
			}
			print "</TABLE>";
		} 
		else
		print "Could not find keys in $table, aborting! <BR>";
	} else
	print "<FONT COLOR=$alert_colour>The query returned no results!</FONT><BR>";
}

//--------------------------------------------------------------------------------------------------
function show_progress($table)
//
{
//	check if there is anything to print at all
	if ($table)
	{
//	get the keys from the table and use them as column titles
		$keys = array_keys($table[0]);	
		
//	if keys could be found print the table
		if ($keys)
		{
			print "Query returned ".count($table)." lines\n";
			$i = 0;
			foreach ($keys as $column_name) 
			{
				if($i++ > 0) print " | ";
				print "$column_name";
			}
			print "\n";		

//	now print the rows
			$linecount = 0;
			foreach ($table as $row)
			{
				print $linecount++ . " : ";
				$fieldcount = 0;
				$i = 0;
				foreach ($row as $field)
				{	
					if($i++ > 0) print " | ";
					$utf8_field = utf8_decode($field);
					print substr("$utf8_field",0,5);
//					print ".";
				}
				$prev_row = $row;
				print "\n";
			}
		} 
		else
		print "Could not find keys in $table, aborting! \n";
	} else
	print "The query returned no results!\n";
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_employee_id_from_en($employee_number)
{
	if($employee_number)
	{
		$query = "SELECT id FROM Employee WHERE opendoor_employee_code = '$employee_number'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_employee_id_from_email($email)
{
	if(isValidEmail($email))
	{
		$query = "SELECT id FROM Employee WHERE email = '$email'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function isValidEmail($email)
{
    return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email);
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_post_id($e_id, $dept_id)
//	return the (first) ID of the post a given employee has with a given department
{
	if($e_id AND $dept_id)
	{
		$query = "SELECT id FROM Post WHERE employee_id = $e_id AND department_id = $dept_id";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_department_code($sub_unit_code)
// get the department code for a given sub-unit code
{
//	$query = 'SELECT department_code FROM SubUnit WHERE sub_unit_code = "' . $sub_unit_code . '"';
	$query = "
		SELECT d.department_code 
		FROM SubUnit su 
		INNER JOIN Department d ON su.department_id = d.id
		WHERE su.sub_unit_code = '$sub_unit_code'
	";
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["department_code"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_department_id($sub_unit_code)
// get the department id for a given sub-unit code
{
	$query = "SELECT department_id FROM SubUnit WHERE sub_unit_code = '$sub_unit_code'";
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["department_id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_sub_unit_id($sub_unit_code)
{
	$query = "SELECT id FROM SubUnit WHERE sub_unit_code = '$sub_unit_code'";
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_staff_class_id($staff_class_code)
{
	$query = "SELECT id FROM StaffClassification WHERE staff_classification_code = '$staff_class_code'";
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function dprint0($data)
//	print the $data for debugging
{
	$the_color = 'RED';
	$the_color = '#FF6600';		//eleven-orange
	if($_SERVER['TERM']) print "\n------------------------------------------------------------\n$data\n------------------------------------------------------------\n";
	else print "<HR COLOR=$the_color><FONT COLOR=$the_color>$data</FONT><HR COLOR=$the_color>";
}


?> 