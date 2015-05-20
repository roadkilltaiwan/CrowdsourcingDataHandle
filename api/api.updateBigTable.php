<?php
/*
  Receive data from Facebook CrowdSourcing Toolkit and save it.
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

<?php

$dir_api = implode("/", explode("/", realpath(__FILE__), -1));

require_once $dir_api . "/../includes/system.inc";
require_once $dir_api . "/../conf/db_constant.inc.php";


$dbh = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE.';charset=utf8', DB_USER, DB_PASS);
$dbh->query("set names 'utf8';");

// $db = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
// mysql_select_db(DB_DATABASE, $db);

// $sql = "set names 'utf8';";
// mysql_query($sql);

$sets = array();
$vals = array();
if (!empty($_REQUEST)) {
#       var_dump($_REQUEST);

        if ($_REQUEST['inWhiteList'] == 'true') {
                $_REQUEST['tagged'] = 'true';
        }

        file_put_contents("req.log", var_export($_REQUEST, true), FILE_APPEND);

        foreach ($_REQUEST as $col => $val) {
                if ($val == 'true') {
                        $val = 1;
                }
                else if ($val == 'false') {
                        $val = 0;
                }
                if (($col != 'cname1')&&($col != 'cname2')&&($col != 'cname3')&&($col != 'sname1')&&($col != 'sname2')&&($col != 'sname3')&&($col != 'spid')&&($col != 'coid')) {
                        if (($col != 'x')&&($col != 'y')&&($col != 'altitude')) {
                                $sets[] = "`$col` = ?";
                                $vals[] = $val;
                        }
                        else if ($col == 'x') {
                                if (!empty($val)) {
                                        $sets[] = "`$col` = ?";
                                        $vals[] = $val;
                                }
                        }
                        else if ($col == 'y') {
                                if (!empty($val)) {
                                        $sets[] = "`$col` = ?";
                                        $vals[] = $val;
                                }
                        }
                        else if ($col == 'altitude') {
                                if (!empty($val)) {
                                        $sets[] = "`$col` = ?";
                                        $vals[] = $val;
                                }
                        }
                }
        }

        file_put_contents("req.log", var_export($sets, true), FILE_APPEND);

        $cnames = $_REQUEST['cname1'] . "|" . $_REQUEST['cname2'] . "|" . $_REQUEST['cname3'];
        $snames = $_REQUEST['sname1'] . "|" . $_REQUEST['sname2'] . "|" . $_REQUEST['sname3'];

        $cnames = trim($cnames, "| ");
        $cnames = str_replace("||", "|", $cnames);

        $snames = trim($snames, "| ");
        $snames = str_replace("||", "|", $snames);

        $sets[] = "`common_name` = ?";
        $vals[] = $cnames;
        $sets[] = "`canonical_name` = ?";
        $vals[] = $snames;

        $ids = array();
        $spids = array();
        $coids = array();
        if (!empty($_REQUEST['spid'])) {
                $spids = explode("|", $_REQUEST['spid']);
        }
        foreach ($spids as $spid) {
                if (!empty($spid)) {
                        $ids["SpecimenID($spid)"] = true;
                }
        }

        if (!empty($_REQUEST['coid'])) {
                $coids = explode("|", $_REQUEST['coid']);
        }
        foreach ($coids as $coid) {
                if (!empty($coid)) {
                        $ids["CollectionID($coid)"] = true;
                }
        }
        $id_string = "";
        if (!empty($ids)) {
                $id_string = implode("|", array_keys($ids));
        }

        $sets[] = "`custom_id` = ?";
        $vals[] = $id_string;


        $sql = "replace into bigTable set " . implode(", ", $sets);
        // $res = mysql_query($sql);
        $query = $dbh->prepare($sql);
        $res = $query->execute($vals);

        //存照片
        $photo_id = $_REQUEST['photo_id'];
        $image = $_REQUEST['picture'];
        $cmd_tpl = "/usr/bin/php $dir_api/../utils/downloadFBImage.php \"%s\" \"%s\" > /dev/null 2>/dev/null &";
        $cmd = sprintf($cmd_tpl, $image, $photo_id);
        $f = exec($cmd, $o);

        if ($res == false) {
                echo json_encode(array('state'=>false, 'SQL'=>$sql, 'save_img' => $cmd, 'o'=>$o));
        }
        else {
                echo json_encode(array('state'=>true, 'SQL'=>$sql, 'save_img' => $cmd, 'o'=>$o));
        }



}
else {
        echo json_encode(array('state'=>false, 'SQL'=>''));
}

?>