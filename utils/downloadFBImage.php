<?php

/*
  Download a photo from Facebook by a direct link, save it and name it with Facebook photo id.
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




$imageTmp = explode("?", @$argv[1]);
$image = $imageTmp[0];
$photo_id = @$argv[2];

$dir_utils = implode("/", explode("/", realpath(__FILE__), -1));

echo "called\n";
if (!empty($image)&&!empty($photo_id)) {
	if (!file_exists($dir_utils.'/../images/pools/'.$photo_id.'.jpg')) {
		echo "file not found\n";
		$image_contents = file_get_contents($image);
		if (!empty($image_contents)) {
			file_put_contents($dir_utils.'/../images/pools/'.$photo_id.'.jpg', $image_contents);
			echo "file saved\n";
		}
	}
}
?>
