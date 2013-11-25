<?php 

/*
  Use the access token generated in script 000_get_access_token.php to update FB group Reptile Road Mortality user data.
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



$dir_002 = implode("/", explode("/", realpath(__FILE__), -1));

## TODO
## 把replace into都改成insert into ... on duplicate update ...

require_once $dir_002 . "/includes/db_default_settings.inc";
require_once $dir_002 . "/includes/system.inc";
require_once $dir_002 . "/includes/LIB_http_modified_subset.php";

require_once $dir_002 . "/includes/rk_log.inc";
$auto_id = log_init();
##############################################################################

# fake
# DEFINE HEADER INCLUSION
#$access_counter = 0;

$access_token = file_get_contents($dir_002."/cache/facebookApp/access_token.txt");

if(!empty($access_token)) {

	$sql = "select * from Person where `username` = '';";
	$persons = $db->fetch_array($sql);

	foreach ($persons as $person) {
		//* get person detailed info
		$personVar = array (
			'access_token' => $access_token,
		);
		$person_url = "https://graph.facebook.com/" . $person['person_id'];
		$person_raw = http($person_url, "", GET, $personVar, FALSE, NULL);
		$pdata = json_decode($person_raw['FILE']);
		$pid = $person['person_id'];
		$pun = empty($pdata->username)?$person['person_id']:$pdata->username;
		$name = @$pdata->name;
		log_update("正在更新 $pid $pun $name 的資料");
		// end */

		if (!empty($pun)) {
			$person_sql = "insert into `Person` (`person_id`, `username`, `name`) values ('$pid', '$pun', '$name')
					on duplicate key update
					`username` = '$pun',
					`name` = '$name'
					";
			$db->query($person_sql);
			echo $person_sql . "\n";
		}
	}
	log_end();
}
else {
	echo("本支程式需要取得認證碼才得執行ㄎㄎ\n");
	log_end("無執行權限");
}

?>
