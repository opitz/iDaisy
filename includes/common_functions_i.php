<?php
//--------------------------------------------------------------------------------------------------------------
// common content independent functions 
// to be included in other scripts
// 2012-06-07 - 1. Version    ==   Happy birthday, Winnie!
// 2012-07-09 - added dprint()
// 2012-09-19 - added dabase name in title for overseers
// 2012-10-01 - added table functions
// 2012-10-05 - added title
// 2012-10-08 - added special header colours and new $switch attribute to print_table()
// 2012-11-08 - added fill_line() and blank_line()
// 2013-02-08 - added start_timer() and stop_timer()
// 2013-02-12 - added p_table()
// 2013-02-13 - added csv2table(), csv_error_explained()
// 2013-02-19 - added show_header(), show_footer(), cleanup()
// 2013-02-21 - added html_checkbox()
// 2013-02-25 - amended show_footer() - show no time when time = 0
// 2013-03-04 - debug functions now for Overseers only
// 2013-03-14 - addedd format_summary_row()
// 2013-04-25 - addedd deb_print()
//
//--------------------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------------------
function this_page()
//	returns the name of the currently opened PHP page without the path
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself
	$parts = Explode('/', $actionpage);
	return $parts[count($parts) - 1];
}

//--------------------------------------------------------------------------------------------------------------
function show_header($title)
{
	if(!$_POST['excel_export'])
	{
		print_header($title);
	}
}

//--------------------------------------------------------------------------------------------------------------
function print_header($title)
{
	if(isset($_POST['department_code']))
	{
		$department_code = $_POST['department_code'];					// get department_code
		$department = get_dept_name_from_code($department_code);
	} else{
		$department_code = FALSE;
		$department = FALSE;
	}
//	$db_name = get_database_name();

	$params = parse_ini_file('idaisy.ini');
	$db_name  = $params['dbname'];
	$db_host = $params['dbhost'];

	$webauth_code = current_user_webauth();
	$fullname =  get_fullname_from_webauth($webauth_code);
	
	$ay_label = get_academic_year_label($_POST['ay_id']);
	
	print '	<head>
		<script language="javascript" src="calendar.js"></script>
		</head>';

	print "<TITLE>iDAISY - $title</TITLE>";
	print "<FONT face='Arial'><B><FONT COLOR=#FF6600>i</FONT>DAISY | $title ($ay_label)</B> ";
	if($department_code) print " for $department ($department_code) ";
//	if(current_user_is_in_DAISY_user_group("Overseer") AND this_page() != 'index.php') print " <FONT SIZE=2> -> <A HREF=index.php>Central Services</A></FONT>";
	print "</FONT> ";
//	print "<BR /><EM>connected to <B>$db_name</B> as user <B>$webauth_code</B></EM>";

	$date = date('l jS \of F Y g:i:s');
//	if ($date) print "<BR><EM>Run on: $date by <B>$webauth_code</B></EM><P>";
	if ($date) 
	{
		print "<BR><FONT face='Arial' COLOR=#766666><EM>$date, run by <B>$fullname</B> ($webauth_code)";
//		if(current_user_is_in_DAISY_user_group("Overseer")) print " connected to $db_name";
		if(current_user_is_in_DAISY_user_group("Overseer")) print " - connected to <B>$db_name</B> on <B>$db_host</B>";
		print "</EM></FONT><P>";
	}
	print "<P>";
	print "<HR>";	
}

//--------------------------------------------------------------------------------------------------------------
function get_data($query)
//	do the query and store the result in a table that is returned
{
//	if $_POST['deb'] == 'query' show the query
//	if(isset($_POST['deb'])) if($_POST['deb'] == 'query') d_print($query);

	$result = mysql_query($query) or die ("Could not execute query: " . d_print($query) . "Error = " . mysql_error());	

	if($result) while ($row = mysql_fetch_assoc($result))	
	{
		$table[] = $row;
	}

	return $table;		
}

//--------------------------------------------------------------------------------------------------------------
function cleanup($table, $number_to_remove)
//	removes all internal variables - which are always the first few ones - from rows
{
	$new_table = array();
	if($table) foreach ($table AS $row)
	{
		for ($i = 1; $i <= $number_to_remove; $i++) 
		{
    			$trash = array_shift($row);
		}

		$new_table[] = $row;
	}
	return $new_table;
}


//--------------------------------------------------------------------------------------------------------------
function print_table($table, $table_width, $switch)
//
{
//	$switch == 0	:	print NO line numbers and NORMAL header	
//	$switch == 1	:	DO print line numbers and NORMAL header	
//	$switch == 2	:	print NO line numbers and SPECIAL header	
//	$switch == 3	:	DO print line numbers and SPECIAL header	
	
	$fontface = "Arial";
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

	if($switch == FALSE) $switch = 0;
	if($switch == TRUE) $switch = 1;

	if($switch === 2 OR $switch === 3) 
	{
		$header_colour = $special_header_colour;
		$linecount_bkgnd_colour = $special_linecount_bkgnd_colour;
	}

//	check if there is anything to print at all
	if ($table[0])
	{
//	get the keys from the table and use them as column titles
		$keys = array_keys($table[0]);	
		
//	if keys could be found print the table
		if ($keys)
		{
			if($switch % 2) print "Query returned ".count($table)." lines<BR>";
			print "<FONT FACE=$fontface>";
			print "<TABLE BORDER = 0>";
			if($keys[0] == '0') print "<TR>";
			else print "<TR bgcolor=$header_colour>";
//			if($keys[0] != '0')
			if(1 == 1)
			{
				if($switch % 2) print "<TH WIDTH = 30></TH>";
				foreach ($keys as $column_name) 
				{
					$width = $table_width["$column_name"];
					if($keys[0] == '0') print "<TD WIDTH='$width'></TD>";
					else print "<TH WIDTH='$width'><FONT COLOR=$header_font_colour>$column_name</FONT></TH>";
				}
				print "</TR>";
			}		
//	now print the rows
			$linecount = 0;
			foreach ($table as $row)
			{
				if ($keys[0] != '0' AND $line_colour == $line_colour_1)
					$line_colour = $line_colour_2;
				else
					$line_colour = $line_colour_1;

				print "<TR bgcolor=$line_colour>";
				$linecount++;
				if($switch % 2) print "<TD bgcolor = $linecount_bkgnd_colour>$linecount</TD>";
				$i = 0;
				foreach ($row as $field)
				{
					$utf8_field = utf8_decode($field);
					if($i++ % 2 == 0 AND $keys[0] == '0')	 print "<TD><B>$utf8_field</B></TD>";
					else print "<TD>$utf8_field</TD>";
				}
				$prev_row = $row;
				print "</TR>";
			}
			print "</TABLE>";
			print "</FONT>";
		} 
		else
		print "<FONT FACE=$fontface>Could not find keys in $table, aborting! <BR></FONT>";
	} else
	print "<FONT FACE=$fontface COLOR=$alert_colour>The query returned no results!</FONT><BR>";
}


//-----------------------------------------------------------------------------------------
function show_footer($version, $totaltime)
{
	if(!$_POST['excel_export'])
	{
		if($totaltime == 0) print "<HR><FONT SIZE=2 COLOR=GREY>v.$version</FONT>";
		else print "<HR><FONT SIZE=2 COLOR=GREY>v.$version  | executed in $totaltime seconds</FONT>";
	} 
}



//--------------------------------------------------------------------------------------------------------------
function p_table($table)
//	print the $table with standard settings
{
	print_table($table, array(),1);
}

//--------------------------------------------------------------------------------------------------------------
function fill_line($array, $filler)
// returns an array with filler entries for every key in the table
{
//	get the keys
	if($array) $keys = array_keys($array);
	if($keys) 
	{
		$row = array();
		foreach($keys AS $key)
		{
			$row[$key] = $filler;
		}
		return $row;
	} 
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function blank_line($array)
// returns an array with blank entries for every key in the table
{
	return fill_line($array, '');
}

//--------------------------------------------------------------------------------------------------------------
function format_summary_row($row)
//	if the output is not for excel wrap all contents in row with bold and underline HTML code
{
	if (!$_POST['ecxel_export'])
	{
		$keys = array_keys($row);
		if($row AND $keys)
		{
			foreach($keys AS $key)
			{
				$row[$key] = "<B><U>" . $row[$key]  . "</U></B>";
			}
		}
	}
	return $row;
}

//--------------------------------------------------------------------------------------------------------------
function html_reset_button111()
{
	$html = "<FORM action='".$_SERVER['PHP_SELF']."' method=POST>";
	$html = $html . "<input type='button' name='Cancel' value='- Reset -' onclick=window.location='$actionpage'  />";
	$html = $html . "</FORM>";
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function write_post_vars_html()
{
	$html = '';
	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		$html = $html. "<input type='hidden' name='$param_key' value='$param'>";
	}
	return $html;
}

//----------------------------------------------------------------------------------------
function html_checkbox($variable)
// returns the HTML code to display a checkbox with the given variable
{
	if ($_POST[$variable]) return "<input type='checkbox' name='$variable' value='TRUE' checked='checked'>";
	else return "<input type='checkbox' name='$variable' value='TRUE'>";
}
//==========================< File Functions >========================
//--------------------------------------------------------------------------------------------------------------
function csv2table($filename, $has_headers)
//	open the file withe the given filename and return the contents in a table - with or without headers
{
	ini_set('auto_detect_line_endings',TRUE);
	if (($handle = fopen($filename, "r")) !== FALSE) 	// open the file for reading
	{
		$assoc_data = array();
		$table = array();
		$row_count = 1;
		while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) 
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
		ini_set('auto_detect_line_endings',FALSE);
		return $table;		
	} else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function csv_error_explained($errno)
//	returns some more meaningful text for a given error number
{
	switch ($errno) 
	{
    		case 0:
        		$text = "Success";
       			break;
   		 case 1:
        		$text = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
        		break;
    		case 2:
        		$text = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
         		break;
  		case 3:
        		$text = "The uploaded file was only partially uploaded.";
         		break;
     		case 4:
        		$text = "No file was uploaded.";
         		break;
    		case 6:
         		$text = "Missing a temporary folder.";
         		break;
   		case 7:
        		$text = "Failed to write file to disk.";
         		break;
    		case 8:
        		$text = "A PHP extension stopped the file upload.";
         		break;
        	default:
        		$text = "Unknown error code : $errno";
        		break;
	}
	return $text;
}

//=========================< Table Functions >=======================
//--------------------------------------------------------------------------------------------------------------
function start_row($width)
//	return the HTML code for starting a row of a table
{
	if($width > 0) return "<TR><TD WIDTH=$width>";
	else return "<TR><TD>";
}

//--------------------------------------------------------------------------------------------------------------
function new_column($width)
//	return the HTML code for a new column of a table
{
	if($width > 0) return "</TD><TD WIDTH=$width>";
	else return "</TD><TD>";
}

//--------------------------------------------------------------------------------------------------------------
function end_row()
//	return the HTML code for ending a row of a table
{
	return "</TD></TR>";
}

//=========================< Timer Functions >=======================
//--------------------------------------------------------------------------------------------------------------
function start_timer()
//	returns the time in milliseconds
{
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
return $mtime; 
}

//--------------------------------------------------------------------------------------------------------------
function stop_timer($starttime)
//	returns the time in milliseconds elapsed since $starttime
{
// 	stop the timer
	$mtime = microtime(); 
	$mtime = explode(" ",$mtime); 
	$mtime = $mtime[1] + $mtime[0]; 
	$endtime = $mtime; 
	return ($endtime - $starttime); 
}

//=========================< DEBUG Functions >======================
//--------------------------------------------------------------------------------------------------------------
function dprint($data)
//	print the $data for debugging
{
	if(current_user_is_in_DAISY_user_group("Overseer"))  
	{
		$the_color = 'RED';
		$the_color = '#FF6600';		//eleven-orange
		print "<HR COLOR=$the_color><FONT COLOR=$the_color>$data</FONT><HR COLOR=$the_color>";
	}
}

//--------------------------------------------------------------------------------------------------------------
function d_print($data)
//	print the $data for debugging
{
	if(current_user_is_in_DAISY_user_group("Overseer"))  
	{
		$the_color = 'RED';
		$the_color = '#FF6600';		//eleven-orange
		$data = str_replace("\n", '<BR>', $data);
		print "<HR COLOR=$the_color><FONT COLOR=$the_color>$data</FONT><HR COLOR=$the_color>";
	}
}

//--------------------------------------------------------------------------------------------------------------
function deb_print($query)
//	print the $data for debugging when $_POST['deb'] == 'query'
//	but only if the user is a DAISY Overseer
{
	if(current_user_is_in_DAISY_user_group("Overseer"))  
	{
		if(isset($_POST['deb'])) if($_POST['deb'] == 'query') d_print($query);
	}
}

//--------------------------------------------------------------------------------------------------------------
function dget()
//	print all $_GET attributes
{
	if(current_user_is_in_DAISY_user_group("Overseer"))  
	{
		$attributes = '';
	
		$para_keys = array_keys($_GET);
		 $i = 0;
		foreach($_GET AS $param)
		{
			$param_key = $para_keys[$i++];
			if ($i > 1) $attributes = $attributes." | ";
			$attributes = $attributes."$param_key = $param";
		}
		if($attributes) dprint("GET: $attributes");
	}
}

//--------------------------------------------------------------------------------------------------------------
function dpost()
//	print all $_POST attributes
{
	if(current_user_is_in_DAISY_user_group("Overseer"))  
	{
		$attributes = '';
	
		$para_keys = array_keys($_POST);
		 $i = 0;
		foreach($_POST AS $param)
		{
			$param_key = $para_keys[$i++];
			if ($i > 1) $attributes = $attributes." | ";
			$attributes = $attributes."$param_key = $param";
		}
		if($attributes) dprint("POST: $attributes");
	}
}

?>
