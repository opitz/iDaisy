<?php
// common content independent functions to export data into files
// to be included in other scripts
//
// Original PHP code by Chirp Internet: www.chirp.com.au
// Please acknowledge use of this code by including this header.
//
// 	12-07-13 	1. Version
//	12-09-28	added excel_header();
//	12-10-24	split into cleanDateExcel() and cleanDateCSV()

//--------------------------------------------------------------------------------------------------

//-----------------------------------------------------------------------------------------
function excel_header($docname)		
//declares the type of data sent back to the browser.  Needs to be put into the code before any other output that goes to the user! Outgoing data will be identified as Excel file
{
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=".$docname.".xls");
	header("Pragma: no-cache");
	header("Expires: 0");
}

//--------------------------------------------------------------------------------------------------
function export_button()
//	return the HTML code to display a button to export a result into an Excel file
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	print "<FORM action='$actionpage' method=POST>";

	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		$html = $html. "<input type='hidden' name='$param_key' value='$param'>";
	}
	$html = $html."<input type='hidden' name='excel_export' value=1>";

	$html = $html."<input type='submit' value='Export to Excel'></FORM>";
	return $html;
}

//--------------------------------------------------------------------------------------------------
function print_export_button0()
//	print the export button 
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	

	print "<FORM action='$actionpage' method=POST>";

	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		print "<input type='hidden' name='$param_key' value='$param'>";
	}
	print "<input type='hidden' name='excel_export' value=1>";	
	print "<input type='submit' value='Export to Excel'></FORM>";
}

//--------------------------------------------------------------------------------------------------
function print_export_buttonxxx()
//	print the export button 
{
//	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	

	print "<FORM action='$actionpage' method=POST>";

	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		print "<input type='hidden' name='$param_key' value='$param'>";
	}
	print "<input type='hidden' name='excel_export' value=1>";	
	print "<TABLE BORDER=1>";
	print "<TR>";
	print "<TD>";
		print "<input type='submit' value='     Go!     '>";
	print "</TD>";
	print "<TD>";
//		print_reset_button();
		print "<input type='submit' value='Export to Excel'>";
	print "</TD>";
	print "<TD>";
//		if($_POST['query_type']) print_export_button();
	print "</TD>";
	print "</TR>";
	print "</TABLE>";

	print "</FORM>";
}

//--------------------------------------------------------------------------------------------------
function cleanDataExcel(&$str)
{
//	escape tab characters
	$str = preg_replace("/\t/", "\\t", $str);

//	escape new lines
	$str = preg_replace("/\r?\n/", "\\n", $str);

//	convert 't' and 'f' to boolean values
	if($str == 't') $str = 'TRUE';
	if($str == 'f') $str = 'FALSE';

//	force certain number/date formats to be imported as strings
	if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) 
	{
		$str = "'$str";
	}

//	escape fields that include double quotes
	if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	
//	force encoding to UTF-16LE because Excel is known to have issues with UTF-8
	$str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');

//	strip tags from output
	$str = strip_tags($str);
}

//--------------------------------------------------------------------------------------------------
function cleanDataCSV(&$str)
{
//	escape tab characters
	$str = preg_replace("/\t/", "\\t", $str);

//	escape new lines
	$str = preg_replace("/\r?\n/", "\\n", $str);

//	convert 't' and 'f' to boolean values
	if($str == 't') $str = 'TRUE';
	if($str == 'f') $str = 'FALSE';

//	force certain number/date formats to be imported as strings
//	if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) 
//	{
//		$str = "'$str";
//	}

//	escape fields that include double quotes
//	if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
	if(strstr($str, '"')) $str = str_replace('"', '""', $str);
//	$str = addslashes($str);
	
//	force encoding to UTF-16LE because Excel is known to have issues with UTF-8
	$str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');

//	strip tags from output
	$str = strip_tags($str);
}

//--------------------------------------------------------------------------------------------------
function export2excel($table, $file_name)
//	export the data from the table into an Excel document
{
//	check if there is anything to export at all
	if ($table)
	{
//	1st test: export as text file
// 		filename for download
  		$filename = $file_name . date('Ymd') . ".xls";

//		header("Content-Type: text/plain"); // plain text file
  		header("Content-Disposition: attachment; filename=\"$filename\"");	// Excel file
  		header("Content-Type: application/vnd.ms-excel");


		$flag = false;
  		foreach($table as $row) 
  		{
    		if(!$flag) 
    		{
// display field/column names as first row
      			echo implode("\t", array_keys($row)) . "\r\n";
      			$flag = true;
    		}
    		array_walk($row, 'cleanDataExcel');	// this will apply the function cleanDataExcel (see above) to each element of the array $row
    		echo implode("\t", array_values($row)) . "\r\n";
  		}
	} else
	print "<FONT COLOR=$alert_colour>The query returned no results!</FONT><BR>";
}

//--------------------------------------------------------------------------------------------------
function export2csv($table, $file_name)
//	export the data from the table into an CSV file
{
//	check if there is anything to export at all
	if ($table)
	{
//	1st test: export as text file
// 		filename for download
  		$filename = $file_name . date('Ymd') . ".csv";

//		header("Content-Type: text/plain"); // plain text file
  		header("Content-Disposition: attachment; filename=\"$filename\"");	// Excel file
//		header("Content-Type: application/vnd.ms-excel");
		header("Content-Type: application/vnd.ms-excel; charset=UTF-16LE"); // with UTF-16LE encoding
		
 		$out = fopen("php://output", 'w');

		$flag = false;
  		foreach($table as $row) 
  		{
    		if(!$flag) 
    		{
// display field/column names as first row
      			fputcsv($out, array_keys($row), ',', '"');
      			$flag = true;
    		}
    		array_walk($row, 'cleanDataCSV');	// this will apply the function cleanDataCSV (see above) to each element of the array $row
      		fputcsv($out, array_values($row), ',', '"');
  		}
		fclose($out);
	} else
	print "<FONT COLOR=$alert_colour>The query returned no results!</FONT><BR>";
}

//--------------------------------------------------------------------------------------------------
function export2csv_header($table, $header)
//	export the data from the table into an CSV file with a header
{
//	check if there is anything to export at all
	if ($table)
	{
		$department_code = $_POST['department_code'];							// get department_code
		$department = get_dept_name_from_code($department_code);
		$ay_label = get_academic_year_label($_POST['ay_id']);

//	1st test: export as text file
// 		filename for download
  		$filename = $header . date('Ymd') . ".csv";
//		header("Content-Type: text/plain"); // plain text file
  		header("Content-Disposition: attachment; filename=\"$filename\"");	// Excel file
//		header("Content-Type: application/vnd.ms-excel");
		header("Content-Type: application/vnd.ms-excel; charset=UTF-16LE"); // with UTF-16LE encoding
		
 		$title = $header . "($ay_label) for " . $department;
		$date = date('l jS \of F Y g:i:s');
		$out = fopen("php://output", 'w');

		fputcsv($out, array($title));
		fputcsv($out, array("Run on: $date"));
		fputcsv($out, array(""));
		$flag = false;
  		foreach($table as $row) 
  		{
    		if(!$flag) 
    		{
// display field/column names as first row
      			fputcsv($out, array_keys($row), ',', '"');
      			$flag = true;
    		}
    		array_walk($row, 'cleanDataCSV');	// this will apply the function cleanDataCSV (see above) to each element of the array $row
      		fputcsv($out, array_values($row), ',', '"');
  		}
		fclose($out);
	} else
	print "<FONT COLOR=$alert_colour>The query returned no results - nothing to export!</FONT><BR>";
}


?>
