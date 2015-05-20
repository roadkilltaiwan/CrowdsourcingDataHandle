<?php

/*******************************************************************************
Version: 0.0.1
Website: https://github.com/roadkilltaiwan/CrowdsourcingDataHandle
Author: Dongpo Deng <dongpo.deng@gmail.com>
Licensed under The MIT License
Redistributions of files must retain the above copyright notice.
*******************************************************************************/

// Regex patterns are modified and extended by Jason Guan-Shuo Mai


function extract_xy($str){
	global $argv;
	$matches = array();
	preg_match('#(?P<x>(118|119|120|121|122)\.[0-9]{1,6})#',$str, $matches1);
	preg_match('#(?P<y>(21|22|23|24|25|26)\.[0-9]{1,6})#',$str, $matches2);
	preg_match('#(?P<y>[^1]((21|22|23|24|25|26)\.[0-9]{1,6}))#',$str, $matches2_1);
	preg_match('#(?P<x>(118|119|120|121|122).{2}[ ]*([0-9]{1,}\.[0-9]{1,})\')#',$str, $matches3_1);
	preg_match('#(?P<y>[^0-9]+(21|22|23|24|25|26).{2}[ ]*([0-9]{1,}\.[0-9]{1,})\')#',$str, $matches4_1);
	preg_match('#(?P<x>(118|119|120|121|122)\°[ ]*([0-9]{1,2})\'[ ]*([0-9]{1,2}\.?[0-9]{0,})\")#',$str, $matches3);
	preg_match('#(?P<y>[^0-9]+(21|22|23|24|25|26)\°[ ]*([0-9]{1,2})\'[ ]*([0-9]{1,2}\.?[0-9]{0,})\")#',$str, $matches4);
	preg_match('#(?P<x>(118|119|120|121|122)\°[ ]*([0-9]{1,2})\`[ ]*([0-9]{1,2}\,?[0-9]{0,})\``)#',$str, $matches5);
	preg_match('#(?P<y>[^0-9]+(21|22|23|24|25|26)\°[ ]*([0-9]{1,2})\`[ ]*([0-9]{1,2}\,?[0-9]{0,})\``)#',$str, $matches6);
#	preg_match('#(?P<x>(118|119|120|121|122)\:([0-9]{1,2})\:([0-9]{1,2}\.?[0-9]{0,}))#',$str, $matches7);
#	preg_match('#(?P<y>[^0-9]+(21|22|23|24|25|26)\:([0-9]{1,2})\:([0-9]{1,2}\.?[0-9]{0,}))#',$str, $matches8);

	preg_match('#(?P<x>(118|119|120|121|122)\.[0-9]{1,6})[^kK]#',$str, $matches7);
	preg_match('#(?P<y>(21|22|23|24|25|26)\.[0-9]{1,6})[^kK]#',$str, $matches8);
	preg_match('#(?P<y>[^1]((21|22|23|24|25|26)\.[0-9]{1,6}))[^kK]#',$str, $matches8_1);

	preg_match('#(?P<x>(118|119|120|121|122)\:([0-9]{1,2})\:([0-9]{1,2}\.?[0-9]{0,}))#',$str, $matches7);
	preg_match('#(?P<y>[^0-9]+(21|22|23|24|25|26)\:([0-9]{1,2})\:([0-9]{1,2}\.?[0-9]{0,}))#',$str, $matches8);
	preg_match('#(?P<x>[Ee]?(118|119|120|121|122)[Dd]([0-9]{1,2}\.?[0-9]{0,})[Mm]?)#',$str, $matches9_1);
	preg_match('#(?P<y>[Nn]?[^0-9]+(21|22|23|24|25|26)[Dd]([0-9]{1,2}\.?[0-9]{0,})[Mm]?)#',$str, $matches10_1); 
	preg_match('#(?P<x>[Ee]?(118|119|120|121|122)[Dd]([0-9]{1,2}\.?[0-9]{0,})[Mm]([0-9]{1,2}\.?[0-9]{0,})[SsDd]?)#',$str, $matches9);
	preg_match('#(?P<y>[Nn]?[^0-9]+(21|22|23|24|25|26)[Dd]([0-9]{1,2}\.?[0-9]{0,})[Mm]([0-9]{1,2}\.?[0-9]{0,})[SsDd]?)#',$str, $matches10); 

	preg_match('#(?P<x>[Ee](118|119|120|121|122)[。°]+([0-9]{1,2}\.?[0-9]{0,})[’]+([0-9]{1,2}\.?[0-9]{0,})[”]+)#',$str, $matches11);
	preg_match('#(?P<y>[Nn][^0-9]+(21|22|23|24|25|26)[。°]+([0-9]{1,2}\.?[0-9]{0,})[’]+([0-9]{1,2}\.?[0-9]{0,})[”]+)#',$str, $matches12); 

	if(!empty($matches1['x'])){$matches['x']=$matches1['x'];}
	if(!empty($matches2['y'])){$matches['y']=$matches2['y'];}
	if(!empty($matches2_1['y'])){$matches['y']=trim($matches2_1[2]);}
#	if(!empty($matches3['x'])){$matches['x']=$matches3['x'];}
	if(!empty($matches3_1['x'])){
		$matches['x']=round($matches3_1[2] + $matches3_1[3] / 60, 6);
	}
	if(!empty($matches4_1['y'])){
		$matches['y']=round($matches4_1[2] + $matches4_1[3] / 60, 6);
	}
	if(!empty($matches3['x'])){
		$matches['x']=round($matches3[2] + $matches3[3] / 60 + $matches3[4] / 3600, 6);
	}
#	if(!empty($matches4['y'])){$matches['y']=$matches4['y'];}
	if(!empty($matches4['y'])){
		$matches['y']=round($matches4[2] + $matches4[3] / 60 + $matches4[4] / 3600, 6);
	}
	if(!empty($matches5['x'])){
		$matches['x']= round($matches5[2] + $matches5[3] / 60 + str_replace(",", ".", $matches5[4]) / 3600, 6);
	}
	if(!empty($matches6['y'])){
		$matches['y']= round($matches6[2] + $matches6[3] / 60 + str_replace(",", ".", $matches6[4]) / 3600, 6);
	}
	if(!empty($matches7['x'])){
#		$matches['x']=round($matches7[2] + $matches7[3] / 60 + $matches7[4] / 3600, 6);
		$matches['x']=$matches7['x'];
	}
	if(!empty($matches8['y'])){
#		$matches['y']=round($matches8[2] + $matches8[3] / 60 + $matches8[4] / 3600, 6);
		$matches['y']=$matches8['y'];
	}
	if(!empty($matches8_1['y'])){
#		$matches['y']=round($matches8[2] + $matches8[3] / 60 + $matches8[4] / 3600, 6);
		$matches['y']=$matches8_1[2];
	}
	if(!empty($matches9_1['x'])){
		$matches['x']=round($matches9_1[2] + $matches9_1[3] / 60, 6);
	}
	if(!empty($matches10_1['y'])){
		$matches['y']=round($matches10_1[2] + $matches10_1[3] / 60, 6);
	}
	if(!empty($matches9['x'])){
		if (($matches9['x'][strlen($matches9['x'])-1] != 'd')&&($matches9['x'][strlen($matches9['x'])-1] != 'D')) {
			$matches['x']=round($matches9[2] + $matches9[3] / 60 + $matches9[4] / 3600, 6);
		}
		else {
			$matches['x']=round($matches9[2] + $matches9[3] / 60, 6);
		}
	}
	if(!empty($matches10['y'])){
		if (($matches10['y'][strlen($matches10['y'])-1] != 'd')&&($matches10['y'][strlen($matches10['y'])-1] != 'D')) {
			$matches['y']=round($matches10[2] + $matches10[3] / 60 + $matches10[4] / 3600, 6);
		}
		else {
			$matches['y']=round($matches10[2] + $matches10[3] / 60, 6);
		}
	}
	if(!empty($matches11['x'])){
		$matches['x']=round($matches11[2] + $matches11[3] / 60 + $matches11[4] / 3600, 6);
	}
	if(!empty($matches12['y'])){
		$matches['y']=round($matches12[2] + $matches12[3] / 60 + $matches12[4] / 3600, 6);
	}
	if (!empty($argv[1])) {
		var_dump($matches1);
		var_dump($matches2);
		var_dump($matches2_1);
		var_dump($matches3_1);
		var_dump($matches4_1);
		var_dump($matches3);
		var_dump($matches4);
		var_dump($matches5);
		var_dump($matches6);
		var_dump($matches7);
		var_dump($matches8);
		var_dump($matches8_1);
		var_dump($matches9_1);
		var_dump($matches10_1);
		var_dump($matches9);
		var_dump($matches10);
		var_dump($matches11);
		var_dump($matches12);
		var_dump($matches);
		var_dump($matches10['y']);
		var_dump($matches10['y'][strlen($matches10[0])-1]);
	}

	return (@$matches);
}



?>
