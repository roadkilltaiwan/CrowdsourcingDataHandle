<?php

$dir_008 = implode("/", explode("/", realpath(__FILE__), -1));

require_once $dir_008."/conf/db_constant.inc.php";
require_once $dir_008."/includes/system.inc";
require_once $dir_008."/lib/webbots/LIB_parse.php";

if (!empty($argv[1])) {
	$stop_format = $argv[1];
}
else {
	$stop_format = "2011-08-01";
	$argv[1] = $stop_format;
}
require_once $dir_008 . "/includes/rk_log.inc";

$auto_id = log_init();

$con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
mysql_select_db(DB_DATABASE, $con);
mysql_query("set names 'utf8';");

$update_location = 0;


$res = mysql_query($sql);

function trickyDownload ($fbid, $updatePicSrc=false) {
	global $dir_008, $con;
	$filePath = $dir_008 . "/images/pools/" . $fbid . ".jpg";

	$url = "https://www.facebook.com/photo.php?fbid=" . $fbid;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($cl, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "$dir_008/cookies/dlimg.txt");
	curl_setopt($ch, CURLOPT_COOKIEJAR, "$dir_008/cookies/dlimg.txt");
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36'); 

	$res = curl_exec($ch);
	curl_close($ch);

	$imgTags = parse_array($res, '<img', '>');
#	var_dump($res);
#	var_dump($imgTags);
	foreach ($imgTags as $tag) {
		$id = get_attribute($tag, 'id');
		if ($id == 'fbPhotoImage') {
			$src = html_entity_decode(get_attribute($tag, 'src'));
			$imgC = file_get_contents($src);
			if (!empty($imgC)) {
				if ($updatePicSrc) {
					$update_src_sql = "update bigTable set `picture` = '".mysql_real_escape_string($src)."' where `photo_id` = '".mysql_real_escape_string($fbid)."';";
					mysql_query($update_src_sql, $con) or die (mysql_error());
					echo $update_src_sql . "\n";
				}
				file_put_contents($filePath, $imgC);
				echo "fix!!!!!!!!!!!!!: " . $src . "\n";
			}
			else {
				echo "神仙難救無命客之: $src" . "\n";
			}
		}
	}
	if (empty($imgTags)) {
		echo "\t" . $url . "\n";
		echo "<a href='$url'>$fbid</a><br/>\n";
	}
}


$q = "select photo_id as pid, picture as pic from bigTable where created_time > '". $stop_format ."' order by created_time desc;";
echo $q . "\n";
$res = mysql_query($q);
while ($row = mysql_fetch_assoc($res)) {
	$needD = false;
	$filePath = $dir_008 . "/images/pools/" . $row['pid'] . ".jpg";
	if (!empty($row['pic'])) {
		if (!file_exists($filePath)) {
			$needD = true;
		}
		else if (filesize($filePath) == 0) {
			$needD = true;
		}
		if ($needD) {
			log_update("找到待補照片, 補法一:FBID=" . $row['pid']);
			$c = file_get_contents($row['pic']);
			if (!empty($c)) {
				file_put_contents($filePath, $c);
				echo "fix!!!!!!!!!: " . $row['pic'] . "\n";
			}
			else {
//				echo "access error: " . $row['pic'] . "\n";
//				echo "access error: " . $row['pic'] . "<br/>";
				log_update("補法一漏接! 採用補法二:FBID=" . $row['pid']);
				trickyDownload ($row['pid']);
			}
		}
	}
	else if (empty($row['pic'])) {
		log_update("連結不存在, 採用補法二:FBID=" . $row['pid']);
		trickyDownload ($row['pid'], $updatePicSrc=true);
	}
}




log_end();

?>
