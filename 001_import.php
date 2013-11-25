<?php 
/*
  Use the access token generated in script 000_get_access_token.php to crawl photo data from Facebook group "Reptile Road Mortality" .
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


$groups = array (
	"238918712815615",	// 0 路殺社
	"177883715557195",	// 1 慕光之城
);

$group_id = $groups[0];


$dir_001 = implode("/", explode("/", realpath(__FILE__), -1));

require_once $dir_001 . "/includes/db_default_settings.inc";
require_once $dir_001 . "/includes/system.inc";
require_once $dir_001 . "/includes/LIB_http_modified_subset.php";
##############################################################################


$sql = "select max(updated_time) as mut from `Post` where `post_to`='" . $group_id . "';";
echo $sql . "\n";
$mut = $db->fetch_array($sql);
echo $mut[0]['mut'] . "\n";

$stop = $last_updated = strtotime($mut[0]['mut']);
echo date("Y-m-d H:i:s", $stop) . "\n";

#$yourdatetime = '2009-08-21 1.45 PM';
if (!empty($argv[1])) {
	$yourdatetime = $argv[1]; // the first post of fb roadkilled groups
	$last_updated = $stop = strtotime($yourdatetime);
}
else {
	$argv[1] = $mut[0]['mut'];
}

require_once $dir_001 . "/includes/rk_log.inc";
$auto_id = log_init();

$type = "feed";

$access_token = file_get_contents($dir_001."/cache/facebookApp/access_token.txt");


function whoDidThisToMe ($id, $getVar, $action) {
	log_update("find out who did $action to $id");
	$limit = 1000;
	$url = "https://graph.facebook.com/$id/$action?limit=" . $limit;
	$whoDid = http($url, "", GET, $getVar, FALSE, NULL);
	$weDid = json_decode($whoDid['FILE']);
#	var_dump($weDid);
	$data = (array) $weDid->data;
#	$counter = 0;
	while (!empty($weDid->paging->next)) {
		$limit += 1000;
#		echo "In whoDidThisToMe:\n";
#		if ($counter>1) break;
		$previous = $url;
#		$url = $weDid->paging->next;
		$url = $previous . $limit;
#		var_dump($url);
		$more = http($url, "", GET, $getVar, FALSE, NULL);
		$weDid = json_decode($more['FILE']);
		$more_data = (array) $weDid->data;
		if (!empty($more_data)) {
			$data = $more_data;
		}
#		var_dump($data);
#		$counter++;
	}
	return $data;
}


$dialog_url = "";

if (!empty($access_token)) {

#	var_dump(5566);
#	var_dump($stop);
#	var_dump(7788);

#	$sql = "select max(updated_time) as mut from `Post` where `post_to`='" . $group_id . "';";
#	echo $sql . "\n";
#	$mut = $db->fetch_array($sql);
#	echo $mut[0]['mut'] . "\n";
	$stop = $last_updated = strtotime($mut[0]['mut']);
	echo date("Y-m-d H:i:s", $stop) . "\n";

	#$yourdatetime = '2009-08-21 1.45 PM';
	if (!empty($argv[1])) {
		$yourdatetime = $argv[1]; // the first post of fb roadkilled groups
		$last_updated = $stop = strtotime($yourdatetime);
	}

	echo "程式僅應處理晚於" . date("Y-m-d H:i:s", $stop) . "時的資料\n";

	$graph_url = "https://graph.facebook.com/$group_id/$type";
	if (empty($access_token)) {
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
			file_put_contents("/tmp/access_token.txt", $params['access_token']);
		}
	}
	else {
		$getVar = array (
			'access_token' => $access_token,
			'limit' => 1000,
		);
	}


	$ackn = $getVar;

	echo "<xmp>";

	$persons = array();

	#$test_and_debug_only = true;
	if (@$test_and_debug_only===true) {
		$getVar = array (
			'access_token' => $params['access_token'],
			'limit' => '25',
			'until' => '1321623978',
		);
	}

#	redo:

	$getFeed =  true;
	$timesup = false;
	while ($getFeed) {
		$getFeed =  false;

		log_update("預備處理下一段FEED");
		$feed = http($graph_url, $dialog_url, GET, $getVar, FALSE, NULL);
		#$feeds = (array) json_decode($feed['FILE']);
		$feeds = json_decode($feed['FILE']);
		#var_dump($feeds);
		foreach ($feeds->data as $index => $post) {
#			var_dump('Idv Post');
#			ob_flush();


			$post_utime = $post->updated_time;
			echo $post_utime . "\n";;
			$utime = strtotime($post_utime) - 8 * 60 * 60; // 減8小時
			echo "$utime, $last_updated\n";
			if ($utime <= $last_updated) {
				echo "$utime, $last_updated\n";
				$timesup = true;
				break;
			}

			$post_ctime = $post->created_time;
			$post_type = $post->type;
			if ($post_type != 'photo') continue;

	#		var_dump($post);
	#		return;

			$post_id = $post->id;
			log_update("處理post: $post_id, FB上的更新時間是" . $post_utime);
			echo ("處理post: $post_id, FB上的更新時間是" . $post_utime);


	#		var_dump($post_id);
			$post_message = str_replace("\\n", " ", mysql_real_escape_string($post->message));
			$post_picture = mysql_real_escape_string($post->picture);
			$post_obj = $post->object_id;
			echo "obj = $post_obj\n";
			$obj = http("https://graph.facebook.com/$post_obj", "", GET, $ackn, FALSE, NULL);
			$photo = json_decode($obj['FILE']);
			log_update("處理photo: $post_obj, 由post: $post_id 於" . $post_ctime . "時張貼");


			$image = mysql_real_escape_string($photo->images[0]->source);

			$uploaded_by = $photo->from->id;

			$downloaded = 0;
			if (!empty($image)) {
				if (!is_dir('images/pools')) {
					mkdir('images/pools', 0777, true);
				}
				if (!file_exists('images/pools/'.$post_obj.'.jpg')) {
					$image_contents = file_get_contents($image);
					if (!empty($image_contents)) {
						file_put_contents('images/pools/'.$post_obj.'.jpg', $image_contents);
						$downloaded = 1;
					}
				}
				else {
					$downloaded = 1;
				}
			}

			foreach ($photo->images as $img) {
				if ($img->width <= 400) {
					$image_fit = $img->source;
					break;
				}
			}
			echo "image = $image\n";
			echo "image_fit = $image_fit\n";

			$post_from = $post->from->id;
			$post_from_name = mysql_real_escape_string($post->from->name);

			/* get person detailed info
			$personVar = array (
				'access_token' => $params['access_token'],
			);
			$person_url = "https://graph.facebook.com/" . $post_from;
			$person_raw = http($person_url, $dialog_url, GET, $personVar, FALSE, NULL);
			$person = json_decode($person_raw['FILE']);
			// end */

	#		$post_to = $post->to->data[0]->id;
			$post_to = $group_id;

			$post_likes = whoDidThisToMe($post_id, $ackn, 'likes');

			$post_likes_count = count($post_likes);
			if (empty($post_likes_count)) $post_likes_count = 0;
	#		var_dump($post_likes);

	#		if ($index > 0) break;
			$post_comments = whoDidThisToMe($post_id, $ackn, 'comments');
#			if ($index == 1) {
#				var_dump("debugging_stop_here");
#				exit(0);
#			}
	#		var_dump($post_comments);


#			$post_sql = "replace into `Post` (`post_id`, `message`, `picture`, `created_time`, `updated_time`, `post_to`, `post_from`, `num_likes`, `image_fit`, `object_id`) 
#	values ('$post_id', '$post_message', '$image', '$post_ctime', '$post_utime', '$post_to', '$post_from', $post_likes_count, '$image_fit', '$post_obj');";

			// 可以試試看這個行不行得通? object_id需不需要再存一次值得商榷
			$post_sql = "insert into `Post` (`post_id`, `message`, `picture`, `created_time`, `updated_time`, `post_to`, `post_from`, `num_likes`, `image_fit`, `object_id`) 
					values ('$post_id', '$post_message', '$image', '$post_ctime', '$post_utime', '$post_to', '$post_from', $post_likes_count, '$image_fit', '$post_obj')
					on duplicate key update 
					`message` = '$post_message',
					`picture` = '$image',
					`updated_time` = '$post_utime',
					`num_likes` = '$post_likes_count',
					`image_fit` = '$image_fit',
					`object_id` = '$post_obj'
					;";
			$db->query($post_sql);
			var_dump(time(true));
			echo $post_sql . "\n";

			$photo_id = $post_obj;
			$photo_picture = $image;
			$photo_x = $photo->images[0]->width;
			$photo_y = $photo->images[0]->height;
			$photo_link = $photo->link;
			$photo_name = mysql_real_escape_string($photo->name);
			$photo_ctime = mysql_real_escape_string($photo->created_time);
			$photo_utime = mysql_real_escape_string($photo->updated_time);
			$photo_sql = "replace into `Photo` (`photo_id`, `name`, `picture`, `created_time`, `updated_time`, `x`, `y`, `link`, `embedded_in`, `downloaded`, `uploaded_by`) 
	values ('$photo_id','$photo_name','$photo_picture','$photo_ctime','$photo_utime','$photo_x','$photo_y','$photo_link', '$post_id', $downloaded, '$uploaded_by');";
			$db->query($photo_sql);
			echo $photo_sql . "\n";

			$persons[$post_from] = $post_from_name;

			foreach ($post_likes as $like) {
				$like_from = $like->id;
				$persons[$like_from] = mysql_real_escape_string($like->name);
				$likes_sql = "replace into `Likes` (`from`, `to`, `to_type`) values ('$like_from', '$post_id', 'post')";
				$db->query($likes_sql);
				echo $likes_sql . "\n";
			}

			foreach ($post_comments as $comment) {
				$comment_from = $comment->from->id;
				$persons[$comment_from] = mysql_real_escape_string($comment->from->name);
				$comment_id = $post_id . '_' . $comment->id;
				$comment_message = str_replace("\\n", " ", mysql_real_escape_string($comment->message));
				$comment_ctime = $comment->created_time;
				log_update("處理comment: $comment_id, FB上的回應時間是" . $comment_ctime);

				$likes_comment = whoDidThisToMe($comment_id, $ackn, 'likes');
				$comment_likes = count($likes_comment);
				foreach ($likes_comment as $like) {
					$like_from = $like->id;
					$persons[$like_from] = mysql_real_escape_string($like->name);
					$likes_sql = "replace into `Likes` (`from`, `to`, `to_type`) values ('$like_from', '$comment_id', 'comment')";
					$db->query($likes_sql);
					echo $likes_sql . "\n";
				}

				if (empty($comment_likes)) $comment_likes = 0;

#				$comments_sql = "replace into `Comments` (`comment_id`, `from`, `to`, `message`, `created_time`, `num_likes`) 
#							values ('$comment_id', '$comment_from', '$post_id', '$comment_message', '$comment_ctime', $comment_likes);";
				$comments_sql = "insert into `Comments` (`comment_id`, `from`, `to`, `message`, `created_time`, `num_likes`) 
							values ('$comment_id', '$comment_from', '$post_id', '$comment_message', '$comment_ctime', $comment_likes)
							on duplicate key update
							`message` = '$comment_message',
							`num_likes` = '$comment_likes'
							;";
				$db->query($comments_sql);
				var_dump(time(true));
				echo $comments_sql . "\n";
			}
			foreach ($persons as $pid => $commenter_name) {
#				$person_sql = "replace into `Person` (`person_id`, `name`) values ('$pid', '$commenter_name')";
				$person_sql = "insert into `Person` (`person_id`, `name`) values ('$pid', '$commenter_name')
						on duplicate key update
						`name` = '$commenter_name'
						";
				$db->query($person_sql);
				echo $person_sql . "\n";
			}
			$persons = array();
		}
		if ($timesup === true) {
			break;
		}

#		var_dump($feeds);

		if (!empty($feeds->paging->next)) {

			$dialog_url = $graph_url;

			echo "</xmp><h2>";
			$needed = array();
			$fpn = $feeds->paging->next;
			$fpp = $feeds->paging->previous;
			echo "<br>$fpn</br><br>$fpp</br>";
			$narg_parts = explode("&", $fpn);
			$parg_parts = explode("&", $fpp);

			foreach ($narg_parts as $ap) {
				if (strpos($ap, "until=")!==false) {
					$until = str_replace("until=", "", $ap);
					echo "\n" . date('Y-m-d\TH:i:s', $until) . "\n";
				}
				else if (strpos($ap, "__paging_token=")!==false) {
				}
				else {
					$needed[] = $ap;
				}
			}
			foreach ($parg_parts as $ap) {
				if (strpos($ap, "since=")!==false) {
					$since = str_replace("since=", "", $ap);
				}
				else {
				}
			}

			if ($until===$since) {
				$bkp = $needed;
				$rollback = true;
				while ($rollback) {
					$until -= 300;
					echo "\n" . date('Y-m-d\TH:i:s', $until) . "\n";
					$needed[] = "until=$until";
					$graph_url = implode("&", $needed);
					$test_feed = http($graph_url, $dialog_url, GET, $getVar, FALSE, NULL);
					$test_feeds = json_decode($test_feed['FILE']);
					echo $graph_url;
					if (empty($test_feeds->data)&&($until >= $stop)) {
						$needed = $bkp;
					}
					else {
						$rollback = false;
					}
				}
				echo $graph_url;
			}
			else {
				$needed[] = "until=$until";
				$graph_url = implode("&", $needed);
				echo $graph_url;
			}


			if ($until >= $stop) {
				$getVar = "";
				echo "</h2><xmp>";
				$getFeed = true;
			}
		}
		else {
			$needed = array();
			if (empty($feeds->data)) {
				$fpn = $graph_url;
				$narg_parts = explode("&", $fpn);
				foreach ($narg_parts as $ap) {
					if (strpos($ap, "until=")!==false) {
						$until = str_replace("until=", "", $ap);
					}
					else if (strpos($ap, "__paging_token=")!==false) {
					}
					else {
						$needed[] = $ap;
					}
				}
				$until -= 300;
				echo "\n下一段要從" . date('Y-m-d\TH:i:s', $until) . "開始\n";
				$needed[] = "until=$until";
				$graph_url = implode("&", $needed);
				if ($until >= $stop) {
					echo "</xmp><h2>s3: $graph_url</h2><xmp>";
					$getVar = "";
					$getFeed = true;
				}
			}
			else {
				echo "</xmp><h3>?????</h3><xmp>";
			}
		}
	}


	echo "Names need to be filled in since " . $last_updated . "\n";
#	$sql = "replace into 

	echo "</xmp>";
#	var_dump($feed);
	log_end();
	return;

}
else {
	echo("The state does not match. You may be a victim of CSRF.");
	log_end("操作權限有誤");
}

?>
