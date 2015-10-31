<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

use Fisharebest\Webtrees\Auth;
//use Fisharebest\Webtrees\Filter;
//use Fisharebest\Webtrees\Functions\FunctionsDb;
//use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
//use Fisharebest\Webtrees\Stats;
//use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleTabInterface;
use Fisharebest\Webtrees\Soundex;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\GedcomTag;

function date_different($a, $b) {
	if (($a==0) || ($b==0)) return false;
	return ($a!==$b);
}
	
function date_same($a, $b) {
	if (($a==0) || ($b==0)) return false;
	return ($a==$b);
}

function date_range($date,&$min, &$max) {
	switch ($date->qual1) {
	case 'BEF':
		$min = $date->minimumDate()->y - 10;
		$max = $date->minimumDate()->y;
		break;
	case 'AFT':
		$min = $date->minimumDate()->y;
		$max = $date->minimumDate()->y + 10;
		break;
	case 'ABT':
	case 'EST':
		$min = $date->minimumDate()->y - 5;
		$max = $date->minimumDate()->y + 5;
		break;
	case 'FROM':
	case 'BET':
		$min = $date->minimumDate()->y;
		$max = $date->maximumDate()->y;
		break;
	default:
		$min = $date->minimumDate()->y;
		$max = $date->minimumDate()->y;
	}	
}

function check_date($a, $b) {
	if ((($a->qual1!=NULL) && (in_array($a->qual1,array('FROM','BET','BEF','AFT','EST','ABT')))) || (($b->qual1!=NULL) && (in_array($b->qual1,array('FROM','BET','BEF','AFT','EST','ABT')))))
	{
		if (($a->minimumDate()->y==0) || ($b->minimumDate()->y==0)) return 1.0;
		date_range($a,$amin,$amax);
		date_range($b,$bmin,$bmax);
		if (($bmax<$amin) || ($bmin>$amax)) return 0.0;
		return 2.0;
	}
	else
	{
		if ((date_different($a->minimumDate()->y,$b->minimumDate()->y)) ||
			(date_different($a->minimumDate()->m,$b->minimumDate()->m)) ||
			(date_different($a->minimumDate()->d,$b->minimumDate()->d)))
		{
			return 0.0;
		}
		else
		{
			$newvalue=1.0;
			if (date_same($a->minimumDate()->y,$b->minimumDate()->y))
			{
				$newvalue++;
				if (date_same($a->minimumDate()->m,$b->minimumDate()->m))
				{
					$newvalue++;
					if (date_same($a->minimumDate()->d,$b->minimumDate()->d))
					{
						$newvalue++;
					}
				}
			}		
			return $newvalue;
		}
	}
}

function check_dates($dates1, $dates2) {
	if (!$dates1 || !$dates2) return 1.0;
	$value=0.0;
	foreach ($dates1 as $date1) {
		foreach ($dates2 as $date2) {
			$value=max($value,check_date($date1,$date2));
		}
	}
	return $value;
}

class DuplicatesTabModule extends AbstractModule implements ModuleTabInterface {
	private $duplicates;

	/** {@inheritdoc} */
	public function getTitle() {
		return /* I18N: Name of a module */ I18N::translate('Duplicates');
	}

	/** {@inheritdoc} */
	public function getDescription() {
		return /* I18N: Description of the “Sources” module */ I18N::translate('A list showing possible duplicates of an individual.');
	}

	/** {@inheritdoc} */
	public function defaultTabOrder() {
		return 1000;
	}

	/** {@inheritdoc} */
	public function hasTabContent() {
		return ($this->get_duplicates()) && (count($this->get_duplicates())>1);
	}

	/** {@inheritdoc} */
	public function isGrayedOut() {
		return !$this->get_duplicates();
	}
	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;
		global $WT_TREE;

		ob_start();		
		echo '<style type="text/css">';
		echo '<!--';
		echo '.duplicates_table:hover {background-color:#efefef;}';
		echo '-->';
		echo '</style>';
		echo '<table class="facts_table">';
		echo '<thead><tr class="descriptionbox">';
		echo '<th>'.GedcomTag::getLabel('NAME').'</th>';
		echo '<th>'.GedcomTag::getLabel('BIRT').'</th>';
		echo '<th>'.GedcomTag::getLabel('PLAC').'</th>';
		echo '<th>'.GedcomTag::getLabel('DEAT').'</th>';
		echo '<th>'.GedcomTag::getLabel('PLAC').'</th>';
		echo '<th>'.I18N::translate('Source').'</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		
		foreach ($this->get_duplicates() as $person) {
			echo '<tr class="optionbox duplicates_table">';
			if ($person->isPendingAddtion()) {
				$class = ' class="new"';
			} elseif ($person->isPendingDeletion()) {
				$class = ' class="old"';
			} else {
				$class = '';
			}
			//Name
			echo '<td>';
			foreach ($person->getAllNames() as $num=>$name) {
				if ($name['type']=='NAME') {
					$title='';
				} else {
					$title='title="'.strip_tags(GedcomTag::getLabel($name['type'], $person)).'"';
				}
				if ($num==$person->getPrimaryName()) {
					$class=' class="name2"';
					$sex_image=$person->getSexImage();
					list($surn, $givn)=explode(',', $name['sort']);
				} else {
					$class='';
					$sex_image='';
				}
				echo '<a '. $title. ' href="'. $person->getHtmlUrl(). '"'. $class. '>'. $name['full']. '</a>'. $sex_image. '<br>';
			}
			echo '</td>';
			//Birth date
			echo '<td>';
			if ($dates=$person->getAllBirthDates()) {
				foreach ($dates as $num=>$date) {
					if ($num) {
						echo '<br>';
					}
					echo $date->Display(!Auth::isSearchEngine());
				}				
			} else {
				echo '&nbsp;';				
			}
			echo '</td>';
			//Birth place
			echo '<td>';
			foreach ($person->getAllBirthPlaces() as $n=>$place) {
				$tmp=new Place($place, $person->getTree());
				if ($n) {
					echo '<br>';
				}
				if (Auth::isSearchEngine()) {
					echo $tmp->getShortName();
				} else {
					echo '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
					echo $tmp->getShortName(). '</a>';
				}
			}
			echo '</td>';
			//Death date
			echo '<td>';
			if ($dates=$person->getAllDeathDates()) {
				foreach ($dates as $num=>$date) {
					if ($num) {
						echo '<br>';
					}
					echo $date->Display(!Auth::isSearchEngine());
				}				
			} else {
				echo '&nbsp;';				
			}
			echo '</td>';				
			//Death place
			echo '<td>';
			foreach ($person->getAllDeathPlaces() as $n=>$place) {
				$tmp=new Place($place, $person->getTree());
				if ($n) {
					echo '<br>';
				}
				if (Auth::isSearchEngine()) {
					echo $tmp->getShortName();
				} else {
					echo '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
					echo $tmp->getShortName(). '</a>';
				}
			}
			echo '</td>';
			echo '<td>';
			echo '<a href="index.php?ctype=gedcom&amp;ged='.$person->getTree()->getNameUrl().'">'.$person->getTree()->getTitleHtml().'</a>';
			echo '</td>';
			
			echo '</tr>';
			if ($person==$controller->record){
				echo '<tr class="descriptionbox">';
				echo '<td colspan="6">';				
				echo '</td>';				
				echo '</tr>';
			}
		}
		echo '</tbody>';
		echo '</table>';		
		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}
	
	function check_given_names($names1,$names2) {
		if (!$names1 || !$names2) return false;
		foreach ($names1 as $name1) {
			foreach ($names2 as $name2) {
				if ($name1==$name2) return true;
			}
		}
		return false;
	}
	
	function get_duplicates() {
		global $controller;
		
		if ($this->duplicates === null) {
			$this->duplicates=array($controller->record);
			if (($controller->record->getAllEventDates('BIRT')) ||
				($controller->record->getAllEventDates('CHR')) ||
				($controller->record->getAllEventDates('DEAT')) ||
				($controller->record->getAllEventDates('BURI')))
			{
				$sql ="SELECT DISTINCT ind.i_id AS xref, ind.i_file AS gedcom_id, ind.i_gedcom AS gedcom FROM `##individuals` ind";
				$sql.=" JOIN `##name` i_n ON (i_n.n_file=ind.i_file AND i_n.n_id=ind.i_id)";
			
				$names=$controller->record->getAllNames();
			
				$sdx_s = array();
				$sdx_g = array();
				foreach($names as $name)
				{
					$sdx_s = array_merge($sdx_s,explode(':',Soundex::daitchMokotoff($name['surn'])));
					$sdx_g = array_merge($sdx_g,explode(':',Soundex::daitchMokotoff($name['givn'])));
				}
				$sdx_s = array_unique($sdx_s);
				$sdx_g = array_unique($sdx_g);
				if ((count($sdx_s)>0) && (count($sdx_g)>0))
				{
					$sql.=" WHERE ((i_n.n_soundex_surn_dm LIKE CONCAT('%', ?, '%'))";
					for ($i=1;$i<count($sdx_s);$i++){
						$sql.=" OR (i_n.n_soundex_surn_dm LIKE CONCAT('%', ?, '%'))";
					}
					$sql .= ")";

					$sql.=" AND ((i_n.n_soundex_givn_dm LIKE CONCAT('%', ?, '%'))";
					for ($i=1;$i<count($sdx_g);$i++){
						$sql.=" OR (i_n.n_soundex_givn_dm LIKE CONCAT('%', ?, '%'))";
					}
					$sql .= ")";

					if ($controller->record->getSex() == 'M') {
						$sql.=" AND (ind.i_sex IN ('U', 'M'))";
					}
					elseif ($controller->record->getSex() == 'F') {
						$sql.=" AND (ind.i_sex IN ('U', 'F'))";
					}
					else {
						$sql.=" AND (ind.i_sex IN ('U', 'M', 'F'))";
					}
				}
				else
				{
					$sql.=" WHERE 0";
				}
			
				$rows=Database::prepare($sql)->execute(array_merge($sdx_s,$sdx_g))->fetchAll();
				foreach ($rows as $row) {
					$person=Individual::getInstance($row->xref, Tree::findById($row->gedcom_id), $row->gedcom);
				
					if (!$person->canShowName()) continue;
					if (($person->getXref()==$controller->record->getXref()) && ($person->getTree()->getTreeId()==$controller->record->getTree()->getTreeId())) continue;
					
					$c_birt =	check_dates($person->getAllEventDates('BIRT'),$controller->record->getAllEventDates('BIRT'));
					$c_chr  =	check_dates($person->getAllEventDates('CHR'),$controller->record->getAllEventDates('CHR'));
					$c_deat	=	check_dates($person->getAllEventDates('DEAT'),$controller->record->getAllEventDates('DEAT'));
					$c_buri	=	check_dates($person->getAllEventDates('BURI'),$controller->record->getAllEventDates('BURI'));
					if ($c_birt*$c_chr*$c_deat*$c_buri>=2){
						$this->duplicates[]=$person;					
					}				
				}
			}
		}
		return $this->duplicates;
	}

	/** {@inheritdoc} */
	public function canLoadAjax() {
		return !Auth::isSearchEngine(); // Search engines cannot use AJAX
	}

	/** {@inheritdoc} */
	public function getPreLoadContent() {
		return '';
	}
}

return new DuplicatesTabModule(__DIR__);
