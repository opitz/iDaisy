<?php

//==================================================================================================
//
//	Script to update the AssessmentUnitDegreeProgramme table
//
//	last changes: M.Opitz | 2013-04-03
//
//==================================================================================================

$version = '130403.1';	//	1st version

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

print "<B>DAISY update</B> |  Update AssessmentUnitDegreeProgramme relations  v.$version";
print "<HR>";

//==========================================< Here we go! >=========================================
/*
// ask for input
fwrite(STDOUT, "Enter term code (e.g. 'TT12') to update supervision data for: ");
// get input
$term_code = trim(fgets(STDIN));
*/
$daisydb_conn = open_daisydb();						// open DAISY database

if(current_user_is_in_DAISY_user_group("Overseer"))
	go_ahead();
else
	show_no_mercy();

mysql_close($daisydb_conn);							// close DAISY database connection
	
//--------------------------------------------------------------------------------------------------
function go_ahead()
{
	get_options();
	$term_code = $_POST['term_code'];

	if($term_code)
	{
		$dwdb_conn = open_data_warehousedb();				// open DATA WAREHOUSE database
		
//		if($_POST['replace']) delete_supervision($term_code);
//		update_pgr_supervision($dwdb_conn, $term_code);
//		update_pgt_supervision($dwdb_conn, $term_code);
//		set_oxford_graduate_year($dwdb_conn, $daisydb_conn, $term_code);
		
//	if supervison data is imported from a Hilary Term copy MSc-Thesis supervision back into the previous Michaelmas Term
		if(substr($term_code, 0, 2) == 'HT')
		{
//			$prev_mt_code = 'MT' . substr($term_code,2,2) - 1;
			$prev_mt_code = 'MT';
dprint($prev_mt_code);
		}	


		odbc_close($dwdb_conn);								// close DATA WAREHOUSE database connection

		echo "<BR /><B>ALL DONE!</B><BR />";
	}
	else
	{
		print "Please select a Term and click on 'Submit' to start the import";
	}
}

//--------------------------------------------------------------------------------------------------
function get_options()
{
	$todays_term = get_term_code_of_today();
//print $todays_term."<BR />";
	$term_options = array("MT11", "HT12", "TT12", "MT12", "HT13", "TT13", "MT13", "HT14", "TT14", "MT14", "HT15", "TT15");
	$actionpage = $_SERVER["PHP_SELF"];
	print "<FORM ACTION='$actionpage' method=POST>";
	
	print "Select Term to update the data for: ";
	print "<SELECT NAME='term_code'>";
	print "<OPTION VALUE = ''>Select a Term</OPTION>";
	foreach($term_options AS $term)
	{
		if ($term == $todays_term) print "<OPTION SELECTED = SELECTED>$term</OPTION>";
		else print "<OPTION>$term</OPTION>";
	}
	print "</SELECT>";
	print "<BR />";
	print "Check if you want to replace exiting data for this term: ";
	if ($_POST['replace']) print "<input type='checkbox' name='replace' value='TRUE' checked='checked'>";
	else print "<input type='checkbox' name='replace' value='TRUE'>";

	print "		
		<BR />
		<BR />
		
		<input type='submit' name='submit' value='Submit' />
		</form>
	";
}

//--------------------------------------------------------------------------------------------------
function get_term_code_of_today()
{
	$the_date = date('Y-m-d');
	$query = "
		SELECT * 
		FROM Term 
		WHERE startdate <= '$the_date'
		AND enddate >= '$the_date'
		";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["term_code"];
}

//--------------------------------------------------------------------------------------------------
function delete_supervision($term_code)
// ATTENTION: THIS IS THE DESTRUCTIVE BIT!!!
// Delete all supervision records for a particular term
{
	print "<H3><FONT COLOR=RED>Deleting existing Supervision Data for: $term_code</FONT></H3>";
	
	$term_id = get_DAISY_term_id($term_code);
	
	$query = "DELETE FROM Supervision WHERE term_id = '$term_id'";
	$result = mysql_query($query) or die ("Could not execute: $query - " . mysql_error());
	
	echo "All supervision records for term <B>$term_code</B> have been deleted!<BR />";
	
	return $result;
}
//--------------------------------------------------------------------------------------------------
function update_pgr_supervision($dwdb_conn, $term_code)
{
	global $debug;
		
	print "<H3>Adding PGR Supervision Data for $term_code</H3>";

//	$relenddate = get_term_startdate($term_code);
	$relenddate = get_term_enddate($term_code);
	
	$query = "	
		SELECT DISTINCT
			gsv.payroll_number, 
			gsv.email_address, 
			sv.personid, 
			std.personnumber, 
			std.lastname,
			std.firstname,
			att.deptcode, 
			att.yearOfStudent,
			sv.sprvsrnametitle, 
			sv.sprvsrstartdate, 
			sv.sprvsrenddate, 
			sv.sprvsrpercent, 
			sv.sprvsrdept					 
		FROM 
			xxigs_df_res_supervisors sv, 
			gss_supervisors_v gsv, 
			xxigs_df_prog_attempts att, 
			xxigs_df_student_details std 
		WHERE 
			(sv.sprvsrenddate IS NULL OR sv.sprvsrenddate BETWEEN '$relenddate' AND '9999')
			AND (sv.sprvsrstartdate BETWEEN '1900' AND '$relenddate')
			AND gsv.oss_person_number = sv.sprvsrpersonnumber
			AND att.personid = sv.personid
			AND sv.personid = std.personid
			AND gsv.payroll_number != ''
			AND att.progattmptstatus = 'ENROLLED'
			AND att.progtype = 'PGRAD_R'
			AND sv.sprvsrstartdate = 
			(
				SELECT DISTINCT
					MAX(sv2.sprvsrstartdate)
				FROM
					xxigs_df_res_supervisors sv2, 
					gss_supervisors_v gsv2, 
					xxigs_df_prog_attempts att2, 
					xxigs_df_student_details std2
				WHERE
					sv.personid = sv2.personid
					AND (sv2.sprvsrenddate IS NULL OR sv2.sprvsrenddate BETWEEN '$relenddate' AND '9999')
					AND (sv2.sprvsrstartdate BETWEEN '1900' AND '$relenddate')
					AND gsv2.oss_person_number = sv2.sprvsrpersonnumber
					AND att2.personid = sv2.personid
					AND sv2.personid = std2.personid
					AND att2.progattmptstatus = 'ENROLLED'
					AND att2.progtype = 'PGRAD_R'					
			)
		ORDER BY 
			att.deptcode 
		DESC
		";
	
	if ($debug) print "<FONT color=#FF6600>... update_pgr_supervision | query = $query</FONT><BR>";
	$result = odbc_exec($dwdb_conn, $query);

	$count_rows = 0;
	$noid_count = 0;
	$add_count = 0;
	
	while(odbc_fetch_row($result)) 
	{
		++$count_rows;
		$payroll_number = odbc_result($result, "payroll_number");
		$email = odbc_result($result, "email_address");

//	check if there is a payroll number
		if($payroll_number)
		{
			if(!$employee_id = get_DAISY_employee_id_from_employee_code($payroll_number))		//if the payroll number does not return an ID...
				$employee_id = get_DAISY_employee_id_from_old_employee_code($payroll_number);	// ... try to match against the old (Trent) employee number
		}
		else
			$employee_id = get_DAISY_employee_id_from_email($email);

		$student_id = get_DAISY_student_id(odbc_result($result, "personnumber"));
		if($employee_id) $supervisor_department_id = get_employee_department_id($employee_id);

		if($employee_id AND $student_id AND $supervisor_department_id)
//		add the record
		{
			$supervision_type_id = get_DAISY_supervision_type_id("DPhil");
			$supervision_startdate = odbc_result($result, "sprvsrstartdate");
			$supervision_enddate = odbc_result($result, "sprvsrenddate");
			$supervision_term_id = get_DAISY_term_id($term_code);
			$supervision_percentage = odbc_result($result, "sprvsrpercent");
			$student_department_code = odbc_result($result, "deptcode");
			$year_of_student = odbc_result($result, "yearOfStudent");
		
			if (strlen($student_department_code) != 4)
				$student_department_code = get_DAISY_department_code($student_department_code);
			$student_department_id = get_DAISY_department_id($student_department_code);

			$approval_status_id = 1;
			$query = "
				INSERT INTO Supervision (
					employee_id, 
					student_id, 
					supervision_type_id, 
					startdate,
					enddate,
					term_id, 
					percentage, 
					department_id, 
					supervisor_department_id,
					approval_status_id,
					oxford_graduate_year
				)
				VALUES (
					'$employee_id', 
					'$student_id', 
					'$supervision_type_id', 
					'$supervision_startdate',
					'$supervision_enddate',
					'$supervision_term_id', 
					'$supervision_percentage', 
					'$student_department_id', 
					'$supervisor_department_id',
					'$approval_status_id',
					'$year_of_student'
				)";
			print".";
			$update_result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
			$add_count++;
		} else
		{
			print "!";
			$noid_count++;
		}
		if ($count_rows % 100 == 0) print $count_rows;
	}
	odbc_free_result($result);
		
//	print "<BR />";
	print "Total number of PGR students: $count_rows<BR />";
	print "$noid_count records NOT updated!<BR />";
	print "$add_count records added!<BR />";
}

//--------------------------------------------------------------------------------------------------
function update_pgt_supervision($dwdb_conn, $term_code)
{
	global $debug;
		
	print "<H3>Adding PGT Supervision Data for $term_code</H3>";

//	$relenddate = get_term_startdate($term_code);
	$relenddate = get_term_enddate($term_code);
	$query = "
		SELECT
			rel.reltype,
			gsv.payroll_number, 
			rel.personid, 
			std.personnumber, 
			att.deptcode, 
			att.yearOfStudent,
			rel.relname, 
			rel.relemail,
			rel.relstartdate, 
			rel.relenddate
		FROM
			xxigs_df_relationships rel,
			gss_supervisors_v gsv, 
			xxigs_df_prog_attempts att, 
			xxigs_df_student_details std 
		WHERE 
			(rel.relenddate IS NULL OR rel.relenddate BETWEEN '$relenddate' AND '9999')
			AND gsv.oss_person_number = rel.relpersonnumber
			AND att.personid = rel.personid
			AND rel.personid = std.personid
			AND att.progattmptstatus = 'ENROLLED'
			AND att.progtype = 'PGRAD_T'
			AND rel.reltype != 'CollAdvisor Is'
			AND rel.reltype != 'Superseded By'
		ORDER BY 
			att.deptcode DESC
			";
	
	if ($debug) print "<FONT color=#FF6600>... update_pgt_supervision | query = $query</FONT><BR>";
	$result = odbc_exec($dwdb_conn, $query);

	$count_rows = 0;
	$noid_count = 0;
	$add_count = 0;
	
	while(odbc_fetch_row($result)) 
	{
		++$count_rows;
		$payroll_number = odbc_result($result, "payroll_number");
		$email = odbc_result($result, "relemail");

//	check if there is a payroll number
		if($payroll_number)
		{
			if(!$employee_id = get_DAISY_employee_id_from_employee_code($payroll_number))		//if the payroll number does not return an ID...
				$employee_id = get_DAISY_employee_id_from_old_employee_code($payroll_number);	// ... try to match against the old (Trent) employee number
		}
		else
			$employee_id = get_DAISY_employee_id_from_email($email);
			
		if($employee_id) $supervisor_department_id = get_employee_department_id($employee_id);
		$student_id = get_DAISY_student_id(odbc_result($result, "personnumber"));
		
//	get the supervision type
		$svt = odbc_result($result, "reltype");
		switch ($svt)
		{
			case "Supervised By":
				$supervision_type_id = get_DAISY_supervision_type_id("PGRAD_T");
				break;
			case "MPhil Thesis Supervised By":
				$supervision_type_id = get_DAISY_supervision_type_id("MPhil-Thesis");
				break;
			case "MSc Dissertation Supervised By":
				$supervision_type_id = get_DAISY_supervision_type_id("MSc-Thesis");
				break;
			case "MSt Dissertation Supervised By":
				$supervision_type_id = get_DAISY_supervision_type_id("MSc-Thesis");
				break;
			default:
				print "<BR />Unrecognized supervision type $svt for student personid = $student_personid<BR />";
				$supervision_type_id = '';
				break;
		}

		if ($student_id AND $supervisor_department_id AND $employee_id AND $supervision_type_id)
//		add the record
		{
			$student_personid = odbc_result($result, "personid");
			$year_of_student = odbc_result($result, "yearOfStudent");

			$supervision_startdate = odbc_result($result, "relstartdate");
			$supervision_enddate = odbc_result($result, "relenddate");
			$supervision_term_id = get_DAISY_term_id($term_code);
			$student_department_code = odbc_result($result, "deptcode");
			if (strlen($student_department_code) != 4)
				$student_department_code = get_DAISY_department_code($student_department_code);
			$student_department_id = get_DAISY_department_id($student_department_code);

			$supervision_percentage = 100;
			$approval_status_id = 1;

			$query = "
				INSERT INTO Supervision (
					employee_id, 
					student_id, 
					supervision_type_id, 
					startdate,
					enddate,
					term_id, 
					percentage, 
					department_id, 
					supervisor_department_id,
					approval_status_id,
					oxford_graduate_year
				)
				VALUES (
					'$employee_id', 
					'$student_id', 
					'$supervision_type_id', 
					'$supervision_startdate',
					'$supervision_enddate',
					'$supervision_term_id', 
					'$supervision_percentage', 
					'$student_department_id', 
					'$supervisor_department_id',
					'$approval_status_id',
					'$year_of_student'
				)";
			print".";
			$update_result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
			$add_count++;
		}
		else
		{
			print "!";
			$noid_count++;
		}
		if ($count_rows % 100 == 0) print $count_rows;
	}
	odbc_free_result($result);
		
//	print "<BR />";
	print "Total number of PGT students: $count_rows<BR />";
	print "$noid_count records NOT updated!<BR />";
	print "$add_count records added!<BR />";
}

//--------------------------------------------------------------------------------------------------
function delete_msc_thesis_supervision($term_code)
// deletes all MSc-Thesis Supervisionfro the given term
{
	$term_id = get_DAISY_term_id($term_code);
	$query = "
DELETE 
sv.*

FROM Supervision sv 
INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id 

WHERE sv.term_id = $term_id
AND svt.supervision_type_code LIKE 'MSc%'	
	";
	return mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
}

//--------------------------------------------------------------------------------------------------
function copy_msc_thesis_supervision($from_term_code, $to_term_code)
// copies all MSc-Thesis Supervision data from one term to another
{
	print "<H3>Copying MSc Thesis Supervision Data from $from_term_code to $to_term_code</H3>";

	$from_term_id = get_DAISY_term_id($from_term_code);
	$to_term_id = get_DAISY_term_id($to_term_code);
	$query = "
INSERT INTO Supervision
  ( 
  employee_id,
  student_id,
  oxford_graduate_year,
  supervision_type_id,
  supervisor_department_id,
  department_id,
  startdate,
  enddate,
  percentage,
  approval_status_id,
  term_code,
  term_id
  )
SELECT 
  sv.employee_id, 
  sv.student_id,
  sv.oxford_graduate_year,
  sv.supervision_type_id,
  sv.supervisor_department_id,
  sv.department_id,
  sv.startdate,
  sv.enddate,
  sv.percentage,
  sv.approval_status_id,
  '$to_term_code',
  $to_term_id
FROM 
  Supervision sv
  INNER JOIN Term t ON sv.term_id = t.id
  INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
WHERE 
  t.term_code = '$from_term_code'
  AND svt.supervision_type_code LIKE 'MSc%'
	";
	return mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
}





//--------------------------------------------------------------------------------------------------
function get_term_startdate($term_code)
// returns the start date for a given term code
{
	global $debug;

	$query = "SELECT * FROM `Term` WHERE term_code = '$term_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());

	$row = mysql_fetch_assoc($result);
	return substr ( $row['startdate'], 0, 10);
}

//--------------------------------------------------------------------------------------------------
function get_term_enddate($term_code)
// returns the end date for a given term code
{
	global $debug;

	$query = "SELECT * FROM `Term` WHERE term_code = '$term_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());

	$row = mysql_fetch_assoc($result);
	return substr ( $row['enddate'], 0, 10);
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_employee_id_from_employee_code($employee_code)
//	returns the DAISY employee id for a given employee code
{
	$query = "SELECT id, department_id FROM Employee WHERE opendoor_employee_code = '$employee_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	$depid = $result_row["department_id"];
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_employee_id_from_old_employee_code($employee_code)
//	returns the DAISY employee id for a given OLD employee code
{
	$query = "SELECT id, department_id FROM Employee WHERE old_opendoor_employee_code = '$employee_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);

	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_employee_id_from_email($email)
//	returns the DAISY employee id for a given email address
{
	$query = "SELECT id, department_id FROM Employee WHERE email = '$email'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);

	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_student_id($oss_personnumber)
//	returns the DAISY student id for a given student OSS code
{
	$query = "
		SELECT 
			id 
		FROM 
			Student 
		WHERE 
			oss_student_code = '$oss_personnumber'
		";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_supervision_type_id($supervision_type_code)
//	returns the DAISY supervision type id for a given supervision type code
{
	$query = "SELECT id FROM SupervisionType WHERE supervision_type_code = '$supervision_type_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_term_id($term_code)
//	returns the DAISY term id for a given term code
{
	$query = "SELECT id FROM Term WHERE term_code = '$term_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_sub_unit_code($department_code)
//	returns the (first found) sub-unit code for a given department code
// 	use this to find the sub-unit code for a department that has no sub-units [...I know...]
{
	$query = "SELECT sub_unit_code FROM SubUnit WHERE department_code = '$department_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["sub_unit_code"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_department_code($sub_unit_code)
//	returns the corresponding department code for a given sub-unit code
{
	switch ($sub_unit_code)
	{
		case '2B':
			return '2B00';
			break;
		case '3C':
			return '3C00';
			break;
		case '4D':
			return '4D00';
			break;
		case '5E':
			return '5E00';
			break;
		default:		
			$query = "SELECT department_code FROM SubUnit WHERE sub_unit_code = '$sub_unit_code'";
			$result = mysql_query($query) or die ('Could not execute "SELECT Department" query: ' . mysql_error());
			$result_row = mysql_fetch_assoc($result);
			return $result_row["department_code"];
			break;
	}
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_department_id($department_code)
//	returns the DAISY id for a given department code
{
	$query = "SELECT id FROM Department WHERE department_code = '$department_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_employee_department_id($employee_id)
//	returns the DAISY id of the department for a given employee id
{
	$query = "SELECT department_id FROM Employee WHERE id = '$employee_id'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["department_id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_sub_unit_id($sub_unit_code)
//	returns the DAISY id for a given sub-unit code
{
	$query = "SELECT id FROM SubUnit WHERE sub_unit_code = '$sub_unit_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_programme_id($prog_code)
//	returns the DAISY id for a given department code
{
	$query = "SELECT id FROM DegreeProgramme WHERE degree_programme_code = '$prog_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["id"];
}

//--------------------------------------------------------------------------------------------------
function student_exists($conn, $oss_student_code)
// returns TRUE if a student with that code exists in DAISY. Will return FALSE otherwise.
{
	$query = "	
				SELECT * 
				FROM Student
				WHERE oss_student_code = '$oss_student_code'
				 ";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());

	if (mysql_num_rows($result))
		return TRUE;
	else
		return FALSE;
}

//--------------------------------------------------------------------------------------------------------------

function set_oxford_graduate_year($dwdb_conn, $conn, $term_code)
{
	print "<H3>Setting Oxford Graduate Year for $term_code</H3>";

	$term_id = get_DAISY_term_id($term_code);

	$query = "
		SELECT 
			prev.personid,
			std.personnumber,
			std.lastname,
			std.firstname, 
			pa.yearOfStudent
		FROM 
			xxigs_df_prevox prev LEFT JOIN xxigs_df_student_details std ON prev.personid = std.personid,
			xxigs_df_prog_attempts pa
		WHERE 
			pa.personid= prev.personid
			AND pa.progattmptstatus = 'ENROLLED'
			AND prev.prevAwdAim ='MPHIL' 
			AND prev.prevProgAttmptStatus = 'COMPLETED'
		ORDER BY
			std.lastname
		ASC
				";

	$result = odbc_exec($dwdb_conn, $query);
 	$counter = 0;
 	
	while(odbc_fetch_row($result)) 
	{
		$student_id = get_DAISY_student_id(odbc_result($result, "personnumber"));
		$programme_year = odbc_result($result, "yearOfStudent");

		$query2 = "
			UPDATE 
				Supervision 
			SET 
				oxford_graduate_year = $programme_year+1 
			WHERE 
				student_id = $student_id
				AND term_id = $term_id
			";
		$update_result = mysql_query($query2) or die ("Could not execute query: $query2 " . mysql_error());
		print "+";
		$counter++;
	}
	print "OGY for $counter previous MPhil students fixed.<BR />";
}


?> 

