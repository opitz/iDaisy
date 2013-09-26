<?php

//==================================================================================================
//
//	Separate file with form functions
//
//	13-08-02	
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function read_form_data($form_name)
{
//	get the table structure
	$table_struct = csv2table("forms/".$form_name.".csv", 1);
	$dataset = array();
	
//	build the query the table to display / edit
	if($table_struct)
	{
		foreach($table_struct AS $field)
		{
			$field['form'] = $form_name;	// in the resulting dataset now every field record "knows" the form it is from
			$dataset[] = $field;
		}
	}
	return $dataset;
}


//--------------------------------------------------------------------------------------------------------------
function eval_form_data($form_name)
{
	$dataset = read_form_data($form_name);

	$new_dataset = array();

	if($dataset)foreach($dataset AS $field)
	{
		if($field['type'] != 'noop' AND $field['field'] != '')	//	ignore fields marked "noop" = 'no operation' or where there is no field name given
		{
			$value = eval_field($field);	//eval each field on its own
			$field['value'] = $value;
		}
	
		$new_dataset[] = $field;
	}
	
	return $new_dataset;
}

//--------------------------------------------------------------------------------------------------------------
function find_field($form_name, $uid)
//	find and return the field record in the named dataset for the given uid (= unique identifier of field referred to)
{
	$dataset = read_form_data($form_name);
//	$dataset = eval_form_data($form_name);

	if($dataset) foreach($dataset AS $field)
	{
		if($field['uid'] == $uid)
		{
			return $field;
		}
	}
	return FALSE;
}


function work_in_progress()
//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------< WORK IN PROGRESS >-----------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
{}

//--------------------------------------------------------------------------------------------------------------
function eval_field($field)
//	evaluates a field from the dataset and returns the result
{
// detect and resolve form variables
	$rel_values = array();
	if(strlen($field['rel_uid']) > 1)
	{
		if(strstr($field['rel_uid'], '$'))	// resolve system variables
		{
//dprint("resolving system variables.");
			switch ($field['rel_uid'])
			{
				case '$ay':
					$value['val'] = $_POST['ay'];
					$rel_values[] = $value;
					break;
				case '$id':
					$value['val'] = $_POST['id'];
					$rel_values[] = $value;
					break;
				case '$department_code':
					$value['val'] = $_POST['department_code'];
					$rel_values[] = $value;
					break;
				default:
					$rel_values[] = FALSE;
			}
		} else					// resolve form variables
		{
dprint("resolving other variables.");
			$uid = $field['rel_uid'];
			$target_field = find_field($field['form'], "$uid");
			$rel_values = eval_field($target_field);
		}
	}


//	nothing external to resolve - so get the value(s)
//	if($field['relation'] == 'DISTINCT') $query = "SELECT DISTINCT id, " . $field['field'] . " FROM " . $field['table'] . " WHERE 1=1 ";
//	else $query = "SELECT id, " . $field['field'] . " FROM " . $field['table'] . " WHERE 1=1 ";

	if($field['relation'] == 'DISTINCT') $query = "SELECT DISTINCT " . $field['field'] . " FROM " . $field['table'] . " WHERE 1=1 "; // do not select an ID for each value
	else $query = "SELECT DISTINCT id, " . $field['field'] . " FROM " . $field['table'] . " WHERE 1=1 ";
	
	if(strlen($field['filter']) > 1) $query = $query . "AND " . $field['filter']." ";

	if($rel_values)	// if there are related values make sure the query result matches at least one of them
	{
		$i = 0;
		$query = $query . "AND (";
		foreach($rel_values AS $rel_value)
		{
			if(++$i > 1) $query = $query . "OR ";
			if(strlen($field['rel_field']) >  1) $query = $query .  $field['rel_field']." = '".$rel_value['val']."'";
			else $query = $query . "id = '".$rel_value['val']."'";		// if no relation field is given use the ID as default
			$query = $query . " ";
		}
		$query = $query . ") ";
	}
	else $query = $query . "AND " . "id =".$_POST['id'];	// if no relation is given select the data for the given record ID

	if(clip_by_academic_year_id($field['table'])) $query = $query . " AND academic_year_id = ".$_POST['ay_id']." "; // if there is an academic_year_id field in the given table clip the output by the selected academic year
//dprint($query);
	$res = get_data($query);

	$result = array();	// the result is an array of arrays: for each possible value the result will carry the value itself and the id of the record
	if($res) foreach($res AS $re)
	{
		$value = array('id' => $re['id'], 'val' => $re[$field['field']]);
		$result[] = $value;
	}
	return $result;
}



function end_of_work_in_progress()
//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------< END OF WORK IN PROGRESS >-------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
{}


//--------------------------------------------------------------------------------------------------------------
function resolve_variables($query)
//	resolves variables in the query text
{
//	resolve system wide variables
	$id = $_POST['id'];						// get id of a selected record (if any)
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	if(strstr($query, '__$')) $query = str_replace('__$', '$', $query);						// replace any '__' in front of a '$' to keep system variables working

	if(strstr($query, '$id')) $query = str_replace('$id', $id, $query);						// replace '$id'
	if(strstr($query, '$ay_id')) $query = str_replace('$ay_id', $ay_id, $query);					// replace '$ay_id'
	if(strstr($query, '$department_code')) $query = str_replace('$department_code', $department_code, $query);	// replace '$department_code'

	return $query;
}

//--------------------------------------------------------------------------------------------------------------
function edit_dataset($dataset)
{
//	build the query the table to display / edit
	if($dataset)
	{
		$form_name = $dataset[0]['form'];
		$html = "";
		$html = $html . "";
		
		$html = $html . "<TABLE BORDER = 1>";
	
//	add a 'back' form
		$html = $html . back_form_html();

//	start the form
		$html = $html . form_header_html($form_name);		

//------------> here we go
		foreach($dataset AS $field)
		{			
			if(strlen($field['field']) > 1) switch ($field['type'])	// ignore rows where the field name is empty
			{
			    case 'noop':
			        break;
			    case 'hide':
			        break;
			    case 'edit':
			        $html = $html . show_label_html($field);	
			        $html = $html . edit_field_html($field);	
			        break;
			    case 'pull':
			        $html = $html . show_label_html($field);	
			        $html = $html . pull_down_html($field);	
			        break;

			    default:
			        $html = $html . show_label_html($field);	
			        $html = $html . show_field_html($field);
			        break;
			}
		}
//------------> now a footer and we are done!
		$html = $html . form_footer_html();	// this closes the form 
		$html = $html . "</TABLE>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function clip_by_academic_year_id($table_name)
//	return TRUE if the given table should be clipped by academic year ID
{
//	exceptions for tables that should NOT be clipped!
	$exceptions = array();
	$exceptions[] = 'AssessmentUnit';
	
	if($exceptions) foreach($exceptions AS $exception)
	{
		if($exception == $table_name)
		{
			return FALSE;
			break;
		}
	}
	return has_academic_year_id($table_name);
}

//--------------------------------------------------------------------------------------------------------------
function clip_by_department_id($table_name)
//	return TRUE if the given table should be clipped by department id
{
//	exceptions for tables that should NOT be clipped by department id
	$exceptions = array();
	$exceptions[] = 'TeachingComponentType';
	$exceptions[] = 'SupervisionComponentType';
	
	if($exceptions) foreach($exceptions AS $exception)
	{
		if($exception == $table_name)
		{
			return FALSE;
			break;
		}
	}
	return has_department_id($table_name);
}

//--------------------------------------------------------------------------------------------------------------
function has_academic_year_id($table_name)
//	return TRUE if the given table of the current database has an 'academic_year_id' field
{
	$query = "DESCRIBE $table_name";
	$result = get_data($query);

	if($result) foreach($result AS $field)
	{
		if($field['Field'] == 'academic_year_id')
		{
			return TRUE;
			break;
		}
	}
	return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function has_department_id($table_name)
//	return TRUE if the given table of the current database has a 'department_id' field
{
	$query = "DESCRIBE $table_name";
	$result = get_data($query);
//p_table($result);
	if($result) foreach($result AS $field)
	{
		if($field['Field'] == 'department_id')
		{
			return TRUE;
			break;
		}
	}
	return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function form_header_html($form_name)
//	produce a nice HTML header for a form
{
//	resolve system wide variables
	$id = $_POST['id'];						// get id of a selected record (if any)
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$html ='';

	$html = $html . "<FORM action='".$_SERVER["PHP_SELF"]."' method=POST>";

	$html = $html . "<input type='hidden' name='action_type' value='save'>";
	$html = $html . "<input type='hidden' name='form_name' value='$form_name'>";
	$html = $html . "<input type='hidden' name='id' value='$id'>";

	$html = $html . "<input type='hidden' name='query_type' value='".$_POST['query_type']."'>";
	if(isset($_POST['query_type'])) $html = $html . "<input type='hidden' name='query_type' value='".$_POST['query_type']."'>";
	if(isset($_POST['ay_id'])) $html = $html . "<input type='hidden' name='ay_id' value='".$_POST['ay_id']."'>";
	if(isset($_POST['department_code'])) $html = $html . "<input type='hidden' name='department_code' value='".$_POST['department_code']."'>";
	if(isset($_POST['deb'])) $html = $html . "<input type='hidden' name='deb' value='".$_POST['deb']."'>";	
	$html = $html . "<input type='submit' value='         Save         '>";
	$html = $html . "<input type='button' name='Cancel' value='    - Cancel -    ' onclick=window.location='index.php'  />";
	$html = $html . "<HR>";
	
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function form_footer_html()
//	return the HTML code showing the plain value of the given field
{
	$html ='';

		$html = $html . "</FORM>";	// this closes the form 
	
	return $html;
}			
			
//--------------------------------------------------------------------------------------------------------------
function save_dataset($dataset, $id)
//	save the record fields submitted via $_POST to the table given. (If an ID is given update the record with that ID otherwise add a new record)
{
	if($dataset)
	{
		foreach($dataset AS $field)	// go through all fields in the dataset
		{
			if(strlen($field['field']) > 1) switch ($field['type'])
			{
			    case 'noop':
			        break;
			    case 'hide':
			        update_field($field);	
			        break;
			    case 'edit':
			        update_field($field);	
			        break;
			    case 'pull':
			        update_field($field);
			        break;

			    default:	// do nothing
			        break;
			}
		}
		feedback("Dataset updated");
	}
}

//--------------------------------------------------------------------------------------------------------------
function update_field($field)
//	updates the field with the matching value of the $_POST variable
{

// 	check if the table has an 'academic_year_id'
	$values = $field['value'];
	
	if($values) foreach($values AS $value)
	{
		$post_field = $field['field']."|".$value['id'];
		if(isset($_POST[$post_field]))	// if a data field with the same name was saved update the data accordingly
		{
//dprint($field['field']);
			$query = "UPDATE ".$field['table']." SET ".$field['field']." = '".$_POST[$post_field]."', updated_at = NOW() WHERE 1=1 ";
			if($field['relation'] != 'DISTINCT') $query = $query . "AND id = ".$value['id']." ";

//	check if the table has an academic_year_id and if so limit the update to the currently selected academic year ID
			if(has_academic_year_id($field['table'])) $query = $query . "AND " . "academic_year_id = " . $_POST['ay_id'] . " ";

			if(strlen($field['rel_uid']) > 1)	// if there is a system variable or other field to filter the updated records by
			{
//dprint("haha!");
				if(strlen($field['rel_field']) >  1) $query = $query . "AND " . $field['rel_field']." = '".$field['rel_uid']."'"; 
				else $query = $query . "AND id = '".$field['rel_uid']."'";		// if no relation field is given use the ID as default
			}
//			else $query = $query . "AND " . 'id = $id';
			$query = resolve_variables($query);
//			$query = resolve_form_variables($query, $field['form']);

			$result = mysql_query($query) or die ("Could not execute query: " . d_print($query) . "Error = " . mysql_error());
		}
		else $result = FALSE;
	}
	
	return $result;
}

//--------------------------------------------------------------------------------------------------------------
function back_form_html()
//	return the HTML code for a proper "Back" button preserving basic query parameters
{
	$html ='';

	$html = $html . "<FORM action='".$_SERVER["PHP_SELF"]."' method=POST>";
	if(isset($_POST['query_type'])) $html = $html . "<input type='hidden' name='query_type' value='".$_POST['query_type']."'>";
	if(isset($_POST['ay_id'])) $html = $html . "<input type='hidden' name='ay_id' value='".$_POST['ay_id']."'>";
	if(isset($_POST['department_code'])) $html = $html . "<input type='hidden' name='department_code' value='".$_POST['department_code']."'>";
	if(isset($_POST['deb'])) $html = $html . "<input type='hidden' name='deb' value='".$_POST['deb']."'>";
	$html = $html . "<input type='submit' value='         Back         '>";
	$html = $html . "</FORM>";	// this closes the form 
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function show_label_html($field)
//	return the HTML code showing label of a field
{
	$html ='';

	$html = $html . "<FONT COLOR=BLUE><B>".$field['label'].":</B> </FONT> ";
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function show_field_html($field)
//	return the HTML code showing the plain value of the given field in an editable form
{
	$values = $field['value'];
	$html ='';

	if($values) foreach($values AS $value)
	{
		$html = $html . "<B>".$value['val']."</B><P>";	
	}
	else $html = $html . "<P>";
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function edit_field_html($field)
//	return the HTML code showing the plain value of the given field in an editable form
{
	$values = $field['value'];
	$html ='';

	if($values) foreach($values AS $value)
	{
		$html = $html . "<input type='text' name = '".$field['field']."|".$value['id']."' value='".$value['val']."' size=50><P>";
	}
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function edit_field_html0($field)
//	return the HTML code showing the plain value of the given field in an editable form
{
	$values = $field['value'];
	$html ='';

	if($values) foreach($values AS $value)
	{
		$html = $html . "<input type='text' name = '".$field['field']."' value='".$value['val']."' size=50>huhu!<P>";
	}
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function pull_down_html($field)
//	return the HTML code for a pulldown relation of a related table to the value of the given field
//	a table related as a pull down menu to another table "knows" which table it is related to
//	the other table does not (need to) know that there is another table related to it
{
	$values = $field['value'];
	$other_table = $field['other_table'];
	$other_field = $field['other_field'];
	$show_field = $field['show_field'];
	
//	get the value options from the other table to populate the pull down menu				
	if(clip_by_department_id($field['other_table'])) $query = "SELECT aaa.* FROM ".$field['other_table']." aaa INNER JOIN Department d ON d.id = aaa.department_id WHERE d.department_code LIKE '".$_POST['department_code']."'";
	else $query = "SELECT * FROM ".$field['other_table']." WHERE 1=1 ";

	if(clip_by_academic_year_id($field['other_table'])) $query = $query . "AND academic_year_id = ".$_POST['ay_id']." "; // if there is an academic_year_id field in the given table clip the output by the selected academic year
	$query = $query . " ORDER BY " . $field['show_field'];

	$options = get_data($query);
	if(!$options)	//	ERROR - no options!
	{
		dprint("ERROR: no options found for pull down field <B>".$field['field']."</B>!");
		break;
	}

//	now build the html for a pull down menu for each value found for the given field and relate it to the proper option of the pull down menu
	$html ='';
	$i=0;
	IF($values) foreach($values AS $value)
	{
		$field_name = $field['field']."|".$value['id'];

		$html = $html . "<select name='$field_name'>";

		foreach($options AS $option)
		{
			if($option[$field['other_field']]==$value['val'])
			{
				$html = $html."<option SELECTED='selected' value=".$option[$field['other_field']].">".$option[$field['show_field']];
			}
			else $html = $html."<option value=".$option[$field['other_field']].">".$option[$field['show_field']];
			$html = $html."</option>";
		}
		$html = $html."</select><P>";
		$i++;
	}

	return $html;
}


//--------------------------------------------------------------------------------------------------------------
function pull_down_html0($field)
//	return the HTML code for a pulldown relation of a related table to the value of the given field
//	a table related as a pull down menu to another table "knows" which table it is related to
//	the other table does not (need to) know that there is another table related to it
{
	$values = $field['value'];
//print_r($values); print "<HR>";

//	get the value options to populate the pull down menu				
	if(clip_by_department_id($field['table'])) $query = "SELECT aaa.* FROM ".$field['table']." aaa INNER JOIN Department d ON d.id = aaa.department_id WHERE d.department_code LIKE '".$_POST['department_code']."'";
	else $query = "SELECT * FROM ".$field['table']." WHERE 1=1 ";

	if(clip_by_academic_year_id($field['table'])) $query = $query . "AND academic_year_id = ".$_POST['ay_id']." "; // if there is an academic_year_id field in the given table clip the output by the selected academic year

	$query = $query . " ORDER BY " . $field['field'];

	$options = get_data($query);
	if(!$options)	//	ERROR - no options!
	{
		dprint("ERROR: no options found for pull down field <B>".$field['field']."</B>!");
		break;
	}

//	get the UID - this links to the - normally hidden - data field in the form where to find the value(s)
	$uid = $field['rel_uid'];
	if(!$uid)	//	ERROR - no UID!	
	{
		dprint("ERROR: no 'rel_uid' set found for pull down field <B>".$field['field']."</B>!");
		break;
	}
	
//	find the field the saved value from the pull down menu is going to
	$target_field = find_field($field['form'], "$uid");
	if(strlen($field['rel_field']) < 2) $field['rel_field'] = 'id';		//	if no related field is given use the ID as default

//	get the target field
	$target_field = $field['form'];


//	now build the html for a pull down menu for each value found for the given field and relate it to the proper option of the pull down menu
	$html ='';
	$i=0;
//dprint(count($values));
	if($values) foreach($values AS $value)
	{
		$target_values = eval_field($target_field);
		$target_value = $target_values[$i];		// assuming there is only one value for now
		
		$field_name = $target_field['field']."|".$target_value['id'];

//		$html = $html . "<select name='".$target_field['field']."|".$value['id']."'>";
//dprint($field_name." = ".$value['val']);
		$html = $html . "<select name='$field_name'>";

		foreach($options AS $option)
		{
			if($option[$field['field']]==$value['val'])
			{
				$html = $html."<option SELECTED='selected' value=".$option[$field['rel_field']].">".$option[$field['field']];
			}
			else $html = $html."<option value=".$option[$field['rel_field']].">".$option[$field['field']];
			$html = $html."</option>";
		}
		$html = $html."</select><P>";
		$i++;
	}
	return $html;
}


//--------------------------------------------------------------------------------------------------------------
function feedback($text)
//	return some text as feedback to the user
{
	dprint($text);
}




?>