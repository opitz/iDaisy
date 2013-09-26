<?php
// common function to open databases used by DAISY reports
// to be included in other scripts
// Last modified by M.Opitz 2013-02-13
//
//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
function open_daisydb()
// opens the DAISY database using the normal method
{
	$params = parse_ini_file('idaisy.ini');

	$conn = mysql_connect($params['dbhost'], $params['dbuser'], $params['dbpass']) or die ('Error connecting to MySQL server!');
	mysql_select_db($params['dbname'], $conn) or die ('Error selecting database');

	mysql_query("SET NAMES 'utf8'"); //this is new
	mysql_query("SET CHARACTER SET 'utf8'"); //this is new

//	echo "<FONT color=#FF0000>>>> connected to DAISY database $dbname on $dbhost as $dbuser.</FONT><BR />";
	
	return $conn;
}

//--------------------------------------------------------------------------------------------------
function openi_daisydb()
// opens the DAISY database using the object oriented method
{
	$params = parse_ini_file('idaisy.ini');

	$conn = mysqli_connect($params['dbhost'], $params['dbuser'], $params['dbpass']) or die ('Error connecting to MySQL server!');
	mysqli_select_db($conn, $params['dbname']) or die ('Error selecting database');

//	echo "<FONT color=#FF0000>>>> connected to DAISY database $dbname as $dbuser.</FONT><BR />";
	
	return $conn;
}

//--------------------------------------------------------------------------------------------------
function open_data_warehousedb()
// opens the DATA_WAREHOUSE database
{
	$params = parse_ini_file('idaisy.ini');

	$conn = odbc_connect($params['dw_dbhost'], $params['dw_dbuser'], $params['dw_dbpass']) or die ("Error connecting to Data Warehouse MSSQL server!");
//	echo "<FONT color=#FF0000>>>> connected to DATA WAREHOUSE database $dbname as $dbuser.</FONT><BR />";
	
	return $conn;
}


//--------------------------------------------------------------------------------------------------
function mssql_connect_data_warehousedb()
// connects to the DATA_WAREHOUSE database using the php5-sybase library
{
	$params = parse_ini_file('idaisy.ini');

	$link = mssql_connect($params['dw_server'], $params['dw_dbuser'], $params['dw_dbpass']);

	if (!$link) {
	die("<br/><br/>Something went wrong while connecting to the Data Warehouse!");
	}
	else {
	$selected = mssql_select_db($params['dw_dbname'], $link) or die("Couldn’t open database databasename");
//	echo "<FONT color=#FF0000>>>> connected to DATA WAREHOUSE database $dbname as $dbuser.</FONT><HR>";
	}
	
	return $link;
}

//--------------------------------------------------------------------------------------------------
function get_database_name()
// returns the name of the selected database
{
	$query = "SELECT DATABASE()";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['DATABASE()'];
}	

//--------------------------------------------------------------------------------------------------
function geti_database_name($conn)
// returns the name of the selected database
{
	$query = "SELECT DATABASE()";
	$result = $conn->query($query) or die ("Could not execute query: $query ". mysql_error());	
	$row = $result->fetch_assoc();
	return $row['DATABASE()'];
}	

//--------------------------------------------------------------------------------------------------

?>
