<?php
/*******************************************************************************
Version: 0.0.1
Website: https://github.com/roadkilltaiwan/CrowdsourcingDataHandle
Author: Dongpo Deng <dongpo.deng@gmail.com>
Licensed under The MIT License
Redistributions of files must retain the above copyright notice.
*******************************************************************************/
error_reporting(E_ALL & ~E_NOTICE);
$dir_005 = implode("/", explode("/", realpath(__FILE__), -1));
require_once $dir_005."/includes/system.inc";
require_once $dir_005."/conf/db_constant.inc.php";
require_once $dir_005."/lib/webbots/LIB_parse.php";
require_once $dir_005."/includes/extractXY.php";

require_once $dir_005 . "/includes/rk_log.inc";
$auto_id = log_init();

if (!empty($argv[1])) {
	$stop_format = $argv[1];
}
else {
	$stop_format = "2011-08-01";
}
$stop = strtotime($stop_format);


$t1=microtime(true);

$con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);
if (!$con){
	die('Could not connect: ' . mysql_error());
	}
	
mysql_select_db(DB_DATABASE, $con);

mysql_query("set names 'utf8';");

$i=0;
$file_handle =fopen ($dir_005.'/dict/placename_tw3.txt', 'rb');
while (!feof($file_handle)){
	$line_of_text = fgets($file_handle);
	$pl=explode ("\t", $line_of_text);
	$pl_name[$i]=$pl[2];
	$i++;
	}

$msg = mysql_query('select object_id, message, updated_time, post_id from Post where updated_time >= "'.$stop_format.'";') or die("????" . mysql_error( ));
#echo 'select object_id, message, updated_time, post_id from Post where updated_time >= "'.$stop_format.'";' . "\n";

while ($row = mysql_fetch_array($msg, MYSQL_NUM)){

	//if (strtotime($row[2]) < $stop) continue;
	log_update ("擷取post:" . $row[3] . "的地點資訊, FB上的更新時間是" . $row[2]);

	if($arr){unset($arr);}
	$arr['id']=$row[0];
	$arr['post']=$row[1];

	$out_pl=extractNames($pl_name, $arr['post']);
	$arr['placename']=$out_pl;
   
	$out_ro=roadmiles($row[1]);
	
	if ($xy = extract_xy($row[1])){
		if(strrchr($xy['x'],"°") || strrchr($xy['x'],":")){
			$arr['x']=DMS2Dec($xy[x]);

		}elseif(!(strrchr($xy['x'],"°")) || !(strrchr($xy['x'],":"))){
			$arr['x']=$xy['x'];

			}
		if(strrchr($xy['y'],"°") || strrchr($xy['y'],":")){
			$arr['y']=DMS2Dec($xy['y']);

		}elseif(!(strrchr($xy['y'],"°")) || !(strrchr($xy['y'],":"))){
			$arr['y']=$xy['y'];
			}
	}
	
	for ($k=0;$k<=count($seg);$k++){
		if(in_array($seg[$k], $pl_name)&&!empty($seg[$k])){
			$arr['placename'][$k]=$seg[$k];
			}
		}
		
	if((!empty($out_pl))&&empty($arr['x'])&&empty($arr['y'])){
		$arr['placename']=array_values($arr['placename']);
		$co=check_xy_via_placename($out_pl);
		$arr['x']=$co[0];
		$arr['y']=$co[1];
		$arr['determine_name']=$co['name'];
		if (!empty($arr['x'])&&!empty($arr['y'])) {
			$arr['placename_id']=@$co[2];
			}
		}
	
	if(!empty($out_ro)){
		if(!empty($out_ro['road1'])){$arr['road']=$out_ro['road1'];}
		if(!empty($out_ro['road2'])){$arr['road']=$out_ro['road2'];}
		if(!empty($out_ro['road3'])){$arr['road']=$out_ro['road3'];}
		if(!empty($out_ro['road4'])){$arr['road']=$out_ro['road4'];}
		if(!empty($out_ro['road5'])){$arr['road']=$out_ro['road5'];}
		if(!empty($out_ro['road6'])){$arr['road']=$out_ro['road6'];}
		if(!empty($out_ro['road7'])){$arr['road']=$out_ro['road7'];}
		if(!empty($out_ro['road8'])){$arr['road']=$out_ro['road8'];}
		if(!empty($out_ro['miles1'])){$arr['miles']=$out_ro['miles1'];}
		if(!empty($out_ro['miles2'])){$arr['miles']=$out_ro['miles2'];}
		if(!empty($out_ro['miles3'])){$arr['miles']=$out_ro['miles3'];}
		if(!empty($out_ro['miles4'])){$arr['miles']=$out_ro['miles4'];}
		}
		
	print_r($arr);
	$output[]=$arr;

	}//while



mysql_select_db(DB_DATABASE, $con);
foreach ($output as $r){

	if($place){unset($place);}

		$result='"'.trim($r[id]).'","'.trim($r[x]).'","'.trim($r[y]).'","'.trim($r[road]).'","'.trim($r[miles]).'","';

		if(!empty($r[placename])){
			foreach($r[placename] as $l){
				$place .= $l." ";
				}
			}
			$result.=$place.'","'.trim($r[determine_name]).'","","","","'.$r['placename_id'].'"';
//			echo "\n";
			

		$sql = 'REPLACE INTO Location VALUES ('.$result.');'."\n";
		echo $sql . "\n";
		mysql_query($sql)or die("????" . mysql_error( ));
			
//		$handle =fopen("result.csv","a+");
//		fwrite($handle, $result);
//		fclose($handle);
	}//foreach
		
	
 	$t2=microtime(true);
	echo ($t2-$t1);
	echo "\n";


mysql_free_result($msg);
log_end();

function extractNames ($dict, $string) {

	foreach ($dict as $needle){
		
	if($needle){
		$pos=strpos($string, $needle);
		if($pos ==! false){
//			echo $needle;
			$pn[]=$needle;
			}
		}
	}
	
// Sorting the array via the length of array components	
//	function mx($m,$n){
//    	return strlen($n)-strlen($m);
//	}

//	uasort($pn,'mx');
//	$pn=array_values($pn);
//print_r($pn);

	for($i=count($pn)-1;$i>=0;$i--){
		for($j=$i-1;$j>=0;$j--){
			if(preg_match("/$pn[$i]/", $pn[$j])){
				unset($pn[$i]);
			}
		}
	}

	return($pn);
}


function DMS2Dec($str){

	if(strrchr($str,"'")){
		$deg=strstr($str,"°",true);
		$min=str_replace("°","",strstr(strstr($str,"'",true),"°"));
		$sec=str_replace("'","",strstr(strstr($str,"'"),'"',true));
		return $deg+((($min*60)+($sec))/3600);
		
	}elseif(strrchr($str,"`")){
		$deg=strstr($str,"°",true);
		$min=str_replace("°","",strstr(strstr($str,"`",true),"°"));
		$sec=str_replace("`","",strstr(strstr($str,"`"),'``',true));
		return $deg+((($min*60)+($sec))/3600);
	}elseif(strrchr($str,":")){
		$arr=preg_split('/:/',$str,-1, PREG_SPLIT_OFFSET_CAPTURE);
		return $arr[0][0]+((($arr[1][0]*60)+($arr[2][0]))/3600);
		}
	}

function check_xy_via_placename($arr){
	global $dir_005;
	@include ($dir_005.'/includes/identifyPlace2/admin.php');
	@include ($dir_005.'/includes/identifyPlace2/official_placename.php');
	@include ($dir_005.'/includes/identifyPlace2/common_placename.php');
	$add=array("市","縣","區","鄉","鎮");
	$non_placename=array("中間","石龍子","山上","後面","四分","湊合","溪畔");
	$con = mysql_connect(DB_SERVER,DB_USER,DB_PASS);

	if (!$con){
		die('Could not connect: ' . mysql_error());
	}
	
	mysql_select_db("Taiwan_placename", $con);
	$arr=array_values($arr);
	for($i=0;$i<count($arr);$i++){
		if($key=array_search($arr[$i], $common_placename)){
			$arr[$i]=$official_placename[$key];
			}
		if($key=array_search($arr[$i], $non_placename)){
			unset($arr[$i]);
			}
		}
	$arr=array_values($arr);
	if(count($arr)==1){
		if(in_array(($arr[0]),$admin)){
			$admin_name=$arr[0];
			}
		if(in_array(($arr[0]."市"),$admin)){
			$admin_name=$arr[0]."市政府";
			}
		if(in_array(($arr[0]."縣"),$admin)){
			$admin_name=$arr[0]."縣政府";
			}
		if(in_array(($arr[0]."區"),$admin)){
			$admin_name=$arr[0]."區公所";
			}
		if(in_array(($arr[0]."鄉"),$admin)){
			$admin_name=$arr[0]."鄉公所";
			}
		if(in_array(($arr[0]."鎮"),$admin)){
			$admin_name=$arr[0]."鎮公所";
			}
		if($admin_name){
			$coor=mysql_query('select Xcoord, Ycoord, id from placename_tw3 where Name like "%'.$admin_name.'%"') 
			or die("????" . mysql_error( ));
			while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
				$row['name']=$arr[0];
				return $row;
				}
			
		}else{
		
		if(stristr($arr[0],'國小')||stristr($arr[0],'國中')||stristr($arr[0],'高中')||stristr($arr[0],'中學')){
			$coor=mysql_query('select Xcoord, Ycoord, id from placename_tw3 where Name like "%'.$arr[0].'%"') 
			or die("????" . mysql_error( ));
			while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
				$row['name']=$arr[0];
				return $row;
				}
			}
	
		$coor=mysql_query('select Xcoord, Ycoord, id from placename_tw3 where Name ="'.$arr[0].'"') 
		or die("????" . mysql_error( ));
		while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
			$row['name']=$arr[0];
			return $row;
			}
		}
	}elseif (count($arr)>1){

		foreach ($arr as $p){
			if(in_array($p, $admin)){
				
				$admin_get[]=$p;
				$admin_full[]=$p;
			}elseif(!(in_array($p, $admin))){
				foreach ($add as $a){
					$t = $p.$a;
					if(in_array($t,$admin)){
						$admin_get[]=$p;
						$admin_full[]=$t;
						}
					}
				}
			}
			
		if (!empty($admin_full)&&
			in_array("台東縣",$admin_full)&&
			in_array("海端鄉",$admin_full)&&
			in_array("崁頂鄉",$admin_full)){
				$i=array_search("崁頂",$admin_get);
				unset($admin_get[$i]);
				$k=array_search("台東市",$admin_full);
				unset($admin_full[$k]);
				$j=array_search("崁頂鄉",$admin_full);
				unset($admin_full[$j]);
				$admin_full=array_values($admin_full);		
			}
		
		if (!empty($admin_full)&&
			in_array("南投市",$admin_full)&&
			in_array("南投縣",$admin_full)&&
			in_array("信義鄉",$admin_full)&&
			in_array("信義區",$admin_full)){
				$admin_full[0]="南投縣";
				$admin_full[1]="信義鄉";
				unset ($admin_full[2]);
				unset ($admin_full[3]);
			}
		if (!empty($admin_full)&&
			in_array("宜蘭市",$admin_full)&&
			in_array("宜蘭縣",$admin_full)&&
			in_array("大同鄉",$admin_full)&&
			in_array("大同區",$admin_full)){
				$admin_full[0]="宜蘭縣";
				$admin_full[1]="大同鄉";
				unset ($admin_full[2]);
				unset ($admin_full[3]);
			}
		if(!empty($admin_get)&&in_array("公館", $admin_get)&&(!in_array("苗栗", $admin_get)||!in_array("苗栗縣", $admin_get))){
			$qq=array_search("公館", $admin_get);
			unset($admin_get[$qq]);
			}
		if (!empty($admin_get[1])&&$admin_get[0]==$admin_get[1]){
			unset ($admin_get[1]);
			}
		
		if (!empty($admin_get[2])&&($admin_get[2]==$admin_get[3])){
			unset ($admin_get[3]);
			}
		
		if(!empty($admin_get)){
			$diff=array_diff($arr,$admin_get);
			}

		if(empty($diff)){
			$last=count($arr)-1;
			$coor=mysql_query('select Xcoord, Ycoord, id, County, Townname from placename_tw3 where Name ="'.$arr[$last].'"') 
			or die("????" . mysql_error( ));
				while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
					$row['name']=$arr[$last];
					return $row;
				}
			if(empty($row['name'])){
				if(in_array(($arr[$last]),$admin)){
					$admin_name=$arr[0];
					}
				if(in_array(($arr[$last]."市"),$admin)){
					$admin_name=$arr[$last]."市政府";
					}
				if(in_array(($arr[$last]."縣"),$admin)){
					$admin_name=$arr[$last]."縣政府";
					}
				if(in_array(($arr[$last]."區"),$admin)){
					$admin_name=$arr[$last]."區公所";
					}
				if(in_array(($arr[$last]."鄉"),$admin)){
					$admin_name=$arr[$last]."鄉公所";
					}
				if(in_array(($arr[$last]."鎮"),$admin)){
					$admin_name=$arr[$last]."鎮公所";
					}
				if($admin_name){
					$coor=mysql_query('select Xcoord, Ycoord, id from placename_tw3 where Name like "%'.$admin_name.'%"') 
					or die("????" . mysql_error( ));
					while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
						$row['name']=$arr[$last];
						return $row;
						}
				}
			}
		}


	foreach ($diff as $b){
		if(stristr($b,'國小')||stristr($b,'國中')||stristr($b,'高中')||stristr($b,'中學')){
			$coor=mysql_query('select Xcoord, Ycoord, id from placename_tw3 where Name like "%'.$b.'%"') 
			or die("????" . mysql_error( ));
			while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
				$row['name']=$b;
				return $row;
				}
			}
		$coor=mysql_query('select Xcoord, Ycoord, id, County, Townname from placename_tw3 where Name ="'.$b.'"') 
		or die("????" . mysql_error( ));	
		
		while ($row = mysql_fetch_array($coor, MYSQL_NUM)){
			
			if(count($admin_get)>1){
				if(count($admin_get)==2){
					if(($admin_full[0]==$row[3]&&$admin_full[1]==$row[4])||
					   ($admin_full[0]==$row[4]&&$admin_full[1]==$row[3])){
						$row['name']=$b;
						return $row;
						}else{
							$row['name']=$b;
							$row['error']="County or Towan cannot match!!\n";
							return $row;
							}
					
					}
					

				}elseif(count($admin_get)==1){
					
						$row['name']=$b;
						return $row;
					}
				}

			}//foreach diff
		
	}
}


function roadmiles ($str){
	
	preg_match('#(?P<road1>[0-9]{1,3}(甲|乙|丙))#',$str, $matches1);
	preg_match('#(?P<road2>[0-9]{1,3}縣道)#',$str, $matches2);
	preg_match('#(?P<road3>縣道[0-9]{1,3})#',$str, $matches3);
	preg_match('#(?P<road4>台[0-9]{1,2}(甲|乙|丙|線))#',$str, $matches4);
	preg_match('#(?P<road8>台[0-9]{1,2})#',$str, $matches12);
	preg_match('#(?P<road5>台(一|二|三|四|五|六|七|八|九))#',$str, $matches5);
	preg_match('#(?P<road6>台(一|二|三|四|五|六|七|八|九)(甲|乙|丙|線))#',$str, $matches6);
	preg_match('#(?P<road7>(宜|北|竹|桃|苗|彰|投|雲|嘉|屏|東|花|澎|南|高|中)[0-9]{1,3})#',$str, $matches7);
	preg_match('#(?P<miles1>[0-9]{1,3}\.[0-9](k|K))#',$str, $matches8);
	preg_match('#(?P<miles2>[0-9]{1,3}(k|K))#',$str, $matches9);
	preg_match('#(?P<miles3>[0-9]{1,3}\.[0-9]公里)#',$str, $matches10);
	preg_match('#(?P<miles4>[0-9]{1,3}公里)#',$str, $matches11);

	if(!empty($matches1['road1'])){$matches['road1']=$matches1['road1'];}
	if(!empty($matches2['road2'])){$matches['road2']=$matches2['road2'];}
	if(!empty($matches3['road3'])){$matches['road3']=$matches3['road3'];}
	if(!empty($matches4['road4'])){$matches['road4']=$matches4['road4'];}
	if(!empty($matches5['road5'])){$matches['road5']=$matches5['road5'];}
	if(!empty($matches6['road6'])){$matches['road6']=$matches6['road6'];}
	if(!empty($matches7['road7'])){$matches['road7']=$matches7['road7'];}
	if(!empty($matches12['road8'])){$matches['road8']=$matches12['road8'];}
	if(!empty($matches8['miles1'])){$matches['miles1']=$matches8['miles1'];}
	if(!empty($matches9['miles2'])){$matches['miles2']=$matches9['miles2'];}
	if(!empty($matches10['miles3'])){$matches['miles3']=$matches10['miles3'];}
	if(!empty($matches11['miles4'])){$matches['miles4']=$matches11['miles4'];}
	if(($matches['road1'])&&($matches['road7'])){
		unset($matches['road7']);
		}
	if(($matches['road2'])&&($matches['road7'])){
		unset($matches['road7']);
		}
	if(($matches['road5'])&&($matches['road6'])){
		unset($matches['road5']);
		}
	if(($matches['road1'])&&($matches['road4'])){
		unset($matches['road1']);
		}
	if(($matches['miles1'])&&($matches['miles2'])){
		unset($matches['miles2']);
		}
	if(($matches['miles3'])&&($matches['miles4'])){
		unset($matches['miles4']);
		}
	return ($matches);
	}

?>

