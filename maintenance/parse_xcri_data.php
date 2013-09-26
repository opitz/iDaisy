<?php

$version = '121129.1';			// 1st version

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';

$conn = open_daisydb();									// open DAISY database
$db_name = get_database_name($conn);

print "<H2>DAISY update</B> |  XML Enrolment Data from SES</H2>
<I>connected to <B>$db_name</B></I>
<HR>";

if(current_user_is_in_DAISY_user_group("Overseer"))
	go_ahead();
else
	show_no_mercy();

mysql_close($conn);							// close DAISY database connection
print "<HR><FONT SIZE=2 COLOR=LIGHTGREY>v.".$version."</FONT>";

//--------------------------------------------------------------------------------------------------
function go_ahead()
{
	upload_file();

	print "<HR>";
	if ($_FILES["file"]["error"] > 0)
	{
	  	echo "Error: " . $_FILES["file"]["error"] . "<br />";
	}
	else
		if ($_FILES["file"]["size"] > 0) 
		{
			$ses_file = $_FILES['file']['name'];
			$ses_tmp_file = $_FILES['file']['tmp_name'];
			
			$start_time = time();
			$xml = new SimpleXMLElement("$ses_tmp_file", null, true);
			$table = array();
			$table = parse_enrolment_data($xml, $table);
			$table = parse_table($table);
		}

	if($table)
	{
		print "<H3>Enrolled Students via SES</H3>";
		print_table($table);

		$end_time = time();
		$diff_time = $end_time - $start_time;
		print "<HR COLOR = LIGHTGREY><FONT COLOR = GREY>executed in $diff_time seconds";
	} else print "Select a valid XML file from SES to upload.";
}

//--------------------------------------------------------------------------------------------------
function upload_file()
{
	$actionpage = $_SERVER["PHP_SELF"];
	print "
		<form action='$actionpage' method='post'
		enctype='multipart/form-data'>
		<label for='file'>Filename:</label>
		<input type='file' name='file' id='file' /> 
		<br />
		<input type='submit' name='submit' value='Submit' />
		</form>
	";
}

//----------------------------------------------------------------------------------------
function parse_table($table)
//	do things with the table before it is returned for output
{
	$i = 0;
	$new_table = array();
	$dup_table = array();	
	foreach($table as $row)
	{
		$ay_id = get_instance_ay_id($row['ti_id']);
//		if($row['DAISY ID'] != '' AND $row['au_code'] != '' AND is_in_academic_year($row['term'], $ay_id))
		if($row['DAISY ID'] != '' AND $row['au_code'] != '')
		{
			if(!enrolment_exists($row, $ay_id) )
			{
				add_enrolment($row, $ay_id);
				$new_row = array();
				
				$new_row['Unit Code'] = $row['au_code'];
				$new_row['Unit Title'] = $row['au_title'];
				$new_row['Student'] = $row['name'];
				$new_row['Status'] = $row['status'];

//				$new_table[] = $row;
				$new_table[] = $new_row;
//				print "+";
			}
			else
			{
				print ".";
//				$dup_table[] = $row;
			}
		}
	if ($i++ % 100 == 0) print " : $i<BR>";
	}
	print "<P>";
	
//	print "<H3>Duplicates</H3>";
//	print_table($dup_table);
//	print "<P>";
//	$new_table = $table;
	
	return $new_table;
}

//----------------------------------------------------------------------------------------
function get_instance_ay_id($ti_id)
//	returns the ID of the academic year a teaching instance is/was given
{
	$query = "
		SELECT
		t.academic_year_id
		
		FROM TeachingInstance ti 
		INNER JOIN Term t ON t.id = ti.term_id
		WHERE ti.id = $ti_id
		";
	$table = get_data($query);
	$record = $table[0];
	return $record['academic_year_id'];	
}
//----------------------------------------------------------------------------------------
function is_in_academic_year($term_code, $ay_id)
//	check if a given term code belongs to a given academic year
{
	$query = "
		SELECT
		*
		
		FROM Term t 
		WHERE t.term_code = '$term_code'
		";
	$table = get_data($query);
	$record = $table[0];
	$acad_year_id = $record['academic_year_id'];
	
	if ($acad_year_id == $ay_id)
	{
		return TRUE;
	} else 
	{
		return FALSE;
	}
	
}

//----------------------------------------------------------------------------------------
function parse_enrolment_data(SimpleXMLElement $element, $table)
//	get the number and ids of the teaching instances from the SES
{
	$value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
	$children   = $element->children();     // get all children
//	$attributes = $element->attributes();   // get all attributes

	if(count($children))
	{
			foreach($children as $child)	// child = teaching instances
			{
				$ti_id = "{$child ->id}";	//	this gives the ID of the Teaching Instance
//print "ti_id = $ti_id<BR>";
				$table = parse_instance($child, $ti_id, $table);
			}
	} 
	return $table;
}

//----------------------------------------------------------------------------------------
function parse_instance(SimpleXMLElement $element, $ti_id, $table)
//	get the number and ids of the teaching instances from the SES
{
	$value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
	$children   = $element->children();     // get all children
//	$attributes = $element->attributes();   // get all attributes

	if(count($children))
	{
		foreach($children as $child)	// child = assessment units
		{
			$au_code = "{$child ->id}";	// this gives the ID of the assessment unit
			$table = parse_unit($child, $ti_id, $au_code, $table);
		}
	} 
	return $table;
}

//----------------------------------------------------------------------------------------
function parse_unit(SimpleXMLElement $element, $ti_id, $au_code, $table)
//	get the number and ids of the teaching instances from the SES
{
	$value   	= trim((string) $element);  // get the value and trim any whitespace from the start and end
	$children  	= $element->children();     // get all children
//	$attributes = $element->attributes();   // get all attributes

	if(count($children))
	{
		foreach($children as $child)	// child = ID and students[]
		{
			$table = parse_students($child, $ti_id, $au_code, $table);
		}
	} 
	return $table;
}

//----------------------------------------------------------------------------------------
function parse_students(SimpleXMLElement $element, $ti_id, $au_code, $table)
//	get the number and ids of the teaching instances from the SES
{
	$value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
	$children   = $element->children();     // get all children

	if(count($children))
	{
		foreach($children as $child)	// student
		{
			$row = parse_values($child, $ti_id, $au_code);
			if($row['status'] != 'WITHDRAWN')
				$table[] = $row;
		}
	} 
	return $table;
}

//----------------------------------------------------------------------------------------
function parse_values(SimpleXMLElement $element, $ti_id, $au_code)
//	get the number and ids of the teaching instances from the SES for a given academic year
{

	$value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
	$values   = $element->children();     // get all children

	if(count($values))
	{
		$row['ti_id'] = $ti_id;
		$teaching_data = get_teaching_data($ti_id, $au_code);
		$subject = $teaching_data['subject'];
		$lecturer = $teaching_data['lecturer'];
		$term = $teaching_data['term'];

		$row['term'] = $term;
		$row['subject'] = $subject;
		$row['lecturer'] = $lecturer;

		$au_code = $teaching_data['assessment_unit_code'];
		$au_title = $teaching_data['title'];
		$row['au_code'] = $au_code;
		$row['au_title'] = $au_title;
		
		
		foreach($values as $value)
		{
//			echo "&nbsp;&nbsp;&nbsp;<FONT COLOR = RED>{$value->getName()}:</FONT> {$value}<BR />";
			$field_name = "{$value->getName()}";
			$field_value = "{$value}";
			$row[$field_name] = $field_value;
			
			if($field_name == 'ossid')
			{
				$student = get_DAISY_student($field_value);
				$st_id = $student['id'];
				$row['DAISY ID'] = $st_id;
				$st_surname = $student['surname'];
				$row['DAISY surname'] = $st_surname;
			}
		}
	} 
	return $row;
}

//--------------------------------------------------------------------------------------------------
function get_DAISY_student($oss_nr)
//
{
	$query ="
		SELECT * FROM Student WHERE oss_student_code = $oss_nr
		";
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------
function get_teaching_data($ti_id, $au_code)
//
{
	$query ="
		SELECT 
		tc.subject,
		e.fullname AS 'lecturer',
		t.term_code AS 'term',
		au.assessment_unit_code,
		au.title
		 
		FROM TeachingInstance ti
		INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
		INNER JOIN Term t on t.id = ti.term_id
		INNER JOIN Employee e ON e.id = ti.employee_id

		LEFT JOIN TeachingComponentAssessmentUnit tcau on tcau.teaching_component_id = tc.id
		LEFT JOIN AssessmentUnit au ON au.id = tcau.assessment_unit_id
				
		WHERE ti.id = $ti_id
		AND au.assessment_unit_code = '$au_code'
		";
	$result = get_data($query);
	return $result[0];
}

//--------------------------------------------------------------------------------------------------------------
function get_data0($query)
//	do the query and store the result in a table
{

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$counter = 0;
	while ($row = mysql_fetch_assoc($result))	
	{
		$table[$counter++] = $row;
	}

	return $table;		
}

//--------------------------------------------------------------------------------------------------
function print_table0($table)
//
{
	global $debug;

	$color[1] = "white"; 
	$color[2] = "lightgrey"; 

//	check if there is anything to print at all
	if ($table)
	{
//	get the keys from the table and use them as column titles
		$keys = array_keys($table[0]);	
		if ($keys)
		{
			print count($table)." lines found<BR>";
			print "<TABLE BORDER = 0>";
			print "<TR bgcolor=DARKBLUE>";
			print "<TH></TH>";
			foreach ($keys as $column_name) print "<TH><FONT COLOR=WHITE>$column_name</FONT></TH>";
			print "</TR>";		
//	now print the rows
			$linecount = 0;
			foreach ($table as $row)
			{
				if ($line_color == $color[1])
					$line_color = $color[2];
				else
					$line_color = $color[1];

				print "<TR bgcolor=$line_color>";
				$linecount++;
				print "<TD bgcolor = LIGHTBLUE>$linecount</TD>";
				foreach ($row as $field)
				{	
					$utf8_field = utf8_decode($field);
					print "<TD>$utf8_field</TD>";
				}
				print "</TR>";
			}
			print "</TABLE>";
		} 
		else
		print "Could not find keys in $table, aborting! <BR>";
	} else
	print "<FONT COLOR=RED>The query returned no results!</FONT><BR>";
}
  

//========================================================================================

//--------------------------------------------------------------------------------------------------
function enrolment_exists($row, $ay_id)
// check if an enrolment or a student with an assessment unit or pgr module already exists
{	
	$st_id = $row['DAISY ID'];
	$au_code = $row['au_code'];
//print "-> st_id = $st_id | au_code = $au_code<BR>";
	if($st_id AND $au_code)
	{
		$query = "
			SELECT * 
			FROM StudentAssessmentUnit sau 
			INNER JOIN AssessmentUnit au ON au.id = sau.assessment_unit_id
			WHERE au.assessment_unit_code = '$au_code' 
			AND sau.student_id = $st_id
			AND sau.academic_year_id = $ay_id
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
		return mysql_num_rows($result);
	} else
		return FALSE;
}

//--------------------------------------------------------------------------------------------------
function add_enrolment($row, $ay_id)
// add an enrolment of a student to an assessment unit or pgr module for a given academic year
{
	$st_id = $row['DAISY ID'];
	$au_code = $row['au_code'];
	$au_id = get_au_id($au_code);
	if($st_id AND $au_id)
	{
		$query = "
			INSERT INTO StudentAssessmentUnit ( 
				student_id,
				assessment_unit_id,
				academic_year_id,
				source
			)
			VALUES (
				'$st_id',
				'$au_id',
				'$ay_id',
				'SES'
			)
			";
		$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	
//print "$query<HR>";
		return mysql_insert_id();
	} else
		return FALSE;
}

//--------------------------------------------------------------------------------------------------
function get_au_id($au_code)
// guess...
{
	$query = "SELECT id from AssessmentUnit WHERE assessment_unit_code = '$au_code'";
	$table = get_data($query);
	$record = $table[0];
	return $record['id'];
}

//----------------------------------------------------------------------------------------
function parse_recursive0(SimpleXMLElement $element, $level = 0)
{
        $indent     = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $level); // determine how much we'll indent
        
        $value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
        $attributes = $element->attributes();   // get all attributes
        $children   = $element->children();     // get all children
        
        echo "{$indent}Parsing '{$element->getName()}'...<BR />";
        if(count($children) == 0 && !empty($value)) // only show value if there is any and if there aren't any children
        {
                echo "{$indent}Value: {$element}<BR />";
        }
        
        // only show attributes if there are any
        if(count($attributes) > 0)
        {
                echo $indent."Has ".count($attributes)." attribute(s):<BR />";
                foreach($attributes as $attribute)
                {
                        echo "{$indent}- {$attribute->getName()}: {$attribute}<BR />";
                }
        }
        
        // only show children if there are any
        if(count($children))
        {
                echo $indent."<HR>Has ".count($children)." child(ren):<BR />";
                foreach($children as $child)
                {
                        parse_recursive0($child, $level+1); // recursion :)
                }
        }
        
        echo $indent.PHP_EOL; // just to make it "cleaner"
}




?>