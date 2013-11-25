<?php

/*
  A class for species' and placenames' named entity recognition
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


ini_set ("memory_limit", "512M");
#include_once "../../vgd/includes/webbots/LIB_parse.php";

class simpleNER {

	function __construct () {
		$this->prefixLengthMax = 4;
		$this->prefixLengthMin = 2;
		$this->scoreThreshold = 0.3;
		$this->_cleanDictAbbrv();
	}

	private function _sortDictByLen($dictArr, $order='desc') {
		foreach ($dictArr as $name) {
			$dictLenArr[$name] = strlen($name);
		}
		if ($order=='desc') {
			arsort($dictLenArr);
		}
		else if ($order=='asc') {
			asort($dictLenArr);
		}
		else {
			arsort($dictLenArr);
		}
		return array_keys($dictLenArr);
	}

	function loadDefaultDict () {
		$this->_disk2Cache ();
	}

	function loadSpeciesCommonNamesDict ($dictFile) {
		$this->loadCSVDict($dictFile, $genExtCallbacks=array(array($this, '_genSpeciesCommonNameAbbrv')));
	}


	private function _cleanDictAbbrv () {
		$this->c2s = array();
		for ($i=$this->prefixLengthMin; $i<= $this->prefixLengthMax; $i++) {
			$this->{"prefix"."$i"} = array();
			$this->{"prefix"."$i"."_replacement"} = array();
			$this->{"prefix"."$i"."_full"} = array();
		}
	}

	private function _genSpeciesCommonNameAbbrv ($name) {
		for ($i=$this->prefixLengthMin; $i<=$this->prefixLengthMax; $i++) {
			if (mb_strlen($name, 'utf-8') >= $i) {
				$prefix = mb_substr($name, 0, $i, "utf-8");
				$this->{"prefix"."$i"}[$prefix] = $prefix;
				$this->{"prefix"."$i"."_replacement"}[$prefix] = "<@".$prefix."@>";
				$this->{"prefix"."$i"."_full"}[$prefix][] = $name;
			}
		}
#		$this->c2s[$val][] = $row['name_code'];
	}

	function loadC2S () {
		$dir_classes = implode("/", explode("/", realpath(__FILE__), -1));
		$c2sMaps = explode("\n",file_get_contents($dir_classes . "/../dict/c2s.csv"));
		foreach ($c2sMaps as $c2sMap) {

			if (empty($c2sMap)) continue;

			$f = explode(",", $c2sMap);
			$nc = $f[0];
			$val = $f[1];
			if (strpos($val, "(")!==false) {
				$vals = explode("(", $val);
				foreach ($vals as $v) {
					$v = trim($v, "()");
					if (mb_strlen($v, "utf-8")>1) {
						$this->c2s[$v][$nc] = $nc;
					}
				}
			}
			else {
				$this->c2s[$val][$nc] = $nc;
			}
		}
	}

	function indexPlaceNames ($update=false, $reset=false) {
		$dir_classes = implode("/", explode("/", realpath(__FILE__), -1));

		if ($reset===true) {
			$this->regexPatterns = array();
			$this->p2g = array();
			$this->p2h = array();
			$this->pids = array();
			$this->urp = array();
			$this->urp_replacement = array();
			$this->upperRegexPatterns = array();
			$update = true;
			$this->reindexPlaceNames = true;
		}

		if (@$this->reindexPlaceNames===false) return;

		if (file_exists($dir_classes."/../cache/placeNames/regexPatterns")) {

			//* 新步驟的一時權宜
			$place_db = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
			mysql_select_db(DB_DATABASE, $place_db);
			$sql = "set names 'utf8';";
			mysql_query($sql);
			$sql = "select * from abbrv";
			$res = mysql_query($sql);
			while ($row = mysql_fetch_assoc($res)) {
				$this->abbrv[] = $row['abbrv'];
				$this->abbrv_replacement[] = '<@'.$row['abbrv'].'@>';
				$this->abbrv2full[$row['abbrv']][] = $row['full'];
			}
			//*/

			if ($this->fastMode===true) {
				$this->regexPatterns = unserialize(file_get_contents($dir_classes."/../cache/placeNames/fastRegexPatterns"));
			}
			else {
				$this->regexPatterns = unserialize(file_get_contents($dir_classes."/../cache/placeNames/regexPatterns"));
			}
			$this->p2coord = unserialize(file_get_contents($dir_classes."/../cache/placeNames/p2coord"));
			$this->p2g = unserialize(file_get_contents($dir_classes."/../cache/placeNames/p2g"));
			$this->p2h = unserialize(file_get_contents($dir_classes."/../cache/placeNames/p2h"));
			$this->pids = unserialize(file_get_contents($dir_classes."/../cache/placeNames/pids"));
			$this->urp = unserialize(file_get_contents($dir_classes."/../cache/placeNames/urp"));
			$this->urp_replacement = unserialize(file_get_contents($dir_classes."/../cache/placeNames/urp_replacement"));
			$tmp = array_keys($this->regexPatterns);
			$this->upperRegexPatterns = array_flip($tmp);

			foreach ($tmp as $o => $r) {
				$this->urp[] = $r;
				$this->urp_replacement[] = '<@' . $r . '@>';
			}

			$this->reindexPlaceNames = false;
			if (!$update) return;
		}

		$std = '、';
		$pndata = file($dir_classes."/../dict/placename_tw3.txt");
		#$pndata = file("placename_tw3_geonames.txt");
		foreach ($pndata as $ln => $l) {
#			echo "$ln\r";
			$l = trim($l, "\r\n");
			$l = str_replace(' ', '', $l);

			$ls = explode("\t", $l);
			$pid = (int) $ls[0];
			$kind = $ls[1];
			$pn = trim($ls[2],"\r\n()'\"");
			$gid = $ls[12];

			if (!empty($gid)) {
				$this->p2g[$pn][] = $gid;
			}

			if (!empty($pid)) {
				$this->pids[$pn][] = $pid;
				$this->p2h[$pid]['county'] = $ls[5];
				$this->p2h[$pid]['town'] = $ls[6];
			}

			$cLen = mb_strlen($ls[5], 'utf-8');
			$tLen = mb_strlen($ls[6], 'utf-8');

			if ($cLen > 2) {
				$county_abbrv = mb_substr($ls[5], 0, $cLen - 1, 'utf-8');
			}
			else {
				$county_abbrv = $ls[5];
			}
			if ($tLen > 2) {
				$town_abbrv = mb_substr($ls[6], 0, $tLen - 1, 'utf-8');
			}
			else {
				$town_abbrv = $ls[6];
			}

			if (!empty($ls[7])) {
				$this->p2coord[$pid]['x'] = $ls[7];
			}
			if (!empty($ls[8])) {
				$this->p2coord[$pid]['y'] = $ls[8];
			}
#			$this->upperRegexPatterns[$county_abbrv] = true;
#			$this->upperRegexPatterns[$town_abbrv] = true;

			$body = '';
			if (empty($this->regexPatterns[$county_abbrv][$pn])||empty($this->regexPatterns[$town_abbrv][$pn])) {
	    			$body_remains = preg_split('/(?<!^)(?!$)/u', $pn);
				foreach ($body_remains as $r) {
					if (ord($r[0]) > ord($std[0])) {
						$body .= $r . '?';
					}
				}
			}
			if (!empty($body)){
				$fastpn = str_replace(array("(",")",",",".","?"," ","　",":",";","'","\"","*","$","!","^","_","[","]","{","}","\\","/","|"), "", $pn);
				$fastRegexPattern = "($fastpn)";
				if (mb_strlen($fastpn)>1) {
					$this->fastRegexPatterns[$county_abbrv][$pn] = $fastRegexPattern;
					$this->fastRegexPatterns[$town_abbrv][$pn] = $fastRegexPattern;
				}
				$this->fastRegexPatterns[$county_abbrv][$county_abbrv] = '('.$county_abbrv.')';
				$this->fastRegexPatterns[$town_abbrv][$town_abbrv] = '('.$town_abbrv.')';

				$first = $body_remains[0];
				$fr = "[^$first]*";
				$regexPattern = "$fr($body)";
				$this->regexPatterns[$county_abbrv][$pn] = $regexPattern;
				$this->regexPatterns[$town_abbrv][$pn] = $regexPattern;
				$this->regexPatterns[$county_abbrv][$county_abbrv] = '('.$county_abbrv.')';
				$this->regexPatterns[$town_abbrv][$town_abbrv] = '('.$town_abbrv.')';
			}
#			$suppIndex = "-1" . "\t" . "地名" ."\t" . $ls[5] . "\t" . "none" . "\t" . $ls[5] . "\t" . $ls[6] . "\n";
#			$suppIndex2 = "-1" . "\t" . "地名" ."\t" . $ls[6] . "\t" . "none" . "\t" . $ls[5] . "\t" . $ls[6] . "\n";
#			$supp
		}

		$tmp = array_keys($this->regexPatterns);

		foreach ($tmp as $o => $r) {
			$tmp2[$r] = strlen($r);
		}

		arsort($tmp2);
		$tmp3 = array_keys($tmp2);

		$this->upperRegexPatterns = array_flip($tmp3);

		foreach ($tmp3 as $o => $r) {
			$this->urp[] = $r;
			$this->urp_replacement[] = '<@' . $r . '@>';
		}

		file_put_contents($dir_classes."/../cache/placeNames/fastRegexPatterns", serialize($this->fastRegexPatterns));
		file_put_contents($dir_classes."/../cache/placeNames/regexPatterns", serialize($this->regexPatterns));
		file_put_contents($dir_classes."/../cache/placeNames/urp", serialize($this->urp));
		file_put_contents($dir_classes."/../cache/placeNames/urp_replacement", serialize($this->urp_replacement));
		file_put_contents($dir_classes."/../cache/placeNames/p2g", serialize($this->p2g));
		file_put_contents($dir_classes."/../cache/placeNames/p2h", serialize($this->p2h));
		file_put_contents($dir_classes."/../cache/placeNames/pids", serialize($this->pids));
		file_put_contents($dir_classes."/../cache/placeNames/p2coord", serialize($this->p2coord));

	}


	function extractPlaceNames ($s, $fastMode=false) {
		if (!isset($this->withContext)) {
			$this->withContext = false;
		}
		$this->fastMode = $fastMode;
		$firstXchar = 5;
		$hashS = md5($s);
		$sLen = mb_strlen($s, 'utf-8');
#		echo "Start\n";

#		mb_regex_set_options('m');
		mb_regex_encoding("UTF-8");

		$this->indexPlaceNames();
#		$this->indexPlaceNames(true, true);

		$std = '、';

		$context = array();
#		$stime = microtime(true);
		$context = $this->extractNames($this->urp, $this->urp_replacement, $s);
		$context = array_flip($context);
#		echo microtime(true) - $stime . "\n";


		$abbrvMatchTmp = array();
		$abbrvMatchTmp = $this->extractNames($this->abbrv, $this->abbrv_replacement, $s);
		foreach ($abbrvMatchTmp as $ab) {
			$key = sprintf('%04d', mb_strlen($ab, 'utf-8')) . $ab;
			foreach ($this->abbrv2full[$ab] as $pn) {
#				We don't do this at First run.
#				$abbrvMatch[$key][$pn] = 1;
			}
		}


		if (empty($context)) {
			$context = $this->upperRegexPatterns;
			$this->context = array();
		}
		else {
			$this->context = $context;
		}

		if ($this->fastMode===true) $context = $this->upperRegexPatterns;

		foreach ($this->regexPatterns as $ctxt => $pnPatterns) {
			if (@!$context[$ctxt]&&@$context[$ctxt]!==0) {
				if ($this->withContext == 'true') {
					continue;
				}
			}
			foreach ($pnPatterns as $pn => $regexPattern) {

				$s2 = $s;
				if ($this->fastMode===true) {
					if (mb_ereg($regexPattern, $s2, $m)) {
						if (!empty($m[1])) {
#							var_dump($regexPattern);
							$mLen = mb_strlen($m[1], 'utf-8');
							$key = sprintf('%04d', $mLen) . $m[1];
							$abbrvMatch[$key][$pn] = 1;
						}
						continue;
					}
					else {
						continue;
					}
				}

				$body_remains = preg_split('/(?<!^)(?!$)/u', $pn);
				$bsize = count($body_remains);

				//** old but better way
				$b = 0;
				$offset = 0;

				while (mb_ereg($regexPattern, $s2, $m)) {
					/*
					if (strpos($regexPattern, "動")!==false&&strpos($regexPattern, "檢")!==false&&strpos($regexPattern, "所")!==false) {
						echo "5566\n";
						var_dump($s2);
						var_dump($regexPattern);
						var_dump($m);
					}
					//*/

	#				$b = 0;
					$advanced = false;
					while ($m[1]===false) {
						$advanced = true;;
						$b++;
						// 可以設成參數 洗洗睡了
						if ($b > $bsize - 2) break;
						if ($b < $bsize - 2) {
							if ($b == $firstXchar) {
								if ($bsize - 5 > $firstXchar) $b = $bsize - 5;
							}
						}
						$body = '';
						for ($bi = $b; $bi < $bsize; $bi++) {
							if (ord($body_remains[$bi][0]) > ord($std[0])) {
								$body .= $body_remains[$bi] . '?';
							}
						}
						if (!empty($body)){
							$first = $body_remains[$b];
							$fr = "[^$first]*";
							$offset = 0;
							$regexPattern = "$fr($body)";

							mb_ereg($regexPattern, $s2, $m);
						}
					}

					if (empty($this->cacheRes[$hashS][$regexPattern][$pn][$m[1]])) {

						/*
						if (strpos($regexPattern, "動")!==false&&strpos($regexPattern, "檢")!==false&&strpos($regexPattern, "所")!==false) {
							echo "7788\n";
							var_dump($s2);
							var_dump($regexPattern);
							var_dump($m[1]);
						}
						//*/
						
						if ($m[1]!==false) {

							if ($advanced) $b--;

							$mPos = mb_strpos($s2, $m[1], $offset, 'utf-8');
#							echo "offset=".$offset."\n";
#							echo "mPos=".$mPos."\n";

							$mLen = mb_strlen($m[1], 'utf-8');
#							$mPos = strpos($s2, $m[1]);

							if ($mPos > 0) {
								$s2 = mb_substr($s2, 0, $mPos, 'utf-8') . mb_substr($s2, $mPos + $mLen, $sLen, 'utf-8');
							}
							else {
								$s2 = mb_substr($s2, $mLen, $sLen, 'utf-8');
							}
#							var_dump('s2');
#							var_dump($s2);
							$offset = $mPos;


#							echo "m1=".$m[1]."\n";
#							echo "s2=".$s2 . "\n";
#							$s2 = str_replace($m[1], '', $s2);
							if ($mLen > 1) {
								$key = sprintf('%04d', $mLen) . $m[1];
								$score = ($mLen / mb_strlen($pn, 'utf-8'));
#								if (strpos($pn, '烏山')!==false) {
#									var_dump(array($regexPattern, $m[1], $pn, $score));
#								}
								if ($score <= 0.1) continue;
								$this->cacheRes[$hashS][$regexPattern][$pn][$m[1]] = $score;
								if (@$abbrvMatch[$key][$pn] < $score) {
									$abbrvMatch[$key][$pn] = $score;
								}
								else {
									continue;
								}
							}
						}
						else {
							break;
						}
					}
					else {
						if ($advanced) $b--;
						foreach ($this->cacheRes[$hashS][$regexPattern][$pn] as $m1 => $score) {

#							if (strpos($pn, '烏山')!==false) {
#								var_dump(array('in cache:', $regexPattern, $m1, $pn, $score));
#							}
							$mPos = @mb_strpos($s2, $m1, $offset, 'utf-8');
							$mPos = @mb_strpos($s2, $m1, 0, 'utf-8');
							$mLen = mb_strlen($m1, 'utf-8');
#							$s2 = str_replace($m1, '', $s2);
							if ($mPos > 0) {
								$s2 = mb_substr($s2, 0, $mPos, 'utf-8') . mb_substr($s2, $mPos + $mLen, $sLen, 'utf-8');
							}
							else {
								$s2 = mb_substr($s2, $mLen, NULL, 'utf-8');
							}
							$offset = $mPos;

							$key = sprintf('%04d', $mLen) . $m1;
							if (@$abbrvMatch[$key][$pn] < $score) {
								$abbrvMatch[$key][$pn] = $score;
							}
						}
					}
				}
			//*/
			}
		}
		if (!empty($abbrvMatch)) {
			krsort($abbrvMatch);
			foreach ($abbrvMatch as $abbrv => &$o) {
				arsort($o);
			}
			$this->extractedPlaceNames = $abbrvMatch;
		}
		else {
			$this->extractedPlaceNames = array();
		}
	}


	// 待改
	function loadCSVDict($dictFile, $genExtCallbacks=array()) {

		$names = file($dictFile);
		$names = $this->_sortDictByLen($names);

		$this->dict = array();
		$this->dict_replacement = array();


		$idx = 0;

		while (list($line_no, $name) = each($names)) {
			$val = trim($name, " ,.'\"\r\n");

			if (strpos($val, "(")!==false) {
				$vals = explode("(", $val);
				foreach ($vals as $v) {
					$v = trim($v, " ()");
					if (mb_strlen($v, "utf-8")>1) {
						$this->dict[$v] = $v;
						$this->dict_replacement[$v] = "<@".$v."@>";
						//  產生外掛資料的地方
						if (!empty($genExtCallbacks)) {
							foreach ($genExtCallbacks as $func) {
								call_user_func($func, $v);
							}
						}
					}
				}
			}
			else {
				//  產生外掛資料的地方
				if (!empty($genExtCallbacks)) {
					foreach ($genExtCallbacks as $func) {
						call_user_func($func, $val);
					}
				}
				$this->dict[$val] = $val;
				$this->dict_replacement[$val] = "<@".$val."@>";
			}
			$idx++;
		}
		#var_dump($this->prefix2_full['紅竹']);
		$this->_cache2Disk();

	}

	private function _cache2Disk ($param_root = NULL) {
		if (empty($param_root)) {
			$dir_classes = implode("/", explode("/", realpath(__FILE__), -1));
			$root = $dir_classes . "/../cache/speciesCommonNames/";
		}
		else {
			$root = $param_root;
		}
		file_put_contents($root."dict", serialize($this->dict));
		file_put_contents($root."dict_replacement", serialize($this->dict_replacement));
#		file_put_contents("c2s", serialize($this->c2s));

		for ($i=$this->prefixLengthMin; $i<=$this->prefixLengthMax; $i++) {
			file_put_contents($root."prefix".$i, serialize($this->{"prefix".$i}));
			file_put_contents($root."prefix".$i."_replacement", serialize($this->{"prefix".$i."_replacement"}));
			file_put_contents($root."prefix".$i."_full", serialize($this->{"prefix".$i."_full"}));
		}
	}

	private function _disk2Cache ($param_root = NULL) {
		if (empty($param_root)) {
			$dir_classes = implode("/", explode("/", realpath(__FILE__), -1));
			$root = $dir_classes . "/../cache/speciesCommonNames/";
		}
		else {
			$root = $param_root;
		}
		$this->dict = unserialize(file_get_contents($root."dict"));
		$this->dict_replacement = unserialize(file_get_contents($root."dict_replacement"));
#		$this->c2s = unserialize(file_get_contents("c2s"));
		for ($i=$this->prefixLengthMin; $i<=$this->prefixLengthMax; $i++) {
			$this->{"prefix".$i} = unserialize(file_get_contents($root."prefix".$i));
			$this->{"prefix".$i."_replacement"} = unserialize(file_get_contents($root."prefix".$i."_replacement"));
			$this->{"prefix".$i."_full"} = unserialize(file_get_contents($root."prefix".$i."_full"));
		}
	}

	function extractNames ($dict, $dict_replacement, $string) {
		$stime = microtime(true);
		$string_replaced = str_replace($dict, $dict_replacement, $string);

		$all = array();

		$len = strlen($string_replaced);
		$level = 0;
		$extracted = "";
		$oldNames = array();
		for ($i=0; $i<$len; ) {
			if (($string_replaced[$i] == '<')&&(@$string_replaced[$i+1] == '@')) {
				# push
				$level++;
				$i = $i + 2;
			}
			else if (($string_replaced[$i] == '@')&&(@$string_replaced[$i+1] == '>')) {
				#pop
				if (!in_array($extracted, $oldNames)) {
					$all[$level][$extracted] = true;
					$oldNames[] = $extracted;
				}
				$level--;
				$i = $i + 2;
			}
			else {
				if ($level != 0) {
					$extracted .= $string_replaced[$i];
				}
				$i++;
			}

			if ($level == 0) {
				$extracted = "";
			}
		}
		ksort($all);
		$before = "";
		$names = array();
		foreach ($all as $a) {
			foreach ($a as $b => $dummy) {
				if (strpos($before, "|" . $b) === false) {
					$before .= "|" . $b;
					if (strpos($string, "#".$b."#")!==false) {
						$b .= "(9999)";
					}
					else if (strpos($string, "# ".$b."#")!==false) {
						$b .= "(9999)";
					}
					else if (strpos($string, "#".$b." #")!==false) {
						$b .= "(9999)";
					}
					else if (strpos($string, "# ".$b." #")!==false) {
						$b .= "(9999)";
					}
					$names[] = $b;
				}
			}
		}
#		var_dump($names);
#		echo "extract time:" . (microtime(true) - $stime) . "\n";
		return $names;
	}

	function extractSpeciesCommonNames ($string) {
		$speciesCommonNamesInString = $this->extractNames($this->dict, $this->dict_replacement, $string);
		arsort(@array_unique($speciesCommonNamesInString));

		foreach ($speciesCommonNamesInString as $n_idx => $n) {
			if (preg_match('/\(9999\)/', $n)) {
				$n_for_nc = trim(str_replace("(9999)", "", $n));
			}
			else {
				$n_for_nc = $n;
			}
			$speciesCommonNamesInStringRet[$n]['name'] = $n;
			$speciesCommonNamesInStringRet[$n]['name_code'] = @$this->c2s[$n_for_nc];
		}
		$this->fullMatch = @$speciesCommonNamesInStringRet;
		$this->combFullMatch = "|".implode("|", $speciesCommonNamesInString);
	}

	function extractSpeciesCommonNamesAbbrv ($string, $context = NULL) {
		$this->combAbbrvMatch = "";
		$preStopWords = array (
			'台灣',
			'阿里', '阿里山',
			'玉山',
			'綠島',
			'蘭嶼',
			'鵝鑾', '鵝鑾鼻',
			'台北',
			'桃園',
			'新竹',
			'苗栗',
			'台中',
			'彰化',
			'雲林',
			'嘉義',
			'台南',
			'高雄',
			'屏東',
			'南投',
			'宜蘭',
			'花蓮',
			'台東',
			'澎湖',
			'金門',
			'馬祖',
			'連江',
			'日本',
			'中國',
			'中華',
		);

		$posStopWords = array (
			'蟲',
			'花',
			'帶',
		);

		$this->scores = array();
		if (empty($context)) $context = $string;
		for ($j=$this->prefixLengthMax; $j>=$this->prefixLengthMin; $j--) {
			${"prefix"."$j"."InString"} = $this->extractNames($this->{"prefix"."$j"}, $this->{"prefix"."$j"."_replacement"}, $string);
			${"prefix"."$j"."InString"} = array_unique(${"prefix"."$j"."InString"});
			${"p"."$j"."_guessed"} = array();
			foreach (${"prefix"."$j"."InString"} as $p) {
				if ((@mb_strpos(@$p3_guessed_string, '|'.$p)===false)&&
				    (@mb_strpos(@$p4_guessed_string, '|'.$p)===false)) {
					if (@mb_strpos(@$this->combFullMatch, '|'.$p)===false) {
#						${"p"."$j"."_guessed"}[] = implode("|", $this->{"prefix"."$j"."_full"}[$p]);
					}
					if (preg_match('/\(9999\)/', $p)) {
						$p_for_full = trim(str_replace("(9999)", "", $p));
					}
					else {
						$p_for_full = $p;
					}
					${"p"."$j"."_guessed"}[] = implode("|", $this->{"prefix"."$j"."_full"}[$p_for_full]);
				}
			}
			${"p"."$j"."_guessed_string"} = '|'.implode("|", ${"p"."$j"."_guessed"});
		}

		$guessed = '|'.@$p4_guessed_string . "|" . @$p3_guessed_string . "|" . @$p2_guessed_string;
		$names_guessed =  @array_unique(explode("|", trim($guessed, "|")));

		foreach ($names_guessed as $name) {
			if (empty($name)) continue;
			$strlen = mb_strlen($name, "utf-8");
			for ($k=$this->prefixLengthMin; $k<=$this->prefixLengthMax; $k++) {
				for ($l=1; $l<=3; $l++) {
					$pre = mb_substr($name, 0, $k, "utf-8");
					$pos = mb_substr($name, (0-$l), $l, "utf-8");
					$score = 0;
					if ($pre)
					if (mb_strpos($context, $pre, 0, "utf-8")!==false) {
						if (!in_array($pre, $preStopWords)) {
							$score += $k;
						}
					}
					if ($pos)
					if (mb_strpos($context, $pos, 0, "utf-8")!==false) {
						if (!in_array($pos, $posStopWords)) {
							$score += $l;
						}
					}
					if ($strlen == 0) {
						$score = 0;
					}
					else {
						$score = $score / $strlen;
					}
#					var_dump(array($context, $name, $pre, $pos, $score));
					if ($score > @$this->scores[$name]) {
						$this->scores[$name] = $score;
						$partials[$name][$pre] = $score;
					}
				}
			}
		}


		if (!empty($this->scores)) {
			arsort($this->scores);
			$ss = array();
			$ss_idx = 0;
			foreach ($this->scores as $name => $score) {
				if ($score >= $this->scoreThreshold) {
					if ($score > 1) $score = 1;
					$ss[$name]['name'] = $name;
					$ss[$name]['score'] = round($score, 2);
					$ss[$name]['abbrv'] = $partials[$name];
					$ss[$name]['name_code'] = @$this->c2s[$name];
					$ss_idx++;
					$this->combAbbrvMatch .= $name . "(".$ss[$name]['score'].")" . "|";
				}
			}
		}
		else {
			$ss = array();
		}
		$this->abbrvMatch = $ss;
		$this->combAbbrvMatch = trim($this->combAbbrvMatch, "|");
	}

}

?>
