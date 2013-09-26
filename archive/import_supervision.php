<?php

//==================================================================================================
//
//	Script to update supervision data from OSS via the Data Warehouse into DAISY
//	Version for DAISY2
//	last changes: M.Opitz | 2012-06-21
//
//==================================================================================================

$version = '120315.1';
$version = '120621.1';	// added term code input

include '../opendb.php';

// some global variables used by the script
global $debug;										// output of debug information when set to TRUE
$debug = FALSE;

//==========================================< Here we go! >=========================================

$daisydb_conn = open_daisydb();						// open DAISY database
$dwdb_conn = open_data_warehousedb();				// open DATA WAREHOUSE database

global $term_code;
//$term_code = get_term_code_of_today();
//$term_code = 'TT12';								// uncomment for manual override

// ask for input
fwrite(STDOUT, "Enter term code (e.g. 'TT12') to update supervision data for: ");
// get input
$term_code = trim(fgets(STDIN));

//	check if input is valid
$allowed_term_codes = array("MT11", "HT12", "TT12", "MT12", "HT13", "TT13", "MT13", "HT14", "TT14", "MT14", "HT15", "TT15");
if(!in_array($term_code, $allowed_term_codes))
{
	print "'$term_code' is not a valid term code!\n";
	print "'execution stopped!\n";
	exit;
} 

print "V.$version\n\n";
print "1. Deleting old Supervision Data for: $term_code\n";
print "================================================\n";
delete_supervision($term_code);

	print "2a. Adding PGR Supervision Data for $term_code\n";
print "==================================================\n\n";

update_pgr_supervision($dwdb_conn, $term_code);

print "2a. Adding PGT Supervision Data for $term_code\n";
print "==============================================\n\n";

update_pgt_supervision($dwdb_conn, $term_code);

print "3. Setting Oxford Graduate Year for $term_code\n";
print "==============================================\n\n";

set_oxford_graduate_year($dwdb_conn, $daisydb_conn, $term_code);

odbc_close($dwdb_conn);								// close DATA WAREHOUSE database connection
mysql_close($daisydb_conn);							// close DAISY database connection

echo "\nALL DONE!\n";

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
	$term_id = get_DAISY_term_id($term_code);
	
	$query = "DELETE FROM Supervision WHERE term_id = '$term_id'";
	$result = mysql_query($query) or die ("Could not execute: $query - " . mysql_error());
	
	echo "\nrecords deleted for term $term_code!\n";
	
	return $result;
}
//--------------------------------------------------------------------------------------------------
function update_pgr_supervision($dwdb_conn, $term_code)
{
	global $debug;
		
//	$relenddate = get_term_startdate($term_code);
	$relenddate = get_term_enddate($term_code);
	
	$query = "	
		SELECT DISTINCT
			gsv.payroll_number, 
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
					AND gsv2.payroll_number != ''
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
		$employee_id = get_DAISY_employee_id(odbc_result($result, "payroll_number"));
		$student_id = get_DAISY_student_id(odbc_result($result, "personnumber"));
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

//		$supervisor_department_code = odbc_result($result, "sprvsrdept");
//		if (strlen($supervisor_department_code) != 4)
//			$supervisor_department_code = get_DAISY_department_code($supervisor_department_code);

//		$supervisor_department_id = get_DAISY_department_id($supervisor_department_code);
		$supervisor_department_id = get_employee_department_id($employee_id);

		$approval_status_id = 1;
//		check 
		if ($student_id == '' OR $supervisor_department_id == '' or $employee_id == '')
		{
				print " ";
				$noid_count++;
		} else
		{
//		otherwise add the record
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
		if ($count_rows % 100 == 0) print $count_rows;
	}
	odbc_free_result($result);
		
	print "\n";
	print "Total number of PGR students: $count_rows\n";
	print "$noid_count records NOT updated!\n";
	print "$add_count records added!\n";
}

//--------------------------------------------------------------------------------------------------
function update_pgt_supervision($dwdb_conn, $term_code)
{
	global $debug;
		
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
			AND gsv.payroll_number != ''
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
		$employee_id = get_DAISY_employee_id(odbc_result($result, "payroll_number"));
		$student_personid = odbc_result($result, "personid");
		$student_id = get_DAISY_student_id(odbc_result($result, "personnumber"));
		$year_of_student = odbc_result($result, "yearOfStudent");

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
				print "\nUnrecognized supervision type $svt for student personid = $student_personid\n";
				$supervision_type_id = '';
				break;
		}

		$supervision_startdate = odbc_result($result, "relstartdate");
		$supervision_enddate = odbc_result($result, "relenddate");
		$supervision_term_id = get_DAISY_term_id($term_code);
		$supervision_percentage = 100;
		$student_department_code = odbc_result($result, "deptcode");
		if (strlen($student_department_code) != 4)
			$student_department_code = get_DAISY_department_code($student_department_code);
		$student_department_id = get_DAISY_department_id($student_department_code);

//		$supervisor_department_code = odbc_result($result, "sprvsrdept");
//		if (strlen($supervisor_department_code) != 4)
//			$supervisor_department_code = get_DAISY_department_code($supervisor_department_code);
//		$supervisor_department_id = get_DAISY_department_id($supervisor_department_code);
		$supervisor_department_id = get_employee_department_id($employee_id);
		$approval_status_id = 1;
//		check 
		if ($student_id == '' OR $supervisor_department_id == '' or $employee_id == '' OR $supervision_type_id == '')
		{
				print " ";
//				print "st_id = $student_id | sv_dept_id = $supervisor_department_id | emp_id = $employee_id\n";
				$noid_count++;
		} else
		{
//		otherwise add the record
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
		if ($count_rows % 100 == 0) print $count_rows;
	}
	odbc_free_result($result);
		
	print "\n";
	print "Total number of PGT students: $count_rows\n";
	print "$noid_count records NOT updated!\n";
	print "$add_count records added!\n";
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
function get_DAISY_employee_id($employee_code)
//	returns the DAISY employee id for a given employee code
{
	$query = "SELECT id, department_id FROM Employee WHERE opendoor_employee_code = '$employee_code'";
	$result = mysql_query($query) or die ("Could not execute query: $query " . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	$depid = $result_row["department_id"];
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
	print "\nOGY for $counter previous MPhil students fixed.\n";
}


?> 

