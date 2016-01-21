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

use Fisharebest\Webtrees\Controller\PageController;

define('WT_SCRIPT_NAME', 'admin_trees_manager_overview.php');
require 'includes/session.php';

$controller = new PageController;
$controller
	->restrictAccess(Auth::isAdmin())
	->setPageTitle(I18N::translate('Tree Manager Overview'));

$all_trees = Tree::getAll();

$controller->pageHeader();

?>
<ol class="breadcrumb small">
	<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
	<li><a href="admin_trees_manage.php"><?php echo I18N::translate('Manage family trees'); ?></a></li>
	<li class="active"><?php echo $controller->getPageTitle(); ?></li>
</ol>

<h1><?php echo $controller->getPageTitle(); ?></h1>

	<table class="table table-bordered table-hover table-condensed table-module-administration">
		<caption class="sr-only">
			<?php echo I18N::translate('Module administration'); ?>
		</caption>
		<thead>
		<tr>
			<th><?php echo I18N::translate('Family tree title'); ?></th>
			<th><?php echo I18N::translate('Family tree'); ?></th>
			<th><?php echo I18N::translate('Real name'); ?></th>
			<th><?php echo I18N::translate('Username'); ?></th>
			<th><?php echo I18N::translate('Role'); ?></th>
			<th><?php echo I18N::translate('Email address'); ?></th>
			<th><?php echo I18N::translate('Email address alias'); ?></th>
		</tr>
		</thead>
		<tbody>
			<?php
				// Valid values for form variables
				$ALL_EDIT_OPTIONS = array(
					'none'   => /* I18N: Listbox entry; name of a role */ I18N::translate('Visitor'),
					'access' => /* I18N: Listbox entry; name of a role */ I18N::translate('Member'),
					'edit'   => /* I18N: Listbox entry; name of a role */ I18N::translate('Editor'),
					'accept' => /* I18N: Listbox entry; name of a role */ I18N::translate('Moderator'),
					'admin'  => /* I18N: Listbox entry; name of a role */ I18N::translate('Manager'),
				);

				$email_aliases_filename = WT_DATA_DIR . 'aliases.csv';
				$email_aliases = array();
				if (file_exists($email_aliases_filename)) {
					$fp = fopen($email_aliases_filename, 'r');
					if ($fp) {
						while (($data = fgetcsv($fp, 0, "\t")) !== false) {
							$email_aliases[strtolower($data[1])] = $data[0];
						}
						fclose($fp);
					}
				}

				foreach($all_trees as $tree) {
					$users = Database::prepare("SELECT tu.user_id, tugs.setting_value FROM `##user` AS tu JOIN `##user_gedcom_setting` AS tugs ON tu.user_id = tugs.user_id WHERE gedcom_id = ? AND tugs.setting_name = 'canedit' AND tugs.setting_value IN ('admin','accept','edit')")->execute(array($tree->getTreeId()))->fetchAll();

					echo '<tr>';
					echo '<td rowspan="'.max(1,sizeof($users)).'">' . $tree->getTitleHtml() . '</td>';
					echo '<td rowspan="'.max(1,sizeof($users)).'">' . $tree->getNameHtml() . '</td>';
					if (sizeof($users) == 0) {
						echo '<td></td><td></td><td></td><td></td><td></td></tr>';
					}
					else {
						$first = true;
						foreach($users as $user) {
							if (!$first) {
								echo '<tr>';
							}

							$userclass = User::find($user->user_id);
							echo '<td><a href="admin_users.php?action=edit&user_id=' . $userclass->getUserId() . '">' . $userclass->getRealNameHtml() . '</a></td>';
							echo '<td><a href="admin_users.php?action=edit&user_id=' . $userclass->getUserId() . '"><span dir="auto">'.$userclass->getUserName().'</span></a></td>';
							echo '<td>'.$ALL_EDIT_OPTIONS[$user->setting_value].'</td>';
							echo '<td><a href="#" onclick="return message(\'' . Filter::escapeHtml($userclass->getUserName()) . '\', \'\', \'\');">' . Filter::escapeHtml($userclass->getEmail()) . '</a></td>';
							if (array_key_exists(strtolower($userclass->getEmail()), $email_aliases)) {
								echo '<td><span dir="auto">'.$email_aliases[strtolower($userclass->getEmail())].'</span></td>';
							}
							else {
								echo '<td></td>';
							}
							echo '</tr>';

							$first = false;
						}
					}
				}
			?>
		</tbody>
	</table>
