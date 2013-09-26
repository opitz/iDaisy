<?php
//====================================
//	The include block to be used elsewhere
//	2012-09-20	1st version
//	2012-09-27	added committee functions
//	2012-11-05	added grant functions
//	2012-11-13	added attendance functions
//====================================

include 'opendb.php';
include 'common_functions.php';
include 'common_DAISY_functions.php';
include 'common_export_functions.php';

include 'functions/staff_functions.php';
include 'functions/unit_functions.php';
include 'functions/programme_functions.php';


//include 'functions/committee_functions.php';
include 'committee/functions/committee_functions.php';

include 'functions/component_functions.php';
include 'functions/leave_functions.php';
include 'functions/publication_functions.php';
//include 'functions/course_functions.php';

//include 'functions/office_functions.php';
include 'office/functions/office_functions.php';


include 'functions/grant_functions.php';
include 'functions/attendance_functions.php';
include 'functions/dtc_functions.php';

include 'common_au_functions.php';

include 'graduate/functions/graduate_functions.php';
include 'ses/functions/ses_functions.php';
//include 'query_boxes.php';

?>