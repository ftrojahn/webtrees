<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Fisharebest\Webtrees\Theme;

use Fisharebest\Webtrees\I18N;

/**
 * The xenea theme.
 */
class XeneaTheme extends AbstractTheme implements ThemeInterface {
	/**
	 * Where are our CSS, JS and other assets?
	 *
	 * @return string A relative path, such as "themes/foo/"
	 */
	public function assetUrl() {
		return 'themes/xenea/css-1.7.0/';
	}

	/**
	 * Add markup to a flash message.
	 *
	 * @param \stdClass $message
	 *
	 * @return string
	 */
	protected function flashMessageContainer(\stdClass $message) {
		// This theme uses jQuery markup.
		return '<p class="ui-state-highlight">' . $message->text . '</p>';
	}

	/**
	 * Create a search field and submit button for the quick search form in the header.
	 *
	 * @return string
	 */
	protected function formQuickSearchFields() {
		return
			'<input type="search" name="query" size="12" placeholder="' . I18N::translate('Search') . '">' .
			'<input type="submit" name="search" value="&gt;">';
	}

	/**
	 * Create the contents of the <header> tag.
	 *
	 * @return string
	 */
	protected function headerContent() {
		return
			//$this->accessibilityLinks() .
			'<div class="header-upper">' .
			$this->formatTreeTitle() .
			$this->formQuickSearch() .
		'</div>' .
		'<div class="header-lower">' .
			$this->formatSecondaryMenu() .
		'</div>';
	}

	/**
	 * Allow themes to add extra scripts to the page footer.
	 *
	 * @return string
	 */
	public function hookFooterExtraJavascript() {
		return
			'<script src="' . WT_JQUERY_COLORBOX_URL . '"></script>' .
			'<script src="' . WT_JQUERY_WHEELZOOM_URL . '"></script>' .
			'<script>' .
			'activate_colorbox();' .
			'jQuery.extend(jQuery.colorbox.settings, {' .
			' width: "85%",' .
			' height: "85%",' .
			' transition: "none",' .
			' slideshowStart: "' . I18N::translate('Play') . '",' .
			' slideshowStop: "' . I18N::translate('Stop') . '",' .
			' title: function() { return jQuery(this).data("title"); }' .
			'});' .
			'</script>' .
			'<script>' .
			'var DELAY_SUB = 400;' .
			'var DELAY = 800;' .
			'var timeoutID;' .
			'var timeoutType;' .
			'var jQueryObj;' .

			'function SubMenuEnter() {' .
			'	var level = 1;' .
			'	while (!jQueryObj.hasClass(\'level\'+level)) { level++; }' .
			'	jQuery(\'#hoverMenu\'+level+\' ul\').remove();' .
			'	if (jQueryObj.children(\'ul\').length) {' .
			'		var subPos = jQueryObj.position();' .
			'		var Pos = jQueryObj.parent().position();' .
			'		var subMouseX = Pos.left + subPos.left + jQueryObj.parent().outerWidth();' .
			'		var subMouseY = Pos.top + subPos.top;' .
			'		var subMenu = jQueryObj.children(\'ul:first\');' .
			'		subMenu.clone(true, true).show().appendTo(\'#hoverMenu\'+level);' .
			'		jQuery(\'#hoverMenu\'+level).css({\'top\':subMouseY,\'left\':subMouseX});' .
			'		jQuery(\'#hoverMenu\'+level).show();' .
			'	}' .
			'}' .

			'function SubMenuLeave() {' .
			'	jQueryObj.find(\'ul\').remove();' .
			'}' .

			'function MenuLeave() {' .
			'	window.clearTimeout(timeoutID);' .
			'	jQuery(\'.menu-tree\').children(\'ul\').hide();' .
			'	jQuery(\'#hoverMenu1 ul\').remove();' .
			'}' .

			'jQuery(document).ready(function() {' .
			'	jQuery(".primary-menu > li").addClass(\'level0\');' .
			'	var level = 1;' .
			'	while (jQuery(".menu-tree li.level"+level).length) {' .
			'		if (level == 1) {	jQuery(\'.menu-tree\').append(\'<div id="hoverMenu\'+level+\'"></div>\'); }' .
			'		else { jQuery(\'#hoverMenu\'+(level-1)).append(\'<div id="hoverMenu\'+level+\'"></div>\'); }' .
			'		jQuery(\'#hoverMenu\'+level).hide().addClass(\'hoverMenu\');' .
			'		jQuery(".menu-tree li.level"+level).bind(\'mouseenter\', function() {' .
			'			window.clearTimeout(timeoutID);' .  //cancel not yet executed hide or show commmand
			'			jQueryObj = jQuery(this);' .
			'			timeoutID = window.setTimeout(SubMenuEnter, DELAY_SUB);' . //show submenu after delay
			'			timeoutType = \'enter\';' .
			'		});' .
			'		jQuery(\'#hoverMenu\'+level).bind(\'mouseenter\', function() {' .
			'			if (timeoutType==\'leave\') {' .
			'				window.clearTimeout(timeoutID);' . //cancel not yet executed hide or show commmand
			'			}' .
			'		});' .
			'		jQuery(\'#hoverMenu\'+level).mouseleave(function(){' .
			'			window.clearTimeout(timeoutID);' . //cancel not yet executed hide or show commmand
			'			jQueryObj = jQuery(this);' .
			'			timeoutID = window.setTimeout(SubMenuLeave, DELAY_SUB);' . //hide submenu after delay
			'			timeoutType = \'leave\';' .
			'		});' .
			'		level++;' .
			'	}' .
			'	jQuery(\'.primary-menu > li:not(.menu-tree)\').mouseenter(function() {' .
			'		MenuLeave();' . //hide instantly
			'	});' .
			'	jQuery(\'.menu-tree\').mouseenter(function() {' .
			'		jQuery(this).children(\'ul\').show();' . //show instantly
			'		window.clearTimeout(timeoutID);' . //cancel not yet executed hide or show commmand
			'	});' .
			'	jQuery(\'.menu-tree\').mouseleave(function() {' .
			'		window.clearTimeout(timeoutID);' . //cancel not yet executed hide or show commmand
			'		timeoutID = window.setTimeout(MenuLeave, DELAY);' . //hide after delay
			'		timeoutType = \'leave\';' .
			'	});' .
			'});' .
			'</script>';
	}

	/**
	 * Misecellaneous dimensions, fonts, styles, etc.
	 *
	 * @param string $parameter_name
	 *
	 * @return string|int|float
	 */
	public function parameter($parameter_name) {
		$parameters = array(
			'chart-background-f'             => 'e9daf1',
			'chart-background-m'             => 'b1cff0',
			'distribution-chart-high-values' => '84beff',
			'distribution-chart-low-values'  => 'c3dfff',
		);

		if (array_key_exists($parameter_name, $parameters)) {
			return $parameters[$parameter_name];
		} else {
			return parent::parameter($parameter_name);
		}
	}

	/**
	 * A list of CSS files to include for this page.
	 *
	 * @return string[]
	 */
	protected function stylesheets() {
		return array(
			'themes/xenea/jquery-ui-1.11.2/jquery-ui.css',
			$this->assetUrl() . 'style.css',
		);
	}

	/**
	 * A fixed string to identify this theme, in settings, etc.
	 *
	 * @return string
	 */
	public function themeId() {
		return 'xenea';
	}

	/**
	 * What is this theme called?
	 *
	 * @return string
	 */
	public function themeName() {
		return /* I18N: Name of a theme. */ I18N::translate('xenea');
	}
}
