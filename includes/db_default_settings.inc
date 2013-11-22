<?php

$parent_dir = str_replace("/".basename(__FILE__), "", realpath(__FILE__));
$grandpa_dir = implode("/", explode("/", $parent_dir, -1));
require_once $grandpa_dir . "/conf/" . "db_constant.inc.php";
require_once $grandpa_dir . "/lib/dbclass/" . "Database.singleton.php";

$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
$db->connect();
#require_once "";

?>
