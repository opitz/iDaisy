<?php

//==================================================================================================
//
//	Separate file with common publication functions
//	Last changes: Matthias Opitz --- 2012-08-07
//
//==================================================================================================
$version_ctcf = "120718.1";			// 1st version
$version_ctcf = "120807.1";			// 2nd version

//--------------------------------------------------------------------------------------------------------------
function show_publication_list($webauth_code)
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
	$author_q = $_POST['author_q'];												// get author_q
	$title_q = $_POST['title_q'];												// get title_q

	$parameter['ay_id'] = $_POST['ay_id'];										// get academic_year_id
	$parameter['department_code'] = $_POST['department_code'];					// get department_code
	$parameter['author_q'] = $_POST['author_q'];								// get author_q
	$parameter['title_q'] = $_POST['title_q'];									// get forename_q

	$query = $_POST['query'];									// get query
	$query = stripslashes($query);

	$db_name = get_database_name();

//	$date = date('l jS \of F Y g:i:s');

//	build build the query using the input
//	if(!$query AND ($department_code OR $fullname_q OR $forename_q OR $webauth_q OR $employee_nr_q))
	if(!$query)
	{

		if($author_q) $query = "
SELECT
pub.id AS 'PUB_ID',
pub.title AS 'Title',
IF(CHAR_LENGTH(j.journal_name) > 0, j.journal_name, pub.volume_title) AS 'Journal/Volume',
pubt.publication_type_name AS 'Type',
#YEAR(pub.publicationdate) AS 'Year',
CONCAT(YEAR(pub.publicationdate),'-',MONTH(pub.publicationdate),'-',DAY(pub.publicationdate)) AS 'Date',
pubs.label AS 'Status'

FROM Publication pub
INNER JOIN PublicationType pubt ON pubt.id = pub.publication_type_id
INNER JOIN Department d ON d.id = pub.department_id
LEFT JOIN Journal j ON j.id = pub.journal_id
LEFT JOIN PublicationStatus pubs ON pubs.id = pub.publication_status_id
INNER JOIN PublicationEmployee pube ON pube.publication_id = pub.id
LEFT JOIN Employee e ON e.id = pube.employee_id

WHERE (e.fullname LIKE '%$author_q%' OR pube.external_employee_name LIKE '%author%')			
			";

		else $query = "
SELECT
pub.id AS 'PUB_ID',
pub.title AS 'Title',
IF(CHAR_LENGTH(j.journal_name) > 0, j.journal_name, pub.volume_title) AS 'Journal/Volume',
pubt.publication_type_name AS 'Type',
#YEAR(pub.publicationdate) AS 'Year',
CONCAT(YEAR(pub.publicationdate),'-',MONTH(pub.publicationdate),'-',DAY(pub.publicationdate)) AS 'Date',
pubs.label AS 'Status'

FROM Publication pub
INNER JOIN PublicationType pubt ON pubt.id = pub.publication_type_id
INNER JOIN Department d ON d.id = pub.department_id
LEFT JOIN Journal j ON j.id = pub.journal_id
LEFT JOIN PublicationStatus pubs ON pubs.id = pub.publication_status_id

WHERE 1=1
			";

		if($department_code) $query = $query."AND d.department_code LIKE '$department_code' ";
//		if($ay_id > 0) $query = $query."AND limit publication date to $ay_id' ";   BAUSTELLE

		$query = $query."ORDER BY pub.title";
	}
	if(!$excel_export)
	{
		print_header("Publication Report", $webauth_code);
		if ($query) print_export_button($parameter);
		publication_query_form($webauth_code, $department_code, $ay_id, $_SERVER["PHP_SELF"]); // this script will call this page again
		print "<HR>";
	}


	if ($query) 
	{
//		$start_time = time();
//      ***********************************************
		$table_width = array();	// specify column width by column title e.g. $table_width['Title'] = 200
		$table_width['Title'] = 350;
		$table_width['Journal/Volume'] = 100;
		$table_width['Type'] = 100;
		$table_width['Author(s)'] = 200;
		$table_width['Date'] = 80;

		$table = get_data($query);
		$new_table = array();
		$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
		if($table) foreach($table as $row)
		{
			$pub_id = $row['PUB_ID'];
//	get the author(s)
			$query = "
				SELECT
				e.*
				
				FROM PublicationEmployee pube
				INNER JOIN Employee e ON e.id = pube.employee_id
				WHERE pube.publication_id = $pub_id
				";
			$authors = get_data($query);
			$author_list = '';
			$i=0;
			if($authors) foreach($authors AS $author)
			{
				if($i++ > 0) $author_list = $author_list.",<BR>";
//				$author_list = $author_list.$author['fullname'];
//				$author_list = $author_list."<A HREF='index.php?e_id=".$author['id'].">".$author['fullname']."</A>";
				$author_list = $author_list."<A HREF=index.php?e_id=".$author['id'].">".$author['fullname']."</A>";
			}
			$row['Author(s)'] = $author_list;
				
//	if an academic year was selected do some more...
			if($ay_id > 0) 
			{
			}
			
			array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
			$new_table[] = $row;
		}
		
//		if ($excel_export AND $ay_id) export2excel($new_table, $export_title);
		if ($excel_export) export2csv($new_table, "DAISYinfo_Publication_Report_");
//		if ($excel_export) dprint('huhu!');
		else print_table($new_table, $table_width, TRUE);
//      ***********************************************
//		$end_time = time();
//		$diff_time = $end_time - $start_time;
//		print "<HR COLOR = LIGHTGREY><FONT COLOR = GREY>executed in $diff_time seconds";
	}
//	mysql_close($conn);												// close database connection $conn
}



?>