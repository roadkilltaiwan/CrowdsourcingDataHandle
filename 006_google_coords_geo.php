<?php
/*
  Use google geo service to get formal administrative area names for threads missing these info or validate existing ones.
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


$dir_006 = implode("/", explode("/", realpath(__FILE__), -1));

require_once $dir_006."/conf/db_constant.inc.php";
require_once $dir_006."/includes/system.inc";

require_once $dir_006 . "/includes/rk_log.inc";
$auto_id = log_init();

$con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
mysql_select_db(DB_DATABASE, $con);
mysql_query("set names 'utf8';");

$sql = "select distinct x,y from Location";
$res = mysql_query($sql);

$limit = 3;
$counter = 0;
$debug = false;

$overwrite = false;

while ($row = mysql_fetch_assoc($res)) {
	if ($row['x']!=0 && $row['y']!=0) {

		$row['x'] = round((float)$row['x'], 6);
		$row['y'] = round((float)$row['y'], 6);
		log_update("處理座標x=" . $row['x'] . ", " . "y=" . $row['y'] . "所在之縣市鄉鎮");

		/* Debug
		$debug = true;
		$row['x'] = round((float) 120.477933, 6);
		$row['y'] = round((float) 23.267756, 6);
		//*/

		$row2 = mysql_fetch_assoc(mysql_query("select * from GoogleLoc where x=".$row['x']." and y=".$row['y'].";"));
#		var_dump($row2);
#		if (($row2 !== false)&&(!$debug)&&(!$overwrite)) continue;

#		if ($counter == 3) break;
		$counter++;

		$latlng = $row['y'].','.$row['x'];
		echo "latlng=$latlng\n";

		$locations = array();
		if (!empty($row2['p1']) && !empty($row2['p2']) && !$debug && !$overwrite) {
			$locations['aal2'] = $row2['p1'];
			$locations['loc'] = $row2['p2'];
		}
		else {
			$google_geocode = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latlng&sensor=false&language=zh-TW";
			$geo = file_get_contents($google_geocode);
			$geo = json_decode($geo);
//			var_dump($geo);
			foreach ($geo->results as $g_res) {
				foreach ($g_res->address_components as $comp_id => $comp) {
					if (in_array('political', $comp->types)) {
						if (in_array('administrative_area_level_2', $comp->types)) {
							$aal2 = $comp->long_name;
						}
						else if (in_array('locality', $comp->types)) {
							$locality = $comp->long_name;
						}
					}
				}
#				$locations[$aal2][$locality] = true;
				$locations['aal2'] = $aal2;
				$locations['loc'] = $locality;
#			       echo "$aal2, $locality\n";
			}
		}
		if (!empty($locations)) {
			$coords_loc[(string)$row['x']][(string)$row['y']] = $locations;
		}
		var_dump($locations);
	}
	if ($debug) break;
}

$sql = "select * from Location";
$res = mysql_query($sql);

while ($row = mysql_fetch_assoc($res)) {
	$row['x'] = round((float)$row['x'], 6);
	$row['y'] = round((float)$row['y'], 6);
	if (($row['x']!=0)&&($row['y']!=0)) {
		$loc = @$coords_loc[(string)$row['x']][(string)$row['y']];
		if (!empty($loc['aal2'])&&!empty($loc['loc'])) {
			$sql = "replace into GoogleLoc values ('".$row['photo_id']."','".$row['x']."','".$row['y']."','".$loc['aal2']."','".$loc['loc']."', NULL);";
			echo $sql . "\n";
			mysql_query($sql);
		}
	}
}

log_end();

?>
