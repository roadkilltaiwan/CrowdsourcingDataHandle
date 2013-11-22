<?php

$rk_log_dir = implode("/", explode("/", realpath(__FILE__), -1));
require_once $rk_log_dir . "/../conf/db_constant.inc.php";
require_once $rk_log_dir . "/../includes/system.inc";

$log_con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
mysql_select_db(DB_DATABASE, $log_con);
mysql_query("set names 'utf8';", $log_con);

#echo "<xmp>";
#var_dump($_SERVER);
#echo "</xmp>";

//	$log['program'] = $this_filename;
$argv[0] = basename(@realpath($argv[0]));
foreach ($argv as &$a) {
	$a = str_replace("|", "&#124;", $a);
}
$log['program'] = implode("|", $argv);


$log['system_user'] = empty($_SERVER['USER'])?(trim(shell_exec('whoami'))):$_SERVER['USER'];
$log['ui_user'] = empty($_REQUEST['ui_user'])?"anonymous":$_REQUEST['ui_user'];
$log['hostname'] = empty($_SERVER['HOSTNAME'])?$_SERVER['HTTP_HOST']:$_SERVER['HOSTNAME'];
$log['system_details'] = json_encode(array('SERVER' => $_SERVER, 'REQUEST' => $_REQUEST));
$log['system_id'] = getmypid();
$log_id = -1;

function log_init ($action='Starting') {
	global $log, $log_con, $log_id;
	$log['action'] = $action;

	foreach ($log as $col => $val) {
		$sets[] = "`$col` = '".mysql_real_escape_string($val)."'";
	}
	$log_sql = "insert into ProcessLog set " . implode(", \n", $sets) . ";\n";
	$log_res = mysql_query($log_sql, $log_con);
	$log_id = mysql_insert_id($log_con);
//	echo "<xmp>" . $log_sql . "</xmp>";
	return $log_id;
}

function log_update ($action) {
	global $log_con, $log_id;
	$log['action'] = $action;
	$log['timestamp'] = date("Y-m-d H:i:s", time());
	$system_id = getmypid();
	foreach ($log as $col => $val) {
		$sets[] = "`$col` = '".mysql_real_escape_string($val)."'";
	}
	$log_sql = "update ProcessLog set " . implode(", \n", $sets) . " where `auto_id` = '$log_id' and `system_id` = '$system_id' ;\n";
	$log_res = mysql_query($log_sql, $log_con);
}

function log_end ($action="Done") {
	global $log_con, $log_id;
	$log['action'] = $action;
	$log['timestamp'] = date("Y-m-d H:i:s", time());
	$log['final_state'] = 1;
	$system_id = getmypid();

	foreach ($log as $col => $val) {
		$sets[] = "`$col` = '".mysql_real_escape_string($val)."'";
	}
	$log_sql = "update ProcessLog set " . implode(", \n", $sets) . " where `auto_id` = '$log_id' and `system_id` = '$system_id' ;\n";
	$log_res = mysql_query($log_sql, $log_con);
}


?>
