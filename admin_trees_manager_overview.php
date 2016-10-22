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

				//$email_alias:
				//	key: real address (messages are forwarded to this address)
				//	value: alias address (messages get send to this address; might have multiple aliases)
				$email_aliases = array();
				if (file_exists($email_aliases_filename)) {
					$fp = fopen($email_aliases_filename, 'r');
					if ($fp) {
						while (($data = fgetcsv($fp, 0, "\t")) !== false) {
							if (array_key_exists(1, $data)) {
								$emails = explode(',',$data[1]);
								foreach ($emails as $email) {
									if (!array_key_exists(strtolower($email),$email_aliases)) {
										$email_aliases[strtolower($email)] = array();
									}
									$email_aliases[strtolower($email)][] = $data[0];
								}
							}
						}
						fclose($fp);
					}
				}
				//$email_alias_reverse:
				//	key: alias address (messages get send to this address)
				//	value: real address (messages are forwarded to this address; might have multiple recipients)
				$email_aliases_reverse = array();
				foreach($email_aliases as $key => $email_alias) {
					foreach($email_alias as $email) {
						if (!array_key_exists(strtolower($email),$email_aliases_reverse)) {
							$email_aliases_reverse[strtolower($email)] = array();
						}
						$email_aliases_reverse[strtolower($email)][] = $key;
					}
				}

				foreach($all_trees as $tree) {
					$users = Database::prepare("SELECT tu.user_id, tugs.setting_value FROM `##user` AS tu JOIN `##user_gedcom_setting` AS tugs ON tu.user_id = tugs.user_id WHERE gedcom_id = ? AND tugs.setting_name = 'canedit' AND tugs.setting_value IN ('admin','accept','edit')")->execute(array($tree->getTreeId()))->fetchAll();

					echo '<tr>';
					echo '<td rowspan="'.max(1,sizeof($users)).'"><a href="index.php?ctype=gedcom&ged=' . $tree->getName() . '">' . $tree->getTitleHtml() . '</a></td>';
					echo '<td rowspan="'.max(1,sizeof($users)).'"><a href="admin_trees_config.php?action=general&ged=' . $tree->getName() . '">' . $tree->getNameHtml() . '</a></td>';
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
							echo '<td><a href="admin_users.php?action=edit&user_id=' . $userclass->getUserId() . '">' . $userclass->getRealNameHtml() . '</a>';
							if ($userclass->getPreference('comment') !== '') {
								echo '<sup title="' . Filter::escapeHtml($userclass->getPreference('comment')) . '">(*)</sup>';
							}
							echo '</td>';
							echo '<td><a href="admin_users.php?action=edit&user_id=' . $userclass->getUserId() . '"><span dir="auto">'.$userclass->getUserName().'</span></a></td>';
							echo '<td>'.$ALL_EDIT_OPTIONS[$user->setting_value].'</td>';
							echo '<td><a href="#" onclick="return message(\'' . Filter::escapeHtml($userclass->getUserName()) . '\', \'\', \'\');">' . Filter::escapeHtml($userclass->getEmail()) . '</a></td>';

							//find aliases that could be used to reach user
							if (array_key_exists(strtolower($userclass->getEmail()), $email_aliases)) {
								$aliases = $email_aliases[strtolower($userclass->getEmail())];
							}
							else {
								$aliases = array();
							}
							echo '<td><span dir="auto">';
							foreach($aliases as $email) {
								if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
									//find all recipients for this alias
									if (array_key_exists(strtolower($email), $email_aliases_reverse)) {
										$recipients = $email_aliases_reverse[strtolower($email)];
									}
									else {
										$recipients = array();
									}
									$count = 0;
									foreach($recipients as $recipient) {
										if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
											$count++;
										}
									}
									echo $email;
									if ($count>1) {
										echo '<sup title="'. I18N::translate('Recipients') .':&#013;'.implode('&#013;',$recipients).'">(*)</sup>';
									}
									echo '<br>';
								}
							}
							echo '</span></td>';
							echo '</tr>';

							$first = false;
						}
					}
				}
			?>
		</tbody>
	</table>
