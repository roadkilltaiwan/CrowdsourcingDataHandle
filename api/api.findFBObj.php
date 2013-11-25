<?php
/*
  Find and retrieve photo data in CrowdSourcing Database table "bigTable".
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

$dir_api = implode("/", explode("/", realpath(__FILE__), -1));

require_once $dir_api . "/../includes/system.inc";
require_once $dir_api . "/../conf/db_constant.inc.php";

$photo_id = $_REQUEST['oid'];

if (!empty($argv[1])) {
	$photo_id = $argv[1];
}

#$photo_id = 387601101367562;

$db = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
mysql_select_db(DB_DATABASE, $db);

$sql = "set names 'utf8';";
mysql_query($sql);

$sql = "select * from general_info where photo_id like '$photo_id'";
$sql = "select * from bigTable where photo_id like '$photo_id'";
$res = mysql_query($sql);

$ret = null;

while ($row = @mysql_fetch_assoc($res)) {

	$cnames = explode("|", @$row['common_name']);
	$snames = explode("|", @$row['canonical_name']);

	$cc = count($cnames);
	$cs = count($snames);
	if ($cc > $cs) {
		$len = $cs;
	}
	else {
		$len = $cc;
	}
	if ($len != 0) {
		if ($len > 3) $len = 3;
		$cnames = array_slice($cnames, 0, $len);
		$snames = array_slice($snames, 0, $len);
	}

	foreach ($snames as $snid => $sname) {
		if (empty($cnames[$snid])&&!empty($sname)) {
			$url = "http://140.109.29.92/solr/another/select?fl=name_zhtw&wt=json&qf=name_clean&fq=rank:species&q=" . $sname;
			$res = json_decode(file_get_contents($url));
			$cnames[$snid] = $res->response->docs[0]->name_zhtw;
		}
	}
	$ids = explode("|", $row['custom_id']);
	$coids = array();
	$spids = array();
	foreach ($ids as $tmp_id) {
		preg_match('/^CollectionID\((.+)\)$/i', $tmp_id, $match1);
		preg_match('/^SpecimenID\((.+)\)$/i', $tmp_id, $match2);
		if (!empty($match1[1])) {
			$coids[] = $match1[1];
		}
		if (!empty($match2[1])) {
			$spids[] = $match2[1];
		}

	}

	$ret['oid'] = @$row['photo_id'];
	$ret['picture'] = @$row['picture'];
	$ret['pid'] = @$row['person_id'];
	$ret['pname'] = @$row['name'];
	$ret['ctime'] = strtotime(@$row['created_time']) + (480 * 60);
	$ret['stime'] = @$row['shot_date'];
	$ret['cname'] = @$cnames;
	$ret['sname'] = @$snames;
	$ret['tagged'] = @$row['tagged'];
	$ret['tiw'] = @$row['inWhiteList'];
	$ret['rk'] = @$row['rk'];
	$ret['needMore'] = @$row['needMore'];
	$ret['auth'] = @$row['authState'];
	$ret['by'] = @$row['byWhom'];
	$ret['spid'] = implode("|", $spids);
	$ret['coid'] = implode("|", $coids);
	$ret['post_id'] = @$row['post_id'];
	$ret['x'] = @$row['x'];
	$ret['y'] = @$row['y'];
	$ret['alt'] = @$row['altitude'];
	$ret['p1'] = @$row['p1'];
	$ret['p2'] = @$row['p2'];
	$ret['p3'] = @$row['p3'];
	$ret['remark'] = @$row['remark'];
	$ret['actOpts'] = @$row['activity'];
	$ret['hu'] = @$row['hu'];
}
echo json_encode($ret);

?>
