<?php

//==================================================================================================
//
//	Separate file with specia report functions
//
//	13-02-15	3rd version
//==================================================================================================

//----------------------------------------------------------------------------------------
function special_query_form()
//	print the form to support the query
{
	$actionpage = $_SERVER["PHP_SELF"];
	if(isset($_POST['year'])) $year = $_POST['year'];	// get year
	else $year = FALSE;
	
	print "<form action='$actionpage' method=POST>";

//	write out all $_POST attributes as hidden input
//	print write_post_vars_html();

	print "<input type='hidden' name='query_type' value='special'>";

	print "<TABLE BORDER=0>";
	print start_row(250);
		print "Academic Year:";
	print new_column(400);
	print academic_year_options();
//		print " <FONT COLOR=GREY>(no effect on REF reports)</FONT>";

	print end_row();

	if (current_user_is_in_DAISY_user_group('Super-Administrator')) print start_row(0) . "Department:" . new_column(0) . department_options("") . end_row();
	else print "<input type='hidden' name='department_code' value='".get_dept_code_of_current_user()."'>";
	
//	print start_row(0) . "Title:" . new_column(0) . "<input type='text' name = 'title_q' value='".$_POST['title_q']."' size=50>" . end_row();		
	print start_row(0) . "Report Type:" . new_column(0) . special_report_options() . end_row();		
	print start_row(0) . "Show Query:" . new_column(0) . html_checkbox('show_query') . end_row();		
	print "</TABLE>";

	print "<HR>";

//	display the buttons
	print_query_buttons();
}

//----------------------------------------------------------------------------------------
function special_report_options()
// shows the options for a publication report
{
	$query_type = $_POST['query_type'];					// get query_type
	
//	print "<input type='hidden' name='query_type' value='concordat'>";
//	$options = array("General report: summary", "General report: standard", "General report: detailed", "REF report", "REF report compact");
	$options = array();
	$options[] = array('Select a report type','');
	$options[] = array('Duplicate Names','dup_names');
//	$options[] = array('Duplicate Employee Numbers','dup_en');
	$options[] = array('AU by Owner','au_by_owner');
	$options[] = array('DP Students by AU','au_dp_students');
	$options[] = array('Dept Students by AU','au_dept_students');
	$options[] = array('Stint Balance','stint_balance');
	$options[] = array('Joint Postholders (SSD)','joint_postholders');
	$options[] = array('TC AU','TC_AU');
	$options[] = array('TI_TC','TI_TC');
	
//	$options = array(array('Select a report type',''), array('Duplicate Names','dup_names'), array('Duplicate Employee Numbers','dup_en'), array('AU by Owner','au_by_owner'), array('Stint Balance','stint_balance'));
	$html = "<select name='query_type'>";
	
	foreach($options AS $option)
	{
		if($option[1]==$query_type) $html = $html."<option SELECTED='selected' value=".$option[1].">".$option[0]."</option>";
		else $html = $html."<option value=".$option[1].">".$option[0]."</option>";
	}
	$html = $html."</select>";

	return $html;
}

?>