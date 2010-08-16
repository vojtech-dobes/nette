<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Forms
 */

namespace Nette\Forms;

use Nette,
	Nette\Utils\Html;



/**
 * Helper that implements the basic control rendering.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 *
 * @property-read mixed $label
 * @property-read Nette\Utils\Html $labelPrototype
 * @property   bool $rendered
*/
abstract class DefaultLabeledControlRenderer extends DefaultControlRenderer
{
	/** @var Nette\Utils\Html  label element template */
	private $labelEl;



	/**
	 * @param  string  caption
	 */
	public function __construct(IControl $control, $caption = NULL)
	{
		parent::__construct($control, $caption);
		$this->labelEl = Html::el('label');
	}



	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getLabel($caption = NULL)
	{
		if ($this->control instanceof Controls\HiddenField) {
			return NULL;
		}
		$label = clone $this->labelEl;
		if ($this->getOption('required')) {
			$label->class('required', TRUE); // TODO
		}
		$label->for = $this->control instanceof Controls\RadioList ? NULL : $this->getHtmlId();
		if ($caption !== NULL) {
			$label->setText($this->translate($caption));

		} elseif ($this->caption instanceof Html) {
			$label->add($this->caption);

		} else {
			$label->setText($this->translate($this->caption));
		}
		return $label;
	}



	/**
	 * Returns label's HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getLabelPrototype()
	{
		return $this->labelEl;
	}

}
