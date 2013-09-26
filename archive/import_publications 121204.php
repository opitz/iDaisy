<?php


//=============================================================================================================
//
//	Script to update HR data from HRIS via CSV file into DAISY incl. Grade!
//	
//	Last Update by Matthias Opitz, 2012-11-26
//
//=============================================================================================================
$version = '121126.2';
//==============================================< Here we go! >====================================================

include '../includes/opendb.php';


print "<B>DAISY update</B> |  'Publication' data from CSV file  v.$version";
print "<HR>";

$conn = open_daisydb();

upload_file();
print "<HR>";
if ($_FILES["file"]["error"] > 0)
{
  	echo "Error: " . $_FILES["file"]["error"] . "<br />";
}
else
	if ($_FILES["file"]["size"] > 0) 
	{
//		import_hris_data();

//		test_publication_data();
		import_publication_data();
	} else print "Please select a valid Publication file to upload.";
	
mysql_close($conn);

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
	if ($publication_table) print_table($publication_table, array(),0);
}

//--------------------------------------------------------------------------------------------------
function import_publication_data()
{
	$d_id = $_POST['dept_id'];
	if(!$d_id)
	{
		print "no department specified - aborting!";
		return FALSE;
	}

	$publication_file = $_FILES['file']['name'];
	$publication_tmp_file = $_FILES['file']['tmp_name'];
	$publication_table = csv2table($publication_tmp_file, 1);

	$num_of_recs = count($publication_table);
	print "read $num_of_recs records from $publication_file".$nl;

//	now check each publication in the table 
	$pub_add = array();
	$pub_upd = array();
	$jour_add = array();
	$jour_upd = array();
	$publ_add = array();
	$publ_upd = array();
	$aut_add = array();
	$aut_upd = array();

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
//				if($journal_id === NULL)print "journal_id = NULL - better!\n";
			}
			if(!$publication_id) 
			{
				$publication_id = add_publication($row, $d_id, $output_type_id, $journal_id, $publisher_id);
				$publications_added++;
			}
			else if($output_type) 
			{
				update_publication($row, $d_id, $output_type_id, $journal_id, $publisher_id);
				if($d_id == 37) $publication_role_id = 2;	// set role id to 2 (co-author) for SPI (id=37) when publication already exists
				$publications_updated++;
			}
//print "prid = $publication_role_id\n";
		}

// check if a relation between the publication and the author exists and add the author if not

		unset($e_nr);
		$e_nr = $row['Employee Number'];
		$author = addslashes(utf8_decode($row['Author name']));
		$ref_rank = $row['Output Number (REF number)'];
		$priority_order = $row['Author order'];
		if($priority_order < 0) $priority_order = 1;
		
		if($e_nr > 0 AND $publication_id > 0) 
		{
			$e_id = get_employee_id($e_nr);
			if(!author_exists($e_id, $publication_id))
			{
//				add_author($e_id, $publication_id, $ref_rank, $priority_order);
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

	print "<BR />";
	print "parsed $row rows:<BR /><BR />";
	if($journals_added) print "Journals added: $journals_added<BR />";
	if($journals_updated) print "Journals updated: $journals_updated<BR />";
	if($publishers_added) print "Publishers added: $publishers_added<BR />";
	if($publications_added) print "Publications added: $publications_added<BR />";
	if($publications_updated) print "Publications updated: $publications_updated<BR />";
	if($authors_added) print "Authors added: $authors_added<BR />";
	if($authors_updated) print "Authors updated: $authors_updated<BR />";
	if($external_authors_added) print "External Authors added: $external_authors_added<BR />";
	if($external_authors_updated) print "External Authors updated: $external_authors_updated<BR />";
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
function update_authors($dept_code)
//	update the authors field in the publication table so it looks good in the interface
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


// ==============================================< Publications >==================================================
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
			is_sensitive,
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
			'".$assoc_data['Sensitive']."', 
			CURDATE()
		)
		";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
	print "Publication '".$assoc_data['Title of Output']."' has been added.<BR />";
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

//===============================================< Helpers >======================================================
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
function department_options($filter)
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
function upload_file()
{
	$department_options = department_options('3C');
	$actionpage = $_SERVER["PHP_SELF"];
	print "
		<form action='$actionpage' method='post'
		enctype='multipart/form-data'>
		<label for='file'>Filename:</label>
		<input type='file' name='file' id='file' /> 
		<br />
		<BR />
		Select Department to allocate Publications: $department_options
		<BR />
		<BR />
		
		<input type='submit' name='submit' value='Submit' />
		</form>
	";
}

//--------------------------------------------------------------------------------------------------
function csv2table($filename, $has_headers)
//	open the file with the given filename and return the contents in a table - with or without headers
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
function csv3table($filename, $has_headers)
//	open the file with the given filename and return the contents in a table - with or without headers
{
	if (($handle = fopen($filename, "r")) !== FALSE) 	// open the file for reading
	{
		return str_getcsv (fgetcsv($handle, 0, ","), ',' , '"' );
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_data($query)
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
function print_table($table, $table_width, $switch)
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
					$utf8_field2 = htmlentities($utf8_field);
					print "<TD>$utf8_field2</TD>";
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
function dprint($data)
//	print the $data for debugging
{
	$the_color = 'RED';
	$the_color = '#FF6600';		//eleven-orange
	if($_SERVER['TERM']) print "\n------------------------------------------------------------\n$data\n------------------------------------------------------------\n";
	else print "<HR COLOR=$the_color><FONT COLOR=$the_color>$data</FONT><HR COLOR=$the_color>";
}


?> 