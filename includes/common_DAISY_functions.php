<?php
// common DAISY functions 
// to be included in other scripts
// 2012-06-14 - 1. Version
// 2012-06-29 - added get_DAISY_roles(), is_DAISY_editor()
// 2012-07-06 - added get_leave_reduction(), get_teaching_stint(), get_supervision_stint()
// 2012-07-09 - added get_dept_id()
// 2012-07-12 - added academic_year_options(), department_options()
// 2012-07-16 - cleanup
// 2012-07-27 - bugfixing for teaching stint calculation
// 2012-08-08 - unified with userlist.php
// 2012-08-14 - added employee_is_borrowed(), employee_has_post(), employee_is_affiliated()
// 2012-08-22 - added get_dept_name()
// 2012-09-28 - added reload_ay_button()
// 2012-11-21 - using webauth on daisy-stage
// 2013-01-16 - added 'Divisional-Reporter'
// 2013-03-05 - only showing academic years from 2010-11 onwards in drop down menus
// 2013-03-07 - added something_went_wrong()
//
//----------------------------------------------------------------------------------------
$version_cdf = '130307.1';

//----------------------------------------------------------------------------------------
function something_went_wrong($error_text)
{
	print_header('Error');
	print "
		<H1><FONT COLOR=#FF6600>Something went wrong!</FONT></H1>
		<P>
		<H2><I>Sorry!</I></H2>
		Something does not work as it should: <BR>
		$error_text <P>
		Please contact the <A HREF='mailto:daisy-support@socsci.ox.ac.uk'>DAISY support team</A>.
		<P>
		<HR>
		";
	exit;
}

//----------------------------------------------------------------------------------------
function show_no_mercy()
{
//	print_header('Access Denied!');
	print "
		<H1><FONT COLOR=#FF6600>Access Denied!</FONT></H1>
		<P>
		<H2><I>Sorry!</I></H2>
		You do not have sufficient rights to use this report!<P>
		Please contact the <A HREF='mailto:daisy-support@socsci.ox.ac.uk'>DAISY support team</A> if you think that is wrong.
		<P>
		<HR>
		";
}

//================================< User Functions >======================================
//----------------------------------------------------------------------------------------
function current_user_webauth()
// returns the webauth code of the currently logged in user
{
	$params = parse_ini_file('idaisy.ini');

//	if a overriding webauth code is defined in the ini file use this otherwise use the webauth of the logged in user

	if($params['webauth']) $webauth_code = $params['webauth'];
	else $webauth_code = $_SERVER['REMOTE_USER'];

	return $webauth_code;
}

//----------------------------------------------------------------------------------------
function current_user_group()
// returns the highest user group of the currently logged in user
{
//	implement a priority order for user groups 
//	the higher the number the more powerful

	$user_group_rank = array(
	"Academic" => 1,
	"Research-Staff" => 2,
	"Reader" => 3,
	"Editor" => 4,
	"Administrator" => 5,
	"Divisional-Reporter" => 6,
	"Super-Administrator" => 7,
	"Overseer" => 99);

//	get the webauth code of logged in user

//	get the webauth_code for the logged in user when on the DAISY server
	if ($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') 
	{
	    $webauth_code = $_SERVER['REMOTE_USER'];
	} else 
//	if NOT on the DAISY live server always assume 'admn2055' (opitz) is using it
	{
	    $webauth_code = "admn2055";								// Matthias Opitz
	}

	$max_user_group = 'Staff';	// This is the default value
	$group_rank = 0;
	
	$query = "
		SELECT g.name

		FROM sf_guard_user u
		INNER JOIN sf_guard_user_group ug ON ug.user_id = u.id
		INNER JOIN sf_guard_group g ON g.id = ug.group_id

		WHERE u.is_active = 1
		AND u.username = '$webauth_code'
		";
	$user_groups = get_data($query);
	if($user_groups) foreach($user_groups AS $user_group)
	{
		if($user_group_rank[$user_group['name']] > $group_rank)
		{
			$group_rank = $user_group_rank[$user_group['name']];
			$max_user_group = $user_group['name'];
		}
	}
	return $max_user_group;
}

//----------------------------------------------------------------------------------------
function get_employee_id_from_webauth($webauth_code)
// 
{
	$query = "SELECT id FROM Employee WHERE webauth_code = '$webauth_code'";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['id'];
}

//----------------------------------------------------------------------------------------
function get_fullname_from_webauth($webauth_code)
// 
{
	$query = "SELECT * FROM Employee WHERE webauth_code = '$webauth_code'";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['fullname'];
}

//----------------------------------------------------------------------------------------
function get_DAISY_roles($e_webauth_code)
//	select DAISY user role(s) for a given webauth code and return them in a table
{
	$query = "
		SELECT
		sgg.name AS 'User Type'
		
		FROM sf_guard_user sgu
		INNER JOIN sf_guard_user_group sgug ON sgug.user_id = sgu.id
		INNER JOIN sf_guard_group sgg ON  sgg.id = sgug.group_id
		
		WHERE sgu.username = '$e_webauth_code'
		AND sgu.is_active > 0
		";
		
	return get_data($query);
}

//----------------------------------------------------------------------------------------
function get_DAISY_roles_from_id($e_id)
//	select DAISY user role(s) for a given webauth code and return them in a table
{
	$query = "
		SELECT
		sgg.name AS 'User Type'
		
		FROM sf_guard_user sgu
		INNER JOIN sf_guard_user_group sgug ON sgug.user_id = sgu.id
		INNER JOIN sf_guard_group sgg ON  sgg.id = sgug.group_id
		INNER JOIN Employee e ON e.id = sgu.user_employee_id
		
		WHERE e.id = '$e_id'
		AND sgu.is_active > 0
		";
		
	return get_data($query);
}

//----------------------------------------------------------------------------------------
function is_DAISY_editor($minimum_level)
//	checks if the logged in user has at least the minimum user level given and returns TRUE if so
{

	return is_in_DAISY_user_group($minimum_level);

}

//----------------------------------------------------------------------------------------
function user_is_in_DAISY_user_group($webauth_code, $minimum_level)
//	checks if the given user has at least the minimum user level given and returns TRUE if so
{

	switch($minimum_level)
	{
		case "Editor":
			$allowed_roles = array("Editor", "Administrator", "Divisional-Reporter", "Super-Administrator", "Overseer");
			break;
			
		case "Administrator":
			$allowed_roles = array("Administrator", "Divisional-Reporter", "Super-Administrator", "Overseer");
			break;
			
		case "Divisional-Reporter":
			$allowed_roles = array("Divisional-Reporter", "Super-Administrator", "Overseer");
			break;
			
		case "Super-Administrator":
			$allowed_roles = array("Super-Administrator", "Overseer");
			break;
			
		case "Overseer":
			$allowed_roles = array("Overseer");
			break;
			
		case "Academic":
			$allowed_roles = array("Academic");
			break;
			
		default:
			$allowed_roles = array("Super-Administrator", "Overseer");
	}
			
	$user_roles = get_DAISY_roles($webauth_code);
	$is_in_user_group = FALSE;
	if($user_roles) foreach ($user_roles AS $user_role)
	{
		if(in_array($user_role['User Type'], $allowed_roles)) $is_in_user_group = TRUE;
	}
	return $is_in_user_group;
}

//----------------------------------------------------------------------------------------
function current_user_is_in_DAISY_user_group($minimum_level)
//	checks if the logged in user has at least the minimum user level given and returns TRUE if so
{
	$webauth_code = current_user_webauth();
	return user_is_in_DAISY_user_group($webauth_code, $minimum_level);
}

//----------------------------------------------------------------------------------------
function get_dept_code_of_user($webauth_code)
{
	$query = "SELECT d.department_code FROM Employee e INNER JOIN Department d ON d.id = e.department_id WHERE e.webauth_code = '$webauth_code'";
	$table = get_data($query);
	$row = $table[0];
	return $row['department_code'];
}

//----------------------------------------------------------------------------------------
function get_dept_code_of_current_user()
{
	$webauth_code = current_user_webauth();
	$query = "SELECT d.department_code FROM Employee e INNER JOIN Department d ON d.id = e.department_id WHERE e.webauth_code = '$webauth_code'";
	$table = get_data($query);
	$row = $table[0];
	return $row['department_code'];
}

//================================< Staff Functions >===================================
//----------------------------------------------------------------------------------------
function employee_has_post($e_id, $dept_id)
//	function returns TRUE if an employee has a post with the given department
{
	$query = "
		SELECT * FROM Post p		
		WHERE p.department_id = $dept_id
		AND p.employee_id = $e_id
		";
	if(get_data($query)) return TRUE;
	else return FALSE;
}

//----------------------------------------------------------------------------------------
function employee_is_borrowed($e_id, $dept_id)
//	function returns TRUE if a post of an employee is borrowed by the given department
{
	$query = "
		SELECT * FROM PostOtherDepartment pod
		INNER JOIN Post p ON p.id = pod.post_id
		
		WHERE pod.other_department_id = '$dept_id'
		AND p.employee_id = $e_id
		";
	if(get_data($query)) return TRUE;
	else return FALSE;
}

//----------------------------------------------------------------------------------------
function employee_is_affiliated($e_id, $dept_id)
//	returns TRUE if the employee has an affiliation with the department
{
	if(employee_has_post($e_id, $dept_id) OR employee_is_borrowed($e_id, $dept_id)) return TRUE;
	else return FALSE;
}

//================================< General Functions >===================================
//----------------------------------------------------------------------------------------
function get_current_academic_year_id()
// returns the ID of the current_academic_year
{
	$date = date('Y-m-d', time());
	
	$query = "
		SELECT
		*
		FROM AcademicYear
		WHERE DATE(startdate) <= '$date'

		ORDER BY startdate DESC
		LIMIT 1
	";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['id'];
}

//----------------------------------------------------------------------------------------
function get_academic_year_label($ay_id)
// returns the academic year code for a given ay ID
{
	$date = date('Y-m-d', time());
	
	$query = "
		SELECT
		*
		FROM AcademicYear
		WHERE id = $ay_id

	";
	$data = get_data($query);
	return $data[0]['label'];
}

//----------------------------------------------------------------------------------------
function get_dept_code($dept_id)
// returns the Department code for a given ID
{
	$query = "SELECT department_code FROM Department WHERE id = $dept_id";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['department_code'];
}

//----------------------------------------------------------------------------------------
function get_dept_id($department_code)
// returns the Department ID for a given code
{
	$query = "SELECT id FROM Department WHERE department_code = '$department_code'";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['id'];
}

//----------------------------------------------------------------------------------------
function get_dept_name_from_id($dept_id)
// returns the Department Name for a given ID
{
	$query = "SELECT department_name FROM Department WHERE id = $dept_id";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['department_name'];
}

//----------------------------------------------------------------------------------------
function get_dept_name_from_code($department_code)
// returns the Department Name for a given code
{
	$query = "SELECT department_name FROM Department WHERE department_code = '$department_code'";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['department_name'];
}

//==================================< Menu Functions >====================================
//----------------------------------------------------------------------------------------
function academic_year_options()
// shows the academic years and the corrsponding id for a selection
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$html = "<select name='ay_id'>";
	$html = $html."<option value='-1'>All Years</option>";

//	get the list of academic years

	$query = "
		SELECT * FROM AcademicYear ORDER BY startdate";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($academic_year = mysql_fetch_assoc($result))	
	{
		if($academic_year['id'] > 2)		// only show academic years from 2010-11 onwards
		{
			$html = $html."<option value='".$academic_year['id']."'";
			if($ay_id == $academic_year['id']) $html = $html." selected='selected'";
			$html = $html.">".$academic_year['label']."</option>";
		}
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function academic_year_options0()
// shows the academic years and the corrsponding id for a selection
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$html = "<select name='ay_id'>";
	$html = $html."<option value='-1'>All Years</option>";

//	get the list of academic years

	$query = "
		SELECT * FROM AcademicYear ORDER BY startdate";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($academic_year = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$academic_year['id']."'";
		if($ay_id == $academic_year['id']) $html = $html." selected='selected'";
		$html = $html.">".$academic_year['label']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function department_options($filter)
// shows the - filtered - departments and the corrsponding code
{
	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];

	$user_dept_code = get_dept_code_of_current_user();
	$user_div_code = substr($user_dept_code,0,2);

	$html = "<select name='department_code'>";
	if (current_user_is_in_DAISY_user_group('Overseer')) $html = $html."<option value=''>All Departments</option>";

//	first get the list of divisions
	$query = "
		SELECT 
			* 
		FROM 
			Division 
		WHERE 
			division_code LIKE '%$filter%'  ";
			if (current_user_is_in_DAISY_user_group('Super-Administrator') AND !current_user_is_in_DAISY_user_group('Overseer')) $query = $query." AND division_code = '$user_div_code'";
			$query = $query."
		ORDER BY 
			division_name 
		ASC";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($division = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$division['division_code']."'";
		if($department_code == $division['division_code']) $html = $html." selected='selected'";
		$html = $html.">".$division['division_name']." (".$division['division_code'].")</option>";
	}

	$html = $html."<option value=''>------------</option>";

//	then get the list of departments
	$query = "
		SELECT 
			* 
		FROM 
			Department 
		WHERE 
			department_code LIKE '%$filter%'  ";
			if (current_user_is_in_DAISY_user_group('Super-Administrator') AND !current_user_is_in_DAISY_user_group('Overseer')) $query = $query." AND department_code LIKE '$user_div_code%'";
			$query = $query."
		ORDER BY 
			department_name 
		ASC";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($department = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$department['department_code']."'";
		if($department_code == $department['department_code']) $html = $html." selected='selected'";
		$html = $html.">".$department['department_name']." (".$department['department_code'].")</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function year_options()
// shows the calendar years for a selection
{	
	$year = $_POST['year'];													// get from_year

	$options = array("1990", "1991", "1992", "1993", "1994", "1995", "1996", "1997", "1998", "1999", "2000", "2001", "2002", "2003", "2004", "2005", "2006", "2007", "2008", "2009", "2010", "2011", "2012", "2013", "2014");
	
	$html = "<select name='year'>";
	$html = $html."<option value=''>All Years</option>";

	foreach($options AS $option)
	{
		if($option==$year) $html = $html."<option SELECTED='selected'>$option</option>";
		else $html = $html."<option>$option</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function from_year_options()
// shows the calendar years for a selection
{	
	$from_year = $_POST['from_year'];													// get from_year	$to_year = $_POST['to_year'];													// get to_year

	$options = array("1990", "1991", "1992", "1993", "1994", "1995", "1996", "1997", "1998", "1999", "2000", "2001", "2002", "2003", "2004", "2005", "2006", "2007", "2008", "2009", "2010", "2011", "2012", "2013", "2014");
	
	$html = "<select name='from_year'>";
	$html = $html."<option value=''>Any Year</option>";

	foreach($options AS $option)
	{
		if($option==$from_year) $html = $html."<option SELECTED='selected'>$option</option>";
		else $html = $html."<option>$option</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function to_year_options()
// shows the calendar years for a selection
{	
	$to_year = $_POST['to_year'];													// get to_year

	$options = array("1990", "1991", "1992", "1993", "1994", "1995", "1996", "1997", "1998", "1999", "2000", "2001", "2002", "2003", "2004", "2005", "2006", "2007", "2008", "2009", "2010", "2011", "2012", "2013", "2014");
	
	$html = "<select name='to_year'>";
	$html = $html."<option value=''>Any Year</option>";

	foreach($options AS $option)
	{
		if($option==$to_year) $html = $html."<option SELECTED='selected'>$option</option>";
		else $html = $html."<option>$option</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function in_year_options()
// shows the calendar years for a selection
{	
	$in_year = $_POST['in_year'];													// get to_year

	$options = array("2005", "2006", "2007", "2008", "2009", "2010", "2011", "2012", "2013", "2014", "2015");
	
	$html = "<select name='in_year'>";
	$html = $html."<option value=''>Any Year</option>";

	foreach($options AS $option)
	{
		if($option==$in_year) $html = $html."<option SELECTED='selected'>$option</option>";
		else $html = $html."<option>$option</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function degree_programme_options()
// shows a drop down selector for all degree programmes available
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];
	if(!$department_code) $department_code = get_dept_code_of_current_user();

	$user_dept_code = get_dept_code_of_current_user();
	$user_div_code = substr($user_dept_code,0,2);

//	$html = "<select name='dp_title_q'>";
	$html = "<select name='dp_id'>";
	$html = $html."<option value=''>All Degree Programmes</option>";

//	get the list of academic years

	$query = "
		SELECT DISTINCT
		dp.* 
		FROM DegreeProgramme dp
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
		INNER JOIN Department d ON d.id = dpd.department_id
		WHERE  1=1 ";
	if($ay_id > 0) $query = $query . "AND dpd.academic_year_id = $ay_id ";
	if(current_user_is_in_DAISY_user_group($minimum_level)) $query = $query . "AND d.department_code LIKE '$user_div_code%' ";
	else $query = $query . "AND d.department_code LIKE '$department_code%' ";
	$query = $query . "
		 ORDER BY dp.title
	";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($degree_programme = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$degree_programme['id']."'";
//		if($dp_id == $degree_programme['id']) $html = $html." selected='selected'";
		if($_POST['dp_id'] == $degree_programme['id']) $html = $html." selected='selected'";
		$html = $html.">".$degree_programme['title']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function non_dphil_degree_programme_options()
// shows a drop down selector for all degree programmes available
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];
	if(!$department_code) $department_code = get_dept_code_of_current_user();

	$user_dept_code = get_dept_code_of_current_user();
	$user_div_code = substr($user_dept_code,0,2);

//	$html = "<select name='dp_title_q'>";
	$html = "<select name='dp_id'>";
	$html = $html."<option value=''>All Degree Programmes</option>";

//	get the list of academic years

	$query = "
		SELECT DISTINCT
		dp.* 
		FROM DegreeProgramme dp
		INNER JOIN DegreeProgrammeDepartment dpd ON dpd.degree_programme_id = dp.id
		INNER JOIN Department d ON d.id = dpd.department_id
		WHERE  1=1 
		AND dp.degree_programme_type != 'PGRAD_R'
		 ";
	if($ay_id > 0) $query = $query . "AND dpd.academic_year_id = $ay_id ";
	if(current_user_is_in_DAISY_user_group($minimum_level)) $query = $query . "AND d.department_code LIKE '$user_div_code%' ";
	else $query = $query . "AND d.department_code LIKE '$department_code%' ";
	$query = $query . "
		 ORDER BY dp.title
	";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($degree_programme = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$degree_programme['id']."'";
//		if($dp_id == $degree_programme['id']) $html = $html." selected='selected'";
		if($_POST['dp_id'] == $degree_programme['id']) $html = $html." selected='selected'";
		$html = $html.">".$degree_programme['title']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function staff_options()
// shows a drop down selector for all staff members available
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];
	if(!$department_code) $department_code = get_dept_code_of_current_user();

	$user_dept_code = get_dept_code_of_current_user();
	$user_div_code = substr($user_dept_code,0,2);

//	$html = "<select name='dp_title_q'>";
	$html = "<select name='staff_id'>";
	$html = $html."<option value=''>Any staff member</option>";

//	get the list of academic years

	$query = "
		SELECT DISTINCT
		e.* 
		FROM Employee e
		INNER JOIN Post p ON p.employee_id = e.id
		INNER JOIN Department d ON d.id = p.department_id,
		AcademicYear ay
		WHERE  1=1 
		AND ay.id = $ay_id
#		AND (p.startdate < ay.enddate OR IF(YEAR(p.enddate > 1980, p.enddate > ay.startdate, 1=1)))
		";
//	if($ay_id > 0) $query = $query . "AND dpd.academic_year_id = $ay_id ";
	$query = $query . "AND d.department_code LIKE '$department_code%' ";
	$query = $query . "
		 ORDER BY e.fullname
	";
	
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($staff = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$staff['id']."'";
//		if($dp_id == $degree_programme['id']) $html = $html." selected='selected'";
		if($_POST['staff_id'] == $staff['id']) $html = $html." selected='selected'";
		$html = $html.">".$staff['fullname']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function committee_options()
// shows a drop down selector for all committees available
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];
	if(!$department_code) $department_code = get_dept_code_of_current_user();

	$user_dept_code = get_dept_code_of_current_user();
	$user_div_code = substr($user_dept_code,0,2);

//	$html = "<select name='dp_title_q'>";
	$html = "<select name='cte_id'>";
	$html = $html."<option value=''>Any committee</option>";

//	get the list of academic years

	$query = "
		SELECT DISTINCT
		c.* 
		FROM Committee c
		INNER JOIN Department d ON d.id =c.department_id
		WHERE  1=1 
		";
	$query = $query . "AND d.department_code LIKE '$department_code%' ";
	$query = $query . "
		 ORDER BY c.name
	";
	
//d_print($query);
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($cttee = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$cttee['id']."'";
//		if($dp_id == $degree_programme['id']) $html = $html." selected='selected'";
		if($_POST['cte_id'] == $cttee['id']) $html = $html." selected='selected'";
		$html = $html.">".$cttee['name']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function office_options()
// shows a drop down selector for all offices available
{
	$ay_id = $_GET['ay_id'];													// get academic_year_id
	if(!$ay_id) $ay_id = $_POST['ay_id'];

	$department_code = $_GET['department_code'];							// get department_code
	if(!$department_code) $department_code = $_POST['department_code'];
//	if(!$department_code) $department_code = get_dept_code_of_current_user();

//	$html = "<select name='dp_title_q'>";
	$html = "<select name='off_id'>";
	$html = $html."<option value=''>Any office</option>";

//	get the list of academic years

	$query = "
		SELECT DISTINCT
		o.* 
		FROM Office o
		INNER JOIN Department d ON d.id =o.department_id
		WHERE  1=1 
		";
	$query = $query . "AND d.department_code LIKE '$department_code%' ";
	$query = $query . "
		 ORDER BY o.title
	";
	
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($office = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$office['id']."'";
//		if($dp_id == $degree_programme['id']) $html = $html." selected='selected'";
		if($_POST['off_id'] == $office['id']) $html = $html." selected='selected'";
		$html = $html.">".$office['title']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//==================================< Button Functions >====================================

//--------------------------------------------------------------------------------------------------
function reload_ay_button()
//	display a button to reload the selected academic year
{
//	print "<FORM></FORM>";			// why, oh why, do I have to put this here? it will NOT work without... VERY strange behaviour... ok, maybe this is fixed now..
	$actionpage = $_SERVER["PHP_SELF"];											// this script will call itself	
	$html = "<FORM action='$actionpage' method=POST>";
	print "<FORM action='$actionpage' method=POST>";		// again I have to print this to the screen to prevent the table cell this button is in to expand unexpectedly (and inexplainable)

	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		$html = $html. "<input type='hidden' name='$param_key' value='$param'>";
	}

	$html = $html."<TABLE BORDER=0>";
	$html = $html."<TR><TD>Academic Year:</TD><TD>".academic_year_options()."</TD>";		

	$html = $html."<TD><input type='submit' value='Reload'></TD></TR></TABLE></FORM>";
//dprint($html);
	return $html;
}

//--------------------------------------------------------------------------------------------------
function print_query_buttons00()
//	display the standard buttons below each query : go, reset and  export to excel
{
//	print "<TABLE BORDER=1 WIDTH = 535>";
	print "<TABLE BORDER=1>";
//	print start_row(250);

//	print "</TD><TD WIDTH=173>";
//	print "</TD><TD WIDTH=180>";
	print "<TR><TD BGCOLOR=LIGHTGREEN ALIGN=LEFT>";
		print "<input type='submit' value='      Go!      '>";
		print "</FORM>";
	print "</TD>";
	print "<TD BGCOLOR=PINK ALIGN=LEFT>";
//		print "<TABLE BORDER=0>";
		print_reset_button();
//		print "</TABLE>";

	print "</TD><TD>";
//		print "<TABLE BORDER=0>";
		if($_POST['query_type']) print_export_button();
//		print "</TABLE>";

	print end_row();
	print "</TABLE>";
}

//--------------------------------------------------------------------------------------------------
function print_query_buttons()
//	display the standard buttons below each query : go, reset and  export to excel
{
	$actionpage = $_SERVER["PHP_SELF"];

//	if $_POST['deb'] is set preserve this
	if(isset($_POST['deb'])) print "<input type='hidden' name='deb' value='" . $_POST['deb'] . "'>";
	
	print "<TABLE BORDER = 0>";
	print "<TR>";
	print "<TD BGCOLOR=LIGHTGREEN>";
		print "<input type='submit' value='         Go!         '>";
	print "</TD>";
	print "<TD BGCOLOR=PINK>";
		print "<input type='button' name='Cancel' value='    - Reset -    ' onclick=window.location='$actionpage'  />";
	print "</TD>";

//	Show a button leading to Central Services but for Overseers only
	if(current_user_is_in_DAISY_user_group("Super-Administrator"))
	{
		$bgcolor = '#FF6600';
		if(this_page() == 'index.php')
		{
			print "<TD BGCOLOR=$bgcolor>";
				print "<input type='button' name='Index' value='    - Central -    ' onclick=window.location='../index.php'  />";
			print "</TD>";
		}
		else 
		{
			print "<TD BGCOLOR=$bgcolor>";
				print "<input type='button' name='Index' value='    - Central -    ' onclick=window.location='index.php'  />";
			print "</TD>";
		}
	}

	print "</FORM>";	// this closes the form that each query page has opened 
	
//	now print the 'Export to Excel' button in its own FORM
	print "<FORM action='$actionpage' method=POST>";
//	write all parameters for the separate FORM needed here
	$para_keys = array_keys($_POST);
	 $i = 0;
	foreach($_POST AS $param)
	{
		$param_key = $para_keys[$i++];
		print "<input type='hidden' name='$param_key' value='$param'>";
	}
//	print "<TD BGCOLOR=#AAAAAA>";
	print "<TD>";
		if($_POST['query_type'])
//		if(TRUE)
		{
			print "<input type='hidden' name='excel_export' value=1>";	
			print "<input type='submit' value='Export to Excel'>";
		}
	print "</TD>";
	print "</FORM>";
	
	print "</TR>";
	print "</TABLE>";
 }

//--------------------------------------------------------------------------------------------------
function new_query_button()
//	display a button for a new query
{
	$actionpage = $_SERVER["PHP_SELF"];						// this script will call itself	
	print "<FORM action='$actionpage' method=POST>";

	$html = $html."<input type='submit' value='- Reset -'></FORM>";
	return $html;
}

?>
