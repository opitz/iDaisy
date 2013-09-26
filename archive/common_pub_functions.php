<?php

//==================================================================================================
//
//	Separate file with common publication functions
//	Last changes: Matthias Opitz --- 2012-09-11
//
//==================================================================================================
$version_ctcf = "120718.1";			// 1st version
$version_ctcf = "120807.1";			// added medium and large report
$version_ctcf = "120815.1";			// added year range in query form
$version_ctcf = "120911.1";			// changed type names for report type

//--------------------------------------------------------------------------------------------------------------
function show_publication_list()
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$this_page = this_page();

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
	$from_year = $_POST['from_year'];											// get from_year
	$to_year = $_POST['to_year'];												// get to_year
	$year = $_POST['year'];														// get year
	$department_code = $_POST['department_code'];							// get department_code
	$author_q = $_POST['author_q'];											// get author_q
	$title_q = $_POST['title_q'];													// get title_q
	$report_type = $_POST['report_type'];										// get report_type

	$query = $_POST['query'];													// get query
	$query = stripslashes($query);

	$db_name = get_database_name();

//	$date = date('l jS \of F Y g:i:s');

//	build build the query using the input
//	if(!$query AND ($department_code OR $fullname_q OR $forename_q OR $webauth_q OR $employee_nr_q))
	if(!$query)
	{
		$query = "
			SELECT
			pub.id AS 'PUB_ID',
			pub.title AS 'Title',
			IF(CHAR_LENGTH(j.journal_name) > 0, j.journal_name, pub.volume_title) AS 'Journal/Volume',
			pubt.publication_type_name AS 'Type',
			#YEAR(pub.publicationdate) AS 'Year',
			CONCAT(YEAR(pub.publicationdate),'-',MONTH(pub.publicationdate),'-',DAY(pub.publicationdate)) AS 'Date',
			pubs.label AS 'Status',";
		if($report_type == 'standard' OR $report_type == 'detailed') $query = $query."
			psh.publisher_name AS 'Publisher',";
		if($report_type == 'detailed') $query = $query."
			pub.journal_volume AS 'Vol.Nr.',
			pub.issue AS 'Issue',
			pub.article_number AS 'Art.Nr.',
			pub.pagination AS 'Pagination',";
		if($report_type == 'standard' OR $report_type == 'detailed') $query = $query."
			pub.isbn AS 'ISBN / ISSN',
			pub.doi AS 'DOI',";
		if($report_type == 'detailed') $query = $query."
			CONCAT('<A HREF=',pub.url,' TARGET=NEW>',pub.url,'</A>') AS 'URL',";
		$query = $query."
			''

			FROM Publication pub
			INNER JOIN PublicationType pubt ON pubt.id = pub.publication_type_id
			INNER JOIN Department d ON d.id = pub.department_id
			LEFT JOIN Journal j ON j.id = pub.journal_id
			LEFT JOIN PublicationStatus pubs ON pubs.id = pub.publication_status_id
			LEFT JOIN Publisher psh ON psh.id = pub.publisher_id ";
		if($author_q) $query = $query."

			INNER JOIN PublicationEmployee pube ON pube.publication_id = pub.id
			LEFT JOIN Employee e ON e.id = pube.employee_id

			WHERE (e.fullname LIKE '%$author_q%' OR pube.external_employee_name LIKE '%author%')			
			";
		else $query = $query."WHERE 1=1 ";

		if($department_code) $query = $query."AND d.department_code LIKE '$department_code' ";
		if($title_q) $query = $query."AND pub.title LIKE '%$title_q%' ";
		if($year) $query = $query."AND YEAR(pub.publicationdate) = '$year' ";
		if($from_year) $query = $query."AND YEAR(pub.publicationdate) >= '$from_year' ";
		if($to_year) $query = $query."AND YEAR(pub.publicationdate) <= '$to_year' ";

		$query = $query."ORDER BY pub.title";
	}

	$table = get_data($query);
	$new_table = array();
	$dept_id = get_dept_id($department_code);

//	now amend the data in the table row by row
	if($table) foreach($table as $row)
	{
//		$pub_id = $row['PUB_ID'];
		$pub_id = array_shift($row);
			
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
//			$author_list = $author_list.$author['fullname'];
//			$author_list = $author_list."<A HREF='$this_page?e_id=".$author['id'].">".$author['fullname']."</A>";
//	only show links to staff affiliated to a user's own department unless the user is a Super-Administrator or better
			if(current_user_is_in_DAISY_user_group("Super-Administrator") OR employee_is_affiliated($author['id'], $dept_id)) $author_list = $author_list."<A HREF=$this_page?e_id=".$author['id']."&department_code=$department_code>".$author['fullname']."</A>";
			else $author_list = $author_list.$author['fullname'];
		}
//		array_shift($row);		// get rid of the ID in the first column that is too ugly to display...
		$authors = array('Author(s)' => $author_list);
//		$row = array_merge((array)$author_list, (array)$row);
		$row = array_merge($authors, $row);
//		$row['Author(s)'] = $author_list;
				
//	if an academic year was selected do some more...
		if($ay_id > 0) 
		{
		}
		
		$new_table[] = $row;
	}

//	Now print the whole lot
	if(!$excel_export)
	{
		print_header("Publication Report");
		publication_query_form(); 
		print "<HR>";
	}


//	if ($excel_export) dprint('huhu!');

	// specify column width by column title e.g. $table_width['Title'] = 200
	$table_width =array('Title' => 350, 'Journal/Volume' => 100, 'Type' => 100, 'Author(s)' => 200, 'Date' => 80);

//	if ($excel_export AND $ay_id) export2excel($new_table, $export_title);
	if ($table AND $excel_export) export2csv($new_table, "DAISYinfo_Publication_Report_");
	else print_table($new_table, $table_width, TRUE);
	
	if($to_year < $from_year) print "Please review the dates selection for the publication year!<P>";

}

?>