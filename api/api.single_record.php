<?php
/*
  Realtime Extract species, date and coordinates informations.
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

require_once ($dir_api."/../includes/extractDate.php");
require_once $dir_api."/../includes/extractExtra.php";
require_once $dir_api."/../includes/extractXY.php";


if (!empty($_REQUEST['username'])) {
	$user = $_REQUEST['username'];
	$db = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	mysql_select_db(DB_DATABASE, $db);
	$sql = "set names 'utf8';";
	mysql_query($sql);
	$sql_tpl = "select * from Person where person_id = '%s' or username = '%s'";
	$sql = sprintf($sql_tpl, mysql_real_escape_string($user), mysql_real_escape_string($user));
	$res = mysql_query($sql);
	$row = @mysql_fetch_assoc($res);
	if (empty($row['authState'])) {
		$authState = '未授權';
	}
	else {
		$authState = $row['authState'];
	}
	if (empty($row['byWhom'])) {
		$byWhom = $row['name'];
	}
	else {
		$byWhom = $row['byWhom'];
	}

	$auth = array('authState'=>$authState, 'byWhom'=>$byWhom);
}
else {
	$auth = array('authState'=>'', 'byWhom'=>'');
}

if (!empty($_REQUEST['message'])) {
	$string = $_REQUEST['message'];
}
else {
	// 測試用字串
	$string = "麻鷺麻雀";
	$string = "在南投*國道*8號路邊撿到的蛇蛇, 好像是#龜殼花#, 請問大大我該如何處理?";
	$string = "台江國家公園第三區，曾文溪北岸堤防道路上，發現蛇隻大體，內臟外露，已扁乾，發臭。
日期：20130813
位置：電桿十吉高幹89右46 M8107 ED40 與 89右45 M8107 ED82 中間。";

	$string = "2013/9/6 下午4:54
臺九線 253.5~254K之間
眼鏡蛇 雨傘節 青竹絲";

	$string = "#豬頭# *Qqq aaa* #小白兔# *Uuu uuu*";

	$string = "
王冀名四處爬爬走(路殺社, Reptile Road Mortality)斯文豪氏遊蛇 2013.9.16 大漢林道17k 比例尺掌寬3cm讚 ·  · 追蹤貼文 · 分享 · 9月23日 林德恩和其他 63 人都說讚。洪小蕙 這比例尺是XXXD9月23日 12:18 · 讚 · 2Guanjin Chen 這比例尺...我的美金輸了9月23日 12:18來自手機 · 讚 · 10Kuei-han Chang 比例尺犯規了喲！9月23日 12:28來自手機 · 讚 · 13森樂 好可愛XDD9月23日 12:59 · 讚 · 5Tomas Yiu XD9月23日 13:44 · 讚 · 1Joyce Chen #斯文豪氏頸槽蛇#, *Rhabdophis swinhonis*9月23日 14:13 · 讚 · 2葉盛隆 可以想像主人把他的小柯基屁股，踩在地上拍照的模樣 XD (誤)9月23日 20:12 · 已編輯 · 讚 · 1王冀名 比例尺不用刻意固定.他們一直以來都很配合.呵呵9月23日 20:24 · 讚 · 14詹哲騎 好像沒明顯傷痕 怎麼死的???9月23日 20:55 · 讚葉盛隆 這個厲害啊~ 訓練有素~9月23日 20:55 · 讚 · 1留言⋯⋯
";

	$string = "在南横向陽黄喉貂";
	$string = "2013/6/18 觸口 台18線31.4k 23.44777 /120.57843 /225m";
	$string = "2013.10.19 台2丙 電力座標：C0540FA89
 #貢德氏赤蛙#";
	$string = "苗62-1,  243319  2701219 960m 黑眉錦蛇幼體
Hiroaki Chen 請問拍攝日期？？
20130925 今天
2013/9/25 
* Orthriophis taeniurus friesei *
可憐的小黑眉
Hiroaki Chen請問這個二度分帶座標是67系統還是97系統？";
	$string = "*****此區訊息僅供確認用, 不允許修改亦不會被傳送*****

#黑眶蟾蜍#
2013/9/26";
	$string = "紅冠水雞幼鳥 宜蘭縣冬山鄉梅林路 24.645535，121.750360 9/25am7:50
#紅冠水雞# *Gallinula chloropus*
2013/9/25";

	$string = "2013/10/11苗栗 大胡 九芎坪 24.43645/120.86729/261m";
	$string = "2013.10.22 台159甲37k (大華公路)
周俠客
臺灣葉鼻蝠
樓上的是正解～前臂明顯撞斷
#台灣葉鼻蝠#
感謝 :)Hua-Te Fang 2013.10.22 台159甲37k (大華公路)讚 ·  · 追蹤貼文 · 分享 · 10 個人都說讚。檢視另3則留言Joyce Chen #台灣葉鼻蝠# · 讚 · 1Hua-Te Fang 感謝 :) · 讚留言⋯⋯";

	$string = "李俊億四處爬爬走(路殺社, Reptile Road Mortality)眼鏡蛇 很新鮮讚 ·  · 追蹤貼文 · 分享 ·  · 編輯紀錄 林德恩和其他 4 人都說讚。林德恩 *Naja atra* · 讚Joyce Chen 2013/09/30 02:00-05:25AM嘉義仁義潭環潭道路 · 讚留言⋯⋯";

	$string = "林泳易四處爬爬走(路殺社, Reptile Road Mortality)20131019 鼬獾 信義幹 14 K8160 HC22讚 ·  · 追蹤貼文 · 分享 ·  林德恩和其他 12 人都說讚。Joyce Chen #鼬獾# · 讚留言⋯⋯";

	$string = "
2013/10/16 鎖鍊蛇-蛻 a-1
台東縣池上鄉福文村
下午拍完鎖鍊蛇屍體後，遇見捕鬼鼠的獵人，問有沒有見到鎖鍊蛇。沒想到獵人說：半小時前放陷阱時，有見到2隻交配。並且好心的帶我到現場，找了1小時，只有這條蛇蛻。（已撿拾）
緯度:	N 23°7.972' (23°7'58.3\")
經度:	E 121°12.387' (121°12'23.2\")
";

	$string = "
*****此區訊息僅供確認用, 不允許修改亦不會被傳送*****
2013/10/29 23.573172,120.45805
#黑眶蟾蜍#
#黑眶蟾蜍#, *Duttaphrynus melanosticus*陳秀晴 2013/10/29 23.573172,120.45805讚 ·  · 追蹤貼文 · 分享 · 蕭宗熙說讚。Joyce Chen #黑眶蟾蜍# · 讚Joyce Chen #黑眶蟾蜍#, *Duttaphrynus melanosticus* · 讚留言⋯⋯
";

	$string = "
20131016 08:42橫山大山背 東經24º41.174'北緯121º8.355'海拔421m 青蛇89cm

";


	$string = "
Waterr Chen2012/03/16 22:12  宜蘭 圳頭  24/45/9.84, 121/39/50.52 上傳者：邱嘉德讚 ·  · 追蹤貼文 · 分享 ·  Joyce Chen #大頭蛇#, *Boiga kraepelini* · 讚Joyce Chen 24D45M9.84S 121D39M50.52S · 讚留言⋯⋯
";


	$string = "
N24。40’28.8” E121。06’08.2”
";

	$string = "
2013.11.13
149乙縣道約8.4K
23.605127，120.672304
#鼬獾#
同一條線道，同一處有兩隻？"
;

	$string = "
發現時間：2013-08-26 16:58 地點：花蓮縣掃叭頂一路 狀態：身體完整 後續處理：當天寄到特有生物保育中心  抱歉如此晚更新！
徒步環島辛苦了 (y)
標本號RN1171
採集號JHW20130826, 感恩^^
";

	$string = "
亞洲家鼠 Rattus tanezumi (?) 2013.2.5. 恆春後灣路 (萬里桐←→後灣) 22.011257,120.694299  已採集
謝謝~ 對這些小朋友的分類真的不甚了解阿 ^^\"
那個...已採集不用另外加井號啦~~
OKOK 那是我自己整理資料的格式(直接複製貼上 ^^\")，已更正
什麼是「已採集不用另外加井號」？看不懂
採集號CWL20130205-3, 感恩
不好意思！這個我原來從照片看像溝鼠的動物，在收到屍體後要修正看法。整體來看，牠最像亞洲家鼠，尤其是腹面是黃棕色。不過與一般的亞洲家鼠似乎有點不同，不知是否是恆春半島的變異，先暫訂。
請問一下與一般亞洲家鼠的變異在哪裡呢? 謝謝
初步看後腳掌有點偏白，胸口有一乳白色斑塊
腳掌會不會是因為死亡所產生的變異？胸口白斑就真的沒注意了 (撿了就閃超怕自己也變肉乾 ^^\")
腳掌是新鮮時就偏白，一般而言，後腳掌白是溝鼠特徵，這個體腳背有一點黑毛，乍看相溝鼠，胸口乳白斑也許只是個體變異，但不是白化的白，而像是刺鼠腹面的乳白，有機會會試著驗驗看牠的DNA
感覺是份有意義的標本啊 :P
";

	if (empty($argv[1])) {
		$string = "無";
	}
}

$string = str_replace("*****此區訊息僅供確認用, 不允許修改亦不會被傳送*****", "", $string);


if (!empty($_REQUEST['group'])) {
	$group = $_REQUEST['group'];
}
else {
	$group = 238918712815615;
}

#include_once "../includes/similar.inc.php";
require_once $dir_api."/../lib/webbots/LIB_parse.php";
require_once $dir_api."/../classes/class.extractCommonNames.php";

/* Init */
$tagged = false;
$ner = new simpleNER ();

# uncomment next line to reset dict data
# $ner->loadSpeciesCommonNamesDict("dict_wo_sort.txt");

$ner->loadC2S();
$ner->loadDefaultDict();


/* Extract */

/*
$ner->extractSpeciesCommonNames($string);
$context = strip_tags(@$context);
$ner->extractSpeciesCommonNamesAbbrv($string, $context);
*/

function extractRKNames ($string, $type) {
	global $argv;
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



#$names = extractCommonNames($dict, $dict_replacement, $string);
#$names = extractRKNames($string, "SN");
#var_dump($names);


$string = str_replace("臺", "台", $string);
$full_thread_string = $string;

$names_matched_in_post = '';
$names_guessed_in_post = '';

$string2 = $string;

if (preg_match('/177883715557195/', $group)) {
	$confirmed_cn = implode("|", extractMothNames($string, "CN"));
	$confirmed_sn = implode("|", extractMothNames($string, "SN"));
}
//else if (preg_match('/238918712815615/', $group)) {
else {
	$confirmed_cn = implode("|", extractRKNames($string, "CN"));
	$confirmed_sn = implode("|", extractRKNames($string, "SN"));
	while (((!preg_match('/^[a-zA-Z][a-z]/', $confirmed_sn))||(preg_match('/[\@\\\?\%\*\/\_\^\$\+\=\:\<\>]/', $confirmed_sn)))&&(!empty($confirmed_sn))) {
		$string_frags = explode("*", $string2, 2);
		$string2 = $string_frags[1];
		$confirmed_sn = implode("|", extractRKNames($string2, "SN"));
	}
}

$tmp_string = $string;
$extra = extractExtra($tmp_string);
$xy = extract_xy($tmp_string);

$ner->extractSpeciesCommonNames($string);
$names_matched_in_post = trim($ner->combFullMatch, "|");

// 如果match不為空且存在分數9999, 如果confirmed_cn不包含於match裡面那就說不過去惹
if (!empty($names_matched_in_post)&&(!empty($confirmed_cn))) {
	$ccns = explode("|", $confirmed_cn);
	$nmips = explode("|", $names_matched_in_post);
	$new_confirmed_cn = array();
	foreach ($ccns as $ccn) {
		if (in_array(($ccn."(9999)"), $nmips)) {
			$new_confirmed_cn[] = $ccn;
		}
	}
	if ((strpos($names_matched_in_post, "(9999)")!==false)||(count($ccns)==1)) {
#		$confirmed_cn = implode("|", $new_confirmed_cn);
#		var_dump(56);
	}
}
else if (empty($names_matched_in_post)&&(!empty($confirmed_cn))) {
	// TODO
	// 若confirmed common name不只一組的話, 這邊是否要改寫?
	$names_matched_in_post = $confirmed_cn . "(9999)";
}


$date = '';
$ed = extractDate($string);
if (!empty($ed)) {
	if (!$ed['hasYear']) {
		$year = date('Y', time(true));
		$date = $year . '-' . $ed['date'];
		if (strtotime($date) > time(true)) {
			$date = '';
		}
	}
	else {
		$date = $ed['date'];
	}
}

$ner->extractSpeciesCommonNamesAbbrv($string);
$names_guessed_in_post = $ner->combAbbrvMatch;

$ccns = explode("|", @trim($confirmed_cn, " |"));
$csns = explode("|", @trim($confirmed_sn, " |"));

if (!empty($ccns)||!empty($csns)) {
	$tagged = true;
	if (!empty($ccns)) {
		$ccns = array_unique($ccns);
	}
	if (!empty($csns)) {
		$csns = array_unique($csns);
	}
}

if (((count($ccns)==1)&&($ccns[0]==""))&&((count($csns)==1)&&($csns[0]==""))) {
	$tagged = false;
}


$mcns = explode("|", $names_matched_in_post);

$ret_match = array();
if (!empty($ccns)) {
	foreach ($ccns as $ccn_key => $ccn) {
		if (!empty($ccn)||!empty($csns[$ccn_key])) {
			if (!empty($csns[$ccn_key])) {
				@$ret_match[$ccn][$csns[$ccn_key]] = true;
			}
			else if (empty($csns[$ccn_key])) {
				$ccn_replaced = str_replace(array("台灣"), array("臺灣"), $ccn);
				$url = "http://140.109.28.72/solr/another/select?fl=is_accepted_name,name_clean&wt=json&qf=name_zhtw&qf=common_name&fq=rank:species&fq=is_accepted_name:yes&rows=1&q=" . urlencode($ccn . " " . $ccn_replaced);
				$res = json_decode(file_get_contents($url));
				if (!empty($res->response->docs)) {
					foreach ($res->response->docs as $r) {
						$sciname = $res->response->docs[0]->name_clean;
						@$ret_match[$ccn][$sciname] = true;
					}
				}
				else {
					@$ret_match[$ccn][''] = true;
				}
			}
		}
	}
}


if (empty($ret_match)&&!empty($mcns)) {
	foreach ($mcns as $mcn) {
		$msns = @$ner->c2s[$mcn];
		if (!empty($msns)) {
			foreach ($msns as $msn) {
				if (!empty($mcn)&&!empty($msn)) {
					$url = "http://140.109.28.72/solr/another/select?fl=is_accepted_name,name_clean&wt=json&qf=name_code&q=" . urlencode($msn);
					$res = json_decode(file_get_contents($url));
					if (!empty($res->response->docs)) {
						$sciname = $res->response->docs[0]->name_clean;
						$ret_match[$mcn][$sciname] = true;
					}
					else {
						$ret_match[$mcn][''] = true;
					}
				}
			}
		}
	}
}

$ret_match_tmp = $ret_match;
$ret_match = array();
$i = 0;
if (!empty($ret_match_tmp)) {
	foreach ($ret_match_tmp as $cn => $sns_tmp) {
		foreach ($sns_tmp as $sn => $dummy) {
			if (empty($cn)&&!empty($sn)) {
				$url = "http://140.109.28.72/solr/another/select?fq=".urlencode("rank:species")."&fl=is_accepted_name,name_zhtw&wt=json&q=" . urlencode($sn);
				$res = json_decode(file_get_contents($url));

if (!empty($argv[1])) {
	var_dump($sn);
}
				foreach ($res->response->docs as $r) {
					if (!empty($r->name_zhtw)) {
						$cn = $r->name_zhtw;
						break;
					}
					break;
				}
			}
			$ret_match[$i]['cn'] = $cn;
			$ret_match[$i]['sn'] = $sn;
			$i++;
		}
	}
}


$res = array ('match'=>$ret_match, 'guess'=>$names_guessed_in_post, 'extra'=>$extra, 'date'=>$date, 'xy'=>$xy, 'tagged'=>$tagged, 'auth' => $auth);
echo json_encode($res);



?>
