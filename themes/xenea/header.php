<?php
// Header for xenea theme
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

use WT\Auth;

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript(WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(WT_JQUERY_WHEELZOOM_URL)
	->addInlineJavascript('activate_colorbox();')
	->addInlineJavascript('jQuery.extend(jQuery.colorbox.settings, {width:"85%", height:"85%", transition:"none", slideshowStart:"'. WT_I18N::translate('Play').'", slideshowStop:"'. WT_I18N::translate('Stop').'"});')
	->addInlineJavascript('
		jQuery.extend(jQuery.colorbox.settings, {
			title: function() {
				var img_title = jQuery(this).data("title");
				return img_title;
			}
		});
	');
?>
<!DOCTYPE html>
<html <?php echo WT_I18N::html_markup(); ?>>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL); ?>
	<title><?php echo WT_Filter::escapeHtml($title); ?></title>
	<link rel="icon" href="<?php echo WT_CSS_URL; ?>favicon.png" type="image/png">
	<link rel="stylesheet" type="text/css" href="<?php echo WT_THEME_URL; ?>jquery-ui-1.11.2/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="<?php echo WT_CSS_URL; ?>style.css">
	<!--[if IE 8]>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
	<![endif]-->
</head>
<body>
	<?php if ($view !== 'simple') { ?>
	<header>
		<div class="header-upper">
			<h1><?php echo WT_TREE_TITLE; ?></h1>
			<div class="header-search">
				<form action="search.php" method="post" role="search">
					<input type="hidden" name="action" value="general">
					<input type="hidden" name="ged" value="<?php echo WT_GEDCOM; ?>">
					<input type="hidden" name="topsearch" value="yes">
					<input type="search" name="query" size="12" placeholder="<?php echo WT_I18N::translate('Search'); ?>">
					<input type="submit" name="search" value="&gt;">
				</form>
			</div>
		</div>
		<div class="header-lower">
			<ul class="secondary-menu" role="menubar">
				<?php echo WT_MenuBar::getFavoritesMenu(); ?>
				<?php echo WT_MenuBar::getThemeMenu(); ?>
				<?php
				if (Auth::check()) {
					echo '<li><a href="edituser.php">', WT_Filter::escapeHtml(Auth::user()->getRealName()), '</a></li> <li>', logout_link(), '</li>';
					if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
						echo ' <li><a href="#" onclick="window.open(\'edit_changes.php\', \'_blank\', chan_window_specs); return false;" style="color:red;">', WT_I18N::translate('Pending changes'), '</a></li>';
					}
				} else {
					echo '<li>', login_link(), '</li> ';
				}
				?>
				<?php echo WT_MenuBar::getLanguageMenu(); ?>
			</ul>
		</div>
		<script src="js/jquery-1.11.2.js"></script>
		<script language="javascript" type="text/javascript">
		<!--
			/*
			jQuery(document).ready(function() {
				var level = 1;
				while (jQuery("#menu-tree li.level"+level).length) {
					if (level == 1) {	jQuery('#menu-tree').append('<div id="hoverMenu'+level+'"></div>'); }
					else { jQuery('#hoverMenu'+(level-1)).append('<div id="hoverMenu'+level+'"></div>'); }
					jQuery('#hoverMenu'+level).hide().addClass('hoverMenu');
					jQuery("#menu-tree li.level"+level).bind('mouseenter', function() {
						var level = 0;
						while (!jQuery(this).hasClass('level'+level)) { level++; }
						jQuery('#hoverMenu'+level+' ul').remove();
						if (jQuery(this).children('ul').length) {
							var subPos = jQuery(this).position();
							var Pos = jQuery(this).parent().position();
							var subMouseX = Pos.left + subPos.left + jQuery(this).parent().outerWidth();
							var subMouseY = Pos.top + subPos.top;
							var subMenu = jQuery(this).children('ul:first')
							subMenu.clone(true, true).show().appendTo('#hoverMenu'+level);
							jQuery('#hoverMenu'+level).css({'top':subMouseY,'left':subMouseX});
							jQuery('#hoverMenu'+level).show();
						}
					})
					jQuery('#hoverMenu'+level).mouseleave(function(){
						jQuery(this).find('ul').remove();
					})
					level++;
				}

				jQuery('#menu-tree').mouseleave(function() {
					jQuery('#hoverMenu1 ul').remove();
					jQuery('#hoverMenu1').hide();
				})
			})
			*/
			var DELAY_SUB = 400;
			var DELAY = 800;
			var timeoutID;
			var timeoutType;
			var jQueryObj;

			function SubMenuEnter() {
				var level = 1;
				while (!jQueryObj.hasClass('level'+level)) { level++; }
				jQuery('#hoverMenu'+level+' ul').remove();
				if (jQueryObj.children('ul').length) {
					var subPos = jQueryObj.position();
					var Pos = jQueryObj.parent().position();
					var subMouseX = Pos.left + subPos.left + jQueryObj.parent().outerWidth();
					var subMouseY = Pos.top + subPos.top;
					var subMenu = jQueryObj.children('ul:first')
					subMenu.clone(true, true).show().appendTo('#hoverMenu'+level);
					jQuery('#hoverMenu'+level).css({'top':subMouseY,'left':subMouseX});
					jQuery('#hoverMenu'+level).show();
				}
			}
			function SubMenuLeave() {
				jQueryObj.find('ul').remove();
			}
			function MenuLeave() {
				jQuery('#menu-tree').children('ul').hide();
				jQuery('#hoverMenu1 ul').remove();
			}

			jQuery(document).ready(function() {
				var level = 1;
				while (jQuery("#menu-tree li.level"+level).length) {
					if (level == 1) {	jQuery('#menu-tree').append('<div id="hoverMenu'+level+'"></div>'); }
					else { jQuery('#hoverMenu'+(level-1)).append('<div id="hoverMenu'+level+'"></div>'); }
					jQuery('#hoverMenu'+level).hide().addClass('hoverMenu');
					jQuery("#menu-tree li.level"+level).bind('mouseenter', function() {
						window.clearTimeout(timeoutID); //cancel not yet executed hide or show commmand
						jQueryObj = jQuery(this);
						timeoutID = window.setTimeout(SubMenuEnter, DELAY_SUB); //show submenu after delay
						timeoutType = 'enter';
					})
					jQuery('#hoverMenu'+level).bind('mouseenter', function() {
						if (timeoutType=='leave') {
							window.clearTimeout(timeoutID); //cancel not yet executed hide or show commmand
						}
					})
					jQuery('#hoverMenu'+level).mouseleave(function(){
						window.clearTimeout(timeoutID); //cancel not yet executed hide or show commmand
						jQueryObj = jQuery(this);
						timeoutID = window.setTimeout(SubMenuLeave, DELAY_SUB); //hide submenu after delay
						timeoutType = 'leave';
					})
					level++;
				}
				jQuery('.primary-menu > li:not(#menu-tree)').mouseenter(function() {
					MenuLeave(); //hide instantly
				})
				jQuery('#menu-tree').mouseenter(function() {
					jQuery(this).children('ul').show(); //show instantly
				})
				jQuery('#menu-tree').mouseleave(function() {
					window.clearTimeout(timeoutID); //cancel not yet executed hide or show commmand
					timeoutID = window.setTimeout(MenuLeave, DELAY); //hide after delay
					timeoutType = 'leave';
				})
			})
		-->
		</script>
		<nav>
			<ul class="primary-menu" role="menubar">
				<?php echo WT_MenuBar::getGedcomMenu();   ?>
				<?php echo WT_MenuBar::getMyPageMenu();   ?>
				<?php echo WT_MenuBar::getChartsMenu();   ?>
				<?php echo WT_MenuBar::getListsMenu();    ?>
				<?php echo WT_MenuBar::getCalendarMenu(); ?>
				<?php echo WT_MenuBar::getReportsMenu();  ?>
				<?php echo WT_MenuBar::getSearchMenu();   ?>
				<?php echo implode('', WT_MenuBar::getModuleMenus()); ?>
			</ul>
		</nav>
	</header>
	<?php } ?>
	<?php echo WT_FlashMessages::getHtmlMessages(); ?>
	<main id="content">
