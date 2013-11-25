<?php
/*
  Decide the most possible species discussed in and extracted from a thread.
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


ERROR_REPORTING(E_ERROR);
$dir_004 = implode("/", explode("/", realpath(__FILE__), -1));
#   include_once "../includes/
#require_once "arc2/ARC2.php";
#require_once "config.php";

mb_regex_encoding("UTF-8");

require_once $dir_004."/includes/db_default_settings.inc";
require_once $dir_004."/includes/system.inc";
require_once $dir_004."/classes/class.extractCommonNames.php";
require_once $dir_004."/includes/cleanname.inc";

require_once $dir_004 . "/includes/rk_log.inc";
$auto_id = log_init();

if (!empty($argv[1])) {
	$stop_format = $argv[1];
}
else {
	$stop_format = "2011-08-01";
}
$stop = strtotime($stop_format);


$ner = new simpleNER();
$ner->loadC2S();

#var_dump($ner->c2s);

$sql = "select post_id as id, post_from as `from`, num_likes as likes, `match`, guess, `confirmed_cn` as ccn, `confirmed_sn` as csn, `object_id` as oid, extracted_date `updated_time` from `Post` where `updated_time` >= '".$stop_format."';";

$posts = $db->fetch_array($sql);

$decide = array();

foreach ($posts as $post) {

//	if (strtotime($post['updated_time']) < $stop) continue;
	log_update("決定討論串:" . $post['id'] . "所討論的物種名");
	$oid = $post['oid'];
	$id = $post['id'];

	$from = $post['from'];

	$tiw = false;
	$tiw_sql = "select * from Person where `person_id` = '$from' or `username` = '$from'";
	$tiw_rows = $db->fetch_array($tiw_sql);
	if ($tiw_rows[0]['inWhiteList'] == 1) {
		var_dump($tiw_rows[0]);
		$tiw = true;
	}
	
#	if ($id != '238918712815615_497198900320927') continue;
#	if ($id != '238918712815615_645409405499875') continue;
#	if ($id != '238918712815615_645078295532986') continue;

	$ed = '';

	if (!empty($post['extracted_date'])) {
		$ed = $post['extracted_date'];
		$decide[$oid]['shot_date'] = $ed;
	}

	#var_dump($ed);

	$thread_ccns[$oid] = array();
	$thread_csns[$oid] = array();

	$thread_ns[$oid] = array();
	if (!empty($post['ccn'])&&!empty($post['csn'])) {
		$cfrags = explode("|", $post['ccn']);
		$sfrags = explode("|", $post['csn']);
		foreach ($cfrags as $cidx => $f) {
			$thread_ccns[$oid][$f] =  true;
			$ns_tmp = array();
			if ($sfrags[$cidx]) {
				$ns_tmp['cn'] = $f;
				$ns_tmp['sn'] = $sfrags[$cidx];
				$thread_ns[$oid][] = $ns_tmp;
			}

			if ($tiw) {
				$whiteList[$oid][$sfrags[$cidx]] = true;
			}
		}
	}
	else if ($post['ccn']) {
		$frags = explode("|", $post['ccn']);
		$ns_tmp = array();
		foreach ($frags as $f) {
			$thread_ccns[$oid][$f] =  true;
			$ns_tmp['cn'] = $f;
			$ns_tmp['sn'] = '';
			$found = false;
			foreach ($thread_ns[$oid] as $set) {
				if ($set['cn'] == $f) {
					$found = true;
				}
			}
			if (!$found) {
				$fill = false;
				foreach ($thread_ns[$oid] as &$set) {
					if (!$set['cn'] && $set['sn']) {
						$fill = true;
						$set['cn'] = $f;
					}
				}
				if (!$fill) {
					$thread_ns[$oid][] = $ns_tmp;
				}
			}
		}
		if ($oid == '662402543780371') {
			var_dump(7788);
			var_dump($ns_tmp);
		}
	}
	else if ($post['csn']) {
		$frags = explode("|", $post['csn']);
		$ns_tmp = array();
		foreach ($frags as $f) {
			$thread_csns[$oid][$f] =  true;
			$ns_tmp['cn'] = '';
			$ns_tmp['sn'] = $f;
			$found = false;
			foreach ($thread_ns[$oid] as $set) {
				if ($set['sn'] == $f) {
					$found = true;
				}
			}
			if (!$found) {
				$fill = false;
				foreach ($thread_ns[$oid] as &$set) {
					if ($set['cn'] && !$set['sn']) {
						$fill = true;
						$set['sn'] = $f;
					}
				}
				if (!$fill) {
					$thread_ns[$oid][] = $ns_tmp;
				}
			}

			if ($tiw) {
				$whiteList[$oid][$f] = true;
			}
		}
	}

	$likes = $post['likes']+1;
	$match = $post['match'];
	$guess = $post['guess'];
	$species = "";
	$score = 0;

	if ($likes < 10) {
		$likes = 1;
	}
	else {
		$likes = log10($likes);
	}

	if (empty($match)) {
		if (!empty($guess)) {
			if (strpos($guess, "|")===false) {
				$species = explode("(", $guess);
				$score = trim($species[1], ")");
				$species = $species[0];
			}
			else {
				$species = explode("|", $guess);
				$species = $species[0];
				$species = explode("(", $species);
				$score = trim($species[1], ")");
				$species = $species[0];
			}
		}
		if ($score > 1) $score = 1;
	}
	else {
		if (strpos($match, "|")===false) {
			$species = $match;
			$species = explode("(", $species);
			$score = trim($species[1], ")");
			$species = $species[0];
		}
		else {
			$species = explode("|", $match);
			$species = $species[0];
			$species = explode("(", $species);
			$score = trim($species[1], ")");
			$species = $species[0];
		}
		if (empty($score)) {
			$score = 1;
		}
	}
	if (!empty($species)) {
//		echo $id . ", " . $species . ", ", ($likes) . "(origin $match, $guess)\n";
		$decide[$oid]['species'] = $species;
		$decide[$oid]['score'] = $score * $likes;
	}

	$sql = "select `from` as cfrom, `to`, `comment_id` as cid, num_likes as clikes, `match` as cmatch, guess as cguess, `confirmed_cn` as ccn, `confirmed_sn` as csn, `extracted_date` from `Comments` where `to`='$id';";
	$comments = $db->fetch_array($sql);

	$bonus = 0;
	$bp = 0.438;#60 / 137;
	$counter = 0;
	foreach ($comments as $index => $comment) {

		$cfrom = $comment['cfrom'];
		$tiw = false;
		$tiw_sql = "select * from Person where `person_id` = '$cfrom' or `username` = '$cfrom'";
		$tiw_rows = $db->fetch_array($tiw_sql);
		if ($tiw_rows[0]['inWhiteList'] == 1) {
			var_dump($tiw_rows[0]);
			$tiw = true;
		}



		if (!empty($comment['ccn'])&&!empty($comment['csn'])) {
			$cfrags = explode("|", $comment['ccn']);
			$sfrags = explode("|", $comment['csn']);
			foreach ($cfrags as $cidx => $f) {
				$thread_ccns[$oid][$f] =  true;
				$ns_tmp = array();
				if ($sfrags[$cidx]) {
					$ns_tmp['cn'] = $f;
					$ns_tmp['sn'] = $sfrags[$cidx];
					$thread_ns[$oid][] = $ns_tmp;
				}
				if ($tiw) {
					$whiteList[$oid][$sfrags[$cidx]] = true;
				}
			}
		}
		else if ($comment['ccn']) {
			$frags = explode("|", $comment['ccn']);
			$ns_tmp = array();
			foreach ($frags as $f) {
				$thread_ccns[$oid][$f] =  true;
				$ns_tmp['cn'] = $f;
				$ns_tmp['sn'] = '';
				$found = false;
				foreach ($thread_ns[$oid] as $set) {
					if ($set['cn'] == $f) {
						$found = true;
					}
				}
				if (!$found) {
					$fill = false;
					foreach ($thread_ns[$oid] as &$set) {
						if (!$set['cn'] && $set['sn']) {
							$fill = true;
							$set['cn'] = $f;
						}
					}
					if (!$fill) {
						$thread_ns[$oid][] = $ns_tmp;
					}
				}
			}
			if ($oid == '662402543780371') {
				var_dump(3344);
				var_dump($ns_tmp);
			}
		}
		else if ($comment['csn']) {
			$frags = explode("|", $comment['csn']);
			$ns_tmp = array();
			foreach ($frags as $f) {
				$thread_csns[$oid][$f] =  true;
				$ns_tmp['cn'] = '';
				$ns_tmp['sn'] = $f;
				$found = false;
				foreach ($thread_ns[$oid] as $set) {
					if ($set['sn'] == $f) {
						$found = true;
					}
				}
				if (!$found) {
					$fill = false;
					foreach ($thread_ns[$oid] as &$set) {
						if ($set['cn'] && !$set['sn']) {
							$fill = true;
							$set['sn'] = $f;
						}
					}
					if (!$fill) {
						$thread_ns[$oid][] = $ns_tmp;
					}
				}

				if ($tiw) {
					$whiteList[$oid][$f] = true;
				}
			}
		}

/*
		if ($comment['ccn']) {
			$frags = explode("|", $comment['ccn']);
			foreach ($frags as $f) {
				$thread_ccns[$oid][$f] =  true;
			}
		}
		if ($comment['csn']) {
			$frags = explode("|", $comment['csn']);
			foreach ($frags as $f) {
				$thread_csns[$oid][$f] =  true;
				if (true) { // check if in whitelist
					$whiteList[$oid][$f] = true;
				}
			}
		}
*/


		if (!empty($ed)) {
			if (!empty($comment['extracted_date'])) {
				if ($comment['cfrom'] == $post['from']) {
					$ed = $comment['extracted_date'];
				}
			}
		}
		else {
			if (!empty($comment['extracted_date'])) {
				if ($comment['cfrom'] == $post['from']) {
					$ed = $comment['extracted_date'];
				}
			}
		}


		$cid = $comment['cid'];
		$to = $comment['to'];
		$clikes = $comment['clike']+1;
		$cmatch = $comment['cmatch'];
		$cguess = $comment['cguess'];
		$species = "";
		$score = 0;

		if ($to == '238918712815615_240852179288935') {
#			var_dump($ed);
#			var_dump($comment['cfrom']);
#			var_dump($post['from']);
#			var_dump($comment['extracted_date']);
		}


		if (empty($cmatch)) {
			if (!empty($cguess)) {
				if (strpos($cguess, "|")===false) {
					$species = explode("(", $cguess);
					$score = trim($species[1], ")");
					$species = $species[0];
				}
				else {
					$species = explode("|", $cguess);
					$species = $species[0];
					$species = explode("(", $species);
					$score = trim($species[1], ")");
					$species = $species[0];
				}
			}
			if ($score > 1) $score = 1;
		}
		else {
			if (strpos($cmatch, "|")===false) {
				$species = $cmatch;
				$species = explode("(", $species);
				$score = trim($species[1], ")");
				$species = $species[0];
			}
			else {
				$species = explode("|", $cmatch);
				$species = $species[0];
				$species = explode("(", $species);
				$score = trim($species[1], ")");
				$species = $species[0];
			}
			if (empty($score)) {
				// 整個match到的後面不會接分數, 見`Post`.`match`與`Comments`.`match`
				$score = 1;
			}
		}
		if (!empty($species)) {
			$counter++;
			$bonus = $bonus + ($bp / $counter);
//			echo $id . ", " . $species . ", ", $score . "(origin $cmatch, $guess)\n";
#			$cscore = $score * ($clikes + ($index * 0.2));
			$cscore = $score * ($clikes + $bonus);




			if ($cscore > $decide[$oid]['score']) {
//				echo $id . ", " . $species . ", ", $score . ", " . $clikes . "," . $cscore . "\n";
				$decide[$oid]['species'] = $species;
				$decide[$oid]['score'] = $cscore;
			}
		}
		if (!empty($ed)) {
			$decide[$oid]['shot_date'] = $ed;
		}
	}
}
#var_dump($decide['10201378041358260']);
foreach ($decide as $oid => $item) {
	if (empty($oid)) continue;

	if ($oid == '662402543780371') {
		var_dump(5566);
		var_dump($item);
	}
	// common names
	$species = mysql_real_escape_string($item['species']);

//	if (!empty($thread_ccns[$oid])) {
//		$
//	}
	$areWhites = array();
#	var_dump($thread_ns);

	if (!empty($thread_ns[$oid])) {
		$tagged = 1;
		$canonical_names = array();
		$common_names = array();
		foreach ($thread_ns[$oid] as $set) {
			if ($oid == '10201378041358260') {
				var_dump($set);
			}
			$sciname = $set['sn'];
			$tmp = preg_replace("/[|0-9a-zA-Z\s]/", '', $sciname);
			if (empty($tmp)) {
				$canonical_names[] = $sciname;
				$common_names[] = $set['cn'];
			}
			else {
				$canonical_names[] = "[spam?] " . $sciname;
				$common_names[] = $set['cn'];
			}
#			var_dump($whiteList);
			if ($whiteList[$oid][$sciname] === true) {
				$areWhites[$sciname] = 1;
			}
			else {
				$areWhites[$sciname] = 0;
			}
		}
/*
		$common_names = array();
		foreach ($thread_ccns[$oid] as $cname => $dummy) {
			$common_names[] = $cname;
		}
*/
	}
	else {
		$tagged = 0;
		$canonical_names = array();
		$common_names = array();
		if (!empty($ner->c2s[$species])) {
			foreach ($ner->c2s[$species] as $nc => $dummy) {
				$url = "http://140.109.29.92/solr/another/select?fl=is_accepted_name,name_clean&wt=json&qf=name_code&q=" . $nc;
				$res = json_decode(file_get_contents($url));
				if (!empty($res->response->docs)) {
					$sciname = $res->response->docs[0]->name_clean;
					$canonical_names[] = $sciname;
					$common_names[] = $species;
					$areWhites[$sciname] = 0;
				}
				else {
					$canonical_names[] = '';
					$common_names[] = $species;
					$areWhites[''] = 0;
				}
			}
		}
		else {
			$canonical_names[] = '';
			$common_names[] = $species;
			$areWhites[''] = 0;
		}

		if ($oid == '662402543780371') {
			var_dump('wtf');
			var_dump($common_names);
			var_dump($canonical_names);
		}
	}
	$ed = $item['shot_date'];

	$score = $item['score'];
	if (empty($score)) $score = 0;

	$sql = "delete from `Decide` where object_id = '$oid';";
	mysql_query($sql);
	foreach ($canonical_names as $idx => $canonical_name) {
		$canonical_name = mysql_real_escape_string($canonical_name);
		$common_name = mysql_real_escape_string($common_names[$idx]);
		$isWhite = $areWhites[$canonical_name];
		if (empty($isWhite)) $isWhite = 0;
		$sql = "insert into `Decide` (`object_id`, `common_name`, `score`, `canonical_name`, `shot_date`, `tagged`, `inWhiteList`) values ('$oid', '$common_name', $score, '$canonical_name', '$ed', $tagged, $isWhite);";
		echo $sql . "\n";
		$db->query($sql);
	}
}

log_end();

?>
