<?php namespace Fisharebest\Localization\Territory;

/**
 * Class AbstractTerritory - Representation of the territory AG - Antigua and Barbuda.
 *
 * @author    Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2015 Greg Roach
 * @license   GPLv3+
 */
class TerritoryAg extends AbstractTerritory implements TerritoryInterface {
	/** {@inheritdoc} */
	public function code() {
		return 'AG';
	}

	/** {@inheritdoc} */
	public function firstDay() {
		return 0;
	}
}