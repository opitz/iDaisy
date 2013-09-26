<?php

if (isset($_POST['query_type'])) $query_type = $_POST['query_type'];													// get query type
if (isset($_POST['excel_export'])) $excel_export = $_POST['excel_export'];												// get excel_export

if (isset($_GET['department_code'])) $department_code = $_GET['department_code'];									// get department_code
if(!$department_code AND (isset($_POST['department_code']))) $department_code = $_POST['department_code'];

if (isset($_GET['ay_id'])) $ay_id = $_GET['ay_id'];																		// get academic_year_id
if(!$ay_id AND (isset($_POST['ay_id']))) $ay_id = $_POST['ay_id'];

if (isset($_GET['dp_id'])) $dp_id = $_GET["dp_id"];																		// get degree programme ID dp_id
if(!$dp_id AND (isset($_POST['dp_id']))) $dp_id = $_POST['dp_id'];

if (isset($_GET['au_id'])) $au_id = $_GET["au_id"];																		// get assessment unit ID au_id
if(!$au_id AND (isset($_POST['au_id']))) $au_id = $_POST['au_id'];

if (isset($_GET['tc_id'])) $tc_id = $_GET['tc_id'];																		// get teaching component ID ti_id
if(!$tc_id AND (isset($_POST['tc_id']))) $tc_id = $_POST['tc_id'];

if (isset($_GET['ti_id'])) $ti_id = $_GET["ti_id"];																			// get teaching instance ID ti_id
if(!$ti_id AND (isset($_POST['ti_id']))) $ti_id = $_POST['ti_id'];

if (isset($_GET['cte_id'])) $cte_id = $_GET["cte_id"];																	// get committee ID cte_id
if(!$cte_id AND (isset($_POST['cte_id']))) $cte_id = $_POST['cte_id'];

if (isset($_GET['rg_id'])) $rg_id = $_GET["rg_id"];																		// get committee ID rg_id
if(!$rg_id AND (isset($_POST['rg_id']))) $rg_id = $_POST['rg_id'];

if (isset($_GET['e_id'])) $e_id = $_GET["e_id"];																			// get employee ID e_id
if(!$e_id AND (isset($_POST['e_id']))) $e_id = $_POST['e_id'];

if (isset($_GET['mod_id'])) $mod_id = $_GET["mod_id"];																// get employee ID e_id
if(!$mod_id AND (isset($_POST['mod_id']))) $mod_id = $_POST['mod_id'];

?>