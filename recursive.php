<?php
ini_set('memory_limit', '-1');
$img[0]['xmax'] = 0;

function get_fold($folder) {
	global $img;
	static $y = 0;
	static $nbfile = 0;
	static $maxheight = 0;

	$open = opendir($folder);
	while ($file = readdir($open)) {
		if ($file != "." && $file != "..") {
			$path = realpath($folder . DIRECTORY_SEPARATOR . $file);
			if (is_file($path) && mime_content_type($path) == "image/png") {
				$tab = getimagesize($path);
				$nbfile++;
				getsiz($path, $tab, $img, $y);
				if ($tab[1] > $maxheight)
					$maxheight = $tab[1];
				if ($y <= $nbfile)
					$y++;
			}
			if (is_dir($path))
				get_fold($path);
		}
	}
	if ($nbfile > 0)
		imagecreat($img, $y, $maxheight);
}

function getsiz($path, $tab, &$img, $y)
{
	$img[$y]['path'] = $path;
	$img[$y]['width'] = $tab[0];
	$img[$y]['height'] = $tab[1];
	$img[$y]['ressource'] = imagecreatefrompng($path);
	$img[$y + 1]['xmax'] = $img[$y]['xmax'] + $img[$y]['width'];
	$img[$y]['numeroimg'] = $y + 1;
}

function imagecreat($img, $y, $maxheight)
{
	$im = imagecreatetruecolor($img[$y]['xmax'], $maxheight);
	imagealphablending($im, false);
	imagesavealpha($im, true);
	$y = 0;
	while ($y <= count($img) - 2) {				
		imagecopy($im, $img[$y]['ressource'], $img[$y]['xmax'], 0, 0, 0,
			$img[$y]['width'], $img[$y]['height']);
		$y++;
	}
	imagepng($im, "sprite.png");
}
function css_generator($pngname = "sprite.png") {
	$y = 0;
	global $img;
	$content = "";

	while (isset($img[$y]['width'])) {
		$content .= ".sprite-" . $img[$y]['numeroimg'] . " {\n";
		$content .= "\tbackground-image: url(" . $pngname . ");\n\theight:" . 
		$img[$y]['height'] . "px;\n\twidth: " . $img[$y]['width'] . 
		"px;\n\tbackground-position: -" . $img[$y]['xmax'] . "px -0px;\n}\n";
		$y++;
	}
	file_put_contents("style.css", $content);
}