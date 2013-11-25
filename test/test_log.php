<?php


echo "看不到咧~~";
return;

$this_filename = __FILE__; //一定要有這行
require_once "rk_log.php";
$auto_id = log_init();

var_dump($auto_id);

log_update("love56");

log_end();
?>
