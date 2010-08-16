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
 * Helper that renders buttons.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
*/
class DefaultButtonRenderer extends DefaultControlRenderer
{

	public function __construct(IControl $control, $caption = NULL, $type = NULL, $src = NULL)
	{
		parent::__construct($control, $caption);
		$this->element->type = $type ? $type : 'submit';
		$this->element->src = $src;
	}



	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$element = parent::getControl($caption);
		$attr = $element->type === 'image' ? 'alt' : 'value';
		$element->$attr = $this->translate($caption === NULL ? $this->caption : $caption);
		$element->formnovalidate = !$this->control->getValidationScope();
		$element->class = $element->type === 'image' ? 'imagebutton' : 'button'; // TODO
		return $element;
	}

}
