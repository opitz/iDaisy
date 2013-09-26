<?php

//=============================================================================================================
//
//	Script to import publication data for REF
//	Last Update by Matthias Opitz, 2012-11-23
//
//=============================================================================================================

$version = '120501.1';			//	1st version
$version = '120502.2';			//	add/update ISSN number in Journals
$version = '120504.2';			//	add/update REF rank for existing authors, fixed duplicate output data
$version = '120508.1';			//	fixed for empty REF rank
$version = '120508.2';			//	added publication update
$version = '120509.1';			//	fixed publishing date
$version = '120511.1';			//	fixed error when apostrophe in Author Name
$version = '120514.1';			//	fixed publication status
$version = '120522.1';			//	fixed non-ASCII chars
$version = '120523.1';			//	added cleanup and restore author in publication
$version = '120612.1';			//	fixed adding/updating priority order where available
$version = '120615.1';			//	fixed main author / co-author for SPI
$version = '121123.1';			//	web interface

include '../opendb.php';

// some global variables used by the script
global $debug;										// output of debug information when set to TRUE
$debug = FALSE;

// ================================================< Here we go! >============================================>  
$conn = open_daisydb();

print "\n\nImporting Publications -v.$version\n";
// ask for input
fwrite(STDOUT, "Enter department code (e.g. 3C05) to import publications for: ");
// get input
$dept_code = trim(fgets(STDIN));

//$dept_code = '3C06';

$d_id = get_dept_id($dept_code);
print "Importing Publications for $ $dept_code -v.$version\n";
print "===================================================\n\n";
//print "A '-' for each updated record, a '+' for each added record and a blank for every ignored entry in the OpenDoor export data.\n";

//print "ID = $d_id\n";

test_publications($dept_code);
//import_publications($dept_code);
//update_authors($dept_code);
//clean_up_publications();

mysql_close($conn);

//--------------------------------------------------------------------------------------------------
function get_data0($query)
//	do the query and store the result in a table
{
	$table = array();
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$counter = 0;
	while ($row = mysql_fetch_assoc($result))	
	{
		$table[$counter++] = $row;
	}

	return $table;		
}

//--------------------------------------------------------------------------------------------------------------
function get_data($query)
//	do the query and store the result in a table that is returned
{

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	while ($row = mysql_fetch_assoc($result))	
	{
		$table[] = $row;
	}

	return $table;		
}

//--------------------------------------------------------------------------------------------------
function get_dept_id($dept_code)
// return the ID for a given department code
{
	$query = "SELECT id from Department WHERE department_code = '$dept_code'";
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
}

//--------------------------------------------------------------------------------------------------
function test_publications($dept_code)
{
	$d_id = get_dept_id($dept_code);
	$assoc_data = array();
	
	$row = 0;
	
	$newline_trigger = 100;
	$update_counter = 0;
	$add_counter = 0;
	$file_name = 'publications_'.$dept_code.'.csv';
	
	if (($handle = fopen("$file_name", "r")) !== FALSE) 
	{
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			if ($row == 0)
			{
				$table_headers = $data;
				$tables = count($table_headers);
print_r($data);				
			}
			else	//	put each row in a nice array
			{
				for ($c=0; $c < $tables; $c++)
				{
					$assoc_data[$table_headers[$c]] = $data[$c];
				}
				$employee_number = $assoc_data['Employee Number'];
				$title = addslashes(utf8_decode($assoc_data['Title of Output']));
				$year = $assoc_data['Date of Publication'];
				if(strlen($year) == 4) $year = '01-01-'.$year;
				$date = date_format(date_create($year), 'd/m/Y');

//				if (strlen($date)>4) print "DoP = $date for '$title'\n";
				if($title) print "DoP = $oyear ->> $year -> $date for '$title'\n";
			}
			if (++$row % $newline_trigger == 0) echo "\n$row:";
			
		}
		fclose($handle);

	} else print "Cannot open file '$file_name' - aborting!\n";
}

//--------------------------------------------------------------------------------------------------
function import_publications($dept_code)
{
	$d_id = get_dept_id($dept_code);
	$assoc_data = array();
	
	$row = 0;
	
	$newline_trigger = 100;
	$update_counter = 0;
	$add_counter = 0;
	$file_name = 'publications_'.$dept_code.'.csv';
	
	if (($handle = fopen("$file_name", "r")) !== FALSE) 
	{
		echo "\n0:";

		$journals_added = 0;
		$journals_updated = 0;
		$publishers_added = 0;
		$publishers_updated = 0;
		$publications_added = 0;
		$publications_updated = 0;
		$authors_added = 0;
		$authors_updated = 0;
		$external_authors_added = 0;
		$external_authors_updated = 0;
		
//	read the file row by row
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
		{
			$publication_role_id = 1;	// set ID for 'Main Author' as default
			if ($row == 0)			// get the table headers
			{
				$table_headers = $data;
				$tables = count($table_headers);
print_r($data);				
			}
			else
			{
//	put each row in a nice array with table headers
				for ($c=0; $c < $tables; $c++)
				{
					$assoc_data[$table_headers[$c]] = $data[$c];
				}
				$employee_number = $assoc_data['Employee Number'];


//	check if publication type exists and flag out if not
				unset($output_type_id);
				$output_type = $assoc_data['Output Type'];
				if($output_type)
				{
					$output_type_id = get_output_type_id($output_type);
					if(!$output_type_id) flag_output_type($output_type);
				}
//	check if journal exists where used and add it if not
				unset($journal_id);
				$journal_title = $assoc_data['Journal Title'];
				$issn = $assoc_data['ISSN'];
				if($journal_title)
					if(journal_exists($journal_title, $d_id) < 1)
					{ 
						$journal_id = add_journal($journal_title, $issn, $d_id);
						$journals_added++;
					}
					else
					{
						$journal_id = get_journal_id($journal_title, $d_id);
						if($issn) 
						{
							update_journal($journal_title, $issn);
							$journals_updated++;
						}
					}

//	check if publisher exists where used and add it if not
				unset($publisher_id);
				$publisher = $assoc_data['Publisher'];
				if($publisher AND strlen($publisher) < 256)
				{
					$publisher_id = get_publisher_id($publisher);
					if(!$publisher_id) 
					{
						add_publisher($publisher);
						$publishers_added++;
					}
				}

//	check if publication exists where used and add it if not

				unset($publication_id);
				$title = $assoc_data['Title of Output'];
				$isbn = $assoc_data['ISBN'];
				$output_type = $assoc_data['Output Type'];
				if($isbn === NULL OR $isbn == '') $isbn = $assoc_data['ISSN'];
				
				if($title)
				{
					$publication_id = get_publication_id($assoc_data, $d_id);
if($journal_id < 1) 
{
//	unset($journal_id);
//	$journal_id = null;
	$journal_id = 'NULL';
//	if($journal_id === NULL)print "journal_id = NULL - better!\n";
}
					if(!$publication_id) 
					{
						$publication_id = add_publication($assoc_data, $d_id, $output_type_id, $journal_id, $publisher_id);
						$publications_added++;
					}
					else if($output_type) 
					{
						update_publication($assoc_data, $d_id, $output_type_id, $journal_id, $publisher_id);
						if($d_id == 37) $publication_role_id = 2;	// set role id to 2 (co-author) for SPI (id=37) when publication already exists
						$publications_updated++;
					}
//print "prid = $publication_role_id\n";
				}

// check if a relation between the publication and the author exists and add the author if not

				unset($e_nr);
				$e_nr = $assoc_data['Employee Number'];
				$author = addslashes(utf8_decode($assoc_data['Author name']));
				$ref_rank = $assoc_data['Output Number (REF number)'];
				$priority_order = $assoc_data['Author order'];
				if($priority_order < 0) $priority_order = 1;
				
//print "enr = $e_nr | pub id = $publication_id /n";
				if($e_nr > 0 AND $publication_id > 0) 
				{
					$e_id = get_employee_id($e_nr);
					if(!author_exists($e_id, $publication_id))
					{
//						add_author($e_id, $publication_id, $ref_rank, $priority_order);
						add_author($e_id, $publication_id, $ref_rank, $priority_order, $publication_role_id);
						$authors_added++;
					}
					else
					{
						update_author($e_id, $publication_id, $ref_rank, $priority_order, $publication_role_id);
						$authors_updated++;
					}
				}

				if(!$e_nr AND $author AND $publication_id) 
				{
					if(!external_author_exists($author, $publication_id))
					{
						add_external_author($author, $publication_id, $priority_order, $publication_role_id);
						$external_authors_added++;
					}
					else
					{
						update_external_author($author, $publication_id, $priority_order, $publication_role_id);
						$external_authors_updated++;
					}
				}
			}
			
			if (++$row % $newline_trigger == 0) echo "->$row:";
			
		}
		fclose($handle);

	} else print "Cannot open file '$file_name' - aborting!\n";
	
	print "\nparsed $row rows:\n\n";
	if($journals_added) print "Journals added: $journals_added\n";
	if($journals_updated) print "Journals updated: $journals_updated\n";
	if($publishers_added) print "Publishers added: $publishers_added\n";
	if($publications_added) print "Publications added: $publications_added\n";
	if($publications_updated) print "Publications updated: $publications_updated\n";
	if($authors_added) print "Authors added: $authors_added\n";
	if($authors_updated) print "Authors updated: $authors_updated\n";
	if($external_authors_added) print "External Authors added: $external_authors_added\n";
	if($external_authors_updated) print "External Authors updated: $external_authors_updated\n";
	print "\nAll done!\n";
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

//--------------------------------------------------------------------------------------------------
function get_employee_id($e_nr)
// return the ID for a given employee number
{
	$query = "SELECT id from Employee WHERE opendoor_employee_code = '$e_nr'";
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
}

//--------------------------------------------------------------------------------------------------
function get_employee_fullname($e_id)
// return the full name for a given employee ID
{
	$query = "SELECT * from Employee WHERE id = $e_id";
	$table = get_data($query);
	$record = $table[0];
	return $record['fullname'];
}

//--------------------------------------------------------------------------------------------------
function get_output_type_id($output_type)
// return the ID for a given output type
{
	$output_type_code = substr($output_type, strpos($output_type, '['),3);
//print "-->$output_type --> $output_type_code\n";
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
	print "Output Type '$output_type' das not exist!\n";
	return FALSE;
}

//--------------------------------------------------------------------------------------------------
function journal_exists($title, $d_id)
// check if a journal with $title already exists in DAISY fro department with $d_id
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
function add_journal($title, $issn, $d_id)
// add a journal with $title for department with $d_id
{
//	$now = date();
	$title = addslashes($title);
	$query = "
		INSERT INTO Journal ( 
			journal_name,
			issn_hard_copy,
			department_id
		)
		VALUES (
			'$title',
			'$issn',
			'$d_id'
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
	print "Journal '$title' has been added.\n";
	return mysql_insert_id();
}

//--------------------------------------------------------------------------------------------------
function update_journal($title, $issn)
// update the ISSN for a journal
{
	$title = addslashes($title);
	$query ="
		UPDATE Journal
		SET issn_hard_copy = '$issn'
		WHERE journal_name = '$title'
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
//	print "Journal '$title' has been updated: ISSN is now '$issn'.\n";
	return $result;
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
				publisher_name
			)
			VALUES (
				'$name'
			)
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
		print "Publisher '$name' has been added.\n";
		return mysql_insert_id();	
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_publication_id($assoc_data, $d_id)
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
	if(strlen($year) == 4) $year = $year.'-01-01';
	$sqldate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $year)));

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
			is_sensitive
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
			'".$assoc_data['URL']."',
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
			'".$assoc_data['Sensitive']."'
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
	print "Publication '".$assoc_data['Title of Output']."' has been added.\n";
	return mysql_insert_id();	
}

//--------------------------------------------------------------------------------------------------
function update_publication($assoc_data, $d_id, $output_type_id, $journal_id, $publisher_id)
// update a publication for department with $d_id
{
//	$publication_status_id IS NULL;
//	if($assoc_data['Pending Publication?']) $publication_status_id = 10; // refers to 'REF pending'
	
	$title = addslashes($assoc_data['Title of Output']);
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
	if(strlen($year) == 4) $year = $year.'-01-01';
	$sqldate = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $year)));
	
//print"$year | $sqldate\n";

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
			url = '".$assoc_data['URL']."',
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
			is_sensitive = '".$assoc_data['Sensitive']."'
			
		WHERE title = '$title' AND department_id = $d_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
//	print "Publication '".$assoc_data['Title of Output']."' has been updated.\n";
	return mysql_insert_id();	
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
function add_author0($e_id, $publication_id, $ref_rank, $priority_order)
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
			'1',
			'$priority_order',
			'0',
			'$rr_id'
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
//	$fullname = get_employee_fullname($e_id);
//	print "Author '$fullname' has been added.\n";
	return mysql_insert_id();
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
//	print "Author '$fullname' has been added.\n";
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
//	print "REF Rank for '$fullname' has been updated to $ref_rank.\n";
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
	
//	print "EXTERNAL Author '$author_name' has been added.\n";
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
function update_authors($dept_code)
//	update the authors field in the publication table
{
	print "Updating Authors for Publications in '$dept_code'\n";
	print "=================================================\n\n";
	
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
//		if ($author_name != $authors) print $x++.": $pub_title -> $author_name >> $authors >>> change needed\n";
		if($author_name != $authors) 
		{
			$authors = htmlspecialchars($authors, ENT_QUOTES);
			$query = "
				UPDATE Publication 
				SET author_name = '$authors'
				WHERE id = $pub_id
				";
			$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());
//			print $x++.": $pub_title -> $author_name >> $authors\n";
		}
	}
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
	print "\nAll cleaned up!\n";
}

?> 
