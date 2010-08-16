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

use Nette;



/**
 * Helper that implements the basic control rendering.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
*/
class DefaultTextAreaRenderer extends DefaultLabeledControlRenderer
{

	public function __construct(IControl $control, $caption = NULL, $cols = NULL, $rows = NULL)
	{
		parent::__construct($control, $caption);
		$this->element->cols = $cols;
		$this->element->rows = $rows;
	}



	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$element = parent::getControl($caption);
		$element->setName('textarea');
		$element->data('nette-empty-value', $this->control->getEmptyValue() === '' ? NULL : $this->translate($this->control->getEmptyValue()));
		$element->setText($this->control->getValue() === '' ? $this->translate($this->control->getEmptyValue()) : $this->control->getValue());
		return $element;
	}

}
