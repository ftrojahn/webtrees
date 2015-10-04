<?php namespace Fisharebest\Localization\Script;

/**
 * Class ScriptLimb - Representation of the Limbu script.
 *
 * @author    Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2015 Greg Roach
 * @license   GPLv3+
 */
class ScriptLimb extends AbstractScript implements ScriptInterface {
	/** {@inheritdoc} */
	public function code() {
		return 'Limb';
	}

	/** {@inheritdoc} */
	public function numerals() {
		return array('᥆', '᥇', '᥈', '᥉', '᥊', '᥋', '᥌', '᥍', '᥎', '᥏');
	}

	/** {@inheritdoc} */
	public function number() {
		return '336';
	}

	/** {@inheritdoc} */
	public function unicodeName() {
		return 'Limbu';
	}
}
