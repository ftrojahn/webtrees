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
namespace Fisharebest\Webtrees;

use Fisharebest\Webtrees\Controller\SearchController;
use Fisharebest\Webtrees\Functions\FunctionsPrint;

define('WT_SCRIPT_NAME', 'search.php');
require './includes/session.php';
require WT_DATA_DIR.'search_config.ini.php';

$controller = new SearchController;
$controller
	->pageHeader()
	->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');

?>
<script>
function checknames(frm) {
	action = "<?php echo $controller->action; ?>";
	if (action === "general") {
		if (frm.query.value.length<2) {
			alert("<?php echo I18N::translate('Please enter more than one character.'); ?>");
			frm.query.focus();
			return false;
		}
	} else if (action === "soundex") {
		year = frm.year.value;
		fname = frm.firstname.value;
		lname = frm.lastname.value;
		place = frm.place.value;

		if (year == "") {
			if (fname.length < 2 && lname.length < 2 && place.length < 2) {
				alert("<?php echo I18N::translate('Please enter more than one character.'); ?>");
				return false;
			}
		}

		if (year != "") {
			if (fname === "" && lname === "" && place === "") {
				alert("<?php echo I18N::translate('Please enter a given name, surname, or place in addition to the year'); ?>");
				frm.firstname.focus();
				return false;
			}
		}
		return true;
	}
	return true;
}
</script>

<div id="search-page">
<h2><?php echo $controller->getPageTitle(); ?></h2>

<?php if ($controller->action === 'general'): ?>

	<form name="searchform" onsubmit="return checknames(this);">
		<input type="hidden" name="action" value="general">
		<input type="hidden" name="isPostBack" value="true">
		<div id="search-page-table">
			<div class="label">
				<?php echo I18N::translate('Search for'); ?>
			</div>
			<div class="value">
				<input id="query" type="text" name="query" value="<?php echo Filter::escapeHtml($controller->query); ?>" size="40" autofocus>
				<?php echo FunctionsPrint::printSpecialCharacterLink('query'); ?>
			</div>
			<div class="label">
				<?php echo I18N::translate('Records'); ?>
			</div>
			<div class="value">
				<label>
					<input type="checkbox" <?php echo $controller->srindi; ?> value="checked" name="srindi">
					<?php echo I18N::translate('Individuals'); ?>
				</label>
				<br>
				<label>
					<input type="checkbox" <?php echo $controller->srfams; ?> value="checked" name="srfams">
					<?php echo I18N::translate('Families'); ?>
				</label>
				<br>
				<label>
					<input type="checkbox" <?php echo $controller->srsour; ?> value="checked" name="srsour">
					<?php echo I18N::translate('Sources'); ?>
				</label>
				<br>
				<label>
					<input type="checkbox" <?php echo $controller->srnote; ?> value="checked" name="srnote">
					<?php echo I18N::translate('Shared notes'); ?>
				</label>
			</div>
			<div class="label">
				<?php echo I18N::translate('Associates'); ?>
			</div>
			<div class="value">
				<input type="checkbox" id="showasso" name="showasso" value="on" <?php echo $controller->showasso === 'on' ? 'checked' : ''; ?>>
				<label for="showasso">
					<?php echo I18N::translate('Show related individuals/families'); ?>
				</label>
			</div>
			<div class="label"></div>
			<div class="value">
				<input type="button" value="<?php echo /* I18N: select all (of the family trees) */ I18N::translate('select all'); ?>" onclick="jQuery('#search_trees :checkbox').each(function(){jQuery(this).prop('checked', true);});return false;">
				<input type="button" value="<?php echo /* I18N: select none (of the family trees) */ I18N::translate('select none'); ?>" onclick="jQuery('#search_trees :checkbox').each(function(){jQuery(this).prop('checked', false);});return false;">
				<input type="button" value="<?php echo I18N::translate('invert selection'); ?>" onclick="jQuery('#search_trees :checkbox').each(function(){jQuery(this).prop('checked', !jQuery(this).prop('checked'));});return false;">
				</div>
			<div class="label">
				<?php echo I18N::translate('Family trees'); ?>
			</div>
			<div id="search_trees" class="value">
				<?php
				function addtoGroup(&$group, $str, $tree) {
					if (strpos($str, ':') !== false) {
						$groupname = trim(substr($str, 0, strpos($str, ':')));
						$str = trim(substr($str, strpos($str, ':') + 1));
						addtoGroup($group[$groupname], $str, $tree);
					}
					else {
						$group[] = $tree;
					}
				}
				function allChecked($groups, $controller) {
					foreach ($groups as $group) {
						if (gettype($group)=='array') {
							if (!allChecked($group, $controller)) return false;
						}
						else {
							$found = false;
							foreach ($controller->search_trees as $tree) {
								if ($tree->getTreeId() == $group->getTreeId())
								{
									$found = true;
									break;
								}
							}
							if (!$found) return false;
						}
					}
					return true;
				}
				function createMenu($groups, $controller, &$groupindex, $level) {
					foreach ($groups as $groupname=>$group) {
						if (gettype($group)=='array') {
							echo '<p><input type="checkbox" id="grp_' . $groupindex . '" value="yes" onclick="jQuery(\'#search_group_' . $groupindex . ' :checkbox\').each(function(value){jQuery(this).prop(\'checked\', value)},[jQuery(this).prop(\'checked\')])"' . (allChecked($group, $controller) ? ' checked' : '') . '>';
							echo '<a href="#" onclick="jQuery(\'#search_group_' . $groupindex . '\').is(\':hidden\')?jQuery(\'#search_group_' . $groupindex . '\').show():jQuery(\'#search_group_' . $groupindex . '\').hide(); return false;">' . $groupname . '</a>';							
							echo '<div id="search_group_' . $groupindex . '" style="margin-left:18px;display:none">';
							$groupindex++;
							createMenu($group, $controller, $groupindex, $level+1);
							echo '</div><p>';
						}
					}
					foreach ($groups as $groupname=>$group) {
						if (gettype($group)=='object') {
							echo '<p>';
							echo '<input type="checkbox" value="yes" id="tree_' . $group->getTreeId() . '" name="tree_' . $group->getTreeId() . '"';
							foreach ($controller->search_trees as $tree) {
								if ($tree->getTreeId() == $group->getTreeId()) {
									echo ' checked';
									break;
								}
							}
							echo '>';
							if (strrpos($group->getTitle(), ':')>0) {
								$title_short = trim(substr($group->getTitle(), strrpos($group->getTitle(), ':')+1));
							}
							else {
								$title_short = $group->getTitle();
							}
							echo '<label for="tree_' . $group->getTreeId() . '"><span dir="auto">' . Filter::escapeHtml($title_short) . '</span></label>';
							echo '</p>';
						}
					}
				}
				//Create Groups
				$groups = array();
				foreach (Tree::getAllIgnoreAccess() as $tree) {
					if (!in_array($tree->getName(),$search_excluded_trees)){
						if (strpos($tree->getTitle(), ':') !== false){
							addtoGroup($groups, $tree->getTitle(), $tree);
						}
						else{
							$groups[I18N::translate('Miscellaneous')][] = $tree;
						}
					}
				}
				$groupindex = 0;
				createMenu($groups, $controller, $groupindex,0);
				?>
			</div>

			<div class="label"></div>
			<div class="value">
				<input type="submit" value="<?php echo /* I18N: button label */ I18N::translate('Search'); ?>">
			</div>
		</div>
	</form>

<?php endif; ?>
<?php if ($controller->action === 'replace'): ?>
	
	<form method="post" name="searchform" onsubmit="return checknames(this);">
		<input type="hidden" name="action" value="replace">
		<input type="hidden" name="isPostBack" value="true">
		<div id="search-page-table">
			<div class="label">
				<?php echo I18N::translate('Search for'); ?>
			</div>
			<div class="value">
				<input name="query" value="<?php echo Filter::escapeHtml($controller->query); ?>" type="text" autofocus>
			</div>
			<div class="label">
				<?php echo I18N::translate('Replace with'); ?>
			</div>
			<div class="value">
				<input name="replace" value="<?php echo Filter::escapeHtml($controller->replace); ?>" type="text">
			</div>
			<script>
				function checkAll(box) {
					if (box.checked) {
						box.form.replaceNames.disabled = true;
						box.form.replacePlaces.disabled = true;
						box.form.replacePlacesWord.disabled = true;
						box.form.replaceNames.checked = false;
						box.form.replacePlaces.checked = false;
						box.form.replacePlacesWord.checked = false;
					} else {
						box.form.replaceNames.disabled = false;
						box.form.replacePlaces.disabled = false;
						box.form.replacePlacesWord.disabled = false;
						box.form.replaceNames.checked = true;
					}
				}
			</script>
			<div class="label">
				<?php echo I18N::translate('Search'); ?>
			</div>
			<div class="value">
				<p>
					<label>
					<input <?php echo $controller->replaceAll; ?> onclick="checkAll(this);" value="checked" name="replaceAll" type="checkbox">
						<?php echo I18N::translate('Entire record'); ?>
					</label>
					<hr>
				</p>
				<p>
					<label>
						<input <?php echo $controller->replaceNames; ?> <?php echo $controller->replaceAll ? 'disabled' : ''; ?> value="checked" name="replaceNames" type="checkbox">
						<?php echo I18N::translate('Names'); ?>
					</label>
				</p>
				<p>
					<label>
						<input <?php echo $controller->replacePlaces; ?> <?php echo $controller->replaceAll ? 'disabled' : ''; ?> value="checked" name="replacePlaces" type="checkbox">
						<?php echo I18N::translate('Places'); ?>
					</label>
				</p>
				<p>
					<label>
					<input <?php echo $controller->replacePlacesWord; ?> <?php echo $controller->replaceAll ? 'disabled' : ''; ?> value="checked" name="replacePlacesWord" type="checkbox">
						<?php echo I18N::translate('Whole words only'); ?>
					</label>
				</p>
			</div>

			<div class="label"></div>
			<div class="value">
				<input type="submit" value="<?php echo /* I18N: button label */ I18N::translate('Replace'); ?>">
			</div>
		</div>
	</form>

<?php endif; ?>
<?php if ($controller->action == "soundex"): ?>

	<form name="searchform" onsubmit="return checknames(this);">
		<input type="hidden" name="action" value="soundex">
		<input type="hidden" name="isPostBack" value="true">
		<div id="search-page-table">
			<div class="label">
				<?php echo I18N::translate('Given name'); ?>
			</div>
			<div class="value">
				<input type="text" data-autocomplete-type="GIVN" name="firstname" value="<?php echo Filter::escapeHtml($controller->firstname); ?>" autofocus>
			</div>
			<div class="label">
				<?php echo I18N::translate('Surname'); ?>
			</div>
			<div class="value">
				<input type="text" data-autocomplete-type="SURN" name="lastname" value="<?php echo Filter::escapeHtml($controller->lastname); ?>">
			</div>
			<div class="label">
				<?php echo I18N::translate('Place'); ?>
			</div>
			<div class="value">
				<input type="text"  data-autocomplete-type="PLAC2" name="place" value="<?php echo Filter::escapeHtml($controller->place); ?>">
			</div>
			<div class="label">
				<?php echo I18N::translate('Year'); ?>
			</div>
			<div class="value"><input type="text" name="year" value="<?php echo Filter::escapeHtml($controller->year); ?>">
			</div>
			<div class="label">
				<?php echo I18N::translate('Phonetic algorithm'); ?>
			</div>
			<div class="value">
				<p>
					<input type="radio" name="soundex" value="Russell" <?php echo $controller->soundex === 'Russell' ? 'checked' : ''; ?>>
					<?php echo I18N::translate('Russell'); ?>
				</p>
				<p>
					<input type="radio" name="soundex" value="DaitchM" <?php echo $controller->soundex === 'DaitchM' || $controller->soundex === '' ? 'checked' : ''; ?>>
 					<?php echo I18N::translate('Daitch-Mokotoff'); ?>
 				</p>
			</div>
			<div class="label">
				<?php echo I18N::translate('Associates'); ?>
			</div>
			<div class="value">
				<input type="checkbox" name="showasso" value="on" <?php echo $controller->showasso === 'on' ? 'checked' : ''; ?>>
				<?php echo I18N::translate('Show related individuals/families'); ?>
			</div>
			<div class="label"></div>
			<div class="value">
				<input type="button" value="<?php echo /* I18N: select all (of the family trees) */ I18N::translate('select all'); ?>" onclick="jQuery('#search_trees :checkbox').each(function(){jQuery(this).attr('checked', true);});return false;">
				<input type="button" value="<?php echo /* I18N: select none (of the family trees) */ I18N::translate('select none'); ?>" onclick="jQuery('#search_trees :checkbox').each(function(){jQuery(this).attr('checked', false);});return false;">
				<input type="button" value="<?php echo I18N::translate('invert selection'); ?>" onclick="jQuery('#search_trees :checkbox').each(function(){jQuery(this).attr('checked', !jQuery(this).attr('checked'));});return false;">
			</div>
			<div class="label">
				<?php echo I18N::translate('Family trees'); ?>
			</div>
			<div id="search_trees" class="value">
			<?php
			function addtoGroup(&$group, $str, $tree) {
				if (strpos($str, ':') !== false) {
					$groupname = trim(substr($str, 0, strpos($str, ':')));
					$str = trim(substr($str, strpos($str, ':') + 1));
					addtoGroup($group[$groupname], $str, $tree);
				}
				else {
					$group[] = $tree;
				}
			}
			function allChecked($groups, $controller) {
				foreach ($groups as $group) {
					if (gettype($group)=='array') {
						if (!allChecked($group, $controller)) return false;
					}
					else {
						$found = false;
						foreach ($controller->search_trees as $tree) {
							if ($tree->getTreeId() == $group->getTreeId())
							{
								$found = true;
								break;
							}
						}
						if (!$found) return false;
					}
				}
				return true;
			}
			function createMenu($groups, $controller, &$groupindex, $level) {
				foreach ($groups as $groupname=>$group) {
					if (gettype($group)=='array') {
						echo '<p><input type="checkbox" id="grp_' . $groupindex . '" value="yes" onclick="jQuery(\'#search_group_' . $groupindex . ' :checkbox\').each(function(value){jQuery(this).prop(\'checked\', value)},[jQuery(this).prop(\'checked\')])"' . (allChecked($group, $controller) ? ' checked' : '') . '>';
						echo '<a href="#" onclick="jQuery(\'#search_group_' . $groupindex . '\').is(\':hidden\')?jQuery(\'#search_group_' . $groupindex . '\').show():jQuery(\'#search_group_' . $groupindex . '\').hide(); return false;">' . $groupname . '</a>';							
						echo '<div id="search_group_' . $groupindex . '" style="margin-left:18px;display:none">';
						$groupindex++;
						createMenu($group, $controller, $groupindex, $level+1);
						echo '</div><p>';
					}
				}
				foreach ($groups as $groupname=>$group) {
					if (gettype($group)=='object') {
						echo '<p>';
						echo '<input type="checkbox" value="yes" id="tree_' . $group->getTreeId() . '" name="tree_' . $group->getTreeId() . '"';
						foreach ($controller->search_trees as $tree) {
							if ($tree->getTreeId() == $group->getTreeId()) {
								echo ' checked';
								break;
							}
						}
						echo '>';
						echo '<label for="tree_' . $group->getTreeId() . '">' . $group->getTitleHtml() . '</label>';
						echo '</p>';
					}
				}
			}
			//Create Groups
			$groups = array();
			foreach (Tree::getAllIgnoreAccess() as $tree) {
				if (!in_array($tree->getName(),$search_excluded_trees)){
					if (strpos($tree->getTitle(), ':') !== false){
						addtoGroup($groups, $tree->getTitle(), $tree);
					}
					else{
						$groups[I18N::translate('Miscellaneous')][] = $tree;
					}
				}
			}
			$groupindex = 0;
			createMenu($groups, $controller, $groupindex,0);
			?>
			</div>

			<div class="label"></div>
			<div class="value">
				<input type="submit" value="<?php echo  /* I18N: button label */ I18N::translate('Search'); ?>">
			</div>
		</div>
	</form>

<?php endif; ?>

<?php $controller->printResults(); ?>

</div>