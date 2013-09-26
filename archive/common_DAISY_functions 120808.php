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
//
//--------------------------------------------------------------------------------------------------
$version_cdf = '120727.1';

//----------------------------------------------------------------------------------------
function get_employee_id_from_webauth($webauth_code)
// 
{
	$query = "SELECT id FROM Employee WHERE webauth_code = '$webauth_code'";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);
	return $row['id'];
}

//--------------------------------------------------------------------------------------------------------------
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

//--------------------------------------------------------------------------------------------------
function is_DAISY_editor0($minimum_level)
//	checks if the logged in user has at least the minimum user level given and returns TRUE if so
{

	switch($minimum_level)
	{
		case "Editor":
			$allowed_editors = array("Editor", "Administrator", "Overseer");
			break;
			
		case "Administrator":
			$allowed_editors = array("Administrator", "Overseer");
			break;
			
		case "Overseer":
			$allowed_editors = array("Overseer");
			break;
			
		default:
			$allowed_editors = array("Super-Administrator", "Overseer");
	}
			
			
	if ($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') 

//	get the webauth_code for the logged in user when on the DAISY server
	{
	    $webauth_code = $_SERVER['REMOTE_USER'];
	} else 
//	if NOT on the DAISY live server always assume 'admn2055' (opitz) is using it
	{
	    $webauth_code = "admn2055";								// Matthias Opitz
//	    $webauth_code = "polf0044";								// Esther Byrom
	}
	$user_roles = get_DAISY_roles($webauth_code);
	$editor_status = FALSE;
	if($user_roles) foreach ($user_roles AS $user_role)
	{
		if(in_array($user_role['User Type'], $allowed_editors)) $editor_status = TRUE;
	}
	return $editor_status;
}

//--------------------------------------------------------------------------------------------------
function is_DAISY_editor($minimum_level)
//	checks if the logged in user has at least the minimum user level given and returns TRUE if so
{

	return is_in_DAISY_user_group($minimum_level);

}

//--------------------------------------------------------------------------------------------------
function user_is_in_DAISY_user_group($webauth_code, $minimum_level)
//	checks if the given user has at least the minimum user level given and returns TRUE if so
{

	switch($minimum_level)
	{
		case "Editor":
			$allowed_roles = array("Editor", "Administrator", "Super-Administrator", "Overseer");
			break;
			
		case "Administrator":
			$allowed_roles = array("Administrator", "Super-Administrator", "Overseer");
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

//--------------------------------------------------------------------------------------------------
function current_user_is_in_DAISY_user_group($minimum_level)
//	checks if the logged in user has at least the minimum user level given and returns TRUE if so
{

	if ($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') 

//	get the webauth_code for the logged in user when on the DAISY server
	{
	    $webauth_code = $_SERVER['REMOTE_USER'];
	} else 
//	if NOT on the DAISY live server always assume 'admn2055' (opitz) is using it
	{
//	    $webauth_code = "admn2055";								// Matthias Opitz
	    $webauth_code = "polf0044";								// Esther Byrom
//	    $webauth_code = "polf0025";								// Andrew Melling
//	    $webauth_code = "qehs0393";								// Prof Anderson
	}
	return user_is_in_DAISY_user_group($webauth_code, $minimum_level);
}

//--------------------------------------------------------------------------------------------------
function current_user_is_in_DAISY_user_group0($minimum_level)
//	checks if the logged in user has at least the minimum user level given and returns TRUE if so
{

	switch($minimum_level)
	{
		case "Editor":
			$allowed_roles = array("Editor", "Administrator", "Super-Administrator", "Overseer");
			break;
			
		case "Administrator":
			$allowed_roles = array("Administrator", "Super-Administrator", "Overseer");
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
			
			
	if ($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') 

//	get the webauth_code for the logged in user when on the DAISY server
	{
	    $webauth_code = $_SERVER['REMOTE_USER'];
	} else 
//	if NOT on the DAISY live server always assume 'admn2055' (opitz) is using it
	{
//	    $webauth_code = "admn2055";								// Matthias Opitz
	    $webauth_code = "polf0044";								// Esther Byrom
//	    $webauth_code = "polf0025";								// Andrew Melling
//	    $webauth_code = "qehs0393";								// Prof Anderson
	}
	$user_roles = get_DAISY_roles($webauth_code);
	$is_in_user_group = FALSE;
	if($user_roles) foreach ($user_roles AS $user_role)
	{
		if(in_array($user_role['User Type'], $allowed_roles)) $is_in_user_group = TRUE;
	}
	return $is_in_user_group;
}

//--------------------------------------------------------------------------------------------------
function get_dept_code_of_user($webauth_code)
{
	$query = "SELECT d.department_code FROM Employee e INNER JOIN Department d ON d.id = e.department_id WHERE e.webauth_code = '$webauth_code'";
	$table = get_data($query);
	$row = $table[0];
	return $row['department_code'];
}

//--------------------------------------------------------------------------------------------------
function get_dept_code_of_current_user()
{
	if ($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') 

//	get the webauth_code for the logged in user when on the DAISY server
	{
	    $webauth_code = $_SERVER['REMOTE_USER'];
	} else 
//	if NOT on the DAISY live server always assume 'admn2055' (opitz) is using it
	{
//	    $webauth_code = "admn2055";								// Matthias Opitz
//	    $webauth_code = "polf0044";								// Esther Byrom
	    $webauth_code = "polf0025";								// Andrew Melling
	}
	$query = "SELECT d.department_code FROM Employee e INNER JOIN Department d ON d.id = e.department_id WHERE e.webauth_code = '$webauth_code'";
	$table = get_data($query);
	$row = $table[0];
	return $row['department_code'];
}

//----------------------------------------------------------------------------------------
function get_leave_reduction0($e_id, $ay_id, $d_id)
//	returns the factor by which the stint obligation is reduced for an academic
//	due to academic leave for a given year and a department
{
	$query = "
SELECT
SUM(eal.leave_percentage) / 300 AS 'leave_perc'

FROM EmployeeAcademicLeave eal
INNER JOIN Term t on t.id = eal.term_id
INNER JOIN Employee e ON e.id = eal.employee_id
INNER JOIN Department d ON d.id = eal.department_id

WHERE t.academic_year_id = $ay_id
AND eal.department_id = $d_id
AND eal.employee_id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$row = mysql_fetch_assoc($result);
	return $row['leave_perc'];
}

//----------------------------------------------------------------------------------------
function get_teaching_stint0($e_id, $ay_id, $d_id)
// returns the sum of teaching stint earned by a given employee in a given academic year for a given department
{
	$query = "
SELECT 
ROUND(SUM(ti.sessions * ti.percentage / 100 * tctt.stint),2) AS 'Stint'

FROM TeachingInstance ti
INNER JOIN Term t ON t.id = ti.term_id
INNER JOIN AcademicYear ay ON ay.id = t.academic_year_id
INNER JOIN TeachingComponent tc ON tc.id = ti.teaching_component_id
INNER JOIN TeachingComponentType tct ON tct.id = tc.teaching_component_type_id
INNER JOIN TeachingComponentTypeTariff tctt ON tctt.teaching_component_type_id = tct.id AND tctt.academic_year_id = ay.id

INNER JOIN Department tc_d ON tc_d.id = tc.department_id
				
WHERE ti.employee_id = $e_id
AND t.academic_year_id = $ay_id
AND IF((SELECT COUNT(*) FROM Post p WHERE p.employee_id = $e_id AND p.person_status = 'ACTV') > 1, tc_d.id = $d_id, 1 = 1)
		";

//dprint($query);
	$table = get_data($query);
	$row = $table[0];		

//	$sum_stint = 0;
//	if($table) foreach($table AS $row)
//	{
//		$sum_stint = $sum_stint + $row['Stint'];
//	}
//	return $row['T Stint'];
	return $row['Stint'];
//	return $sum_stint;
}

//----------------------------------------------------------------------------------------
function get_supervision_stint000($e_id, $ay_id, $d_id)
// returns the sum of supervision stint earned by a given employee in a given academic year for a given department
{
	$query = "
SELECT
FORMAT(SUM(IF(sdp.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sdp.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND sdp.status = 'ENROLLED'

WHERE t.academic_year_id = $ay_id
AND sv.department_id = $d_id
AND e.id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$row = mysql_fetch_assoc($result);
	return $row['S Stint'];
}

//----------------------------------------------------------------------------------------
function get_supervision_stint00($e_id, $ay_id, $d_id)
// returns the sum of supervision stint earned by a given employee in a given academic year for a given department
{
//	First get all ACTV posts of the given employee
	$query = "
		SELECT 
		*
		FROM Post p 
		
		WHERE p.employee_id = $e_id
		AND p.person_status = 'ACTV'
		";
	$posts = get_data($query);
//	sum um the total stint obligation and put the post id into a table for reference later
	$total_stob = 0;
	$actv_post_ids = array();
	foreach($posts AS $post)
	{
		$total_stob = $total_stob + $post['dept_stint_obligation'];
		$actv_post_dept_ids[] = $post['department_id'];
	}
	$dept_stob = get_stint_obligation($e_id, $d_id);

	
//	now query for all supervision EXCEPT DPhil supervision for the post with the given department $d_id
	$query = "
SELECT

FORMAT(SUM(IF(sdp.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sdp.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code NOT LIKE '%DPhil%'
AND sv.department_id = $d_id
AND e.id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$sv_stint = $row['S Stint'];
	
	
//	now query for all supervision EXCEPT DPhil supervision for departments without a post
	$query = "
SELECT

FORMAT(SUM(IF(sdp.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sdp.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code NOT LIKE '%DPhil%' 
AND e.id = $e_id
";
	if($actv_post_dept_ids) foreach($actv_post_dept_ids AS $post_dept_id)
		$query = $query."AND sv.department_id != $post_dept_id ";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$other_sv_stint = $row['S Stint'];

//	the other stint will be accounted to the given department accordingly to the stint obligation share	
	if($total_stob) $sv_stint = $sv_stint + $other_sv_stint * $dept_stob / $total_stob;
	else $sv_stint = $sv_stint + $other_sv_stint / sizeof($actv_post_dept_ids);

//	Finally get all DPhil supervision
	$query = "
SELECT

FORMAT(SUM(IF(sdp.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sdp.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code LIKE '%DPhil%' 
AND e.id = $e_id
";
//print "<HR>$query<HR>";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$dphil_sv_stint = $row['S Stint'];

//	ALL DPhil stint will be accounted to the given department accordingly to the stint obligation share	
	if($total_stob) $sv_stint = $sv_stint + $dphil_sv_stint * $dept_stob / $total_stob;
	else $sv_stint = $sv_stint + $dphil_sv_stint / sizeof($actv_post_dept_ids);	
	
	return $sv_stint;
}

//----------------------------------------------------------------------------------------
function get_supervision_stint0($e_id, $ay_id, $d_id)
// returns the sum of supervision stint earned by a given employee in a given academic year for a given department
{
//	First get all ACTV posts of the given employee
	$query = "
		SELECT 
		*
		FROM Post p 
		
		WHERE p.employee_id = $e_id
		AND p.person_status = 'ACTV'
		";
	$posts = get_data($query);
//	sum um the total stint obligation and put the post id into a table for reference later
	$total_stob = 0;
	$actv_post_ids = array();
	if($posts) foreach($posts AS $post)
	{
		$total_stob = $total_stob + $post['dept_stint_obligation'];
		$actv_post_dept_ids[] = $post['department_id'];
	}
	$dept_stob = get_stint_obligation($e_id, $d_id);

	
//	now query for all supervision EXCEPT DPhil supervision for the post with the given department $d_id
	$query = "
SELECT

FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code NOT LIKE '%DPhil%'
AND sv.department_id = $d_id
AND e.id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$sv_stint = $row['S Stint'];
	
	
//	now query for all supervision EXCEPT DPhil supervision for departments without a post
	$query = "
SELECT

FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code NOT LIKE '%DPhil%' 
AND e.id = $e_id
";
	if($actv_post_dept_ids) foreach($actv_post_dept_ids AS $post_dept_id)
		$query = $query."AND sv.department_id != $post_dept_id ";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$other_sv_stint = $row['S Stint'];

//	the other stint will be accounted to the given department accordingly to the stint obligation share	
	if($total_stob) $sv_stint = $sv_stint + $other_sv_stint * $dept_stob / $total_stob;
	else if(sizeof($actv_post_dept_ids)) $sv_stint = $sv_stint + $other_sv_stint / sizeof($actv_post_dept_ids);

//	Finally get all DPhil supervision
	$query = "
SELECT

FORMAT(SUM(IF(sv.oxford_graduate_year < 4, sv.percentage / 300 * svtt.stint, IF(sv.oxford_graduate_year = 4, sv.percentage / 600 * svtt.stint, 0))),2) AS 'S Stint'

FROM Supervision sv
INNER JOIN Term t ON t.id = sv.term_id
INNER JOIN Employee e ON e.id = sv.employee_id

INNER JOIN SupervisionType svt ON svt.id = sv.supervision_type_id
INNER JOIN SupervisionTypeTariff svtt ON svtt.supervision_type_id = svt.id AND svtt.academic_year_id = t.academic_year_id

#INNER JOIN StudentDegreeProgramme sdp ON sdp.student_id = sv.student_id AND sdp.academic_year_id = t.academic_year_id AND IF((SELECT COUNT(*) FROM StudentDegreeProgramme sdp2 WHERE sdp2.student_id = sv.student_id AND sdp2.academic_year_id = sdp.academic_year_id AND sdp2.status ='ENROLLED' > 0),sdp.status = 'ENROLLED', (sdp.status = 'INACTIVE' OR sdp.status = 'INTERMIT' OR sdp.status = 'COMPLETED') ) 

WHERE t.academic_year_id = $ay_id
AND svt.supervision_type_code LIKE '%DPhil%' 
AND e.id = $e_id
";
//print "<HR>$query<HR>";
	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	
	$row = mysql_fetch_assoc($result);	
	$dphil_sv_stint = $row['S Stint'];

//	ALL DPhil stint will be accounted to the given department accordingly to the stint obligation share	
	if($total_stob) $sv_stint = $sv_stint + $dphil_sv_stint * $dept_stob / $total_stob;
	else if(sizeof($actv_post_dept_ids)) $sv_stint = $sv_stint + $dphil_sv_stint / sizeof($actv_post_dept_ids);	
	
	return $sv_stint;
}

//----------------------------------------------------------------------------------------
function get_stint_obligation0($e_id, $d_id)
//	returns stint obligation of a given academic with a given department
{
	$query = "
SELECT
*

FROM Post p

WHERE p.department_id = $d_id
AND p.employee_id = $e_id
		";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	$row = mysql_fetch_assoc($result);
	
	return $row['dept_stint_obligation'];
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
function academic_year_options($selection)
// shows the academic years and the corrsponding id for a selection
{
	global $debug;

	$html = '';
	$html = $html."<select name='ay_id'>";
	$html = $html."<option value=''>All Years</option>";

//	get the list of academic years

	$query = "
		SELECT * FROM AcademicYear ORDER BY startdate";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($academic_year = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$academic_year['id']."'";
		if($selection == $academic_year['id']) $html = $html." selected='selected'";
		$html = $html.">".$academic_year['label']."</option>";
	}
	$html = $html."</select>";
	return $html;
}

//----------------------------------------------------------------------------------------
function department_options($filter, $selection)
// shows the departments and the corrsponding code for a selection
{
	global $debug;

	$html = '';
	$html = $html."<select name='department_code'>";
	$html = $html."<option value=''>All Departments</option>";

//	get the list of divisions
	$query = "
		SELECT 
			* 
		FROM 
			Division 
		WHERE 
			division_code LIKE '%$filter%' 
		ORDER BY 
			division_name 
		ASC";
	if ($debug>1) print "<FONT color=#FF0000>... get_departments | query = $query</FONT><BR>";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($division = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$division['division_code']."'";
		if($selection == $division['division_code']) $html = $html." selected='selected'";
		$html = $html.">".$division['division_name']." (".$division['division_code'].")</option>";
	}

//	get the list of departments
	$query = "
		SELECT 
			* 
		FROM 
			Department 
		WHERE 
			department_code LIKE '%$filter%' 
		ORDER BY 
			department_name 
		ASC";
	if ($debug>1) print "<FONT color=#FF0000>... get_departments | query = $query</FONT><BR>";

	$result = mysql_query($query) or die ("Could not execute query: <BR><FONT COLOR = RED>$query</FONT><BR>Error = " . mysql_error());	

	while ($department = mysql_fetch_assoc($result))	
	{
		$html = $html."<option value='".$department['department_code']."'";
		if($selection == $department['department_code']) $html = $html." selected='selected'";
		$html = $html.">".$department['department_name']." (".$department['department_code'].")</option>";
	}
	$html = $html."</select>";
	return $html;
}





?>
