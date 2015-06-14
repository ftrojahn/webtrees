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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

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
		$min = $date->date1->y - 10;
		$max = $date->date1->y;
		break;
	case 'AFT':
		$min = $date->date1->y;
		$max = $date->date1->y + 10;
		break;
	case 'ABT':
	case 'EST':
		$min = $date->date1->y - 5;
		$max = $date->date1->y + 5;
		break;
	case 'FROM':
	case 'BET':
		$min = $date->date1->y;
		$max = $date->date2->y;
		break;
	default:
		$min = $date->date1->y;
		$max = $date->date1->y;
	}	
}

function check_date($a, $b) {
	if ((($a->qual1!=NULL) && (in_array($a->qual1,array('FROM','BET','BEF','AFT','EST','ABT')))) || (($b->qual1!=NULL) && (in_array($b->qual1,array('FROM','BET','BEF','AFT','EST','ABT')))))
	{
		if (($a->date1->y==0) || ($b->date1->y==0)) return 1.0;
		date_range($a,$amin,$amax);
		date_range($b,$bmin,$bmax);
		if (($bmax<$amin) || ($bmin>$amax)) return 0.0;
		return 2.0;
	}
	else
	{
		if ((date_different($a->date1->y,$b->date1->y)) ||
			(date_different($a->date1->m,$b->date1->m)) ||
			(date_different($a->date1->d,$b->date1->d)))
		{
			return 0.0;
		}
		else
		{
			$newvalue=1.0;
			if (date_same($a->date1->y,$b->date1->y))
			{
				$newvalue++;
				if (date_same($a->date1->m,$b->date1->m))
				{
					$newvalue++;
					if (date_same($a->date1->d,$b->date1->d))
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

class duplicates_tab_WT_Module extends WT_Module implements WT_Module_Tab {
	private $duplicates;

	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Duplicates');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Sources” module */ WT_I18N::translate('A list showing possible duplicates of an individual.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 1000;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return ($this->get_duplicates()) && (count($this->get_duplicates())>1);
	}

	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return !$this->get_duplicates();
	}
	// Implement WT_Module_Tab
	public function getTabContent() {
		global $SEARCH_SPIDER,$controller;

		ob_start();
		echo '<style type="text/css">';
		echo '<!--';
		echo '.duplicates_table:hover {background-color:#efefef;}';
		echo '-->';
		echo '</style>';
		echo '<table class="facts_table">';
		echo '<thead><tr class="descriptionbox">';
		echo '<th>'.WT_Gedcom_Tag::getLabel('NAME').'</th>';
		echo '<th>'.WT_Gedcom_Tag::getLabel('BIRT').'</th>';
		echo '<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		echo '<th>'.WT_Gedcom_Tag::getLabel('DEAT').'</th>';
		echo '<th>'.WT_Gedcom_Tag::getLabel('PLAC').'</th>';
		echo '<th>'.WT_I18N::translate('Source').'</th>';
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
					$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $person)).'"';
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
					echo $date->Display(!$SEARCH_SPIDER);
				}				
			} else {
				echo '&nbsp;';				
			}
			echo '</td>';
			//Birth place
			echo '<td>';
			foreach ($person->getAllBirthPlaces() as $n=>$place) {
				$tmp=new WT_Place($place, WT_GED_ID);
				if ($n) {
					echo '<br>';
				}
				if ($SEARCH_SPIDER) {
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
					echo $date->Display(!$SEARCH_SPIDER);
				}				
			} else {
				echo '&nbsp;';				
			}
			echo '</td>';				
			//Death place
			echo '<td>';
			foreach ($person->getAllDeathPlaces() as $n=>$place) {
				$tmp=new WT_Place($place, WT_GED_ID);
				if ($n) {
					echo '<br>';
				}
				if ($SEARCH_SPIDER) {
					echo $tmp->getShortName();
				} else {
					echo '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
					echo $tmp->getShortName(). '</a>';
				}
			}
			echo '</td>';
			echo '<td>';
			echo '<a href="index.php?ctype=gedcom&amp;ged='.WT_TREE::get($person->getGedcomId())->tree_name_url.'">'.WT_TREE::get($person->getGedcomId())->tree_title_html.'</a>';
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
					$sdx_s = array_merge($sdx_s,explode(':',WT_Soundex::daitchMokotoff($name['surn'])));
					$sdx_g = array_merge($sdx_g,explode(':',WT_Soundex::daitchMokotoff($name['givn'])));
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
				}
				else
				{
					$sql.=" WHERE 0";
				}
			
				$rows=WT_DB::prepare($sql)->execute(array_merge($sdx_s,$sdx_g))->fetchAll();
				foreach ($rows as $row) {
					$person=WT_Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
				
					if (!$person->canShowName()) continue;
					if (($person->getXref()==$controller->record->getXref()) && ($person->getGedcomId()==$controller->record->getGedcomId())) continue;
					
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

	// Implement WT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}
}
