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
class DefaultInputControlRenderer extends DefaultLabeledControlRenderer
{

	/**
	 * Changes element's type.
	 * @param  string
	 * @return DefaultInputControlRenderer  provides a fluent interface
	 */
	public function setType($type)
	{
		$this->element->type = $type;
		return $this;
	}



	/**
	 * Returns element's type.
	 * @return string
	 */
	public function getType()
	{
		return $this->element->type;
	}



	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$element = parent::getControl($caption);

		if ($this->control instanceof Controls\TextInput) {
			$element->type = $this->control->getType();
			$element->data('nette-empty-value', $this->control->getEmptyValue() === '' ? NULL : $this->translate($this->control->getEmptyValue()));
			$element->min = $this->control->minValue;
			$element->max = $this->control->maxValue;
			$element->maxlength = $this->control->maxLength;
			$element->class = 'text'; // TODO
			if ($element->type !== 'password') {
				$element->value = $this->control->getValue() === '' ? $this->translate($this->control->getEmptyValue()) : $this->control->getValue();
			}

		} elseif ($this->control instanceof Controls\UploadControl) {
			$element->type = 'file';
			$element->class = 'text'; // TODO

		} elseif ($this->control instanceof Controls\HiddenField) {
			$element->type = 'hidden';
			$element->data['nette-rules'] = FALSE;
			$element->value = $this->control->forcedValue === NULL ? $this->control->getValue() : $this->control->forcedValue;

		} elseif ($this->control instanceof Controls\Checkbox) {
			$element->type = 'checkbox';
			$element->checked = $this->control->value;
		}

		return $element;
	}

}
