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
namespace Fisharebest\Webtrees\Census;

use Fisharebest\Webtrees\Date;

/**
 * Definitions for a census
 */
class CensusOfWales1891 extends CensusOfWales implements CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '05 MAR 1891';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new CensusColumnFullName($this, 'Name', 'Name and surname'),
			new CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new CensusColumnCondition($this, 'Condition', 'Condition'),
			new CensusColumnAgeMale($this, 'AgeM', 'Age (males)'),
			new CensusColumnAgeFemale($this, 'AgeF', 'Age (females)'),
			new CensusColumnOccupation($this, 'Occupation', 'Rank, profession or occupation'),
			new CensusColumnNull($this, 'Empl', 'Employer'),
			new CensusColumnNull($this, 'Empd', 'Employed'),
			new CensusColumnNull($this, 'OAC', 'Own account'),
			new CensusColumnBirthPlace($this, 'Birthplace', 'Where born'),
			new CensusColumnNull($this, 'Infirm', 'Whether deaf-and-dumb, blind, lunatic or imbecile'),
		);
	}
}
