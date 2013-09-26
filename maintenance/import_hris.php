<?php


//=============================================================================================================
//
//	Script to update HR data from HRIS via CSV file into DAISY incl. Grade!
//	
//	Last Update by Matthias Opitz, 2013-06-05
//
//=============================================================================================================
$version = '130213.1';
$version = '130327.3';		//	correcting uppercase double names while importing, now storing post number, using post number to determine posts
$version = '130412.1';		//	dealing with 'Grade' or 'Actual Grade' in HR table
$version = 'D3-130605.1';	//	DAISY3 compatible
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
	  	echo "Error: " . $_FILES["file"]["error"] . " - ".csv_error_explained($_FILES["file"]["error"])."<br />";
	}
	else
		if ($_FILES["file"]["size"] > 0) 
		{
			if($_POST['reset_status'])
			{
				set_staff_status_to_LEFT();				// set all ACTV staff to LEFT  before updating the data from HRIS
				set_post_status_to_LEFT();					// set all ACTV post to LEFT (and enter today as enddate!) before updating the data from HRIS
	
//				set_ssd_staff_status_to_LEFT();			// set all ACTV SSD staff to LEFT  before updating the data from HRIS
//				set_ssd_post_status_to_LEFT();			// set all ACTV SSD post to LEFT (and enter today as enddate!) before updating the data from HRIS
			}
//			test_hris_data();
			import_hris_data();
		} else print "Please select a valid HRIS file to upload.";
}

//--------------------------------------------------------------------------------------------------------------
function upload_file()
{
	$actionpage = $_SERVER["PHP_SELF"];
	print "
		<form action='$actionpage' method='post'
		enctype='multipart/form-data'>
		<label for='file'>Filename:</label>
		<input type='file' name='file' id='file' /> 
		<br />
		Reset current status: <input type='checkbox' name='reset_status' value='TRUE'>
		<BR />
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
	$fail_table = array();
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

	$surname = addslashes(ucwords(strtolower($row['Surname'])));
	$fullname = addslashes(ucwords(strtolower($row['Full Name'])));
	if(strstr($surname, '-'))
	{
		$surname = uppercase_hyphen_surname($surname);
		$fullname = uppercase_hyphen_fullname($fullname, $surname);
	}
	
	
	$query = "
		INSERT INTO Employee ( 
			created_at,
			opendoor_employee_code, 
			old_opendoor_employee_code, ";
	if($dept_id) $query = $query ."department_id, ";
	if($sub_unit_id) $query = $query ."sub_unit_id, ";
	$query = $query . "
			status,
			surname,
			forename,
			initials,
			title,
			gender,
			fullname ";
//	if(strlen($row['Trent Employee Number']) > 3) $query = $query .", old_opendoor_employee_code ";
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
			'" . $row['Trent Employee Number'] . " ',  " ;

	if($dept_id) $query = $query ."$dept_id, ";
	if($sub_unit_id) $query = $query ."$sub_unit_id, ";
	$query = $query . "
			'$status',
			'$surname',
			'".addslashes(ucwords(strtolower($row['Forename'])))."',
			'".$row['Initials']."',
			'".$row['Title']."',
			'".$row['Gender']."',
			'$fullname'
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
//dprint("deptID = $dept_id | subunit ID = $sub_unit_id");
	}
	
	$initials = $row['Initials'];
	$full_name_parts = explode(' ', $row['Full Name']);
	$title = end($full_name_parts);
	$initials = prev($full_name_parts);			//	get the original uppercase Initials
	$r_initials = ucwords(strtolower($initials));	//	Make them Ab

	$fullname = addslashes(ucwords(strtolower($row['Full Name'])));
	$fullname = str_replace(' '.$r_initials.' ', ' '.$initials.' ', $fullname);
	$surname = addslashes(ucwords(strtolower($row['Surname'])));
	
	if(strstr($surname, '-'))
	{
		$surname = uppercase_hyphen_surname($surname);
		$fullname = uppercase_hyphen_fullname($fullname, $surname);
	}
	
	
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
			if($dept_id) $query = $query ."department_id = ".$dept_id.", ";
			if($sub_unit_id) $query = $query ."sub_unit_id = ".$sub_unit_id.", ";
			
			if($row['Person Status'] == 'Active') $status = 'ACTV';
			else $status = 'LEFT';			
			$query = $query ."status = '$status', ";

//			$query = $query ."surname = '".addslashes(ucwords(strtolower($row['Surname'])))."', ";
			$query = $query ."surname = '$surname', ";
			$query = $query ."forename = '".addslashes(ucwords(strtolower($row['Forename'])))."', ";
			$query = $query ."initials = '".$initials."', ";
			$query = $query ."title = '".addslashes(utf8_decode($row['Title']))."', ";
			$query = $query ."gender = '".$row['Gender']."', ";
//			if(strlen($row['Trent Employee Number']) > 3) $query = $query ."old_opendoor_employee_code = '".$row['Trent Employee Number']."', ";
			$query = $query ."old_opendoor_employee_code = '".$row['Trent Employee Number']."', ";
//			$query = $query ."fullname = '".addslashes(ucwords(strtolower($row['Full Name'])))."', ";
			$query = $query ."fullname = '$fullname.' ";
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
	
	$post_number = $row['Post Number'];
	$startdate = date('Y-m-d', strtotime($row['Contract Start Date']));
	
//	if($p_id = get_DAISY_post_id($e_id, $dept_id))
	if($p_id = get_DAISY_post_id($e_id, $dept_id, $post_number))
	{
		update_post($p_id, $row);
	} else
	{
		if($p_id = get_DAISY_post_id_no_pn($e_id, $dept_id, $startdate)) update_post($p_id, $row);
		else add_post($e_id, $row);
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
	
	$surname = addslashes(ucwords(strtolower($row['Surname'])));
	$fullname = addslashes(ucwords(strtolower($row['Full Name'])));
	if(strstr($surname, '-'))
	{
		$surname = uppercase_hyphen_surname($surname);
		$fullname = uppercase_hyphen_fullname($fullname, $surname);
	}
	
	$grade = $row['Grade'];
	if (!$grade) $grade = $row['Actual Grade'];

	if($dept_id AND $sub_unit_id)
	{
		$query = "
			INSERT INTO Post ( 
				created_at,
				employee_id,
				fullname,
				grade,
				post_number,
				staff_classification_id,
				fte_department, 
				person_status,
				startdate,
				enddate,
				department_id,
				sub_unit_id
			) VALUES (
				CURDATE(),
				'".$e_id."',
				'".$fullname."',
				'".$grade."',
				'".$row['Post Number']."',
				'".get_DAISY_staff_class_id($row['Staff Class Code'])."',
				'".$row['FTE']."',
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
function update_post($post_id, $row)
// update the post record
{
	if($row['Person Status'] == 'Active') $status = 'ACTV';
	else $status = 'LEFT';
	
//	$staff_class_id = get_DAISY_staff_class_id($row['Staff Class Code'])
	if($row['Personnel Number'] > 9990000)	//	if this is a college post holder
		if($row['Staff Class Code'] === NULL OR strstr($row['Staff Class Code'], '-')) $row['Staff Class Code'] = 'AC'; 
//		$row['Staff Class Code'] = 'AC';
	
	$surname = addslashes(ucwords(strtolower($row['Surname'])));
	$fullname = addslashes(ucwords(strtolower($row['Full Name'])));
	if(strstr($surname, '-'))
	{
		$surname = uppercase_hyphen_surname($surname);
		$fullname = uppercase_hyphen_fullname($fullname, $surname);
	}
	
	$grade = $row['Grade'];
	if (!$grade) $grade = $row['Actual Grade'];

	$query ="
		UPDATE Post
		SET 
			updated_at = CURDATE(),
			person_status = '".$status."',
			fullname = '".$fullname."',
			staff_classification_code = '".$row['Staff Class Code']."',
			staff_classification_id = '".get_DAISY_staff_class_id($row['Staff Class Code'])."',
			grade = '".$grade."',
			fte_department = '".$row['FTE']."',
			post_number = '".$row['Post Number']."',
			startdate = '".date('Y-m-d', strtotime($row['Contract Start Date']))."', ";
			if(date('Y', strtotime($row['Date Left'])) > 1980) $query = $query . "enddate = '".date('Y-m-d', strtotime($row['Date Left']))."', ";
			else $query = $query . "enddate = '', ";
			$query = $query . " fullname = '". $fullname ."'

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
function get_DAISY_post_id($e_id, $dept_id, $post_number)
//	return the (first) ID of the post a given employee has with a given post number
{
	if($e_id AND $dept_id AND $post_number)
	{
		$query = "SELECT id FROM Post WHERE employee_id = $e_id AND department_id = $dept_id AND post_number = '$post_number'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_post_id_no_pn($e_id, $dept_id, $startdate)
//	return the (first) ID of the post a given employee has with a given department when the post number stored in DAISY is empty
{
	if($e_id AND $dept_id)
	{
//		$query = "SELECT id FROM Post WHERE employee_id = $e_id AND department_id = $dept_id AND startdate = $startdate AND (post_number = '' OR post_number IS NULL)";
//		$query = "SELECT id FROM Post WHERE employee_id = $e_id AND department_id = $dept_id AND startdate = $startdate";
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



//--------------------------------------------------------------------------------------------------------------
function uppercase_hyphen_surname($surname)
//	uppercase each word in a double name with hyphens
{
	return str_replace(' ', '-', ucwords(str_replace('-', ' ', $surname)));
}

//--------------------------------------------------------------------------------------------------------------
function uppercase_hyphen_fullname($fullname, $surname)
//	replace the first word in a fullname (the surname) with a uppercased surname given
{
	$words = explode(' ',trim($fullname));	//	get the words of the fullname - the 1st will be the surname
	return str_replace($words[0], $surname, $fullname);
}


?> 