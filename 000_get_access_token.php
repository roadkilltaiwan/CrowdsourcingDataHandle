<?php 

/*
  Log in Facebook with user permissions to access stream and groups and cache the access token.
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


session_start();

$dir_000 = implode("/", explode("/", realpath(__FILE__), -1));
require_once $dir_000 . "/conf/fb_app_constant.php";

$this_filename = __FILE__; //一定要有這行
require_once $dir_000 . "/includes/rk_log.inc";

$log['program'] = basename(__FILE__);
$auto_id = log_init();

##############################################################################

$app_id = APP_ID;
$app_secret = APP_SECRET;
$my_url = "http://roadkill.tw/cs/000_get_access_token.php";

$code = @$_REQUEST["code"];

$scope = "read_stream,user_groups";

if(empty($code)&&empty($access_token)) {
	log_update("第一階段認證中");
	$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
	$dialog_url = "http://www.facebook.com/dialog/oauth?client_id=" 
		. $app_id . "&redirect_uri=" . urlencode($my_url) . "&state="
		. $_SESSION['state'] . "&scope=" . $scope;

	echo("<script> top.location.href='" . $dialog_url . "'</script>");
}
else {
	$dialog_url = "";
}


if(($_REQUEST['state'] == $_SESSION['state'])||(!empty($access_token))) {

	if (empty($access_token)) {
		log_update("第二階段認證中");
		$token_url = "https://graph.facebook.com/oauth/access_token?"
			. "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)
			. "&client_secret=" . $app_secret . "&code=" . $code;

		$response = file_get_contents($token_url);
		$params = null;
		parse_str($response, $params);

		$getVar = array (
			'access_token' => $params['access_token'],
			'limit' => 1000,
		);

		if (!empty($params['access_token'])) {
			file_put_contents($dir_000."/cache/facebookApp/access_token.txt", $params['access_token']);
		}
	}
	log_end("認證成功");
}
else {
	echo("The state does not match. You may be a victim of CSRF.");
	if (empty($dialog_url)) {
		log_end("第二階段認證失敗");
	}
	else {
		log_end("第一階段認證完畢");
	}
}

?>
