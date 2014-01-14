<?php

/*
  A function to extract dates of different formats
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




/*
$m[] = "2013年05月22日09:50 地點：橫山大山背S：120度8.140'　Ｎ：24度40.892' 高度474M 長度39cm 台灣鈍頭蛇(?)";
$m[] = '2013-08-05 福山植物園  這邊遇到的第三種蛇、第六條蛇... 卻是剛被壓到頭，身體還在扭動的個體...';
$m[] = '6/8 早上  北橫56.3';
$m[] = '2013/6/17 約上午十點半 座標 25.036619,121.433325 (google map) 於 輔仁大學 文開樓附近道路 發現(好像已經有很多天了...都乾扁掉了QAQ)';
$m[] = '2013/07/7 台7線23.8';
$m[] = '時間：2013/8/7 下午2:00 地點：南投縣名間鄉松柏村松柏街 不認識..';
$m[] = '2013/08/07 台7乙  6km左右   1:30 鼬獾 應該被撞不久 移至路邊讓牠回歸於大自然';
$m[] = '時間: 2013/6/23 下午 3:32 地點: 新北市林口區新寮路, 師大林口校區大門後方靠近新...';
$m[] = '2013/07/30 PM 3:15 巴拉卡公路  25.1936297  121.505742 龜...';
$m[] = '2013/05/16 凌晨2：45 北橫63k 感覺像黃口攀蜥從白梅花肚子蹦出來……';
$m[] = '拉氏清溪蟹(我亂猜的) 2012/10/29 10:15AM 花蓮縣富里鄉羅山村羅山瀑布公廁前馬路上';
$m[] = '2013.08.08  0850 新竹縣峨眉鄉中豐公路 24.690247,121.018864 青蛇  移至路邊，大體還是軟的，死亡時間應該今天早上';
$m[] = '2012.5.4 台28線 旗山路段  不知道是哪種哺乳類?有點像鼬獾??好像又不是?';
$m[] = '102.06.23拍攝 新店區烏來四崁水，沿路都是蛙、蛇蚯和各種蝸牛的屍體.......';
$m[] = '時間:2013.07.22 上午9:49 地點:金山';
$m[] = '20121021 南投縣151號6k 青蛇';
$m[] = '20130510 15:22 地點:高雄市大樹區鳳梨果園 品種:南蛇 備註:傳宗接代中.....';
$m[] = '紫地蟹 中山大學海科院旁道路 20130726 2215';
$m[] = '11/13日晚上剛拍的';
$m[] = '121d12.094\' (121d9\'12.5 ';
$m[] = '2013/9/6 下午4:54
臺九線 253.5~254K之間
眼鏡蛇';
$m[] = '9月7日17:30台南市六甲區174縣道38km處';
$m[] = '25.104098,121.555481
20130617';
$m[] = '曾漸漸四處爬爬走(路殺社, Reptile Road Mortality)25.104098,121.555481 20130617讚 ·  · 追蹤貼文 · 分享 ·  林德恩和其他 2 人都說讚。Joyce Chen 林大利 · 讚林大利 #野鴿# *Columba livia* · 讚留言⋯⋯';

foreach ($m as $mm) {
	$res = extractDate($mm);
	var_dump($res);
}

//*/

$debug = false;
if (!empty($argv[1])) {
        $debug = true;
        var_dump(5566);
}

function extractDate ($string) {
        global $debug;
        $thisYear = (int) date('Y', time(true));
#       echo $thisYear;
        $delimiter = array (
                '\/',
                '-',
                '\.',
                ',',
                ' ',
                '、',
        );

        $hasYear = true;
        $matched = false;
        foreach ($delimiter as $d) {
                $p0 = sprintf('/([0-9]{4})%s([0-9]{1,2})%s([0-9]{1,2})[^0-9]/', '年', '月'); // 西元 yyyy-mm-dd
                preg_match_all($p0, $string, $match);
#               var_dump($debug);
                if ($debug) {
                        var_dump($p0);
                }
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $year = (int) $match[1][0];
                        $month = (int) $match[2][0];
                        $day = (int) $match[3][0];
                        if (($year >= 1888)&&($year <= $thisYear)&&($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
#                               echo $date . "\n";
                                break;
                        }
                }
                $p0_1 = sprintf('/([0-9]{1,2})%s([0-9]{1,2})%s/', '月', '日'); // 西元 yyyy-mm-dd
                preg_match_all($p0_1, $string, $match);
                if ($debug) var_dump($p0_1);
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $month = (int) $match[1][0];
                        $day = (int) $match[2][0];
                        if (($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%02d-%02d", $month, $day);
                                $hasYear = false;
#                               echo $date . "\n";
                                break;
                        }
                }
                $p1 = sprintf('/([0-9]{4})%s([0-9]{1,2})%s([0-9]{1,2})[^0-9]/', $d, $d); // 西元 yyyy-mm-dd
                preg_match_all($p1, $string, $match);
                if ($debug) var_dump($p1);
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $year = (int) $match[1][0];
                        $month = (int) $match[2][0];
                        $day = (int) $match[3][0];
                        if (($year >= 1888)&&($year <= $thisYear)&&($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
#                               echo $date . "\n";
                                break;
                        }
                }
                $p1_1 = sprintf('/([0-9]{4})%s([0-9]{1,2})%s([0-9]{1,2})$/', $d, $d); // 西元 yyyy-mm-dd
                preg_match_all($p1_1, $string, $match);
                if ($debug) var_dump($p1_1);
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $year = (int) $match[1][0];
                        $month = (int) $match[2][0];
                        $day = (int) $match[3][0];
                        if (($year >= 1888)&&($year <= $thisYear)&&($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
#                               echo $date . "\n";
                                break;
                        }
                }

                $p2 = sprintf('/([0-9]{2,3})%s([0-9]{1,2})%s([0-9]{1,2})[^0-9]/', $d, $d); // 民國 yy[y]-mm-dd
                preg_match_all($p2, $string, $match);
                if ($debug) var_dump($p2);
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $year = (int) $match[1][0] + 1911;
                        $month = (int) $match[2][0];
                        $day = (int) $match[3][0];
                        if (($year >= 1888)&&($year <= $thisYear)&&($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
#                               echo $date . "\n";
                                break;
                        }
                }
                $p3 = sprintf('/([0-9]{4})([0-9]{2})([0-9]{2})[^0-9]/'); // 西元 yyyymmdd
                preg_match_all($p3, $string, $match);
                if ($debug) var_dump($p3);
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $year = (int) $match[1][0];
                        $month = (int) $match[2][0];
                        $day = (int) $match[3][0];
                        if (($year >= 1888)&&($year <= $thisYear)&&($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
#                               echo $date . "\n";
                                break;
                        }
                }
                $p3_1 = sprintf('/([0-9]{4})([0-9]{2})([0-9]{2})$/'); // 西元 yyyymmdd
                preg_match_all($p3_1, $string, $match);
                if ($debug) var_dump($p3_1);
                if (!empty($match[0])) {
                        if ($debug) echo $match[0] . "\n";
                        $year = (int) $match[1][0];
                        $month = (int) $match[2][0];
                        $day = (int) $match[3][0];
                        if (($year >= 1888)&&($year <= $thisYear)&&($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                $matched = true;
                                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
#                               echo $date . "\n";
                                break;
                        }
                }
        }
        if (empty($date)) {
                foreach ($delimiter as $d) {
                        $p4 = sprintf('/([0-9]{1,2})%s([0-9]{1,2})[^\'\"]?[^0-9\'\"]/', $d); // mm-dd
                        preg_match_all($p4, $string, $match);
                        if ($debug) var_dump($p4);
                        if (!empty($match[0])) {
                                if ($debug) echo $match[0] . "\n";
                                $month = (int) $match[1][0];
                                $day = (int) $match[2][0];
                                if (((int)$month >= 1)&&((int)$month <= 12)&&((int)$day >= 1)&&((int)$day <= 31)) {
                                        $matched = true;
                                        $date = sprintf("%02d-%02d", $month, $day);
                                        $hasYear = false;
        #                               echo $date . "\n";
                                        break;
                                }
                        }
                        $p5 = sprintf('/[^0-9\'\"°]([0-9]{2})([0-9]{2})[^0-9\'\"]/'); // mmdd
                        preg_match_all($p5, $string, $match);
                        if ($debug) var_dump($p5);
                        if (!empty($match[0])) {
                                if ($debug) echo $match[0] . "\n";
                                $month = (int) $match[1][0];
                                $day = (int) $match[2][0];
                                if (($month >= 1)&&($month <= 12)&&($day >= 1)&&($day <= 31)) {
                                        $matched = true;
                                        $date = sprintf("%02d-%02d", $month, $day);
                                        $hasYear = false;
        #                               echo $date . "\n";
                                        break;
                                }
                        }
                }
        }
        if ($matched) {
                return array('string' => $string, 'hasYear' => $hasYear, 'date' => $date);
        }
        else {
                return false;
        }
}


?>
