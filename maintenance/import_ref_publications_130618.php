<?php


//=============================================================================================================
//
//	Script to update REF publication data from the CSV file following the REF stucture
//	
//	Last Update by Matthias Opitz, 2013-06-18
//
//=============================================================================================================
$version = '130618.1'; // starting...
//==============================================< Here we go! >====================================================

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

//print "<B>DAISY update</B> |  REF 'Publication' data from CSV file  v.$version";
//print "<HR>";

$conn = open_daisydb();

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!$ay_id) $ay_id = get_current_academic_year_id();
$_POST['ay_id'] = $ay_id;

print_header("Update - REF Publication data from CSV file");

if(current_user_is_in_DAISY_user_group("Overseer"))
	go_ahead();
else
	show_no_mercy();

mysql_close($conn);
show_footer($version,0);


//--------------------------------------------------------------------------------------------------
function go_ahead()
{
	upload_file();
print "<HR>";
print $_POST['dept_id'];
	print "<HR>";
	if ($_FILES["file"]["error"] > 0)
	{
	  	echo "Error: " . $_FILES["file"]["error"] . "<br />";
	}
	else
		if ($_FILES["file"]["size"] > 0) 
		{
//			test_publication_data();
			import_publication_data();

		} else print "Please select a valid Publication file to upload.";
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
function test_publication_data()
{
	$publication_file = $_FILES['file']['name'];
	$publication_tmp_file = $_FILES['file']['tmp_name'];

	$publication_table = csv2table($publication_tmp_file, 1);
	$num_of_recs = count($publication_table);
	print "read $num_of_recs records from $publication_file<BR />";

	if ($publication_table) p_table($publication_table);
}

//--------------------------------------------------------------------------------------------------
function import_publication_data()
{
	$publication_file = $_FILES['file']['name'];
	$publication_tmp_file = $_FILES['file']['tmp_name'];
	$publication_table = csv2table($publication_tmp_file, 1);

	$num_of_recs = count($publication_table);
	print "read $num_of_recs records from $publication_file<P>";

//	now check each publication in the table 
	$result_table = array();

	$publications_added = 0;
	$publications_updated = 0;
	$journals_added = 0;
	$journals_updated = 0;
	$publishers_added = 0;
	$authors_added = 0;
	$authors_updated = 0;
	$external_authors_added = 0;
	$external_authors_updated = 0;

	

	$i = 0;
	if($publication_table)
	{
//	get the department
		$dept_uoa = $publication_table[0]['unitOfAssessment'] . $publication_table[0]['multipleSubmission'];
		$dept_id = get_dept_id_from_uoa($dept_uoa);		// get the department ID
		if($dept_id)
		{
			$department = read_record('Department',$dept_id);
			print "Importing Publications from REF data for <B>" . $department['department_name'] . "</B><P>";
		}
		else dprint("DEPT WITH A UOA '$dept_uoa' IS UNKNOWN!?!");
		
		foreach($publication_table AS $row)
		{
			$i++;
			if($row['title'] != NULL AND !known_title($row['title']))	// a new publication, we will have to add it!
			{
				$publications_added++;
				print "$i - New Publication: <B>" . $row['title'] . "</B><BR>";

//	check, if we know the author
				$author_id = check_author($row['staffIdentifier']);
				
				if($author_id)	// we do!
				{
				
//	get publication type ID
					$publication_type_id = get_publishing_type_id($row['outputType']);

//	get publisher ID - add a new publisher if needed
					$publisher_id = known_publisher($row['publisher']);
					if(!$publisher_id)
					{
						$publisher_id = add_publisher($row['publisher']);
						if($publisher_id) $publishers_added++;
					}

//	get journal ID - add a new journal if needed
					$journal_id = check_journal($row['volumeTitle'], $row['issn']);
					if(!$journal_id)
					{
						$journal_id = add_journal($row['volumeTitle'], $row['issn'], $dept_id);
						if($journal_id) $journals_added++;
					}
				
//	check for the REF rank
					$ref_rank_id = $row['outputNumber'];
//	check if the publication status	is pending
					if($row['isPendingPublication'] == 'TRUE') $pub_status_id = 10;
					if($row['isPendingPublication'] == 'true') $pub_status_id = 10;
					if($row['isPendingPublication'] == 'True') $pub_status_id = 10;

//	make the title fit
					$title = addslashes($row['title']);
					
//	Now add the publication itself
					$query = "
					INSERT INTO publication
					(
						title,
						publication_type_id
						publisher_id,
						journal_id,
						journal_volume,
						issue,
						pagination,
						isbn,
						doi,
						patent_registration_number,
						year_published,
						url,
						output_media,
						publication_status,
						duplicate_output,
						non_english,
						interdisciplinary,
						propose_double_weighting,
						double_weighting_statement,
						reserve_output,
						conflict_panel_members,
						cross_referral_uoa,
						additional_information,
						english_abstract,
						research_group,
						is_sensitive,
						created_at
					) 
					VALUES
					(
						'".$title."',
						".$publication_type_id.",
						".$publisher_id.",
						".$journal_id.",
						'".$row['journal_volume']."',
						'".$row['issue'].",
						'".$row['firstPage']."',
						'".$row['isbn']."',
						'".$row['doi']."',
						'".$row['patentNumber']."',
						'".$row['year']."',
						'".$row['url']."',
						'".$row['mediaOfOutput']."',
						".$pub_status_id.",
						'".$row['isDuplicateOutput']."',
						'".$row['isNonEnglishLanguage']."',
						'".$row['isInterdisciplinary']."',
						'".$row['proposeDoubleWeighting']."',
						'".$row['doubleWeightingStatement']."',
						'".$row['reserveOutput']."',
						'".$row['conflictedPanelMembers']."',
						'".$row['crossReferToUoa']."',
						'".$row['additionalInformation']."',
						'".$row['englisgAbstract']."',
						'".$row['researchGroup']."',
						'".$row['isSensitive']."',
						CURDATE()
					)
					";
//d_print($query);
					$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());

//	get the ID of the new publication record
					$pub_id = mysql_insert_id();
	
					print "<U>--> Publication '".$row['title']."' has been added.</U><BR />";

//	finally add the author-publication relation
					
					add_authorship($pub_id, $author_id, $ref_rank_id);
					
					
					print "<BR />";
				} else print "'".$row['title']."' was not added - no Author found! <P>";
			}
		}
	}

	print "<BR />";
	print "Parsed $i rows:<BR /><BR />";
	if($journals_added) print "Journals added: $journals_added<BR />";
	if($journals_updated) print "Journals updated: $journals_updated<BR />";
	if($publishers_added) print "Publishers added: $publishers_added<BR />";
	if($publications_added) print "Publications added: $publications_added<BR />";
	if($publications_updated) print "Publications updated: $publications_updated<BR />";
	if($authors_added) print "Authors added: $authors_added<BR />";
	if($authors_updated) print "Authors updated: $authors_updated<BR />";
	if($external_authors_added) print "External Authors added: $external_authors_added<BR />";
	if($external_authors_updated) print "External Authors updated: $external_authors_updated<BR />";
	if($result_table)
	{
		print "<H3>Results</H3>";
		print_table($result_table, array(), 1);
	}
	print "<BR />All done!<BR />";
}




//--------------------------------------------------------------------------------------------------
function get_publishing_type_id($type_code)
// return the ID of an Employee already in DAISY or return FALSE otherwise
{
	if($type_code)
	{
		$type_code = "(".$type_code.")";
		$query = "SELECT id FROM PublicationType WHERE publication_type_name LIKE '%$type_code%'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	}
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function check_author($staff_id)
// return the ID of an Employee already in DAISY or return FALSE otherwise
{
	$e_id = known_author($staff_id);
	if(!$e_id)
	{
		if($staff_id > 0)
			print "<FONT COLOR=RED><B>-- $staff_id is unknown!</B></FONT>";
		else
			print "<FONT COLOR=RED><B>-- NO Author ID found!</B></FONT><BR>";
	}
	return $e_id;
}

//--------------------------------------------------------------------------------------------------
function add_authorship0($pub_id, $author_id, $ref_rank_id)
// add a relation between a given publication and author
{
//	if($pub_id AND $author_id)
	if($author_id)
	{
		if($ref_rank_id < 1 OR $ref_rank_id > 8) $ref_rank_id = 9;
		$employee = read_record('Employee',$author_id);
		$query = "
			INSERT INTO PublicationEmployee
			( 
				employee_id,
				publication_id,
				publication_role_id,
				ref_rank_id,
				created_at
			)
			VALUES
			(
				$author_id,
				$pub_id,
				1,
				$ref_rank_id,
				CURDATE()
			)
			";
//		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
		print "Authorship for '".$employee['fullname']."' with a REF rank of $ref_rank_id has been added.<BR />";
//		return mysql_insert_id();	
		return TRUE;	
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_authorship($pub_id, $author_id, $ref_rank_id)
// add a relation between a given publication and author
{
	if($pub_id AND $author_id)
//	if($author_id)
	{
		if($ref_rank_id < 1 OR $ref_rank_id > 8) $ref_rank_id = 9;
		$employee = read_record('Employee',$author_id);
		$query = "
			INSERT INTO PublicationEmployee
			( 
				employee_id,
				publication_id,
				publication_role_id,
				ref_rank_id,
				created_at
			)
			VALUES
			(
				$author_id,
				$pub_id,
				1,
				$ref_rank_id,
				CURDATE()
			)
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
		print "Authorship for '".$employee['fullname']."' with a REF rank of $ref_rank_id has been added.<BR />";
		return mysql_insert_id();	
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function known_publisher($publisher_name)
// return the ID of an already known publisher of that name and FALSE otherwise
{
	$publisher_name = addslashes(utf8_decode($publisher_name));
	if($publisher_name)
	{
		$query = "SELECT id FROM Publisher WHERE publisher_name = '$publisher_name'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_publisher0($name)
// add a journal with $title for department with $d_id
{
//	$now = date();
	if($name)
	{
		$name = addslashes($name);
		$query = "
			INSERT INTO Publisher ( 
				publisher_name
			)
			VALUES (
				'$name'
			)
			";
//		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
		print "--> Publisher '$name' has been added.<BR />";
		return TRUE;	
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_publisher($name)
// add a journal with $title for department with $d_id
{
//	$now = date();
	if($name)
	{
		$name = addslashes($name);
		$query = "
			INSERT INTO Publisher ( 
				publisher_name,
				created_at
			)
			VALUES (
				'$name',
				CURDATE()
			)
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
		print "--> Publisher '$name' has been added.<BR />";
		return mysql_insert_id();	
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_journal0($title, $issn, $d_id)
// return the ID of a newly added journal to DAISY - using title and ISSN - for a given department (ID)
{
	if($title AND $issn)
	{
	$query = "
		INSERT INTO Journal ( 
			journal_name,
			issn_hard_copy,
			department_id,
			created_at
		)
		VALUES (
			'$title',
			'$issn',
			'$d_id',
			CURDATE()
		)
		";
//d_print($query);		
	print "--> Journal '$title' with ISSN $issn was added to DAISY!<BR>";
		return TRUE;
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_journal($title, $issn, $d_id)
// add a journal with $title for department with $d_id
{
//	$now = date();
	$title = addslashes($title);
	$query = "
		INSERT INTO Journal ( 
			journal_name,
			issn_hard_copy,
			department_id,
			created_at
		)
		VALUES (
			'$title',
			'$issn',
			'$d_id',
			CURDATE()
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
	print "--> Journal '$title' with ISSN $issn was added to DAISY!<BR>";
	return mysql_insert_id();
}

//--------------------------------------------------------------------------------------------------
function update_journal($id, $title, $issn)
// update a journal
{
	$title = addslashes($title);
	$query ="
		UPDATE Journal
		SET
			journal_name = '$title',
			issn_hard_copy = '$issn',
			updated_at = CURDATE()
		WHERE id = $id
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
//	print "Journal '$title' has been updated: ISSN is now '$issn'.<BR />";
	return $result;
}

//--------------------------------------------------------------------------------------------------
function check_journal($title, $issn)
// return the ID of a journal already in DAISY - identified by title or ISSN - or return FALSE otherwise
{
	if($title AND $issn)
	{
		$j_id = known_journal($title);
		if(!$j_id) $j_id = known_issn($issn);
		
		return $j_id;
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function known_title($title)
// return the ID of an already known publication of that title and FALSE otherwise
{
	if($title)
	{
		$title = addslashes(utf8_decode($title));
		$query = "SELECT id FROM Publication WHERE title = '$title'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function known_author($staffID)
// return the ID of an already known author of that name and FALSE otherwise
{
	if($staffID)
	{
		$query = "SELECT id FROM Employee WHERE opendoor_employee_code = '$staffID' OR old_opendoor_employee_code = '$staffID'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function known_journal($journal_name)
// return the ID of an already known journal of that name and FALSE otherwise
{
	$journal_name = addslashes(utf8_decode($journal_name));
	if($journal_name)
	{
		$query = "SELECT id FROM Journal WHERE journal_name = '$journal_name'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function known_issn($issn)
// return the ID of an already known publisher of that name and FALSE otherwise
{
	if($issn)
	{
		$query = "SELECT id FROM Journal WHERE issn_hard_copy LIKE '$issn%'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}



//--------------------------------------------------------------------------------------------------
function get_dept_id_from_uoa($dept_uoa)
// return the ID of the department with the given unit of assessment or FALSE else
{
	if($dept_uoa)
	{
		$query = "SELECT id FROM Department WHERE departmental_uoa LIKE '$dept_uoa%'";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row['id'];
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function read_record($table, $id)
// return record with a given ID from a specified table
{
	if($table AND $id)
	{
		$query = "SELECT * FROM $table WHERE id = $id";
		$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
		$result_row = mysql_fetch_assoc($result);
		return $result_row;
	} else return FALSE;
}






//--------------------------------------------------------------------------------------------------
function import_publication_data0($d_id)
{
	$publication_file = $_FILES['file']['name'];
	$publication_tmp_file = $_FILES['file']['tmp_name'];
	$publication_table = csv2table($publication_tmp_file, 1);

	$num_of_recs = count($publication_table);
	print "read $num_of_recs records from $publication_file<P>";

//	now check each publication in the table 
	$result_table = array();

	$publications_added = 0;
	$publications_updated = 0;
	$journals_added = 0;
	$journals_updated = 0;
	$publishers_added = 0;
	$authors_added = 0;
	$authors_updated = 0;
	$external_authors_added = 0;
	$external_authors_updated = 0;

	$i = 0;
	if($publication_table) foreach($publication_table AS $row)
	{
//		if($i == 250) break; //	break for testing purposes

		$publication_role_id = 1;	// set ID for 'Main Author' as default

//	check if publication type exists and flag out if not
		unset($output_type_id);
		$output_type = $row['Output Type'];
		if($output_type)
		{
			$output_type_id = get_output_type_id($output_type);
			if(!$output_type_id) flag_output_type($output_type);
		}
//	check if journal exists where used and add it if not
		unset($journal_id);
		$journal_title = $row['Journal Title'];
		$issn = $row['ISSN'];
		if($journal_title)
		{
			if(journal_exists($journal_title, $d_id) < 1)
			{ 
				$journal_id = add_journal($journal_title, $issn, $d_id);
				$result_table[] = array('Name/Title' => $journal_title, 'Type' => 'Journal', 'Action' => 'Add');
				$journals_added++;
			}
			else
			{
				$journal_id = get_journal_id($journal_title, $d_id);
				if($issn) 
				{
					update_journal($journal_title, $issn);
//					$result_table[] = array('Name/Title' => $journal_title, 'Type' => 'Journal', 'Action' => 'Update');
					$journals_updated++;
				}
			}
		}
//	check if publisher exists where used and add it if not
		unset($publisher_id);
		$publisher = $row['Publisher'];
		if($publisher AND strlen($publisher) < 256)
		{
			$publisher_id = get_publisher_id($publisher);
			if(!$publisher_id) 
			{
				add_publisher($publisher);
				$result_table[] = array('Name/Title' => $publisher, 'Type' => 'Publisher', 'Action' => 'Add');
				$publishers_added++;
			}
		}
//	check if publication exists where used and add it if not

		unset($publication_id);
		$title = $row['Title of Output'];
		$isbn = $row['ISBN'];
		$output_type = $row['Output Type'];
		if($isbn === NULL OR $isbn == '') $isbn = $row['ISSN'];
		
		if($title)
		{
			$publication_id = get_publication_id($row, $d_id);
			if($journal_id < 1) 
			{
//				unset($journal_id);
//				$journal_id = null;
				$journal_id = 'NULL';
//				if($journal_id === NULL)print "journal_id = NULL - better!<BR />";
			}
			if(!$publication_id) 
			{
				$publication_id = add_publication($row, $d_id, $output_type_id, $journal_id, $publisher_id);
				$result_table[] = array('Name/Title' => $row['Title of Output'], 'Type' => 'Publication', 'Action' => 'Add');
				$publications_added++;
			}
			else if($output_type) 
			{
				update_publication($row, $d_id, $output_type_id, $journal_id, $publisher_id);
				if($d_id == 37) $publication_role_id = 2;	// set role id to 2 (co-author) for SPI (id=37) when publication already exists
//				$result_table[] = array('Name/Title' => $row['Title of Output'], 'Type' => 'Publication', 'Action' => 'Update');
				$publications_updated++;
			}
//print "prid = $publication_role_id<BR />";
		}

// check if a relation between the publication and the author exists and add the author if not

		unset($e_nr);
		unset($ref_rank);
		$e_nr = $row['Employee Number'];
		$author = addslashes(utf8_decode($row['Author name']));

		if(isset($row['REF Number'])) $ref_rank = $row['REF Number'];
		if($ref_rank == '' AND isset($row['REF number'])) $ref_rank = $row['REF number'];
//		if(isset($row['REF number'])) $ref_rank = $row['REF number'];
		if($ref_rank == '' AND isset($row[' REF number'])) $ref_rank = $row[' REF number'];
		if($ref_rank == '' AND isset($row['Output Number (REF number)'])) $ref_rank = $row['Output Number (REF number)'];
//		$ref_rank = 1;
//dprint($ref_rank);
//print_r($row);
		$priority_order = $row['Author order'];
		if($priority_order < 0) $priority_order = 1;

//	if the authors employee number gives back a DAISY ID
		if($e_nr > 0 AND $publication_id > 0) 
		{
			$e_id = get_employee_id($e_nr);
			if($e_id > 0)
			{
				$fullname = get_employee_fullname($e_id);
				if(!author_exists($e_id, $publication_id))
				{
//					add_author($e_id, $publication_id, $ref_rank, $priority_order);
					add_author($e_id, $publication_id, $ref_rank, $priority_order, $publication_role_id);
					$result_table[] = array('Name/Title' => $fullname, 'Type' => 'Author', 'Action' => 'Add');
					$authors_added++;
				}
				else
				{
					update_author($e_id, $publication_id, $ref_rank, $priority_order, $publication_role_id);
//					$result_table[] = array('Name/Title' => $fullname, 'Type' => 'Author', 'Action' => 'Update');
					$authors_updated++;
				}
			}
			else
			{
				print "Unknown Employee Number <B><FONT COLOR=RED>$e_nr</FONT></B>. Author <B><FONT COLOR=RED>$author</FONT>  NOT</B> added! for <B>'$title'</B><BR />";
//				$result_table[] = array('Name/Title' => $e_nr, 'Type' => "Author NOT found!", 'Action' => "ERROR!");
			}
		}

		if(!$e_nr AND $author AND $publication_id) 
		{
			if(!external_author_exists($author, $publication_id))
			{
				add_external_author($author, $publication_id, $priority_order, $publication_role_id);
				$result_table[] = array('Name/Title' => $author, 'Type' => 'Ext. Author', 'Action' => 'Add');
				$external_authors_added++;
			}
			else
			{
				update_external_author($author, $publication_id, $priority_order, $publication_role_id);
//				$result_table[] = array('Name/Title' => $author, 'Type' => 'Ext. Author', 'Action' => 'Update');
				$external_authors_updated++;
			}
		}
		$i++;
	} //xxxx

	print "<BR />";
	print "Parsed $i rows:<BR /><BR />";
	if($journals_added) print "Journals added: $journals_added<BR />";
	if($journals_updated) print "Journals updated: $journals_updated<BR />";
	if($publishers_added) print "Publishers added: $publishers_added<BR />";
	if($publications_added) print "Publications added: $publications_added<BR />";
	if($publications_updated) print "Publications updated: $publications_updated<BR />";
	if($authors_added) print "Authors added: $authors_added<BR />";
	if($authors_updated) print "Authors updated: $authors_updated<BR />";
	if($external_authors_added) print "External Authors added: $external_authors_added<BR />";
	if($external_authors_updated) print "External Authors updated: $external_authors_updated<BR />";
	if($result_table)
	{
		print "<H3>Results</H3>";
		print_table($result_table, array(), 1);
	}
	print "<BR />All done!<BR />";
}

// ===============================================< Authors >====================================================
//--------------------------------------------------------------------------------------------------
function get_employee_id($employee_number)
// return the ID for a given employee number
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
function author_exists($employee_id, $publication_id)
// check if a relation between employee and publication already exists in DAISY
{	
	if($employee_id < 1 ) return FALSE;
	if($publication_id < 1 ) return FALSE;
	
	$query = "
		SELECT * 
		FROM PublicationEmployee 
		WHERE publication_id = $publication_id
		AND employee_id = $employee_id
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	if(mysql_num_rows($result) > 0) return TRUE;
		else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_author($e_id, $publication_id, $ref_rank, $priority_order, $publication_role_id)
// add an author to the publication with $publication_id
{
	if($e_id < 1) return FALSE;
	
	$rr_id = get_ref_rank_id($ref_rank);
	$query = "
		INSERT INTO PublicationEmployee ( 
			employee_id,
			external_employee_name,
			publication_id,
			publication_role_id,
			priority_order,
			delete_record,
			ref_rank_id
		)
		VALUES (
			'$e_id',
			'',
			'$publication_id',
			'$publication_role_id',
			'$priority_order',
			'0',
			'$rr_id'
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
//	$fullname = get_employee_fullname($e_id);
//	print "Author '$fullname' has been added.<BR />";
	return mysql_insert_id();
}

//--------------------------------------------------------------------------------------------------
function update_author($e_id, $publication_id, $ref_rank, $priority_order, $publication_role_id)
// update the ISSN for a journal
{
	$rr_id = get_ref_rank_id($ref_rank);
	$title = addslashes(utf8_decode($title));
	$query ="
		UPDATE PublicationEmployee
		SET ref_rank_id = $rr_id, priority_order = '$priority_order', publication_role_id = $publication_role_id
		WHERE employee_id = $e_id AND publication_id = $publication_id
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
//	$fullname = get_employee_fullname($e_id);
//	print "REF Rank for '$fullname' has been updated to $ref_rank.<BR />";
	return $result;
}

//--------------------------------------------------------------------------------------------------
function external_author_exists($author_name, $publication_id)
// check if a relation between employee and publication already exists in DAISY
{	
	$query = "
		SELECT * 
		FROM PublicationEmployee 
		WHERE publication_id = $publication_id
		AND external_employee_name = '$author_name'
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	if(mysql_num_rows($result) > 0) return TRUE;
		else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_external_author($author_name, $publication_id, $priority_order, $publication_role_id)
// add a journal with $title for department with $d_id
{
	$query = "
		INSERT INTO PublicationEmployee ( 
			employee_id,
			external_employee_name,
			publication_id,
			publication_role_id,
			priority_order,
			delete_record,
			ref_rank_id
		)
		VALUES (
			'NULL',
			'$author_name',
			'$publication_id',
			'$publication_role_id',
			'$priority_order',
			'0',
			''
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
//	print "EXTERNAL Author '$author_name' has been added.<BR />";
	return mysql_insert_id();
}

//--------------------------------------------------------------------------------------------------
function update_external_author($author_name, $publication_id, $priority_order, $publication_role_id)
// update the ISSN for a journal
{
	$rr_id = get_ref_rank_id($ref_rank);
	$title = addslashes(utf8_decode($title));
	$query ="
		UPDATE PublicationEmployee
		SET priority_order = '$priority_order', publication_role_id = $publication_role_id
		WHERE external_employee_name = '$author_name' AND publication_id = $publication_id
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	return $result;
}

//--------------------------------------------------------------------------------------------------
function update_authors0($dept_code)
//	update the authors field in the publication table so it looks good in the interface
{
	print "<HR>";
	print "Updating Authors for Publications in '$dept_code'<BR />";
	print "<HR>";
	
//	get publications
	$query = "
		SELECT pub.* 
		FROM Publication pub
		INNER JOIN Department d ON d.id = pub.department_id
		WHERE d.department_code = '$dept_code'
		";
//	$query = "SELECT * FROM Publication";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

// with every publication record do 
	$table = array();
	
	while ($record = mysql_fetch_assoc($result))
	{
		$table[] = $record;
	}
	$x = 0;
	foreach($table AS $row)
	{
		$authors = '';
		$pub_id = $row['id'];
		$author_name = $row['author_name'];
		$pub_title = $row['title'];

		$i = 0;
		
//		get the DAISY authors of a publication
		$query = "
			SELECT
			e.*,
			pe.employee_id,
			pe.external_employee_name
			
			FROM PublicationEmployee pe
			INNER JOIN Employee e ON e.id = pe.employee_id
			
			WHERE pe.publication_id = $pub_id
			AND pe.employee_id > 0
			ORDER BY e.surname
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
		while ($record = mysql_fetch_assoc($result))
		{
			if($i++ > 0) $authors = $authors.", ";
			$authors = $authors.$record['title'].' '.$record['forename'].' '.$record['surname'];
		}
/**/
//		get the external authors of a publication
		$query = "
			SELECT
			pe.employee_id,
			pe.external_employee_name
			
			FROM PublicationEmployee pe
			
			WHERE pe.publication_id = $pub_id
			AND (pe.employee_id < 1 OR pe.employee_id IS NULL)
			ORDER BY pe.external_employee_name
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
		while ($record = mysql_fetch_assoc($result))
		{
			if($i++ > 0) $authors = $authors.", ";
			$authors = $authors.$record['external_employee_name'];
		}

//		write the authors to the publication record if there were any changes
//		if ($author_name != $authors) print $x++.": $pub_title -> $author_name >> $authors >>> change needed<BR />";
		if($author_name != $authors) 
		{
			$authors = htmlspecialchars($authors, ENT_QUOTES);
			$query = "
				UPDATE Publication 
				SET author_name = '$authors'
				WHERE id = $pub_id
				";
			$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
//			print $x++.": $pub_title -> $author_name >> $authors<BR />";
		}
	}
}

//--------------------------------------------------------------------------------------------------
function update_authors($dept_id)
//	update the authors field in the publication table so it looks good in the interface
{
	print "<HR>";
	print "Updating Authors for Publications<BR />";
	print "<HR>";
	
//	get publications
	$query = "
		SELECT pub.* 
		FROM Publication pub
		INNER JOIN Department d ON d.id = pub.department_id
		WHERE d.id = '$dept_id'
		";
//	$query = "SELECT * FROM Publication";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

// with every publication record do 
	$table = array();
	
	while ($record = mysql_fetch_assoc($result))
	{
		$table[] = $record;
	}
	$x = 0;
	$result_table = array();
	foreach($table AS $row)
	{
		$authors = '';
		$pub_id = $row['id'];
		$author_name = $row['author_name'];
		$pub_title = $row['title'];

		$i = 0;
		
//		get the DAISY authors of a publication
		$query = "
			SELECT
			e.*,
			pe.employee_id,
			pe.external_employee_name
			
			FROM PublicationEmployee pe
			INNER JOIN Employee e ON e.id = pe.employee_id
			
			WHERE pe.publication_id = $pub_id
			AND pe.employee_id > 0
			ORDER BY e.surname
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
		while ($record = mysql_fetch_assoc($result))
		{
			if($i++ > 0) $authors = $authors.", ";
			$authors = $authors.$record['title'].' '.$record['forename'].' '.$record['surname'];
		}
/**/
//		get the external authors of a publication
		$query = "
			SELECT
			pe.employee_id,
			pe.external_employee_name
			
			FROM PublicationEmployee pe
			
			WHERE pe.publication_id = $pub_id
			AND (pe.employee_id < 1 OR pe.employee_id IS NULL)
			ORDER BY pe.external_employee_name
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
		while ($record = mysql_fetch_assoc($result))
		{
			if($i++ > 0) $authors = $authors.", ";
			$authors = $authors.$record['external_employee_name'];
		}

//		write the authors to the publication record if there were any changes
//		if ($author_name != $authors) print $x++.": $pub_title -> $author_name >> $authors >>> change needed<BR />";
		if($author_name != $authors) 
		{
			$authors = htmlspecialchars($authors, ENT_QUOTES);
			$query = "
				UPDATE Publication 
				SET author_name = '$authors'
				WHERE id = $pub_id
				";
			$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
//			print $x++.": $pub_title -> $author_name >> $authors<BR />";
//			print "Updated $pub_title -> $author_name >> $authors<BR />";
			$result_table[] = array('Title' => $pub_title, 'Old' => $author_name , 'New' => $authors);
		}
	}
	if($result_table)
	{
		print "<H3>Update Authors</H3>";
		print_table($result_table, array(), 1);
	}
}

// ==============================================< Publishers >====================================================
//--------------------------------------------------------------------------------------------------
function get_publisher_id($name)
// return the ID for a given publisher name
{
	$name = addslashes($name);
	$query = "SELECT id from Publisher WHERE publisher_name = '$name'";
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
}

// ==============================================< Journals >====================================================
//--------------------------------------------------------------------------------------------------
function journal_exists($title, $d_id)
// check if a journal with $title already exists in DAISY for department with $d_id
{	
	$title = addslashes(utf8_decode($title));
	$query = "
		SELECT * 
		FROM Journal 
		WHERE journal_name = '$title' 
		AND department_id = $d_id
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	return mysql_num_rows($result);
}

//--------------------------------------------------------------------------------------------------
function get_journal_id($title, $d_id)
// return the ID for a given journal name
{
	$title = addslashes($title);
	$query = "SELECT id from Journal WHERE journal_name = '$title' AND department_id = $d_id";
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
}


// ==============================================< Publications >==================================================
//--------------------------------------------------------------------------------------------------
function get_publication_id0($assoc_data, $d_id)
// return the ID for a given publication if it exists
{
	$query = "
		SELECT id 
		FROM Publication
		WHERE title = '".addslashes($assoc_data['Title of Output'])."' 
			AND department_id = $d_id
		";
	
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
//		WHERE title = '".utf8_decode($assoc_data['Title of Output'])."' 
}

//--------------------------------------------------------------------------------------------------
function get_publication_id($assoc_data, $d_id)
// return the ID for a given publication if it exists
{
	$query = "
		SELECT id 
		FROM Publication
		WHERE UPPER(title) = '".addslashes(strtoupper($assoc_data['Title of Output']))."' 
			AND department_id = $d_id
		";
	
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
//		WHERE title = '".utf8_decode($assoc_data['Title of Output'])."' 
}

//--------------------------------------------------------------------------------------------------
function add_publication($assoc_data, $d_id, $output_type_id, $journal_id, $publisher_id)
// add a publication for department with $d_id
{
//	$publication_status_id IS NULL;
//	if($assoc_data['Pending Publication?']) $publication_status_id = 10; // refers to 'REF pending'
	
	$pub_status = $assoc_data['Pending Publication? '];	
	switch($pub_status)
	{
		case 'In press':
			$publication_status_id = 1;
			break;
		case 'In print':
			$publication_status_id = 1;
			break;
		case 'Planned':
			$publication_status_id = 2;
			break;
		case 'Proposal accepted':
			$publication_status_id = 3;
			break;
		case 'Accepted for publication':
			$publication_status_id = 3;
			break;
		case 'Submitted':
			$publication_status_id = 5;
			break;
		case 'Under contract':
			$publication_status_id = 6;
			break;
		case 'Under review':
			$publication_status_id = 7;
			break;
		case 'Under revision':
			$publication_status_id = 8;
			break;
		case 'Published online':
			$publication_status_id = 9;
			break;
		case '1':
			$publication_status_id = 10;
			break;
		case 'Forthcoming':
			$publication_status_id = 11;
			break;
		case 'In preparation':
			$publication_status_id = 11;
			break;
		default :
			$publication_status_id = 'NULL';
	}
	$year = $assoc_data['Date of Publication'];
	if(!$year) $year = '2013';
	if(strlen($year) == 4) $year = $year.'-01-01';
	$sqldate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $year)));
	if ($publisher_id < 1) $publisher_id = 'NULL';
	if ($journal_id < 1) $journal_id = 'NULL';
	if ($output_type_id < 1) $output_type_id = 'NULL';
	if ($publication_status_id < 1) $publication_status_id = 'NULL';
	
	$query = "
		INSERT INTO Publication ( 
			department_id,
			author_name,
			title,
			publication_type_id,
			publicationdate,
			volume_title,
			isbn,
			journal_id,
			journal_volume,
			issue,
			article_number,
			pagination,
			publisher_id,
			doi,
			url,
			place,
			patent_registration_number,
			output_media,
			publication_status_id,
			duplicate_output,
			non_english,
			interdisciplinary,
			propose_double_weighting,
			double_weighting_statement,
			reserve_output,
			conflict_of_interest,
			conflict_panel_members,
			is_cross_referral,
			cross_referral_uoa,
			additional_information,
			english_abstract,
			is_sensitive,
			estimated_ref_quality_rating,
			created_at
		)
		VALUES (
			'$d_id',
			'".addslashes($assoc_data['Author name'])."',
			'".addslashes($assoc_data['Title of Output'])."',
			'$output_type_id',
			'$sqldate',
			'".addslashes($assoc_data['Volume Title (Books/scholarly editions)'])."',
			'".$assoc_data['ISBN']."',
			'$journal_id',
			'".$assoc_data['Journal Volume Number']."',
			'".$assoc_data['Journal Issue Number']."',
			'".$assoc_data['Article Number']."',
			'".$assoc_data['Pagination']."',
			'$publisher_id',
			'".$assoc_data['Digital Object Identifier']."',
			'".addslashes($assoc_data['URL'])."',
			'".$assoc_data['Place']."',
			'".$assoc_data['Patent Number']."',
			'".$assoc_data['Media of Output']."',
			$publication_status_id,
			'".$assoc_data['Duplicate Output?']."',
			'".$assoc_data['Non English language output?']."',
			'".$assoc_data['Interdisciplinary Output?']."',
			'".$assoc_data['Propose Double Weighting?']."',
			'".addslashes($assoc_data['Double Weighting Statement'])."',
			'".$assoc_data['Reserve Output?']."',
			'".$assoc_data['Conflict of Interest?']."',
			'".addslashes($assoc_data['Conflicted panel members'])."',
			'".$assoc_data['Cross referral?']."',
			'".$assoc_data['Cross-referral UOA']."',
			'".addslashes($assoc_data['Additional information'])."',
			'".addslashes($assoc_data['English Abstract'])."',
			'".$assoc_data['Sensitive']."', 
			'".$assoc_data['Estimated REF Quality Rating']."', 
			CURDATE()
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
//	print "Publication '".$assoc_data['Title of Output']."' has been added.<BR />";
	return mysql_insert_id();	
}

//--------------------------------------------------------------------------------------------------
function update_publication($assoc_data, $d_id, $output_type_id, $journal_id, $publisher_id)
// update a publication for department with $d_id
{
//	$publication_status_id IS NULL;
//	if($assoc_data['Pending Publication?']) $publication_status_id = 10; // refers to 'REF pending'
	
	$title = addslashes($assoc_data['Title of Output']);
	
	$erqr = get_est_ref_rank($title, $d_id);
	if($assoc_data['Estimated REF Quality Rating'] > $erqr) $erqr = $assoc_data['Estimated REF Quality Rating'];
	
	
	$isbn = $assoc_data['ISBN'];
	
	$pub_status = $assoc_data['Pending Publication? '];
	switch($pub_status)
	{
		case 'In press':
			$publication_status_id = 1;
			break;
		case 'In print':
			$publication_status_id = 1;
			break;
		case 'Planned':
			$publication_status_id = 2;
			break;
		case 'Proposal accepted':
			$publication_status_id = 3;
			break;
		case 'Accepted for publication':
			$publication_status_id = 3;
			break;
		case 'Submitted':
			$publication_status_id = 5;
			break;
		case 'Under contract':
			$publication_status_id = 6;
			break;
		case 'Under review':
			$publication_status_id = 7;
			break;
		case 'Under revision':
			$publication_status_id = 8;
			break;
		case 'Published online':
			$publication_status_id = 9;
			break;
		case '1':
			$publication_status_id = 10;
			break;
		case 'Forthcoming':
			$publication_status_id = 11;
			break;
		case 'In preparation':
			$publication_status_id = 11;
			break;
		default :
			$publication_status_id = 'NULL';
	}
	$year = $assoc_data['Date of Publication'];
	if(!$year) $year = '2013';
	if(strlen($year) == 4) $year = $year.'-01-01';
	$sqldate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $year)));
	
	if ($publisher_id < 1) $publisher_id = 'NULL';
	if ($journal_id < 1) $journal_id = 'NULL';
	if ($output_type_id < 1) $output_type_id = 'NULL';
	if ($publication_status_id < 1) $publication_status_id = 'NULL';

//print"$year | $sqldate<BR />";

//dprint($assoc_data['Estimated REF Quality Rating']);

	$query ="
		UPDATE Publication
		SET 
			author_name = '".addslashes($assoc_data['Author name'])."',
			title = '".addslashes($assoc_data['Title of Output'])."',
			publication_type_id = '$output_type_id',
			publicationdate = '$sqldate',
			volume_title = '".addslashes($assoc_data['Volume Title (Books/scholarly editions)'])."',
			isbn = '".$assoc_data['ISBN']."',
			journal_id = $journal_id,
			journal_volume = '".$assoc_data['Journal Volume Number']."',
			issue = '".$assoc_data['Journal Issue Number']."',
			article_number = '".$assoc_data['Article Number']."',
			pagination = '".$assoc_data['Pagination']."',
			doi = '".$assoc_data['Digital Object Identifier']."',
			url = '".addslashes($assoc_data['URL'])."',
			place = '".$assoc_data['Place']."',
			patent_registration_number = '".$assoc_data['Patent Number']."',
			output_media = '".$assoc_data['Media of Output']."',
			publication_status_id = $publication_status_id,
			duplicate_output = '".$assoc_data['Duplicate Output?']."',
			non_english = '".$assoc_data['Non English language output?']."',
			interdisciplinary = '".$assoc_data['Interdisciplinary Output?']."',
			propose_double_weighting = '".$assoc_data['Propose Double Weighting?']."',
			double_weighting_statement = '".addslashes($assoc_data['Double Weighting Statement'])."',
			reserve_output = '".$assoc_data['Reserve Output?']."',
			conflict_of_interest = '".$assoc_data['Reserve Output?']."',
			conflict_panel_members = '".addslashes($assoc_data['Conflicted panel members'])."',
			is_cross_referral = '".$assoc_data['Cross referral?']."',
			cross_referral_uoa = '".$assoc_data['Cross-referral UOA']."',
			additional_information = '".addslashes($assoc_data['Additional information'])."',
			english_abstract = '".addslashes($assoc_data['English Abstract'])."',
			is_sensitive = '".$assoc_data['Sensitive']."',
			estimated_ref_quality_rating = '".$erqr."',
			updated_at = CURDATE()

		WHERE title = '$title' AND department_id = $d_id
		";


	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
//	print "Publication '".$assoc_data['Title of Output']."' has been updated.<BR />";
	return mysql_insert_id();	
}

//--------------------------------------------------------------------------------------------------
function clean_up_publications()
//	make sure there a no 0 where there should be a NULL
{
	$fields2check = array();
	
	$field2check[] = 'department_id';
	$field2check[] = 'research_project_id';
	$field2check[] = 'impact_activity_id';
	$field2check[] = 'research_grant_id';
	$field2check[] = 'publication_type_id';
	$field2check[] = 'employee_id';
	$field2check[] = 'publication_status_id';
	$field2check[] = 'ref_rank_id';
	$field2check[] = 'research_centre_id';
	$field2check[] = 'publisher_id';
	$field2check[] = 'journal_id';
	$field2check[] = 'research_event_id';
	$field2check[] = 'esteem_indicator_id';
	$field2check[] = 'research_interest_id';
	$field2check[] = 'volume_publication_id';
	$field2check[] = 'journal_id';

	foreach($field2check AS $field_id)
	{
		$query = "UPDATE Publication SET $field_id = NULL WHERE $field_id = 0;";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	}
	print "<BR />All cleaned up!<BR />";
}

//===============================================< Helpers >======================================================
//--------------------------------------------------------------------------------------------------
function get_est_ref_rank($title, $d_id)
// return the Estimated REF Rank for a given publication title and department
{
	$query = "SELECT * from Publication WHERE title = '$title' AND department_id = $d_id";
	$table = get_data($query);
	$record = $table[0];
	$value = $record['estimated_ref_quality_rating'];
	if($value) return $value;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function delete_publications($dept_id)
// delete all publications and related authorships from the database
{
//	first delete the authorships
	$query = "
		DELETE
		pube.*

		FROM Publication pub
		INNER JOIN PublicationEmployee pube ON pube.publication_id = pub.id

		WHERE pub.department_id = $dept_id
	";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

//	then delete the publications
	$query = "DELETE FROM Publication WHERE department_id = $dept_id";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
}

//--------------------------------------------------------------------------------------------------
function get_ref_rank_id($ref_rank)
// return the ID for a given ref rank and '9' if none found
{
	$query = "SELECT id from RefRank WHERE label = '$ref_rank'";
	$table = get_data($query);
	$record = $table[0];
	$rr_id = $record['id'];
	if($rr_id) return $rr_id;
	else return 9;
}

//----------------------------------------------------------------------------------------
function department_options0($filter)
// shows the - filtered - departments and the corrsponding id
{
	$html = "<select name='dept_id'>";

	$query = "
		SELECT 
			* 
		FROM 
			Department 
		WHERE 
			department_code LIKE '%$filter%'  
		ORDER BY 
			department_name 
		ASC";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$html = $html . "<option value = ''>Please select a department</option>";
	while ($department = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$department['id']."'";
		$html = $html.">".$department['department_name']." (".$department['department_code'].")</option>";
	}
	$html = $html."</select>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function get_output_type_id($output_type)
// return the ID for a given output type
{
//	$output_type_code = substr($output_type, strpos($output_type, '['),3);
	$output_type_code = substr($output_type, strpos($output_type, '('),3);
//print "-->$output_type --> $output_type_code<BR />";
	$query = "SELECT id from PublicationType WHERE publication_type_name LIKE '%$output_type_code%'";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['id'];



	$table = get_data($query);
	$record = $table[0];
//print_r($table);
	return $record['id'];
}

//--------------------------------------------------------------------------------------------------
function flag_output_type($output_type)
// add a journal with $title for department with $d_id
{
	print "Output Type '$output_type' does not exist!<BR />";
	return FALSE;
}

//--------------------------------------------------------------------------------------------------
function upload_file()
{
	$department_options = department_options0('3C');
	$actionpage = $_SERVER["PHP_SELF"];
	print "
		<form action='$actionpage' method='post'
		enctype='multipart/form-data'>
		<label for='file'>Filename:</label>
		<input type='file' name='file' id='file' /> 
		<BR />
	";
	print "Replace exiting publications: ";
	if ($_POST['replace']) print "<input type='checkbox' name='replace' value='TRUE' checked='checked'>";
	else print "<input type='checkbox' name='replace' value='TRUE'>";

	print "		
		<BR />
		Select Department to allocate Publications: $department_options
		<BR />
		<BR />
		
		<input type='submit' name='submit' value='Submit' />
		</form>
	";
}

//--------------------------------------------------------------------------------------------------
function csv3table($filename, $has_headers)
//	open the file with the given filename and return the contents in a table - with or without headers
{
	if (($handle = fopen($filename, "r")) !== FALSE) 	// open the file for reading
	{
		return str_getcsv (fgetcsv($handle, 0, ","), ',' , '"' );
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function with_obituary($query)
{
	if($_SERVER['TERM']) return  "Could not execute query:<BR />$query<BR />Error = ";
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
			print "Query returned ".count($table)." lines<BR />";
			$i = 0;
			foreach ($keys as $column_name) 
			{
				if($i++ > 0) print " | ";
				print "$column_name";
			}
			print "<BR />";		

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
				print "<BR />";
			}
		} 
		else
		print "Could not find keys in $table, aborting! <BR />";
	} else
	print "The query returned no results!<BR />";
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
function get_employee_fullname($e_id)
{
	$query = "SELECT * FROM Employee WHERE id = $e_id";
	$result = mysql_query($query) or die (with_obituary($query) . mysql_error());
	$result_row = mysql_fetch_assoc($result);
	return $result_row["fullname"];
}




?> 