<?php

/*
$m = array (
	'#雨傘節#, *Bungarus multicincintus multicincintus* 標本號RN1140 採集號XHL20130801, 感恩',
	'#斯文豪氏攀蜥#, *Japalura swinhonis * 標本號RN1141 採集號YDZ20130801, 感恩',
	'#雨傘節#, *Bungarus multicincintus multicincintus* 標本號RK0291, 採集號WRY20130529-3, 感恩^^',
	'#紅竹蛇#, *Oreocryptophis porphyraceus kawakamii* 標本號RN0974, 採集號WRY20130529-1, 感恩',
	'已製成標本，標本號：RK0200，採集號：ZHX20121016-2~THX^^',
	'標本號RN1015, 採集號YLL20130607-4, 感恩',
);

foreach ($m as $text) {
	var_dump(extractExtra($text));
}
//*/

function extractExtra ($text) {

	$spnoPattern1 = '/[^a-zA-Z0-9](RK[0-9]{3,})/';
	$spnoPattern2 = '/[^a-zA-Z0-9](RN[0-9]{4,})/';
	$clnoPattern = '/[^a-zA-Z0-9]([A-Z]{2,4}[0-9]{8}(\-[0-9])?)/';

	preg_match($spnoPattern1, $text, $match);
	if (empty($match[1])) {
		preg_match($spnoPattern2, $text, $match);
	}
	if (!empty($match[1])) {
		$specimenID = $match[1];
	}
	else {
		$specimenID = '';
	}
	preg_match($clnoPattern, $text, $match1);
	if (!empty($match1[1])) {
		$collectionID = $match1[1];
	}
	else {
		$collectionID = '';
	}
	if (!empty($specimenID)||!empty($collectionID)) {
		return array('SpecimenID' => $specimenID, 'CollectionID' => $collectionID);
	}
	else {
		return false;
	}

}








?>
