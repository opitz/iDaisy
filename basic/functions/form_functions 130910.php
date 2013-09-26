<?php

//==================================================================================================
//
//	Separate file with form functions
//
//	13-09-06	
//==================================================================================================

//--------------------------------------------------------------------------------------------------------------
function add_form_links($table, $form_name, $id_column_name, $link_column_name)
//	add a link to a single record to the given link column entry of the listed table using the ID column name provided
{
	if (!current_user_is_in_DAISY_user_group('Overseer')) return $table;

	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$query_type = $_POST['query_type'];				// get query_type

	$new_table = array();
	if($table) foreach($table AS $row)
	{
		if($row[$id_column_name] > 0) $row[$link_column_name] = "<A HREF=index.php?ay_id=$ay_id&department_code=$department_code&query_type=$query_type&form=".$form_name."&id=".$row[$id_column_name].">" . $row[$link_column_name] . "</A>";
		$new_table[] = $row;
	}
	
	return $new_table;
}

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
function new_form_data($form_name)
{
	$dataset = read_form_data($form_name);

	$new_dataset = array();

	if($dataset)foreach($dataset AS $field)
	{
		if($field['type'] != 'noop' AND $field['field'] != '')	//	ignore fields marked "noop" = 'no operation' or where there is no field name given
		{
			$value = new_field($field);	//eval each field on its own
			$field['value'] = $value;
		}
	
		$new_dataset[] = $field;
	}
	
	return $new_dataset;
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

//--------------------------------------------------------------------------------------------------------------
function new_field($field)
//	returns a field from the new dataset with default value
{

//	$result = array();	// the result is an array of arrays: for each possible value the result will carry the value itself and the id of the record
	$value = array('id' => 0, 'val' => '');
	$result[] = $value;
	return $result;
}



//--------------------------------------------------------------------------------------------------------------
function new_field0($field)
//	returns a field from the new dataset with default value
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
		} 
	}


//	nothing external to resolve - so insert the default value(s)


//	$result = array();	// the result is an array of arrays: for each possible value the result will carry the value itself and the id of the record
	$value = array('id' => 0, 'val' => '');
	$result[] = $value;
	return $result;
}



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
		if($re['id'] < 1) $re['id'] = -1;
		$value = array('id' => $re['id'], 'val' => $re[$field['field']]);
		$result[] = $value;
	}
	return $result;
}



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
		
		$html = $html . "<TABLE BORDER = 0>";
	
//	add a 'back' form
		$html = $html . back_form_html();

//	start the form
		$html = $html . form_header_html($form_name);		
//if($_POST['action_type'] == 'new') p_table($dataset);
//------------> here we go
		foreach($dataset AS $field)
		{			
			$html = $html . "<TR>";
			if(strlen($field['field']) > 1) switch ($field['type'])	// ignore rows where the field name is empty
			{
			    case 'noop':	// will be hidden
			        break;
			    case 'hide':	// will be hidden
			        break;
			    case 'edit':	// the classic way of typing data into a field
			        $html = $html . "<TD VALIGN=TOP>" . show_label_html($field) . "</TD>";	
			        $html = $html . "<TD>" . edit_field_html($field) . "</TD>";	
			        break;
			    case 'pull':	// use a pull down menu to enter the data
			        $html = $html . "<TD VALIGN=TOP>" . show_label_html($field) . "</TD>";	
			        $html = $html . "<TD>" . pull_down_html($field) . "</TD>";	
			        break;

			    default:		// show - no edit - the field value
			        $html = $html . "<TD VALIGN=TOP>" . show_label_html($field) . "</TD>";	
			        $html = $html . "<TD>" . show_field_html($field) . "</TD>";
			        break;
			}
			else $html = $html . "<TD>&nbsp;</TD>";	// display a blank line when there is a line without a field name in the form
			$html = $html . "</TR>";
		}
//------------> now a footer and we are done!
		$html = $html . form_footer_html();	// this closes the form 
		$html = $html . "</TABLE>";
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function save_dataset($dataset, $id)
//	save the record fields submitted via $_POST to the table given. (If an ID is given update the record with that ID otherwise add a new record)
{
//dprint("--->".$id);
	if($dataset)
	{
		if($id >= 0)
		{
			foreach($dataset AS $field)	// go through all fields in the dataset
			{
				if(strlen($field['field']) > 1) switch ($field['type'])
				{
				    case 'noop':
				        break;
				    case 'hide':
				        save_field($field);	
				        break;
				    case 'edit':
				        save_field($field);	
				        break;
				    case 'pull':
				        save_field($field);
				        break;

				    default:	// do nothing
				        break;
				}
			}
			feedback("Dataset updated");
		} else
		{
//			print_r($dataset);
			feedback("Here we will add a new dataset");
			foreach($dataset AS $field)	// go through all fields in the dataset
			{
				p_table(get_constraints($field['table']));
			}
		}
	}
}

//--------------------------------------------------------------------------------------------------------------
function save_dataset0($dataset, $id)
//	save the record fields submitted via $_POST to the table given. (If an ID is given update the record with that ID otherwise add a new record)
{
//dprint("--->".$id);
	if($dataset)
	{
		foreach($dataset AS $field)	// go through all fields in the dataset
		{
			if(strlen($field['field']) > 1) switch ($field['type'])
			{
			    case 'noop':
			        break;
			    case 'hide':
			        save_field($field);	
			        break;
			    case 'edit':
			        save_field($field);	
			        break;
			    case 'pull':
			        save_field($field);
			        break;

			    default:	// do nothing
			        break;
			}
		}
		feedback("Dataset updated");
	}
}

//--------------------------------------------------------------------------------------------------------------
function save_field($field)
//	saves the field with the matching value of the $_POST variable
{
// 	check if the table has an 'academic_year_id'
	$values = $field['value'];

	if($values) foreach($values AS $value)
	{
		if(!$_POST['id']) add_field($field, $value);
		elseif(strlen($field['rel_uid']) > 1 AND $value['id'] === 0) add_related_field($field, $value);
		else $result = update_field($field, $value);
	}
	else $result = FALSE;
	
	return $result;
}

//--------------------------------------------------------------------------------------------------------------
function save_field0($field)
//	saves the field with the matching value of the $_POST variable
{

// 	check if the table has an 'academic_year_id'
	$values = $field['value'];
	
	if($values) foreach($values AS $value)
	{
		$post_field = $field['field']."|".$value['id'];
		if(isset($_POST[$post_field]))	// if a data field with the same name was saved with the form update the data accordingly
		{
//dprint($field['field']);
			$query = "UPDATE ".$field['table']." SET ".$field['field']." = '".$_POST[$post_field]."', updated_at = NOW() WHERE 1=1 ";
			if($field['relation'] != 'DISTINCT') $query = $query . "AND id = ".$value['id']." ";

//	check if the table has an academic_year_id and if so limit the update to the currently selected academic year ID
			if(clip_by_academic_year_id($field['table'])) $query = $query . "AND " . "academic_year_id = " . $_POST['ay_id'] . " ";

			if(strlen($field['rel_uid']) > 1)
			{
				if(strlen($field['rel_field']) >  1) $query = $query . "AND " . $field['rel_field']." = '".$field['rel_uid']."'";
				else $query = $query . "AND id = '".$field['rel_uid']."'";		// if no relation field is given use the ID as default
			}
//			else $query = $query . "AND " . 'id = $id';
//dprint($query);
			$query = resolve_variables($query);
//			$query = resolve_form_variables($query, $field['form']);

			$result = mysql_query($query) or die ("Could not execute query: " . d_print($query) . "Error = " . mysql_error());
		}
		else $result = FALSE;
	}
	
	return $result;
}

//--------------------------------------------------------------------------------------------------------------
function update_field($field, $value)
//	updates the field with the matching value of the $_POST variable
{
	$post_field = $field['field']."|".$value['id'];
	if(isset($_POST[$post_field]))	// if a data field with the same name was saved with the form update the data accordingly
	{
		$query = "UPDATE ".$field['table']." SET ".$field['field']." = '".$_POST[$post_field]."', updated_at = NOW() WHERE 1=1 ";
//		if($field['relation'] != 'DISTINCT') $query = $query . "AND id = ".$value['id']." ";
		if($field['relation'] != 'DISTINCT')
		{
			if($value['id'] > 0) $query = $query . "AND id = ".$value['id']." ";
			else $query = $query . "AND id = ".$_POST['id']." ";
		}

//	check if the table has an academic_year_id and if so limit the update to the currently selected academic year ID
		if(clip_by_academic_year_id($field['table'])) $query = $query . "AND " . "academic_year_id = " . $_POST['ay_id'] . " ";

		if(strlen($field['rel_uid']) > 1)
		{
			if(strlen($field['rel_field']) >  1) $query = $query . "AND " . $field['rel_field']." = '".$field['rel_uid']."'";
			else $query = $query . "AND id = '".$field['rel_uid']."'";		// if no relation field is given use the 'id' field as default
		}
		$query = resolve_variables($query);
dprint($query);
		$result = mysql_query($query) or die ("Could not execute query: " . d_print($query) . "Error = " . mysql_error());
		if($result) return $value['id'];
	}
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------------------
function add_field($field, $value)
//	adds the field with the matching value of the $_POST variable
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$post_field = $field['field']."|".$value['id'];
	if(isset($_POST[$post_field]))	// if a data field with the same name was saved with the form update the data accordingly
	{
		$query = "
			INSERT INTO ".$field['table']."
			( 
				".$field['field'].", ";
		if(has_academic_year_id($field['table'])) $query = $query . "academic_year_id, ";
		if(has_department_id($field['table'])) $query = $query . "department_id, ";
		if(1==1) $query = $query . "";
		
		$query = $query . "
				created_at
			)
			VALUES
			(
				'".$_POST[$post_field]."', ";
		if(has_academic_year_id($field['table'])) $query = $query . "$ay_id, ";
		if(has_department_id($field['table'])) $query = $query . "$dept_id, ";
		$query = $query . "
				NOW()
			)
		";
dprint($query);
		$result = mysql_query($query) or die ("Could not execute query: " . d_print($query) . "Error = " . mysql_error());
	}
//	$_POST['id'] = 7320;
	
	if($result) $_POST['id'] =  mysql_insert_id();
	
	return $result;
}

//--------------------------------------------------------------------------------------------------------------
function add_related_field($field, $value)
//	adds the field with the matching value of the $_POST variable
{
	$ay_id = $_POST['ay_id'];					// get academic_year_id
	$department_code = $_POST['department_code'];			// get department code
	$dept_id = get_dept_id($department_code);			// get department id

	$post_field = $field['field']."|".$value['id'];
	if(isset($_POST[$post_field]))	// if a data field with the same name was saved with the form update the data accordingly
	{
		$query = "
			INSERT INTO ".$field['table']."
			( 
				".$field['field'].",
				".$field['rel_field'].",
		";
		if(has_academic_year_id($field['table'])) $query = $query . "academic_year_id, ";
		if(has_department_id($field['table'])) $query = $query . "department_id, ";
		if(1==1) $query = $query . "";
		
		$query = $query . "
				created_at
			)
			VALUES
			(
				'".$_POST[$post_field]."',
				'".$field['rel_uid']."',
		";
		if(has_academic_year_id($field['table'])) $query = $query . "$ay_id, ";
		if(has_department_id($field['table'])) $query = $query . "$dept_id, ";
		$query = $query . "
				NOW()
			)
		";
dprint($field['form']);
		$query = resolve_variables($query);
dprint($query);
		$result = mysql_query($query) or die ("Could not execute query: " . d_print($query) . "Error = " . mysql_error());
	}
//	$_POST['id'] = 7320;
	
	if($result) $_POST['id'] =  mysql_insert_id();
	
	return $result;
}

//==============================================< HTML >========================================================
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
function new_form_html()
//	return the HTML code for a proper "New" button preserving basic query parameters
{
	$html ='';

	$html = $html . "<FORM action='".$_SERVER["PHP_SELF"]."' method=POST>";
	$html = $html . "<input type='hidden' name='action_type' value='new'>";
	if(isset($_POST['query_type'])) $html = $html . "<input type='hidden' name='query_type' value='".$_POST['query_type']."'>";
	if(isset($_POST['ay_id'])) $html = $html . "<input type='hidden' name='ay_id' value='".$_POST['ay_id']."'>";
	if(isset($_POST['department_code'])) $html = $html . "<input type='hidden' name='department_code' value='".$_POST['department_code']."'>";
	if(isset($_POST['deb'])) $html = $html . "<input type='hidden' name='deb' value='".$_POST['deb']."'>";
	$html = $html . "<input type='submit' value='         New         '>";
	$html = $html . "</FORM>";	// this closes the form 
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function show_label_html($field)
//	return the HTML code showing label of a field
{
	$html ='';

	$html = $html . "<FONT COLOR=#555588><B>".$field['label'].":</B> </FONT> ";
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function show_field_html($field)
//	return the HTML code showing the plain value of the given field in a non-editable form
{
	$values = $field['value'];
	$html ='';

	if($values) foreach($values AS $value)
	{
		if(strlen($field['other_table'])>1 AND $field['type'] != 'bare')
		{
//	get the value from the other table
			$query = "SELECT * FROM ".$field['other_table']." WHERE ".$field['other_field']." = ".$value['val']." ";
			$result = get_data($query);
			$res = $result[0];
			
			$html = $html . "<B>".$res[$field['show_field']]."</B><P>";
		}
		else $html = $html . "<B>".$value['val']."</B><P>";	
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

	if(count($values) < 0) $values = new_field($field);
	
	foreach($values AS $value)
	{
//var_export($value);
		$val_len = $field['size'];
		if($field['size'] > 0) $val_len = $field['size'];
		else $val_len = strlen($value['val']) + 5;
//dprint($val_len);
		$html = $html . "<input type='text' name = '".$field['field']."|".$value['id']."' value='".$value['val']."' size=$val_len><P>";
	}
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function edit_field_html0($field)
//	return the HTML code showing the plain value of the given field in an editable form
{
	$values = $field['value'];
	$html ='';

	if(count($values) > 0) foreach($values AS $value)
	{
//var_export($value);
		$val_len = $field['size'];
		if($field['size'] > 0) $val_len = $field['size'];
		else $val_len = strlen($value['val']) + 5;
//dprint($val_len);
		$html = $html . "<input type='text' name = '".$field['field']."|".$value['id']."' value='".$value['val']."' size=$val_len><P>";
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
	if(clip_by_department_id($field['other_table'])) $query = "SELECT aaa.* FROM ".$field['other_table']." aaa INNER JOIN Department d ON d.id = aaa.department_id WHERE d.department_code LIKE '".$_POST['department_code']."%'";
	else $query = "SELECT * FROM ".$field['other_table']." WHERE 1=1 ";

	if(clip_by_academic_year_id($field['other_table'])) $query = $query . "AND academic_year_id = ".$_POST['ay_id']." "; // if there is an academic_year_id field in the given table clip the output by the selected academic year
	$query = $query . " ORDER BY " . $field['show_field'];
	$options = get_data($query);
	if(!$options)	//	ERROR - no options!
	{
		dprint("ERROR: no options found for pull down field <B>".$field['field']."</B>!");
//		dprint($query);
		die;
	}

//	now build the html for a pull down menu for each value found for the given field and relate it to the proper option of the pull down menu
	$html ='';
	$i=0;
	IF($values) foreach($values AS $value)
	{
		$field_name = $field['field']."|".$value['id'];

		$html = $html . "<select name='$field_name'>";

//	no value
		if($option[$field['other_field']]=='')
		{
			$html = $html."<option SELECTED='selected' value=''>"."Please select a value";
		}
		else $html = $html."<option value=".$option[$field['other_field']].">".$option[$field['show_field']];
		$html = $html."</option>";

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
	$html = $html . "<input type='button' name='Cancel' value='    - Reset -    ' onclick=window.location='index.php'  />";
	$html = $html . "<HR>";
//$html = $html . "<TABLE BORDER = 2>";	// this opens a table - to be closed in the footer
	
	
	return $html;
}

//--------------------------------------------------------------------------------------------------------------
function form_footer_html()
//	return the HTML code showing the plain value of the given field
{
	$html ='';

//		$html = $html . "</TABLE>";	// this closes the table 
		$html = $html . "</FORM>";	// this closes the form 
	
	return $html;
}			
			
//==============================================< HELPERS>======================================================
//--------------------------------------------------------------------------------------------------------------
function feedback($text)
//	return some text as feedback to the user
{
	dprint($text);
}

//--------------------------------------------------------------------------------------------------------------
function clip_by_academic_year_id($table_name)
//	return TRUE if the given table should be clipped by academic year ID
{
//	exceptions for tables that should NOT be clipped!
	$exceptions = array();
	$exceptions[] = 'AssessmentUnit';
	$exceptions[] = 'TeachingInstance';
	
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
function get_constraints($table_name)
//	returns a list of all constraints for a given table name
{
	$params = parse_ini_file('../includes/idaisy.ini');
	$db_name  = $params['dbname'];
	$db_host = $params['dbhost'];

	$query = "
		SELECT
			concat(table_name, '.', column_name) as 'foreign key',  
			concat(referenced_table_name, '.', referenced_column_name) as 'references'
		FROM
			information_schema.key_column_usage
		WHERE
			referenced_table_name is not null
			and table_schema = '$db_name' 
			and table_name = '$table_name';
	";
dprint($query);
	return get_data($query);
}



?>