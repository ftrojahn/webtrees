<?php
// Media Firewall - Serves media images, after checking access
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009 PGV Development Team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

use WT\Log;
use WT\Auth;

define('WT_SCRIPT_NAME', 'mediafirewall2.php');
require './includes/session.php';

Zend_Session::writeClose();

$murl   = WT_Filter::get('file');

// Send a “Not found” error
function send404AndExit() {
	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');
	header('Content-Type: text/html');
	echo WT_I18N::translate('The file was not found');
	exit;
}

// Redirect to login page
function send403AndExit() {
	header('Location: ' . WT_LOGIN_URL . '?url=' . rawurlencode(WT_SCRIPT_NAME . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')), true, 301);
	exit;
}

function _detectFileMimeType($file)
{
	$type = null;
	// First try with fileinfo functions
	if (function_exists('finfo_open')) {
		$_fileInfoDb = @finfo_open(FILEINFO_MIME);
		if ($_fileInfoDb) {
			$type = finfo_file($_fileInfoDb, $file);
		}

	} elseif (function_exists('mime_content_type')) {
		$type = mime_content_type($file);
	}

	// Fallback to the default application/octet-stream
	if (! $type) {
		$type = 'application/octet-stream';
	}
	return $type;
}

// Only registered users may view files
if (!Auth::isMember()) {
	send403AndExit();	
}

//only published files should be accessible 
require WT_ROOT.'data/media/media_config.ini.php';
$exists = false;
foreach ($media_special_trees as $file) {
	if ($file->Filename == $murl) {
		$exists = true;
		break;
	}
}

if (!$exists) {
	send404AndExit();
}

$serverFilename = WT_DATA_DIR . $MEDIA_DIRECTORY . $murl;

if (!file_exists($serverFilename)) {
	send404AndExit();
}

$mimetype = _detectFileMimeType($serverFilename);

$protocol       = $_SERVER['SERVER_PROTOCOL'];  // determine if we are using HTTP/1.0 or HTTP/1.1
$filetime       = @filemtime($serverFilename);
$filetimeHeader = gmdate('D, d M Y H:i:s', $filetime) . ' GMT';
$expireOffset   = 3600 * 24;  // tell browser to cache this image for 24 hours
$expireHeader = gmdate('D, d M Y H:i:s', WT_TIMESTAMP + $expireOffset) . ' GMT';

// parse IF_MODIFIED_SINCE header from client
$if_modified_since = 'x';
if (@$_SERVER['HTTP_IF_MODIFIED_SINCE']) {
	$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
}

// parse IF_NONE_MATCH header from client
$if_none_match = 'x';
if (@$_SERVER['HTTP_IF_NONE_MATCH']) {
	$if_none_match = str_replace('"', '', $_SERVER['HTTP_IF_NONE_MATCH']);
}

// add caching headers.  allow browser to cache file, but not proxy
header('Last-Modified: ' . $filetimeHeader);
header('Expires: ' . $expireHeader);
header('Cache-Control: max-age=' . $expireOffset . ', s-maxage=0, proxy-revalidate');

// if this file is already in the user’s cache, don’t resend it
if (($if_modified_since == $filetimeHeader)) {
	header($protocol . ' 304 Not Modified');
	exit;
}

// send headers for the image
header('Content-Type: ' . $mimetype);
header('Content-Disposition: filename="' . addslashes(basename($murl)) . '"');

// determine filesize of image (could be original or watermarked version)
$filesize = filesize($serverFilename);

// set content-length header, send file
header('Content-Length: ' . $filesize);

// Some servers disable fpassthru() and readfile()
if (function_exists('readfile')) {
	readfile($serverFilename);
} else {
	$fp = fopen($serverFilename, 'rb');
	if (function_exists('fpassthru')) {
		fpassthru($fp);
	} else {
		while (!feof($fp)) {
			echo fread($fp, 65536);
		}
	}
	fclose($fp);
}
