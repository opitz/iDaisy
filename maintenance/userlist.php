<?php
// simple function to provide user access
// to be included in other scripts
// Last modified by M.Opitz 2012-04-20
//
//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
function is_user()
// returns true if the webauth code is in the list of users
{
//	get the webauth code of logged in user

	if ($_SERVER['HTTP_HOST'] == 'daisy.socsci.ox.ac.uk') 

//	get the webauth_code for the logged in user when on the DAISY server
	{
	    $webauth_code = $_SERVER['REMOTE_USER'];
	} else 
//	if NOT on the DAISY live server always assume 'admn2055' (opitz) is using it
	{
	    $webauth_code = "admn2055";								// Matthias Opitz
	}



	$user = array("admn2055", "admn2410", "bjtaylor");
	$is_in_list = FALSE;
	
	if (in_array($webauth_code, $user)) return $webauth_code;
	else return FALSE;
}

//--------------------------------------------------------------------------------------------------
function show_no_mercy()
{
	print "
		<HR>
		<H2>Sorry!</H2>
		You are not allowed to use this script!<BR>
		Please contact <A HREF='mailto:matthias.opitz@socsci.ox.ac.uk'>M.Opitz</A> if you think that is wrong.
		<P>
		";
}
?>
