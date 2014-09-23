<?php
/*
  Generate a big table "bigTable" from CrowdSourcing Database for scientists to use.
  Copyright (C) 2013  Jason Guan-Shuo Mai

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


$dir_007 = implode("/", explode("/", realpath(__FILE__), -1));

require_once $dir_007."/conf/db_constant.inc.php";
require_once $dir_007."/includes/system.inc";

if (!empty($argv[1])) {
	$stop_format = $argv[1];
}
else {
	$stop_format = "2011-08-01";
	$argv[1] = $stop_format;
}
require_once $dir_007 . "/includes/rk_log.inc";
$auto_id = log_init();

$con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
mysql_select_db(DB_DATABASE, $con);
mysql_query("set names 'utf8';");

$update_location = 0;

// 更新地點時bigTable自己也要更新自己一下okㄟ耶?


$sql = "select  `Photo`.`photo_id` AS `photo_id`,`Photo`.`link` AS `link`,`Photo`.`picture` AS `picture`,`Photo`.`embedded_in` AS `post_id`,
`Person`.`person_id` AS `person_id`,`Person`.`name` AS `name`,`Photo`.`created_time` AS `created_time`,`Decide`.`shot_date` AS `shot_date`,
`Decide`.`common_name` AS `common_name`,`Decide`.`canonical_name` AS `canonical_name`,`Decide`.`tagged` AS `tagged`,`Decide`.`inWhiteList` AS `inWhiteList`,
`Post`.`post_from` AS `post_from`,`Person`.`authState` AS `authState`, `byWhom`, `extra_ids`.`custom_id` AS `custom_id`, `extra_ids`.`id_type` as id_type ,
`GoogleLoc`.`p1` AS `p1`,`GoogleLoc`.`p2` AS `p2`,`Location`.`placename_id` AS `placename_id`, `Location`.`x` AS `x`, `Location`.`y` AS `y`
from  ((((((`Photo` left join `Post` on((`Photo`.`photo_id` = `Post`.`object_id`)))
left join `Decide` on ((`Photo`.`photo_id` = `Decide`.`object_id`)))
left join `Person` on((`Photo`.`uploaded_by` = `Person`.`person_id`)))
left join `extra_ids` on(((convert(`Photo`.`photo_id` using utf8) = `extra_ids`.`object_id`))))
left join `GoogleLoc` on((`Photo`.`photo_id` = `GoogleLoc`.`photo_id`)))
left join `Location` on((convert(`Photo`.`photo_id` using utf8) = `Location`.`photo_id`)))
where `Post`.`updated_time` >= '".$stop_format."'
order by `Photo`.`photo_id`";

/*
$sql = "select  `Photo`.`photo_id` AS `photo_id`,`Photo`.`link` AS `link`,`Photo`.`picture` AS `picture`,`Photo`.`embedded_in` AS `post_id`,
`Person`.`person_id` AS `person_id`,`Person`.`name` AS `name`,`Photo`.`created_time` AS `created_time`,`Decide`.`shot_date` AS `shot_date`,
`Decide`.`common_name` AS `common_name`,`Decide`.`canonical_name` AS `canonical_name`,`Decide`.`tagged` AS `tagged`,`Decide`.`inWhiteList` AS `inWhiteList`,
`Post`.`post_from` AS `post_from`,`Person`.`authState` AS `authState` 
from  (((`Photo` left join `Post` on((`Photo`.`photo_id` = `Post`.`object_id`)))
left join `Decide` on ((`Photo`.`photo_id` = `Decide`.`object_id`)))
left join `Person` on((`Photo`.`uploaded_by` = `Person`.`person_id`)))
order by `Photo`.`photo_id`";
*/

echo $sql . "\n";

$res = mysql_query($sql);

while ($rowCrawled = mysql_fetch_assoc($res)) {
	log_update("建立照片:".$rowCrawled['photo_id']."之metadata");
	$q = "select * from bigTable where photo_id = '".$rowCrawled['photo_id']."';";
	$res2 = mysql_query($q);
	$rowExist = mysql_fetch_assoc($res2);
	$sets = array();
	$sets_location = array();
#	var_dump(empty($rowExist));
	if (empty($rowExist)) {
		foreach ($rowCrawled as $col => $val) {
			if (($col != 'person_id')&&($col != 'post_from')&&($col != 'id_type')&&($col != 'custom_id')&&($col != 'x')&&($col != 'y')) {
				$sets[] = "`$col` = '".mysql_real_escape_string($val)."'";
			}
		}

		if ($rowCrawled['x'] == 0) {
			$sets[] = "`x` = NULL";
		}
		else {
			$sets[] = "`x` = '".mysql_real_escape_string($rowCrawled['x'])."'";
		}

		if ($rowCrawled['y'] == 0) {
			$sets[] = "`y` = NULL";
		}
		else {
			$sets[] = "`y` = '".mysql_real_escape_string($rowCrawled['y'])."'";
		}

		var_dump($sets);

		$person_id = '';
		if (!empty($rowCrawled['person_id'])) {
			$person_id = $rowCrawled['person_id'];
		}
		else if (!empty($rowCrawled['post_from'])) {
			$person_id = $rowCrawled['post_from'];
		}
		$sets[] = "`person_id` = '".mysql_real_escape_string($person_id)."'";
		$custom_id = '';
		if (!empty($rowCrawled['custom_id'])) {
			$custom_id = $rowCrawled['id_type'] . "(" . $rowCrawled['custom_id'] . ")";
			$sets[] = "`custom_id` = '".mysql_real_escape_string($custom_id)."'";
		}
		$sql = "insert into bigTable set " . implode(", ", $sets); 
		echo $sql . "\n";
		mysql_query($sql);
	}
	else {
		$tmp_location = array();
		var_dump(implode("|", $rowCrawled));
		var_dump(implode("|", $rowExist));

		$tmp = array();
		foreach ($rowExist as $col2 => $val2) {
			if (!empty($val2)) {
				$tmp[$col2] = explode("|", $val2);
				if ($col2 == 'custom_id') {
					$tmp[$col2] = array_unique($tmp[$col2]);
				}
			}
			else {
				$tmp[$col2] = array();
			}
		}
		foreach ($rowCrawled as $col => $val) {
			if (
				($col != 'person_id')&&
				($col != 'post_from')&&
				($col != 'id_type')&&
				($col != 'custom_id')&&
				($col != 'p1')&&
				($col != 'p2')
			) {
				if (($col == 'tagged')||($col == 'inWhiteList')||($col == 'authState')||($col == 'byWhom')||($col == 'placename_id')) {
					if (!is_null($val)||($val !== '')) {
						$tmp[$col][0] = $val;
					}
				}
				else {
					if (!in_array($val, $tmp[$col])) {
						if (!empty($val)) {
							$tmp[$col][] = $val;
						}
					}
				}
			}
		}

		if (!empty($rowCrawled['p1'])) {
			$tmp['p1'][0] = $rowCrawled['p1'];
			$tmp_location['p1'][0] = $rowCrawled['p1'];
		}
		if (!empty($rowCrawled['p2'])) {
			$tmp['p2'][0] = $rowCrawled['p2'];
			$tmp_location['p2'][0] = $rowCrawled['p2'];
		}

		$person_id = '';
		if (!empty($rowCrawled['person_id'])) {
			$person_id = $rowCrawled['person_id'];
		}
		else if (!empty($rowCrawled['post_from'])) {
			$person_id = $rowCrawled['post_from'];
		}
		if (!in_array($person_id, $tmp['person_id'])) {
			if (!empty($person_id)) {
				$tmp['person_id'][] = $person_id;
			}
		}
		$custom_id = '';
		if (!empty($rowCrawled['custom_id'])) {
			$custom_id = ucfirst($rowCrawled['id_type']) . "(" . $rowCrawled['custom_id'] . ")";
			if (!in_array($custom_id, $tmp['custom_id'])) {
				if (!empty($custom_id)) {
					$tmp['custom_id'][] = $custom_id;
				}
			}
		}
		foreach ($tmp as $col2 => $val2s) {
			$val2 = implode("|", $val2s);
			$sets[] = "`$col2` = '".mysql_real_escape_string($val2)."'";
		}
		foreach ($tmp_location as $col2 => $val2s) {
			$val2 = implode("|", $val2s);
			$sets_location[] = "`$col2` = '".mysql_real_escape_string($val2)."'";
		}
		if ($rowExist['hu'] != 1) {
			$sql = "update bigTable set " . implode(", ", $sets) . " where photo_id = '".$rowExist['photo_id']."'";
			mysql_query($sql);
			echo $sql . "\n";
		}
		else if ($update_location == 1) {
			$sql = "update bigTable set " . implode(", ", $sets_location) . " where photo_id = '".$rowExist['photo_id']."'";
			mysql_query($sql);
			echo $sql . "\n";
		}
	}
}
$updateCoordSQL = "update bigTable set x = NULL, y = NULL where x = 0 or y = 0;";
mysql_query($updateCoordSQL);

log_end();

?>
