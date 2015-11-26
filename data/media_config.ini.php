<?php
use Fisharebest\Webtrees\Site;
use Fisharebest\Webtrees\Tree;

global $media_special_trees;
$media_special_trees = array();

$MEDIA_DIRECTORY = Tree::findByName(Site::getPreference('DEFAULT_GEDCOM'))->getPreference('MEDIA_DIRECTORY');
$filename = WT_DATA_DIR . $MEDIA_DIRECTORY . 'files.csv';
if (file_exists($filename)) {
	$fp = fopen($filename, 'r');
	if ($fp) {
		while (($data = fgetcsv($fp, 0, ';')) !== false) {
			$media_special_trees[] = (object) array('Name' => $data[0],	'Filename' => $data[1]);
		}
		fclose($fp);
	}
}
?>
