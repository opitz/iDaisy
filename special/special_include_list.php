<?php
//====================================
//	The include block for special reports
//	2013-04-12	3rd version
//====================================

include '../includes/opendb.php';
include '../includes/common_functions.php';
include '../includes/common_DAISY_functions.php';
include '../includes/common_export_functions.php';
//include '../includes/the_usual_suspects.php';

include 'functions/special_functions.php';
include 'functions/duplicate_names_report.php';
include 'functions/duplicate_employee_numbers_report.php';
include 'functions/au_by_owner_report.php';
include 'functions/stint_balance_report.php';
include 'functions/joint_postholders.php';
include 'functions/TC_AU.php';

include 'functions/au_dp_students_report.php';
include 'functions/au_dept_students_report.php';


?>