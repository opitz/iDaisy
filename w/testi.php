<?php


//=============================================================================================================
//
//	Script to test msqli
//	
//	Last Update by Matthias Opitz, 2013-06-18
//
//=============================================================================================================
$version = 'i130618.1'; // starting...
//==============================================< Here we go! >====================================================

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

//print "<B>DAISY update</B> |  REF 'Publication' data from CSV file  v.$version";
//print "<HR>";

$conn = open_daisydb();
$conni = openi_daisydb();

print_header("TEST - mysqli");

	go_ahead();

mysqli_close($conni);
mysql_close($conn);
show_footer($version,0);


//--------------------------------------------------------------------------------------------------
function go_ahead()
{
	print "huhu!";
}	




?> 