<?php

/*
  Extract species common names, scientific names,  date of the photo  taken, collection id and specimen id from data crawled from Facebook group.
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




$dir_003 = implode("/", explode("/", realpath(__FILE__), -1));
$api = false;
if (!empty($_GET['q'])) {
	$q = $_GET['q'];
	$api = true;
}

require_once $dir_003."/includes/db_default_settings.inc";
require_once $dir_003."/includes/system.inc";
require_once $dir_003."/classes/class.extractCommonNames.php";
require_once $dir_003."/lib/webbots/LIB_parse.php";

require_once $dir_003."/includes/extractDate.php";
require_once $dir_003."/includes/extractExtra.php";

$this_filename = __FILE__; //一定要有這行
require_once $dir_003 . "/includes/rk_log.inc";
$auto_id = log_init();

/* Init */
$ner = new simpleNER ();

# reset data
$ner->loadSpeciesCommonNamesDict($dir_003."/dict/dict_wo_sort.txt");

$ner->loadC2S();
$ner->loadDefaultDict();

/* Extract */

/*
$ner->extractSpeciesCommonNames($string);
$context = strip_tags(@$context);
$ner->extractSpeciesCommonNamesAbbrv($string, $context);
*/

function extractRKNames ($string, $type) {
	switch ($type) {
		case "CN":
			$dlmt = "#";
			break;
		case "SN":
			$dlmt = "\\*";
			break;
		default:
			break;
	}
	$names = parse_array($string, $dlmt, $dlmt);
	foreach ($names as $key => $name) {
		$name = str_replace($dlmt, "", $name);
		$names[$key] = trim(str_replace("*", "", $name));
	}
	return $names;
}


$string = "麻鷺麻雀";
$string = "在南投*國道*8號路邊撿到的蛇蛇, 好像是#龜殼花#, 請問大大我該如何處理?";

#$names = extractCommonNames($dict, $dict_replacement, $string);
#$names = extractRKNames($string, "SN");
#var_dump($names);
#return;
if (!empty($argv[1])) {
	$stop_format = $argv[1];
}
else {
	$stop_format = "2011-08-01";
}
$stop = strtotime($stop_format);


$sql = "select * from `Post` where `updated_time` >= '".$stop_format."' order by `updated_time` desc;";
$posts = $db->query($sql);

while ($post = mysql_fetch_assoc($posts)) {

	no_loop:
#	debugging
#	if ($post['post_id'] !== '238918712815615_606930349347781') continue;


#	if (strtotime($post['updated_time']) < $stop) continue;
	log_update("準備抓取post:" . $post['post_id'] . "中的物種名, FB上的更新時間是:" . $post['updated_time']);


	$string = $post['message'];

	if ($api===true) {
		$string = $q;
	}

	$string = str_replace("臺", "台", $string);
	$full_thread_string = $string;

	$names_matched_in_post = array();
	$names_guessed_in_post = array();

	$string2 = $string;
	if (preg_match('/238918712815615_/', $post['post_id'])) {
		$confirmed_cn = implode("|", extractRKNames($string, "CN"));
		$confirmed_sn = implode("|", extractRKNames($string, "SN"));
		while (((!preg_match('/^[a-zA-Z][a-z]/', $confirmed_sn))||(preg_match('/[\@\\\?\%\*\/\_\^\$\+\=\:\<\>]/', $confirmed_sn)))&&(!empty($confirmed_sn))) {
			$string_frags = explode("*", $string2, 2);
			$string2 = $string_frags[1];
			$confirmed_sn = implode("|", extractRKNames($string2, "SN"));
		}
	}
	else if (preg_match('/177883715557195_/', $post['post_id'])) {
		$confirmed_cn = implode("|", extractMothNames($string, "CN"));
		$confirmed_sn = implode("|", extractMothNames($string, "SN"));
	}

	$tmp_string = $string;
	$extra = extractExtra($tmp_string);
	while ($extra !== false) {
		$specimenID = mysql_real_escape_string(@$extra['SpecimenID']);
		if (!empty($specimenID)) {
			$sql = "replace into `extra_ids` (`object_id`, `custom_id`, `id_type`) values ('".$post['object_id']."','$specimenID','SpecimenID');";
			echo "---------------------------------5566" . $sql . "\n";
			mysql_query($sql);
			$tmp_string = str_replace($specimenID, "", $tmp_string);
		}
		$collectionID = mysql_real_escape_string(@$extra['CollectionID']);
		if (!empty($collectionID)) {
			$sql = "replace into `extra_ids` (`object_id`, `custom_id`, `id_type`) values ('".$post['object_id']."','$collectionID','CollectionID');";
			echo "---------------------------------7788" . $sql . "\n";
			mysql_query($sql);
			$tmp_string = str_replace($collectionID, "", $tmp_string);
		}
		$extra = extractExtra($tmp_string);
	}



	$ner->extractSpeciesCommonNames($string);
	$names_matched_in_post[$post['post_id']] = trim($ner->combFullMatch, "|");

	// 如果match不為空且存在分數9999, 如果confirmed_cn不包含於match裡面那就說不過去惹
	if (!empty($names_matched_in_post[$post['post_id']])&&(!empty($confirmed_cn))) {
		$ccns = explode("|", $confirmed_cn);
		$nmips = explode("|", $names_matched_in_post[$post['post_id']]);
		$new_confirmed_cn = array();
		foreach ($ccns as $ccn) {
			if (in_array(($ccn."(9999)"), $nmips)) {
				$new_confirmed_cn[] = $ccn;
			}
		}
		if ((strpos($names_matched_in_post[$post['post_id']], "(9999)")!==false)||(count($ccns)==1)) {
			$confirmed_cn = implode("|", $new_confirmed_cn);
		}
	}
	else if (empty($names_matched_in_post[$post['post_id']])&&(!empty($confirmed_cn))) {
		$names_matched_in_post[$post['post_id']] = $confirmed_cn . "(9999)";
	}

	$date = '';
	$ed = extractDate($string);
	if (!empty($ed)) {
		if (!$ed['hasYear']) {
			$year = date('Y', strtotime($post['created_time']));
			$date = $year . '-' . $ed['date'];
			if (strtotime($date) > strtotime($post['created_time'])) {
				$date = '';
			}
		}
		else {
			$date = $ed['date'];
		}
	}


	$sql = "update `Post` set `match` = '" . mysql_real_escape_string($names_matched_in_post[$post['post_id']]) . "'";
	$sql .= ", `confirmed_cn` = '" . mysql_real_escape_string($confirmed_cn) . "'";
	$sql .= ", `confirmed_sn` = '" . mysql_real_escape_string($confirmed_sn) . "'";
	$sql .= ", `extracted_date` = '" . mysql_real_escape_string($date) . "'";
	$sql .= " where `Post`.`post_id` = '" . $post['post_id'] . "';";

	if ($api!==true) {
		echo $sql . "\n";
		$db->query($sql);
	}

	$ner->extractSpeciesCommonNamesAbbrv($string);
#	$guessed_array = $ner->abbrvMatch;
#	$names_guessed_in_post[$post['post_id']] = implode("|", @array_keys($guessed_array));
	$names_guessed_in_post[$post['post_id']] = $ner->combAbbrvMatch;

	if ($api===true) {
		$res = array ($names_matched_in_post, $names_guessed_in_post);
		echo json_encode($res);
		break;
	}

#	echo "Post: ".$string."\n";
#	echo $names_matched_in_post[$post['post_id']],",",$names_guessed_in_post[$post['post_id']]."\n";

	$sql = "select * from `Comments` where `to` = '" . $post['post_id'] . "';";
	$comments = $db->query($sql);

	$names_matched_in_comment = array();
	$names_guessed_in_comment = array();
	while ($comment = mysql_fetch_assoc($comments)) {

		log_update("準備抓取comment:" . $comment['comment_id'] . "中的物種名, FB上的更新時間是:" . $comment['created_time']);

		$string = $comment['message'];
		$string = mb_ereg_replace("台灣", "臺灣", $string);
		$full_thread_string .= "," . $string;

		$tmp_string = $string;
		$extra = extractExtra($tmp_string);
		while ($extra !== false) {
			$specimenID = mysql_real_escape_string(@$extra['SpecimenID']);
			if (!empty($specimenID)) {
				$sql = "replace into `extra_ids` (`object_id`, `custom_id`, `id_type`) values ('".$post['object_id']."','$specimenID','SpecimenID');";
				echo "-------------------------------------------2266" . $sql . "\n";
				mysql_query($sql);
				$tmp_string = str_replace($specimenID, "", $tmp_string);
			}
			$collectionID = mysql_real_escape_string(@$extra['CollectionID']);
#			var_dump($collectionID);
			if (!empty($collectionID)) {
				$sql = "replace into `extra_ids` (`object_id`, `custom_id`, `id_type`) values ('".$post['object_id']."','$collectionID','CollectionID');";
				echo "--------------------------------------------183" . $sql . "\n";
				mysql_query($sql);
				$tmp_string = str_replace($collectionID, "", $tmp_string);
			}
			$extra = extractExtra($tmp_string);
#			var_dump($tmp_string);
#			var_dump($extra);
		}


		$string3 = $string;
		if (preg_match('/238918712815615_/', $post['post_id'])) {
			$confirmed_cn = implode("|", extractRKNames($string, "CN"));
			$confirmed_sn = implode("|", extractRKNames($string, "SN"));
			while (((!preg_match('/^[a-zA-Z][a-z]/', $confirmed_sn))||(preg_match('/[\@\\\?\%\*\/\_\^\$\+\=\:\<\>]/', $confirmed_sn)))&&(!empty($confirmed_sn))) {
				$string_frags = explode("*", $string3, 2);
				$string3 = $string_frags[1];
				$confirmed_sn = implode("|", extractRKNames($string3, "SN"));
			}
		}
		else if (preg_match('/177883715557195_/', $post['post_id'])) {
			$confirmed_cn = implode("|", extractMothNames($string, "CN"));
			$confirmed_sn = implode("|", extractMothNames($string, "SN"));
		}

		preg_match('/^[ -~]*/', $confirmed_cn, $invalid_head);
		preg_match('/[ -~]*$/', $confirmed_cn, $invalid_tail);
		if ((strlen($invalid_head[0])>1)||(strlen($invalid_tail[0])>1)) {
			$confirmed_cn = "";
		}

		$ner->extractSpeciesCommonNames($string);
		$names_matched_in_comment[$post['post_id']][$comment['comment_id']] = trim($ner->combFullMatch, "|");

		// 如果match不為空且存在分數9999, 如果confirmed_cn不包含於match裡面那就說不過去惹
		if (!empty($names_matched_in_comment[$post['post_id']][$comment['comment_id']])&&(!empty($confirmed_cn))) {
			$ccns = explode("|", $confirmed_cn);
			$nmics = explode("|", $names_matched_in_comment[$post['post_id']][$comment['comment_id']]);
			$new_confirmed_cn = array();
			foreach ($ccns as $ccn) {
				if (in_array(($ccn."(9999)"), $nmics)) {
					$new_confirmed_cn[] = $ccn;
				}
			}
			if ((strpos($names_matched_in_comment[$post['post_id']][$comment['comment_id']], "(9999)")!==false)||(count($ccns)==1)) {
				$confirmed_cn = implode("|", $new_confirmed_cn);
			}
		}
		else if (empty($names_matched_in_comment[$post['post_id']][$comment['comment_id']])&&(!empty($confirmed_cn))) {
			$names_matched_in_comment[$post['post_id']][$comment['comment_id']] = $confirmed_cn . "(9999)";
		}

		$date = '';
		$ed = extractDate($string);
		if (!empty($ed)) {
			if (!$ed['hasYear']) {
				$year = date('Y', strtotime($post['created_time']));
				$date = $year . '-' . $ed['date'];
				if (strtotime($date) > $comment['created_time']) {
					$date = '';
				}
			}
			else {
				$date = $ed['date'];
			}
		}

		echo "Comment: $string\n";
		$sql = "update `Comments` set `match` = '" . 
			mysql_real_escape_string($names_matched_in_comment[$post['post_id']][$comment['comment_id']]) . "'";
		$sql .= ", `confirmed_cn` = '" . mysql_real_escape_string($confirmed_cn) . "'";
		$sql .= ", `confirmed_sn` = '" . mysql_real_escape_string($confirmed_sn) . "'";
		$sql .= ", `extracted_date` = '" . mysql_real_escape_string($date) . "'";
		$sql .= " where `Comments`.`comment_id` = '".$comment['comment_id']."';";
		echo $sql . "\n";
		$db->query($sql);

#		for ($j=4; $j>1; $j--) {
#			${"prefix"."$j"."_in_comment"} = extractCommonNames(${"prefix"."$j"}, ${"prefix"."$j"."_replacement"}, $string);
#			${"prefix"."$j"."_in_comment"} = array_unique(${"prefix"."$j"."_in_comment"});
#			${"p"."$j"."_guessed"} = array();
#			foreach (${"prefix"."$j"."_in_comment"} as $p) {
#				if ((mb_strpos($p3_guessed_string, $p)===false)&&
#				    (mb_strpos($p4_guessed_string, $p)===false)&&
#				    (mb_strpos($names_matched_in_comment[$post['post_id']][$comment['comment_id']], $p)===false)) {
#					${"p"."$j"."_guessed"}[] = implode("|", ${"prefix"."$j"."_full"}[$p]);
#				}
#			}
#			${"p"."$j"."_guessed_string"} = implode("|", ${"p"."$j"."_guessed"});
#		}
#		$guessed = $p4_guessed_string . "|" . $p3_guessed_string . "|" . $p2_guessed_string;
#		$names_guessed_in_comment[$post['post_id']][$comment['comment_id']]  = 
#			implode("|", @array_unique(explode("|", trim($guessed, "|"))));

		$ner->extractSpeciesCommonNamesAbbrv($string, $full_thread_string);
#		$guessed_array = $ner->abbrvMatch;
#		$names_guessed_in_comment[$post['post_id']][$comment['comment_id']] = implode("|", @array_keys($guessed_array));
		$names_guessed_in_comment[$post['post_id']][$comment['comment_id']] = $ner->combAbbrvMatch;

#		echo "Comment: ".$string."\n";
#		echo $names_matched_in_comment[@$comment[$post['post_id']]['comment_id']],",";
#		echo $names_guessed_in_comment[@$comment[$post['post_id']]['comment_id']]."\n";
	}
	echo "Post: ";
	foreach ($names_guessed_in_post as $post_id => $guessed) {
		$sql = "update `Post` set `guess` = '" . mysql_real_escape_string($guessed) . "'";
		$sql .= " where `Post`.`post_id` = '$post_id';";
		echo $sql . "\n";
		$db->query($sql);

		echo "Comment: ";
		if (!empty($names_guessed_in_comment))  ## 沒有comment就不做啦
		foreach ($names_guessed_in_comment[$post_id] as $comment_id => $guessed) {
			$sql = "update `Comments` set `guess` = '" . mysql_real_escape_string($guessed) . "'";
			$sql .= " where `Comments`.`comment_id` = '$comment_id';";
			echo $sql . "\n";
			$db->query($sql);
		}
	}
}

log_end("抓取完成");

?>
