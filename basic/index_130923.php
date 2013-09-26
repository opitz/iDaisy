<?php

//==================================================================================================
//
//	basic reports main index page
//	Last changes: Matthias Opitz --- 2013-09-23
//
//==================================================================================================
$version = "130923.1";					// starting again

include 'include_list.php';

//	open the database and start a timer
$conn = open_daisydb();					// open DAISY database
$starttime = start_timer(); 


if(isset($_GET['id'])) $_POST['id'] = $_GET['id'];
if(isset($_GET['ay'])) $_POST['ay'] = $_GET['ay'];
if(isset($_GET['department_code'])) $_POST['department_code'] = $_GET['department_code'];
if(isset($_GET['query_type'])) $_POST['query_type'] = $_GET['query_type'];


//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];

//	if no academic year is selected in the first place propose the current academic year as a selection
if(!isset($_POST['ay_id'])) $_POST['ay_id'] = get_current_academic_year_id();

if(isset($_POST['query_type'])) $query_type = $_POST['query_type'];
else $query_type = '';

//	debug
if(isset($_GET['deb'])) $_POST['deb'] = $_GET['deb'];
//if(isset($_POST['deb']))
{
	dget();
	dpost();
}

$header = "Teaching Data";

//	see if there is the request for a single record and if so display it
if ($_POST['action_type'] == 'new')
{
//dprint("new!");
	
//	get the correct form for the selected report
	switch ($query_type)
	{
		case 'emp':
			$form_name = 'Employee';
			break;
		case 'post':
			$form_name = 'Post';
			break;
		case 'au':
			$form_name = 'AssessmentUnit';
			break;
		case 'tc':
			$form_name = 'TeachingComponent';
			break;
		case 'ti':
			$form_name = 'TeachingInstance';
			break;
		case 'teaching':
			$form_name = 'TeachingData';
			break;
		case 'ti':
			$form_name = 'TeachingInstance';
			break;
		case 'ses':
			$form_name = 'SESdata';
			break;
		default:
			$form_name = FALSE;
	}


	$dataset = new_form_data($form_name);
	$record_html = edit_dataset($dataset);
//	p_table($dataset);
//	$form_name = $dataset[0]['form'];
	print_header($form_name . " - New Record");
}

elseif ($_GET['query_type'] == 'del' AND isset($_GET['id']))
{
//dprint("delete!");
	
	$_POST['query_type'] = 'emp';
	$rxecord_html = rdelete_table_data($_GET['form'], $_GET['id']);
//	$record_html = delete_form_data($_GET['form'], $_GET['id']);
//	p_table($dataset);
//	$form_name = $dataset[0]['form'];
	alert("Record has been deleted!");
	print_header($_GET['form'] . " - Delete Record");
}

elseif (isset($_GET['form']) AND isset($_GET['id']))	// evaluate and edit the data from the given form using the given record ID
{
	$dataset = eval_form_data($_GET['form'], $_GET['id']);
	$record_html = edit_dataset($dataset);
//	p_table($dataset);
	$form_name = $dataset[0]['form'];
	print_header($form_name . " - Edit Record");
}

//	save a record
elseif ($_POST['action_type'] == 'save')
{
//	if($_POST['id'] > 0) $dataset = eval_form_data($_POST['form_name'], $_POST['id']);
	if($_POST['id'] > 0) $dataset = eval_form_data($_POST['form_name']);
	else $dataset = new_form_data($_POST['form_name'], $_POST['id']);
	save_dataset($dataset, $_POST['id']);
	if($_POST['id'] > 0) $dataset = eval_form_data($_POST['form_name']);
//	else $dataset = new_form_data($_POST['form_name'], $_POST['id']);
	$record_html = edit_dataset($dataset);
	$form_name = $dataset[0]['form'];
	print_header($form_name . " - Record Saved");
}



//	no record to display - let's find out what basic query to show

elseif($query_type == 'emp') 
{
	$header = "Employees";
	$table = employees();
} 
elseif($query_type == 'Post') 
{
	$header = "Posts";
	$table = posts();
} 
elseif($query_type == 'au') 
{
	$header = "Assessment Units";
	$table = assessment_units();
} 
elseif($query_type == 'tc') 
{
	$header = "Teaching Components";
	$table = teaching_components();
} 
elseif($query_type == 'ti') 
{
	$header = "Teaching Instances";
	$table = teaching_instances();
} 


elseif($query_type == 'teaching') 
{
	$header = "Teaching Data";
	$table = teaching_data();
} 

elseif($query_type == 'ses') 
{
	$header = "SES Data";
	$table = ses_data();
} 



elseif($query_type == 'st') 
{
	$header = "Students";
	$table = students();
} 
elseif($query_type == 'tst') 
{
	$header = "Teaching Stint Tariff";
	$table = teaching_stint_tariff();
} 
elseif($query_type == 'svst') 
{
	$header = "Supervision Stint Tariff";
	$table = supervision_stint_tariff();
} 

if($table)  	
{
	if(isset($_POST['excel_export']))
	{
		export2csv($table, $header."  ");
	} else
	{
		print "<FONT FACE = 'Arial'>";
		print_header($header);
		show_basic_query();
	
		p_table($table);

		print "</FONT>";
	}
}
elseif($record_html)
{
//	print_header("Single Record");
	print $record_html;
}
else	// not nuch else to do so display the basic query to start with
{
	print_header($header);
	show_basic_query();
}

//	stop the timer
$totaltime = stop_timer($starttime);
show_footer($version, $totaltime);
mysql_close($conn);												// close database connection $conn

	

//========================================================================================
//				Functions
//========================================================================================


?>
