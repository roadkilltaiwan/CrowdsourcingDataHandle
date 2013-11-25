<?php
/*
  Realtime Extract placenames and shorten placenames.
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
ini_set ("memory_limit", "256M");
error_reporting(0);

$evl = true;
$fastMode = false;

if (!empty($_REQUEST['fastMode'])) {
	if ($_REQUEST['fastMode'] == 1) {
		$fastMode = true;
	}
}

require_once $dir_api."/../classes/class.extractCommonNames.php";
$cfd = 0.5;
if (!empty($_REQUEST['text'])) {
	$string = $_REQUEST['text'];
	$api = true;
	$context = @$_REQUEST['context'];
	$postId = @$_REQUEST['postId'];
	$cfd = @$_REQUEST['confidence'];
}
else if (!empty($_REQUEST['url'])) {
	$url = $_REQUEST['url'];
	$string = file_get_contents($url);
	$api = true;
}

if (!empty($_REQUEST['autocomplete'])) {
	$string = $_REQUEST['term'];
	$fastMode = true;
	$api = true;

	$msg = trim($string, " \r\n");
	preg_match_all('/<tgn:[0123456789]+>/', $msg, $matches);
	if (!empty($matches[0])) {
		$last = array_pop($matches[0]);
		$msg_tmp = explode($last, $msg);
		$msg = array_pop($msg_tmp);
	}
	$msg_tmp = explode(".", $msg);
	$msg = array_pop($msg_tmp);
	$msg_tmp = explode(" ", $msg);
	$msg = array_pop($msg_tmp);
	$msg_tmp = explode("\n", $msg);
	$msg = array_pop($msg_tmp);

	$msg_orig = $msg;

	$string = mb_substr($msg_orig, -30, mb_strlen($string, 'utf-8'), 'utf-8');
}


if (@!$api) {
	$string = "麻鷺麻雀";
	$string = "在南投*國道*8號路邊撿到的蛇蛇, 好像是#龜殼花#, 請問大大我該如何處理?";
	$string = "柴棺龜殼花什的... 好像朵花 <= 有夠蠢的例子 <= 失敗";
	$string = "史丹吉, 你家的那隻小雨蛙呢? <= 一樣蠢";
	$string = "紅紋鳳蝶小雨蛙5566龜殼花擬龜殼花台灣鳳";
	$string = "
台灣低海拔植物 
　　在台灣多山的環境中, 大致以海拔1000公尺來區分山地及低海拔平野, 本館植物園展示的台灣各地低海拔植物社會, 包括了以榕樹及楠木為主的熱帶植物社會, 和以楠木及櫧樹為主的亞熱帶植物社會。
台灣的熱帶植物 
　　就台灣植物起源的觀點來說, 除了恒春半島及蘭嶼綠島為熱帶起源的植物外, 台灣其他地區均為北溫帶起源之植物。本館植物園展示的熱帶植物包括有季風雨林區、隆起珊瑚礁區及海岸林區等恒春半島的三種植物社會, 以及蘭嶼生態區等。而海岸山脈的台東蘇鐵區, 因受黑潮的影響, 也呈現類似熱帶起源的植物社會。很多熱帶起源的植物, 台灣就成為該物種分布的北界, 如象牙樹、楓港柿、……… 等, 因族群較小, 在台灣均被列為稀有植物。又恒春半島的西邊, 因受冬季乾旱的影響, 呈現以黃荊為主的落葉植物社會。

榕樹植物 
　　一般人通稱的榕樹, 在植物學上其實是榕屬的眾多種類的統稱; 而在盆景上運用的榕樹, 包括有各式各樣的正榕, 如厚葉榕 (正榕的變種)、金門榕、瓜子葉榕 (二者均為正榕的栽培品種); 以及其他榕屬的植物如雀榕、白肉榕、鵝鑾鼻蔓榕、山豬枷 (毛榕)、……等, 大家均混稱榕樹。

台東蘇鐵 
　　台東蘇鐵是台灣特有種植物, 只有分布在台東縣海岸山脈及鹿野溪一帶, 由於生育地大都已被開發, 且在保留區設置之前曾被濫採, 目前面臨嚴重瀕臨絕滅的階段。

北部低海拔植物 
　　北部低海拔地區因受東北季風的影響, 冬季呈現濕冷的氣候, 因此在此地平野, 全年恒濕, 樹木週年常綠, 且因冬季溫度較低, 故缺乏熱帶的植物社會, 而以楠木及櫧樹為主的亞熱帶植物社會為主。

中部低海拔植物 
　　在中部多山的環境下, 一些長在山壁的樹木紛紛以落葉的策略, 適應冬季的乾旱及低溫, 故到了秋冬之際, 紅葉繽紛就成了本地的一大特色, 其中以黃連木、楓香、和各種楓樹最為出色。 由於冬季的乾旱, 在南部地區除了在潮濕的溪谷可以看到以榕樹為主的熱帶植物社會外, 大多地區呈現出稀樹草地的狀況, 若干植物為適應乾旱, 分別發展成小葉 (如福建茶) 及多刺 (如刺裸實) 的型態。

南部低海拔植物 
　　由於冬季的乾旱, 在南部地區除了在潮濕的溪谷可以看到以榕樹為主的熱帶植物社會外, 大多地區呈現出稀樹草地的狀況, 若干植物為適應乾旱, 分別發展成小葉 (如福建茶) 及多刺 (如刺裸實) 的型態。

東部低海拔植物 
　　東部陡峭的地形, 再加上石灰岩地質, 所形成生育環境, 孕育出特殊的岩壁植物社會景觀。其中包括如細葉蚊母樹等多種石灰岩地區的指標植物及太魯閣櫟等稀有植物。

";
#	$string = "在南投*國道*8號路邊撿到的蛇蛇, 好像是#龜殼花#, 請問大大我該如何處理? 暖暖";
#$string = "本館植物園";
#$string = '2013年6月8日,%20上午%2009:33:02%20北橫56.3K%20史丹吉氏斜鱗蛇';
#$string = '20130621上午9點10分左右新烏路新店往烏來方向約10.5公里處過山刀';
$string = '社團四大宗旨：改善路死－－藉由大量的資料蒐集，分析路死嚴重的路段、季節及種類，改善道路設計或增加廊道、圍籬等設施，以減少野生動物因國內道路開發與車流量日漸增加而造成的直接死亡推廣公民科學－－推廣公民參與科學調查的概念，特別是國內目前最缺乏的爬行類公民科學調查。生態保育與環境教育－－藉由民眾參與「路死動物」的記錄活動，推廣並提升國人生態保育觀念。尊重生命與標本典藏－－將路死動物個體製成標本典藏，提供學術研究、教學或展示之用，藉此減少因學術研究所需而造成的活體犧牲。目前哺乳類、鳥類、爬行類及甲殼類的標本全部都收協助寄送標本方式如下：使用到付的方式，運費由我們來出本社團主要以記錄道路上死亡之野生動物為主路旁發現活體動物也歡迎記錄，因為會出現在路旁，表示未來也有可能遭遇車輛撞擊，歡迎各位朋友在野外遇見路上壓死的動物時，能拍下照片並po上來分享，照片說明欄請務必寫下發現日期-西元年月日when及地點where，因為資料完整的記錄，才具有後續研究價值。地點描述以：十進位的經緯度為最佳傳統的度分秒為６０進位, 例如底下的連結... 昇天鳳蝶厚殼仔
由特生中心(@南投)協助五五六六還有硬是要湊超過五百字不然尬起輸贏來如果差距只在咫尺就不是說聲瞎瞎就可以當沒發生過的這樣子你懂ㄇ小子幾歲安安注哪想見面ㄇㄎㄎㄎ南化烏山';

$string = "去台南烏山看厚殼仔就跟在花蓮太魯閣櫟雞蛋裡挑山羌一樣你這個死白花鬼";
$string = "大花咸豐 青帶 寬青帶";
$string = "大花咸豐 長尾水青 長尾";
$string = "長尾水青 長尾";
$string = "羅東林管處";
$string = "東海附中";
$string = "屏東屏科大";
$string = "北科大 北市動檢所";
$string = "太平山烏山";$fastMode=true;
$string = "龍崎烏山";$fastMode=true;
$string = "時間：20130729
地點：延平林道15K
描述：只剩皮毛的山羌，內臟與肉已全被啃食殆盡，只剩三隻腳，少一隻後腿是原本就殘缺或被獵食者咬走了無法得知。應為黃鼠狼獵捕，眼睛水亮，殘餘皮毛尚屬新鮮，初判剛死亡不久。
處置：因狂犬病之故，不予採集，僅移至路邊草叢。";
#$string = '社團四大宗旨：改善路死藉由大量的資料蒐集，分析路死嚴重的路段、季節及種類原能會，改善道路設計或增加廊道';
#$string = '20130621上午9點10分左右南投方向約10.5公里特生中心亞忽';
}
else {
	$encoding = mb_detect_encoding ($string, array("ASCII","UTF-8","BIG5","GB2312","GBK"));
	if ($encoding != 'UTF-8') {
		$string = iconv($encoding, 'UTF-8', $string);
	}
}

$string = str_replace("臺", "台", $string);

$ner = new simpleNER ();
$ner->withContext = $_REQUEST['withContext'];

#return;
$ner->extractPlaceNames($string, $fastMode);
#echo "<xmp>";
#echo "extract time:" . (microtime(true) - $stime) . "\n";
#var_dump($ner->extractedPlaceNames);
$p2g = $ner->p2g;
$p2h = $ner->p2h;
$pids = $ner->pids;
$p2coord = $ner->p2coord;
#var_dump($p2g);
$exists_pn = array();
foreach ($ner->extractedPlaceNames as $m => $pns) {

	$pid = '';
	$gid = '';
	$nameOriginal = $m;
	$name = substr($m, 4);
	$mLen = mb_strlen($name, 'utf-8');

	$fids = array(); // 同名異地的好多筆完整id array
	$fid_idx = 0;
	$maxScore = 0;
	while (list($full, $score) = each($pns)) {
		$rscore = round($score, 4);
		if ($rscore > $maxScore) $maxScore = $rscore;

		$pid = @$pids[$full];
		if (empty($pid)) $pid[0] = -1; // 意指不存在於資料庫

		foreach ($pid as $idx => $p) {
			$bonus_rscore = $rscore;
			if ($p != -1) {
				$uri = "http://pomelo.iis.sinica.edu.tw:2020/resource/geoname_tw/" . $p;
			}
			else {
				$uri = "";
			}
			$uri_tmp = trim($uri, '/');
			$about = basename($uri_tmp);

			if ($score >= $cfd) {
				$fids[$fid_idx]['@uri'] = $uri;
				$fids[$fid_idx]['@label'] = $full;
				$fids[$fid_idx]['@types'] = "Schema:Place, DBpedia:Place";
				$fids[$fid_idx]['@county'] = $p2h[$p]['county'];
				$fcounty = mb_substr($p2h[$p]['county'], 0, mb_strlen($p2h[$p]['county'], 'utf-8') - 1, 'utf-8');
				$fids[$fid_idx]['@town'] = $p2h[$p]['town'];
				$ftown = mb_substr($p2h[$p]['town'], 0, mb_strlen($p2h[$p]['town'], 'utf-8') - 1, 'utf-8');
				if (@!$ner->context[$fcounty]&&@$ner->context[$fcounty]!==0) {
#					$rscore += ((1.1 - $rscore) / 20);
				}
				else {
					$bonus_rscore += ((1.1 - $rscore) / 20);
				}
				if (@!$ner->context[$ftown]&&@$ner->context[$ftown]!==0) {
#					$rscore += ((1.1 - $rscore) / 20);
				}
				else {
					$bonus_rscore += ((1.1 - $bonus_rscore) / 20);
				}
				$fids[$fid_idx]['@finalScore'] = $bonus_rscore;
				$fids[$fid_idx]['@x'] = $p2coord[$p]['x'];
				$fids[$fid_idx]['@y'] = $p2coord[$p]['y'];
				$fid_idx++;
			}
		}
		continue;
	}

#	$poss = array_keys($posSet[$nameOriginal]);
#	foreach ($poss as $pos) {

	if (!empty($fids)) {
		foreach($fids as $key => $fid) {
			$fscore[$key] = $fid['@finalScore'];
		}
		array_multisort($fscore, SORT_DESC, $fids);
	}
	if ($evl) {
		$fids = array_slice($fids, 0, 5, true);
	}

	$offset = 0;
	$pos = mb_strpos($string, $name, $offset, 'utf-8');
	while ($pos !== false) {

		$o = false;

		if (!$evl) {
			for ($si = $pos; $si < $pos + $mLen; $si++) {
				if (@$exists[$si]) $o = true;
			}
			if (!$o) {
				$surfaceForm['@name'] = $name;
				$surfaceForm['@offset'] = $pos;
				$surfaceForm['resource'] = $fids;

				if (!empty($fids)) {
					for ($si = $pos; $si < $pos + $mLen; $si++) {
						$exists[$si] = true;
					}
					$surfaceForms[] = $surfaceForm;
				}

			}
		}
		else {
			for ($si = $pos; $si < $pos + $mLen; $si++) {
# 暫時拿掉用做測試
#				if (@$exists_pn[$si]) $o = true;
			}

			if (!$o) {
				$surfaceForm['@name'] = $name;
				$surfaceForm['@offset'] = $pos;
				$surfaceForm['resource'] = $fids;

				if (!empty($fids)) {
					for ($si = $pos; $si < $pos + $mLen; $si++) {
						$exists_pn[$si] = true;
					}
					$surfaceForms_pn[] = $surfaceForm;
				}

			}
		}

		$offset = $pos + $mLen;
		$pos = mb_strpos($string, $name, $offset, 'utf-8');
	}

}


$ret['annotation']['@text'] = $string;
$ret['annotation']['surfaceForm_pn'] = @$surfaceForms_pn;

#var_dump(md5(json_encode($ret)));

echo json_encode($ret);


?>
